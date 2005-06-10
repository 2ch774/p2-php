<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    expack - �X���b�h���c���[�\������
    �c���[�\���ȊO�̃��[�`����read.php����q��
*/

require_once 'conf/conf.php';
require_once (P2_LIBRARY_DIR . '/thread.class.php');    //�X���b�h�N���X�Ǎ�
require_once (P2_LIBRARY_DIR . '/threadread.class.php');    //�X���b�h���[�h�N���X�Ǎ�
require_once (P2_LIBRARY_DIR . '/filectl.class.php');
require_once (P2_LIBRARY_DIR . '/ngabornctl.class.php');
require_once (P2_LIBRARY_DIR . '/showthread.class.php');    //HTML�\���N���X
require_once (P2_LIBRARY_DIR . '/showthreadpc.class.php');  //HTML�\���N���X
require_once (P2_LIBRARY_DIR . '/showthreadtree.class.php'); // �c���[�\���N���X

authorize(); // ���[�U�F��

/*if (P2Util::isBrowserSafariGroup()) {
    $_conf['meta_charset_ht'] = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
    mb_http_output('UTF-8');
    ob_start('mb_output_handler');
}*/

//================================================================
// �ϐ�
//================================================================

$newtime = date('gis'); // ���������N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[
//$_today = date('y/m/d');

$_info_msg_ht = '';

if (empty($_GET['host']) || empty($_GET['bbs']) || empty($_GET['key'])) {
    die("p2 - read_tree.php: �X���b�h�̎w�肪�ςł��B");
}

$host = $_GET['host'];
$bbs  = $_GET['bbs'];
$key  = $_GET['key'];


//=================================================
// ���X�t�B���^
//=================================================
$_conf['filtering'] = false;

if (isset($_REQUEST['word']))   { $word = $_REQUEST['word']; }
if (isset($_REQUEST['field']))  { $res_filter['field']  = $field = $_REQUEST['field']; }
if (isset($_REQUEST['match']))  { $res_filter['match']  = $_REQUEST['match']; }
if (isset($_REQUEST['method'])) { $res_filter['method'] = $_REQUEST['method']; }

if (isset($word) && strlen($word) > 0 &&
    !((!$_exconf['flex']['*'] || $res_filter['method'] == 'regex') && preg_match('/^\.+$/', $word))
) {

    // �f�t�H���g�I�v�V����
    if (empty($res_filter['field']))  { $res_filter['field']  = 'hole'; }
    if (empty($res_filter['match']))  { $res_filter['match']  = 'on'; }
    if (empty($res_filter['method'])) { $res_filter['method'] = 'or'; }

    include_once (P2_LIBRARY_DIR . '/strctl.class.php');
    $word_fm = StrCtl::wordForMatch($word, $res_filter['method']);
    if (!preg_match('/[^. ]/', $word)) {
        $word = null;
    } else {
        $word = htmlspecialchars($word);
    }
    $_conf['filtering'] = true;
    if ($res_filter['method'] != 'just') {
        if (P2_MBREGEX_AVAILABLE == 1) {
            $words_fm = mb_split('\s+', $word_fm);
            $word_fm = mb_ereg_replace('\s+', '|', $word_fm);
        } else {
            $words_fm = preg_split('/\s+/u', $word_fm);
            $word_fm = preg_replace('/\s+/u', '|', $word_fm);
        }
    }
    if ($_conf['ktai']) {
        $page = (isset($_REQUEST['page'])) ? max(1, intval($_REQUEST['page'])) : 1;
        $filter_range = array();
        $filter_range['start'] = ($page - 1) * $_conf['k_rnum_range'] + 1;
        $filter_range['to'] = $filter_range['start'] + $_conf['k_rnum_range'] - 1;
    }
    $last_hit_resnum = 1;
} else {
    $word = null;
}


//=================================================
// �t�B���^�l�ۑ�
//=================================================
$cachefile = $_conf['pref_dir'] . '/p2_res_filter.txt';

// �t�B���^�w�肪�Ȃ���ΑO��ۑ���ǂݍ��ށi�t�H�[���̃f�t�H���g�l�ŗ��p�j
if (!isset($word) || strlen($word) == 0) {

    if (file_exists($cachefile) && ($res_filter_cont = file_get_contents($cachefile))) {
        $res_filter = unserialize($res_filter_cont);
    }

// �t�B���^�w�肪�����
} else {

    // �{�^����������Ă����Ȃ�A�t�@�C���ɐݒ��ۑ�
    if (isset($_REQUEST['submit_filter'])) {    // !isset($_REQUEST['idpopup'])
        FileCtl::make_datafile($cachefile, $_conf['p2_perm']); // �t�@�C�����Ȃ���ΐ���
        if ($res_filter) {
            $res_filter_cont = serialize($res_filter);
        }
        if ($res_filter_cont) {
            $fp = @fopen($cachefile, 'wb') or die("Error: $cachefile ���X�V�ł��܂���ł���");
            @flock($fp, LOCK_EX);
            fputs($fp, $res_filter_cont);
            @flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

}


//==================================================================
// �����C��
//==================================================================
$aThread = &new ThreadRead;


//==========================================================
// idx�̓ǂݍ���
//==========================================================

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
if (!isset($aThread->keyidx)) {
    $aThread->setThreadPathInfo($host, $bbs, $key);
}

// �f�B���N�g����������΍��
//FileCtl::mkdir_for($aThread->keyidx);

$aThread->itaj = P2Util::getItaName($host, $bbs);
if (!$aThread->itaj) {
    $aThread->itaj = $aThread->bbs;
}

// idx�t�@�C��������Γǂݍ���
if (is_readable($aThread->keyidx)) {
    $lines = @file($aThread->keyidx);
    $l = rtrim($lines[0]);
    $data = explode('<>', $l);
} else {
    $data = array_fill(0, 10, '');
}
$aThread->getThreadInfoFromIdx();


//===========================================================
// DAT�̃_�E�����[�h
//===========================================================
if (empty($_GET['offline'])) {
    if (!(isset($word) && strlen($word) > 0 && file_exists($aThread->keydat))) {
        $aThread->downloadDat();
    }
}

// ��DAT��ǂݍ���
$aThread->readDat();

// �I�t���C���w��ł����O���Ȃ���΁A���߂ċ����ǂݍ���
if (empty($aThread->datlines) && !empty($_GET['offline'])) {
    $aThread->downloadDat();
    $aThread->readDat();
}


$aThread->setTitleFromLocal(); // �^�C�g�����擾���Đݒ�


//===========================================================
// �\�����X�Ԃ͈̔͂�ݒ�
//===========================================================
$aThread->ls = 'all';
$aThread->lsToPoint();


//===============================================================
// ���v�����g
//===============================================================
$ptitle_ht = htmlspecialchars($aThread->itaj).' / '.$aThread->ttitle_hd;

//===========================================================
// ���[�J��Dat��ϊ�����HTML�\��
//===========================================================
if ($aThread->rescount) {

    // ���w�b�_ �\��
    ob_start();
    include (P2_LIBRARY_DIR . '/read_header.inc.php');
    $header = ob_get_clean();

    $form_fmt = '<form id="header" method="GET" action="%s"';
    $form_search = sprintf($form_fmt, $_conf['read_php']);
    $form_replace = sprintf($form_fmt, $_SERVER['PHP_SELF']);

    echo str_replace($form_search, $form_replace, $header);
    flush();


    // ���X������A�����w�肪�����
    if (isset($word) && strlen($word) > 0) {
        $all = $aThread->rescount;
        $GLOBALS['filter_hits'] = 0;

        $hits_line = "<p><b id=\"filerstart\">{$all}���X�� <span id=\"searching\">{$GLOBALS['filter_hits']}</span>���X���q�b�g</b></p>";
        echo <<<EOP
<script type="text/javascript">
<!--
document.writeln('{$hits_line}');
var searching = document.getElementById('searching');

function filterCount(n){
    if (searching) {
        searching.innerHTML = n;
    }
}
-->
</script>
EOP;
    }


    // ���c���[ �\��
    $aShowThread = &new ShowThreadTree($aThread);

    // async
    $aShowThread->printASyncObjJs();
    // SPM
    if ($_exconf['spm']['*']) {
        $aShowThread->printSPMObjJs();
    }

    $res1 = $aShowThread->quoteOne(); // >>1�|�b�v�A�b�v�p
    echo $res1['q'];

    $aShowThread->datToTree();


    // �t�B���^���ʂ�\��
    if (isset($word) && strlen($word) > 0) {
        echo <<<EOP
<script type="text/javascript">
<!--
var filerstart = document.getElementById('filerstart');
if (filerstart) {
    filerstart.style.backgroundColor = 'yellow';
    filerstart.style.fontWeight = 'bold';\n
EOP;
        if (isset($GLOBALS['MYSTYLE']['base']['.filtering'])) {
            // set my-filter-style
            foreach ($GLOBALS['MYSTYLE']['base']['.filtering'] as $_mfs_prop => $_mfs_value) {
                $_mfs_prop = strtolower($_mfs_prop);
                $_mfs_value = addslashes($_mfs_value);
                if (strstr('-', $_mfs_prop)) {
                    $_prop_parts = explode('-', $_mfs_prop);
                    $_mfs_prop = array_shift($_prop_parts);
                    $_mfs_prop .= implode('', array_map('ucfirst', $_prop_parts));
                }
                echo "\tfilerstart.style.{$_mfs_prop} = '{$_mfs_value}';\n";
            }
        }
        echo <<<EOP
}
-->
</script>\n
EOP;
        if ($GLOBALS['filter_hits'] > 5) {
            echo "<p><b class=\"filtering\">{$all}���X�� {$GLOBALS['filter_hits']}���X���q�b�g</b></p>\n";
        }
    }


    // ���t�b�^ �\��
    include (P2_LIBRARY_DIR . '/read_footer.inc.php');
}

?>
