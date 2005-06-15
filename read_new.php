<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 - �X���b�h�\���X�N���v�g - �V���܂Ƃߓǂ�
    �t���[��������ʁA�E������
*/

//�v���Z�X�J�n���ԋL�^
$CPU_start=microtime();

require_once 'conf/conf.php'; // �ݒ�
require_once (P2_LIBRARY_DIR . '/threadlist.class.php');    // �X���b�h���X�g �N���X
require_once (P2_LIBRARY_DIR . '/thread.class.php');    //�X���b�h �N���X
require_once (P2_LIBRARY_DIR . '/threadread.class.php');    //�X���b�h���[�h �N���X
require_once (P2_LIBRARY_DIR . '/showthread.class.php');    // HTML�\���N���X
require_once (P2_LIBRARY_DIR . '/showthreadpc.class.php');  // HTML�\���N���X
require_once (P2_LIBRARY_DIR . '/filectl.class.php');
require_once (P2_LIBRARY_DIR . '/ngabornctl.class.php');

require_once (P2_LIBRARY_DIR . '/read_new.inc.php');

authorize(); // ���[�U�F��

// �܂Ƃ߂�݂̃L���b�V���ǂ�
if (!empty($_GET['cview'])) {
    $cnum = (isset($_GET['cnum'])) ? intval($_GET['cnum']) : NULL;
    if ($cont = getMatomeCache($cnum)) {
        echo $cont;
    } else {
        echo 'p2 error: �V���܂Ƃߓǂ݂̃L���b�V�����Ȃ���';
    }
    exit;
}

//==================================================================
// ���ϐ�
//==================================================================
if (isset($_conf['rnum_all_range']) && $_conf['rnum_all_range'] > 0) {
    $rnum_all_range = $_conf['rnum_all_range'];
}

$sb_view = 'shinchaku';
$newtime = date('gis');

//=================================================
// �̎w��
//=================================================

$host    = isset($_REQUEST['host'])   ? $_REQUEST['host']   : NULL;
$bbs     = isset($_REQUEST['bbs'])    ? $_REQUEST['bbs']    : NULL;
$spmode  = isset($_REQUEST['spmode']) ? $_REQUEST['spmode'] : NULL;
$onlyfav = (empty($spmode) && isset($_REQUEST['onlyfav'])) ? $_REQUEST['onlyfav'] : NULL;
$refresh = empty($_REQUEST['norefresh']);

if ((!isset($host) || !isset($bbs)) && !isset($spmode)) {
    die('p2 error: �K�v�Ȉ������w�肳��Ă��܂���');
}

//=================================================
// ���ځ[��&NG���[�h�ݒ�ǂݍ���
//=================================================
$GLOBALS['ngaborns'] = NgAbornCtl::loadNgAborns();

//====================================================================
// �����C��
//====================================================================

register_shutdown_function('saveMatomeCache');

$read_new_html = '';
ob_start();

$aThreadList = &new ThreadList;

// ���ƃ��[�h�̃Z�b�g ===================================
$ta_keys = array();
if ($spmode) {
    if ($spmode == 'taborn' or $spmode == 'soko') {
        $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
    }
    $aThreadList->setSpMode($spmode);
} else {
    $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
    $datdir_host = P2Util::datdirOfHost($host);

    // subject.txt ���X�V
    $subject_url = "http://{$host}/{$bbs}/subject.txt";
    $subjectfile = "{$datdir_host}/{$bbs}/subject.txt";
    FileCtl::mkdir_for($subjectfile); //�f�B���N�g����������΍��
    if ($refresh || !file_exists($subjectfile)) {
        P2Util::subjectDownload($subject_url, $subjectfile);
    }

    // ���X���b�h���ځ[�񃊃X�g�Ǎ�
    $tabornidx = $datdir_host."/".$bbs."/p2_threads_aborn.idx";
    if (file_exists($tabornidx) && ($tabornlines = file($tabornidx))) {
        $ta_num = sizeOf($tabornlines);
        foreach ($tabornlines as $l) {
            $l = rtrim($l);
            $tarray = explode('<>', $l);
            $ta_keys[ $tarray[1] ] = true;
        }
    }
}

// ���\�[�X���X�g�Ǎ� ===================================
$lines = $aThreadList->readList();

// �����C�ɃX�����X�g�Ǎ� ===============================
if ($onlyfav) {
    $favlines = @file($_conf['favlist_file']);
    if ($favlines) {
        foreach ($favlines as $l) {
            $l = rtrim($l);
            $data = explode('<>', $l);
            $fav_keys[ $data[1] ] = true;
        }
    }
}

// ���y�[�W�w�b�_�\�� ===================================
$ptitle_hd = htmlspecialchars($aThreadList->ptitle);
$ptitle_ht = "{$ptitle_hd} �� �V���܂Ƃߓǂ�";

if ($aThreadList->spmode) {
    $sb_ht =<<<EOP
        <a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}" target="subject">{$ptitle_hd}</a>
EOP;
} else {
    $sb_ht =<<<EOP
        <a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}" target="subject">{$ptitle_hd}</a>
