<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
	p2 - ���j���[�̔񓯊��ǂݍ���
	����ł͂��C�ɔ�RSS�̃Z�b�g�؂�ւ��̂ݑΉ�
*/

require_once 'conf/conf.php';	//��{�ݒ�t�@�C���Ǎ�
require_once (P2_LIBRARY_DIR . '/p2util.class.php');	// p2�p�̃��[�e�B���e�B�N���X
require_once (P2_LIBRARY_DIR . '/brdctl.class.php');
require_once (P2_LIBRARY_DIR . '/showbrdmenupc.class.php');

authorize(); // ���[�U�F��

$_conf['ktai'] = FALSE;

// {{{ HTTP�w�b�_��XML�錾

if (P2Util::isBrowserSafariGroup()) {
	header('Content-Type: application/xml; charset=UTF-8');
	$xmldec = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
} else {
	header('Content-Type: text/html; charset=Shift_JIS');
	// ���p�Łu�H���v�������Ă镶������R�����g�ɂ���ƃp�[�X�G���[
	//$xmldec = '<' . '?xml version="1.0" encoding="Shift_JIS" ?' . '>' . "\n";
	$xmldec = '';
}

// }}}
// {{{ �{�̐���

// ���C�ɔ�
if (isset($_GET['m_favita_set'])) {
	$aShowBrdMenuPc = &new ShowBrdMenuPc;
	ob_start();
	$aShowBrdMenuPc->print_favIta();
	$menuItem = ob_get_clean();
	$menuItem = preg_replace('/^\s*<div class="menu_cate">.+?<div class="itas" id="c_favita">\s*/s', '', $menuItem);
	$menuItem = preg_replace('/\s*<\/div>\s*<\/div>\s*$/s', '', $menuItem);

// RSS
} elseif (isset($_GET['m_rss_set'])) {
	ob_start();
	@include_once (P2EX_LIBRARY_DIR . '/rss/menu.inc.php');
	$menuItem = ob_get_clean();
	$menuItem = preg_replace('/^\s*<div class="menu_cate">.+?<div class="itas" id="c_rss">\s*/s', '', $menuItem);
	$menuItem = preg_replace('/\s*<\/div>\s*<\/div>\s*$/s', '', $menuItem);

// ���̑�
} else {
	$menuItem = 'p2 error: �K�v�Ȉ������w�肳��Ă��܂���';
}

// }}}
// {{{ �{�̏o��

if (P2Util::isBrowserSafariGroup()) {
	$menuItem = mb_convert_encoding($menuItem, 'UTF-8', 'SJIS-win');
}
echo $xmldec;
echo $menuItem;

// }}}

?>
