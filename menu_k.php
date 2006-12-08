<?php
/*
    p2 -  ���j���[ �g�їp
*/

include_once './conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/brdctl.class.php';
require_once P2_LIBRARY_DIR . '/showbrdmenuk.class.php';

$_login->authorize(); // ���[�U�F��

//==============================================================
// �ϐ��ݒ�
//==============================================================
$_conf['ktai'] = 1;
$brd_menus = array();
$GLOBALS['menu_show_ita_num'] = 0;

// {{{ �����̂��߂̐ݒ�

if (isset($_GET['word'])) {
    $word = $_GET['word'];
} elseif (isset($_POST['word'])) {
    $word = $_POST['word'];
}

if (isset($word) && strlen($word) > 0) {

    if (preg_match('/^\.+$/', $word)) {
        $word = '';
    }

    // and����
    include_once P2_LIBRARY_DIR . '/strctl.class.php';
    $word_fm = StrCtl::wordForMatch($word, 'and');
    if (P2_MBREGEX_AVAILABLE == 1) {
        $GLOBALS['words_fm'] = @mb_split('\s+', $word_fm);
        $GLOBALS['word_fm'] = @mb_ereg_replace('\s+', '|', $word_fm);
    } else {
        $GLOBALS['words_fm'] = @preg_split('/\s+/', $word_fm);
        $GLOBALS['word_fm'] = @preg_replace('/\s+/', '|', $word_fm);
    }
}

// }}}

//============================================================
// ����ȑO�u����
//============================================================
// ���C�ɔ̒ǉ��E�폜
if (isset($_GET['setfavita'])) {
    include_once P2_LIBRARY_DIR . '/setfavita.inc.php';
    setFavIta();
}

//================================================================
// ���C��
//================================================================
$aShowBrdMenuK =& new ShowBrdMenuK();

//============================================================
// �w�b�_
//============================================================
if ($_GET['view'] == "favita") {
    $ptitle = "���C�ɔ�";
} elseif ($_GET['view'] == "rss") {
    $ptitle = "RSS";
} elseif ($_GET['view'] == "cate"){
    $ptitle = "��ؽ�";
} elseif (isset($_GET['cateid'])){
    $ptitle = "��ؽ�";
} else {
    $ptitle = "��޷��p2";
}

echo $_conf['doctype'];
echo <<<EOP
<html>
<head>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle}</title>
EOP;

echo <<<EOP
</head>
<body{$_conf['k_colors']}>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = '';

//==============================================================
// ���C�ɔ��v�����g����
//==============================================================
if($_GET['view'] == "favita"){
    $aShowBrdMenuK->printFavItaHtml();

//RSS���X�g�ǂݍ���
} elseif ($_GET['view'] == "rss" && $_conf['expack.rss.enabled']) {
    //$mobile = &Net_UserAgent_Mobile::singleton();
    if ($mobile->isNonMobile()) {
        output_add_rewrite_var('b', 'k');
    }
    @include_once P2EX_LIBRARY_DIR . '/rss/menu.inc.php';


// ����ȊO�Ȃ�brd�ǂݍ���
} else {
    $brd_menus =  BrdCtl::read_brds();
}

//===========================================================
// ����
//===========================================================
if ($_GET['view'] != "favita" && $_GET['view'] != "rss" && !$_GET['cateid']) {
    $kensaku_form_ht = <<<EOFORM
<form method="GET" action="{$_SERVER['SCRIPT_NAME']}" accept-charset="{$_conf['accept_charset']}">
    <input type="hidden" name="_hint" value="{$_conf['detect_hint']}">
    {$_conf['k_input_ht']}
    <input type="hidden" name="nr" value="1">
    <input type="text" id="word" name="word" value="{$word}" size="12">
    <input type="submit" name="submit" value="����">
</form>\n
EOFORM;

    echo $kensaku_form_ht;
    echo "<br>";
}

//===========================================================
// �������ʂ��v�����g
//===========================================================
// {{{ �������[�h�������

if (isset($_REQUEST['word']) && strlen($_REQUEST['word']) > 0) {

    $hd['word'] = htmlspecialchars($word, ENT_QUOTES);

    if ($GLOBALS['ita_mikke']['num']) {
        $hit_ht = "<br>\"{$hd['word']}\" {$GLOBALS['ita_mikke']['num']}hit!";
    }
    echo "��ؽČ�������{$hit_ht}<hr>";
    if ($word) {

        // �����������ăv�����g����
        if ($brd_menus) {
            foreach ($brd_menus as $a_brd_menu) {
                $aShowBrdMenuK->printItaSearch($a_brd_menu->categories);
            }
        }

    }
    if (!$GLOBALS['ita_mikke']['num']) {
        $_info_msg_ht .=  "<p>\"{$hd['word']}\"���܂ޔ͌�����܂���ł����B</p>\n";
        unset($word);
    }
    $modori_url_ht = <<<EOP
<div><a href="menu_k.php?view=cate&amp;nr=1{$_conf['k_at_a']}">��ؽ�</a></div>
EOP;
}

// }}}
//==============================================================
// �J�e�S����\��
//==============================================================
if ((isset($_REQUEST['word']) && $_REQUEST['word'] == "") or $_GET['view'] == "cate") {
    echo "��ؽ�<hr>";
    if($brd_menus){
        foreach($brd_menus as $a_brd_menu){
            $aShowBrdMenuK->printCate($a_brd_menu->categories);
        }
    }

}

//==============================================================
// �J�e�S���̔�\��
//==============================================================
if (isset($_GET['cateid'])) {
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printIta($a_brd_menu->categories);
        }
    }
    $modori_url_ht = <<<EOP
<a href="menu_k.php?view=cate&amp;nr=1{$_conf['k_at_a']}">��ؽ�</a><br>
EOP;
}

echo $_info_msg_ht;
$_info_msg_ht = '';

//==============================================================
// �Z�b�g�؂�ւ��t�H�[����\��
//==============================================================

if (($_GET['view'] == 'favita' && $_conf['favita_set_num'] > 0) ||
    ($_GET['view'] == 'rss' && $_conf['expack.rss.set_num'] > 0))
{
    echo '<hr>';
    if ($_GET['view'] == 'favita') {
        $set_name = 'm_favita_set';
        $set_title = '���C�ɔ�';
    } elseif ($_GET['view'] == 'rss') {
        $set_name = 'm_rss_set';
        $set_title = 'RSS';
    }
    echo FavSetManager::makeFavSetSwitchForm($set_name, $set_title, NULL, NULL, FALSE, array('view' => $_GET['view']));
}

// �t�b�^��\��
echo '<hr>';
echo $list_navi_ht;
echo $modori_url_ht;
echo $_conf['k_to_index_ht'];
echo '</body></html>';


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * mode: php
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
