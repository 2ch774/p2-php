<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
	p2 -  ���j���[ �g�їp
*/

require_once 'conf/conf.php';	//��{�ݒ�t�@�C���Ǎ�
require_once (P2_LIBRARY_DIR . '/brdctl.class.php');
require_once (P2_LIBRARY_DIR . '/showbrdmenuk.class.php');

authorize(); // ���[�U�F��

$debug = false;

//==============================================================
// �ϐ��ݒ�
//==============================================================
$_conf['ktai'] = true;
$brd_menus = array();

if (isset($_GET['word'])) {
	$word = $_GET['word'];
} elseif (isset($_POST['word'])) {
	$word = $_POST['word'];
}

// ������ ====================================
if (isset($word) && strlen($word) > 0) {

	if (!preg_match('/[^. ]/', $word)) {
		$word = '';
	}
	$word_ht = htmlspecialchars($word);

	// ���K�\������
	include_once (P2_LIBRARY_DIR . '/strctl.class.php');
	$word_fm = StrCtl::wordForMatch($word);
}


//============================================================
// ����ȑO�u����
//============================================================
// ���C�ɔ̒ǉ��E�폜
if (isset($_GET['setfavita'])) {
	include (P2_LIBRARY_DIR . '/setfavita.inc.php');
}

//================================================================
// ���C��
//================================================================
$aShowBrdMenuK = &new ShowBrdMenuK;

//============================================================
// �w�b�_
//============================================================
if ($_GET['view'] == 'favita') {
	$ptitle = '���C�ɔ�';
} elseif ($_GET['view'] == 'rss') {
	$ptitle = 'RSS';
} elseif ($_GET['view'] == 'cate') {
	$ptitle = '��ؽ�';
} elseif (isset($_GET['cateid'])) {
	$ptitle = '��ؽ�';
} else {
	$ptitle = '��޷��p2';
}

P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<title>{$ptitle}</title>
EOP;

echo <<<EOP
</head>
<body{$k_color_settings}>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = '';

//==============================================================
// ���C�ɔ��v�����g����
//==============================================================
if ($_GET['view'] == 'favita') {
	$aShowBrdMenuK->print_favIta();

} elseif ($_GET['view'] == 'rss' && $_exconf['rss']['*']) { //RSS���X�g�ǂݍ���
	@include_once (P2EX_LIBRARY_DIR . '/rss/menu.inc.php');

// ����ȊO�Ȃ�brd�ǂݍ���
} else {
	$brd_menus =  BrdCtl::read_brds();
}

//===========================================================
// ����
//===========================================================
if ($_GET['view'] != 'favita' && $_GET['view'] != 'rss' && !$_GET['cateid']) {
	$kensaku_form_ht = <<<EOFORM
<form method="GET" action="{$_SERVER['PHP_SELF']}" accept-charset="{$_conf['accept_charset']}">
	<input type="hidden" name="detect_hint" value="����">
	<input type="hidden" name="nr" value="1">
	<input type="text" id="word" name="word" value="{$word_ht}" size="12">
	<input type="submit" name="submit" value="����">
</form>\n
EOFORM;

	echo $kensaku_form_ht;
	echo "<br>\n";
}

//===========================================================
// �������ʂ��v�����g
//===========================================================
if (isset($word) && strlen($word) > 0) {

	if ($GLOBALS['ita_mikke']['num']) {
		$hit_ht = "<br>&quot;{$word_ht}&quot; {$GLOBALS['ita_mikke']['num']}hit!";
	}
	echo "��ؽČ�������{$hit_ht}<hr>";
	if ($word) {

		// �����������ăv�����g���� ==========================
		if ($brd_menus) {
			foreach ($brd_menus as $a_brd_menu) {
				$aShowBrdMenuK->printItaSearch($a_brd_menu->categories);
			}
		}

	}
	if (!$GLOBALS['ita_mikke']['num']) {
		$_info_msg_ht .=  "<p>&quot;{$word_ht}&quot;���܂ޔ͌�����܂���ł����B</p>\n";
		unset($word);
	}
	$modori_url_ht = <<<EOP
<div><a href="menu_k.php?view=cate&amp;nr=1">��ؽ�</a></div>
EOP;
} else {
	$menu_update_q = preg_replace('/(^|&)(k=1|nt=\d+)/', '', $_SERVER['QUERY_STRING']);
	$menu_update_q = htmlspecialchars($menu_update_q) . '&amp;nt=' . time();
	$modori_url_ht = <<<EOP
<a href="menu_k.php?{$menu_update_q}">�ƭ����X�V</a>\n
EOP;
}

//==============================================================
// �J�e�S����\��
//==============================================================
if ($_GET['view'] == 'cate') {
	echo '��ؽ�<hr>';
	if ($brd_menus) {
		foreach ($brd_menus as $a_brd_menu) {
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
	$modori_url_ht = '<a href="menu_k.php?view=cate&amp;nr=1">��ؽ�</a><br>';
}

echo $_info_msg_ht;
$_info_msg_ht = '';

//==============================================================
// �Z�b�g�؂�ւ��t�H�[����\��
//==============================================================

if ($_exconf['etc']['multi_favs'] && ($_GET['view'] == 'favita' || $_GET['view'] == 'rss')) {
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

//==============================================================
// �t�b�^��\��
//==============================================================

echo '<hr>';
echo $list_navi_ht;
echo $kensaku_form_ht;
echo $modori_url_ht;
echo $_conf['k_to_index_ht'];
echo '</body></html>';

?>