EOP;
}

//include (P2_LIBRARY_DIR . '/read_header.inc.php');

P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOHEADER
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle_ht}</title>
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=read&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=mona&amp;skin={$skin_en}" type="text/css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="js/basic.js"></script>
    <script type="text/javascript" src="js/respopup.js"></script>
    <script type="text/javascript" src="js/htmlpopup.js"></script>
    <script type="text/javascript" src="js/strutil.js"></script>
    <script type="text/javascript" src="js/invite.js"></script>
    <script type="text/javascript" src="js/loadthumb.js"></script>
    <script type="text/javascript">
        var read_new = 1;
    </script>
    <script type="text/javascript">
    <!--
    gIsPageLoaded = false;
    // ���C�ɃZ�b�g�֐�
    function setFav(host, bbs, key, favdo, obj)
    {
        /*
        // �y�[�W�̓ǂݍ��݂��������Ă��Ȃ���΁A�Ȃɂ����Ȃ�
        if (!gIsPageLoaded) {
            return false;
        }
        */

        var objHTTP = getXmlHttp();
        if (!objHTTP) {
            // alert("Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B") ;
            // XMLHTTP�i�� obj.parentNode.innerHTML�j �ɖ��Ή��Ȃ珬����
            return OpenSubWin('info.php?host='+host+'&amp;bbs='+bbs+'&amp;key='+key+'&amp;setfav='+favdo+'&amp;popup=2',{$STYLE['info_pop_size']},0,0);
        }
        // �L���b�V�����p
        var now = new Date();
        // �����̕������ encodeURIComponent �ŃG�X�P�[�v����̂��悢
        query = 'host='+host+'&bbs='+bbs+'&key='+key+'&setfav='+favdo+'&nc='+now.getTime();
        url = 'httpcmd.php?' + query + '&cmd=setfav';   // �X�N���v�g�ƁA�R�}���h�w��
        objHTTP.open('GET', url, false);
        objHTTP.send(null);
        if (objHTTP.status != 200 || objHTTP.readyState != 4 && !objHTTP.responseText) {
            // alert("Error: XMLHTTP ���ʂ̎�M�Ɏ��s���܂���") ;
        }
        var res = objHTTP.responseText;
        var rmsg = "";
        if (res) {
            if (res == '1') {
                rmsg = '����';
            }
            if (rmsg) {
                if (favdo == '1') {
                    nextset = '0';
                    favmark = '��';
                    favtitle = '���C�ɃX������O��';
                } else {
                    nextset = '1';
                    favmark = '+';
                    favtitle = '���C�ɃX���ɒǉ�';
                }
                var favhtm = '<a href="info.php?host='+host+'&amp;bbs='+bbs+'&amp;key='+key+'&amp;setfav='+nextset+'" target="info" onClick="return setFav(\''+host+'\', \''+bbs+'\', \''+key+'\', \''+nextset+'\', this);" title="'+favtitle+'">���C��'+favmark+'</a>';
                obj.parentNode.innerHTML = favhtm;
            }
        }
        return false;
    }

    // ���O�폜�֐�
    function deleLog(query, obj)
    {
        /*
        // �y�[�W�̓ǂݍ��݊������Ă��Ȃ���΃����N��
        if (!gIsPageLoaded) {
            return true;
        }
        */

        var objHTTP = getXmlHttp();

        if (!objHTTP) {
            // alert("Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B") ;

            // XMLHTTP�i�� obj.parentNode.innerHTML�j �ɖ��Ή��Ȃ�ʏ탊���N�� // [better]�����̕����x�^�[
            return true;
        }

        // �L���b�V�����p
        var now = new Date();
        // �����̕������ encodeURIComponent �ŃG�X�P�[�v����̂��悢
        query = query + '&nc='+now.getTime();
        url = 'httpcmd.php?' + query + '&cmd=delelog';  // �X�N���v�g�ƁA�R�}���h�w��
        objHTTP.open('GET', url, false);
        objHTTP.send(null);
        if (objHTTP.status != 200 || objHTTP.readyState != 4 && !objHTTP.responseText) {
            // alert("Error: XMLHTTP ���ʂ̎�M�Ɏ��s���܂���") ;
        }
        var res = objHTTP.responseText;
        var rmsg = "";

        if (res) {
            // alert(res);
            if (res == '1') {
                rmsg = '����';
            } else if (res == '2') {
                rmsg = '�Ȃ�';
            }
            if (rmsg) {
                obj.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.style.filter = 'Gray()';  // IE ActiveX�p
                obj.parentNode.innerHTML = rmsg;
            }
        }

        return false;
    }

    function pageLoaded()
    {
        gIsPageLoaded = true;
        setWinTitle();
    }
    //-->
    </script>\n
EOHEADER;
if ($_exconf['aMona']['*'] || $_exconf['editor']['with_aMona'] || $_exconf['spm']['with_aMona']) {
    $am_aafont = str_replace(",", "','", $_exconf['aMona']['aafont']);
    $am_normalfont = str_replace('","', ",", $STYLE['fontfamily']);
    echo <<<EOJS
    <script type="text/javascript" src="js/asciiart.js"></script>
    <script type="text/javascript">
    <!--
        var am_aa_fontFamily = "{$am_aafont}";
        var am_fontFamily = "{$am_normalfont}";
        var am_read_fontSize = "{$STYLE['read_fontsize']}";
        var am_respop_fontSize = "{$STYLE['respop_fontsize']}";
    // -->
    </script>\n
EOJS;
}
if ($_exconf['etc']['async_respop']) {
    echo "\t<script type=\"text/javascript\" src=\"js/async.js\"></script>\n";
}
if ($_exconf['spm']['*']) {
    echo "\t<script type=\"text/javascript\" src=\"js/smartpopup.js\"></script>\n";
}
echo <<<EOP
</head>
<body onload="pageLoaded();">
<div id="popUpContainer"></div>\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht = '';

//==============================================================
// �����ꂼ��̍s���
//==============================================================

$linesize = sizeof($lines);
$subject_txts = array();
$online_num = 0;
$newthre_num = 0;
$checked_bbs = array();

for ($x = 0; $x < $linesize; $x++) {

    if (isset($rnum_all_range) && $rnum_all_range <= 0) {
        break;
    }

    if (is_string($lines[$x])) {
        $l = rtrim($lines[$x]);
    } elseif (is_array($lines[$x])) {
        $l = $lines[$x];
    } else {
        continue;
    }

    $aThread = &new ThreadRead;

    $aThread->torder = $x + 1;

    // ���f�[�^�ǂݍ���
    // spmode�Ȃ�
    if ($aThreadList->spmode) {
        switch ($aThreadList->spmode) {
        case 'recent':  // ����
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case 'res_hist':    // �������ݗ���
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case 'fav': // ���C��
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case 'taborn':  // �X���b�h���ځ[��
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case 'palace':  // �X���̓a��
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case 'news':    // �j���[�X�`�F�b�N
            $aThread->isonline = true;
            $aThread->key = $l['key'];
            $aThread->ttitle = $l['ttitle'];
            $aThread->rescount = $l['rescount'];
            $aThread->host = $l['host'];
            $aThread->bbs = $l['bbs'];
            $aThread->itaj = $l['itaj'];
            if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }
            break;
        }
    // subject (not spmode)�̏ꍇ
    } else {
        $aThread->getThreadInfoFromSubjectTxtLine($l);
        $aThread->host = $aThreadList->host;
        $aThread->bbs = $aThreadList->bbs;
        // �ʂ��C�ɃX���V���܂Ƃߓǂ�
        if ($onlyfav && !isset($fav_keys[$aThread->key])) {
            unset($aThread);
            continue;
        }
    }

    // host��bbs���s���Ȃ�X�L�b�v
    if (!($aThread->host && $aThread->bbs)) {
        unset($aThread);
        continue;
    }

    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);

    // �����X���b�h�f�[�^��idx����擾
    $aThread->getThreadInfoFromIdx();

    // ���V���̂�(for subject) =========================================
    if (!$aThreadList->spmode && $sb_view == 'shinchaku') {
        if ($aThread->unum < 1) {
            unset($aThread);
            continue;
        }
    }

    // ���X���b�h���ځ[��`�F�b�N =====================================
    if ($aThreadList->spmode != 'taborn' && isset($ta_keys[$aThread->key])) {
        unset($ta_keys[$aThread->key]);
        continue; //���ځ[��X���̓X�L�b�v
    }

    // ��spmode(�a�����������)�Ȃ�  ====================================
    if ($aThreadList->spmode && $sb_view != 'edit') {

        // subject.txt ����DL�Ȃ痎�Ƃ��ăf�[�^��z��Ɋi�[
        if (empty($subject_txts["$aThread->host/$aThread->bbs"])) {
            $datdir_host = P2Util::datdirOfHost($aThread->host);
            $subject_url = "http://{$aThread->host}/{$aThread->bbs}/subject.txt";

            $subjectfile = "{$datdir_host}/{$aThread->bbs}/subject.txt";

            FileCtl::mkdir_for($subjectfile); // �f�B���N�g����������΍��
            if ($refresh || !file_exists($subjectfile)) {
                P2Util::subjectDownload($subject_url, $subjectfile);
            }
            if (extension_loaded('zlib') and strstr($aThread->host, ".2ch.net")) {
                $subject_txts["$aThread->host/$aThread->bbs"] = @gzfile($subjectfile);
            } else {
                $subject_txts["$aThread->host/$aThread->bbs"] = @file($subjectfile);
            }

        }

        // ���X�����擾 =============================
        if ($subject_txts["$aThread->host/$aThread->bbs"]) {
            foreach ($subject_txts["$aThread->host/$aThread->bbs"] as $l) {
                if (@preg_match("/^{$aThread->key}/", $l)) {
                    $aThread->getThreadInfoFromSubjectTxtLine($l); // subject.txt ����X�����擾
                    break;
                }
            }
        }

        // �V���̂�(for spmode) ===============================
        if ($sb_view == 'shinchaku' && empty($_GET['word'])) {
            if ($aThread->unum < 1) {
                unset($aThread);
                continue;
            }
        }
    }

    if ($aThread->isonline) { $online_num++; }  // ������set

    echo $_info_msg_ht;
    $_info_msg_ht = '';

    $read_new_html .= ob_get_flush();
    ob_start();

    if (($aThread->readnum < 1) || $aThread->unum) {
        readNew($aThread);
    } elseif ($aThread->diedat) {
        echo $aThread->getdat_error_msg_ht;
        echo "<hr>\n";
    }

    $read_new_html .= ob_get_flush();
    ob_start();

    // ���X�g�ɒǉ� ========================================
    //$aThreadList->addThread($aThread);
    $aThreadList->num++;
    unset($aThread);
}

//==================================================================
//$aThread = &new ThreadRead;

//======================================================================
// �� �X���b�h�̐V��������ǂݍ���ŕ\������
//======================================================================
function readNew(&$aThread)
{
    global $_conf, $_exconf, $newthre_num, $STYLE;
    global $_info_msg_ht;

    $newthre_num++;

    //==========================================================
    // �� idx�̓ǂݍ���
    //==========================================================

    //�@host�𕪉�����idx�t�@�C���̃p�X�����߂�
    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);

    //FileCtl::mkdir_for($aThread->keyidx); //�f�B���N�g����������΍�� //���̑���͂����炭�s�v

    $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
    if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }

    // idx�t�@�C��������Γǂݍ���
    if (is_readable($aThread->keyidx)) {
        $lines = @file($aThread->keyidx);
        $l = rtrim($lines[0]);
        $data = explode('<>', $l);
    }
    $aThread->getThreadInfoFromIdx();

    //==================================================================
    // ��DAT�̃_�E�����[�h
    //==================================================================
    $aThread->downloadDat();

    // DAT��ǂݍ���
    $aThread->readDat();
    $aThread->setTitleFromLocal(); // ���[�J������^�C�g�����擾���Đݒ�

    //===========================================================
    // ���\�����X�Ԃ͈̔͂�ݒ�
    //===========================================================
    if ($aThread->isKitoku()) { // �擾�ς݂Ȃ�
        $from_num = $aThread->readnum + 1 - $_conf['respointer'] - $_conf['before_respointer_new'];
        if ($from_num < 1) {
            $from_num = 1;
        } elseif ($from_num > $aThread->rescount) {
            $from_num = $aThread->rescount - $_conf['respointer'] - $_conf['before_respointer_new'];
        }

        //if (!$aThread->ls) {
            $aThread->ls = "$from_num-";
        //}
    }

    $aThread->lsToPoint();

    //==================================================================
    // ���w�b�_ �\��
    //==================================================================
    $motothre_url = $aThread->getMotoThread();
    if ($_conf['motothre_ime']) {
        $motothre_url_ime = P2Util::throughIme($motothre_url, TRUE);
    } else {
        $motothre_url_ime = htmlspecialchars($motothre_url);
    }

    $ttitle_en = base64_encode($aThread->ttitle);
    $ttitle_urlen = rawurlencode($ttitle_en);
    $ttitle_en_q = '&amp;ttitle_en='.$ttitle_urlen;
    $bbs_q = '&amp;bbs='.$aThread->bbs;
    $key_q = '&amp;key='.$aThread->key;
    $popup_q = '&amp;popup=1';

    //include (P2_LIBRARY_DIR . '/read_header.inc.php');

    $prev_thre_num = $newthre_num - 1;
    $next_thre_num = $newthre_num + 1;
    if ($prev_thre_num != 0) {
        $prev_thre_ht = "<a href=\"#ntt{$prev_thre_num}\">��</a>";
    } else {
        $prev_thre_ht = '';
    }
    $next_thre_ht = "<a href=\"#ntt{$next_thre_num}\">��</a>";

    echo $_info_msg_ht;
    $_info_msg_ht = '';

    $invite_js = sprintf("Invite('%s','%s','','','','')",
        htmlspecialchars($aThread->ttitle, ENT_QUOTES),
        htmlspecialchars($aThread->getMotoThread(), ENT_QUOTES)
    );

    // ���w�b�_����HTML  
    $read_header_ht = <<<EOP
    <table id="ntt{$newthre_num}" width="100%" style="padding:0px 10px 0px 0px;">
        <tr>
            <td align="left">
                <h3 class="thread_title" onclick="{$invite_js}">{$aThread->ttitle_hd}</h3>
            </td>
            <td align="right">
                {$prev_thre_ht}
                {$next_thre_ht}
            </td>
        </tr>
    </table>\n
EOP;

    //==================================================================
    // �����[�J��Dat��ǂݍ����HTML�\��
    //==================================================================
    $aThread->resrange['nofirst'] = true;
    $GLOBALS['newres_to_show_flag'] = false;
    if ($aThread->rescount) {
        //$aThread->datToHtml(); // dat �� html �ɕϊ��\��
        $aShowThread = &new ShowThreadPc($aThread);
        // async
        if ($_exconf['etc']['async_respop']) {
            $read_header_ht .= $aShowThread->printASyncObjJs();
        }
        // SPM
        if ($_exconf['spm']['*']) {
            $read_header_ht .= $aShowThread->printSPMObjJs();
        }

        $res1 = $aShowThread->quoteOne();
        $read_cont_ht = $res1['q'];

        $read_cont_ht .= $aShowThread->getDatToHtml();

        unset($aShowThread);
    }

    //==================================================================
    // ���t�b�^ �\��
    //==================================================================
    //include (P2_LIBRARY_DIR . '/read_footer.inc.php');

    //----------------------------------------------
    // $read_footer_navi_new  ������ǂ� �V�����X�̕\��
    $newtime = date('gis'); // �����N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[

    $info_st = '���';
    $delete_st = '�폜';
    $prev_st = '�O';
    $next_st = '��';

    $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-&amp;nt=$newtime#r{$aThread->rescount}\">�V�����X�̕\��</a>";

    $dores_ht = <<<EOP
        <a href="post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}" target='_self' onclick="return OpenSubWin('post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}{$popup_q}&amp;from_read_new=1',{$STYLE['post_pop_size']},0,0)">���X</a>
EOP;

    // ���c�[���o�[����HTML =======

    // ���C�Ƀ}�[�N�ݒ�
    $favmark = (!empty($aThread->fav)) ? '��' : '+';
    $favdo = (!empty($aThread->fav)) ? 0 : 1;
    $favtitle = $favdo ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
    $favdo_q = '&amp;setfav='.$favdo;
    $itaj_hd = htmlspecialchars($aThread->itaj);

    $toolbar_right_ht = <<<EOTOOLBAR
            <a href="{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$key_q}" target="subject" title="���J��">{$itaj_hd}</a>
            <a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}" target="info" onClick="return OpenSubWin('info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$popup_q}',{$STYLE['info_pop_size']},0,0)" title="�X���b�h����\��">{$info_st}</a> 
            <span class="favdo"><a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$favdo_q}{$sid_q}" target="info" onClick="return setFav('{$aThread->host}', '{$aThread->bbs}', '{$aThread->key}', '{$favdo}', this);" title="{$favtitle}">���C��{$favmark}</a></span> 
            <span><a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;dele=true" target="info" onClick="return deleLog('host={$aThread->host}{$bbs_q}{$key_q}', this);" title="���O���폜����">{$delete_st}</a></span> 
<!--            <a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;taborn=2" target="info" onclick="return OpenSubWin('info.php?host={$aThread->host}{$bbs_q}&amp;key={$aThread->key}{$ttitle_en_q}&amp;popup=2&amp;taborn=2',{$STYLE['info_pop_size']},0,0)" title="�X���b�h�̂��ځ[���Ԃ��g�O������">���ڂ�</a> -->
            <a href="{$motothre_url_ime}" title="�T�[�o��̃I���W�i���X����\��">���X��</a>
EOTOOLBAR;

    if($_exconf['status']['datsize'] ){
	// ���ݓǂ�ł���X����.dat�e�ʂ��擾����
	require_once(P2EX_LIBRARY_DIR . '/status/datsize.inc.php');
	$thread_size = getthread_dir( $aThread->host, $aThread->bbs, $aThread->key) ." KB";
	$thread_size_ht = <<<EOT
        <div align="right">dat : {$thread_size}</div>\n
EOT;
    }

    // ���X�̂��΂₳
    $spd_ht = '';
    if ($spd_st = $aThread->getTimePerRes() and $spd_st != "-") {
        $spd_ht = '<span class="spd" title="���΂₳������/���X">'."" . $spd_st."".'</span>';
    }

    // ���t�b�^����HTML
    $read_footer_ht = <<<EOP
        <table width="100%" style="padding:0px 10px 0px 0px;">
            <tr>
                <td align="left">
                    {$res1['body']} | <a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;offline=1&amp;rc={$aThread->rescount}#r{$aThread->rescount}">{$aThread->ttitle_hd}</a> | {$dores_ht} {$spd_ht}
                </td>
                <td align="right">
                    {$toolbar_right_ht}
                </td>
                <td align="right">
                    <a href="#ntt{$newthre_num}">��</a>
                </td>
            </tr>
        </table>
{$thread_size_ht}
EOP;

    // �������ځ[��ŕ\�����Ȃ��ꍇ�̓X�L�b�v
    if ($GLOBALS['newres_to_show_flag']) {
        echo '<div style="width:100%;">'."\n";  // �ق�IE ActiveX��Gray()�̂��߂����Ɉ͂��Ă���
        echo $read_header_ht;
        echo $read_cont_ht;
        echo $read_footer_ht;
        echo '</div>'."\n\n";
        echo '<hr>'."\n\n";
    }

    // �e���r�ԑg����2ch�Ȃǂ̓��O�Eidx�E������ۑ����Ȃ�
    if (P2Util::isHostNoCacheData($aThread->host)) {
        //@unlink($aThread->keydat); // ThreadRead::readDat()�ō폜����
        exit;
    }

    //==================================================================
    // ��key.idx�̒l�ݒ�
    //==================================================================

    if ($aThread->rescount) {
        $aThread->readnum = min($aThread->rescount, max(0, $data[5], $aThread->resrange['to']));

        $newline = $aThread->readnum + 1;   // $newline�͔p�~�\�肾���A���݊��p�ɔO�̂���

        $sar = array($aThread->ttitle, $aThread->key, $data[2], $aThread->rescount,
                     $aThread->modified, $aThread->readnum, $data[6], $data[7], $data[8], $newline);
        $s = implode('<>', $sar);
        P2Util::recKeyIdx($aThread->keyidx, $s); // key.idx�ɋL�^
    }

    unset($aThread);
}

//==================================================================
// ���y�[�W�t�b�^�\��
//==================================================================
$newthre_num++;

if (!$aThreadList->num) {
    $GLOBALS['matome_naipo'] = TRUE;
    echo '�V�����X�͂Ȃ���';
    echo '<hr>';
}

if( $_exconf['status']['processtime'] ){
	// �v���Z�X�^�C�������܂łɗv�������Ԃ��擾����
	require_once(P2EX_LIBRARY_DIR . '/status/process_time.inc.php');
	$process_time = getprocess_time( $CPU_start ) ." sec";
	echo <<<EOP

<div align="right">
	CPU : {$process_time}
</div>\n
EOP;
}

if ($onlyfav) {
    $onlyfav_ht = '���C�ɃX���� ';
    $onlyfav_a = '&amp;onlyfav=1';
} else {
    $onlyfav_ht = $onlyfav_a = '';
}

if (!isset($rnum_all_range) || $rnum_all_range > 0) {
    echo <<<EOP
    <div id="ntt{$newthre_num}" align="center">
        $sb_ht �� {$onlyfav_ht}<a href="{$_conf['read_new_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}&amp;nt={$newtime}{$onlyfav_a}">�V���܂Ƃߓǂ݂��X�V</a>
    </div>\n
EOP;
} else {
    echo <<<EOP
    <div id="ntt{$_newthre_num}" align="center">
        {$sb_ht} �� {$onlyfav_ht}<a href="{$_conf['read_new_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}&amp;nt={$newtime}&amp;norefresh=1{$onlyfav_a}">�V���܂Ƃߓǂ݂̑���</a>
    </div>\n
EOP;
}

echo '</body></html>';

$read_new_html .= ob_get_flush();

// ��NG���ځ[����L�^
NgAbornCtl::saveNgAborns();

?>
