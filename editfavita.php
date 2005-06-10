<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 -  ���C�ɓ���ҏW

require_once 'conf/conf.php';  // ��{�ݒ�
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

authorize(); // ���[�U�F��

// �ϐ� =============
$_info_msg_ht = '';

//================================================================
// ������ȑO�u����
//================================================================

// ���C�ɔ̒ǉ��E�폜�A���ёւ�
if (isset($_GET['setfavita']) or isset($_POST['setfavita'])) {
	include (P2_LIBRARY_DIR . '/setfavita.inc.php');
}
// ���C�ɔ̃z�X�g�𓯊�
if (isset($_GET['syncfavita']) or isset($_POST['syncfavita'])) {
	include (P2_LIBRARY_DIR . '/syncfavita.inc.php');
}

// �v�����g�p�ϐ� ======================================================

// ���C�ɔǉ��t�H�[��
if ($_conf['ktai']) {
	$add_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}">
	<input type="hidden" name="detect_hint" value="����">
	<p>
		URL: <input type="text" id="url" name="url" value="http://">
		��: <input type="text" id="itaj" name="itaj" value="">
		<input type="hidden" id="setfavita" name="setfavita" value="1">
		<input type="submit" name="submit" value="�V�K�ǉ�">
	</p>
</form>\n
EOFORM;
} else {
	$add_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}" accept-charset="{$_conf['accept_charset']}" target="_self">
	<input type="hidden" name="detect_hint" value="����">
	<p>
		URL: <input type="text" id="url" name="url" value="http://" size="48">
		��: <input type="text" id="itaj" name="itaj" value="" size="16">
		<input type="hidden" id="setfavita" name="setfavita" value="1">
		<input type="submit" name="submit" value="�V�K�ǉ�">
	</p>
</form>\n
EOFORM;
}

// ���C�ɔ����t�H�[��
$sync_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}" target="_self">
	<p>
		<input type="hidden" id="syncfavita" name="syncfavita" value="1">
		<input type="submit" name="submit" value="���X�g�Ɠ���">
	</p>
</form>\n
EOFORM;

// ���C�ɔؑփt�H�[��
if ($_exconf['etc']['multi_favs']) {
	$switch_favita_form_ht = FavSetManager::makeFavSetSwitchForm('m_favita_set', '���C�ɔ�', NULL, NULL, !$_conf['ktai']);
} else {
	$switch_favita_form_ht = '';
}

//================================================================
// �w�b�_
//================================================================
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
if ($_conf['ktai']) {
	echo <<<EOP
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<title>p2 - ���C�ɔ̕��ёւ�</title>
</head>
<body{$k_color_settings}>
EOP;
} else {
	echo <<<EOP
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<title>p2 - ���C�ɔ̕��ёւ�</title>
	<link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
	<link rel="stylesheet" href="css.php?css=editfavita&amp;skin={$skin_en}" type="text/css">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
EOP;
}

echo $_info_msg_ht;
$_info_msg_ht = '';

//================================================================
// ���C������HTML�\��
//================================================================

//================================================================
// ���C�ɔ�
//================================================================

// favita�t�@�C�����Ȃ���ΐ���
FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm']);
// favita�ǂݍ���
$lines = file($_conf['favita_path']);

// PC�p
if (!$_conf['ktai']) {
	$onclick = " onclick=\"parent.menu.location.href='{$_conf['menu_php']}?nr=1'\"";
	$m_php = $_SERVER['PHP_SELF'];
// �g�їp
} else {
	$onclick = '';
	$m_php = 'menu_k.php?view=favita&amp;nr=1&amp;?nt=' . time();
}

echo <<<EOP
<div><b>���C�ɔ̕ҏW</b> [<a href="{$m_php}"{$onclick}>���j���[���X�V</a>] {$switch_favita_form_ht}</div>
EOP;

echo $add_favita_form_ht;

if ($lines) {
	echo '<hr>';
	echo '<table>'; // �g�тł�XHTML Basic�Ή��[���̓e�[�u����\���ł���
	foreach ($lines as $l) {
		$l = rtrim($l);
		if (preg_match("/^\t?(.+)\t(.+)\t(.+)$/", $l, $matches)) {
			$host = $matches[1];
			$bbs = $matches[2];
			$itaj = rtrim($matches[3]);
			$itaj_en = rawurlencode(base64_encode($itaj));
			$itaj_view = htmlspecialchars($itaj);
			$itaj_q = '&amp;itaj_en='.$itaj_en;
			$setfavita_url = $_SERVER['PHP_SELF'] . '?host=' . $host . '&amp;bbs=' . $bbs;
			if ($_conf['ktai']) {
				echo <<<EOP
<tr>
<td><a href="{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}">{$itaj_view}</a></td>
<td><small>[<a href="{$setfavita_url}{$itaj_q}&amp;setfavita=top">��</a><a href="{$setfavita_url}{$itaj_q}&amp;setfavita=up">��</a><a href="{$setfavita_url}{$itaj_q}&amp;setfavita=down">��</a><a href="{$setfavita_url}{$itaj_q}&amp;setfavita=bottom">��</a>]</small></td>
<td><small>[<a href="{$setfavita_url}&amp;setfavita=0">��</a>]</small></td>
</tr>
EOP;
			} else {
				echo <<<EOP
	<tr>
		<td><a href="{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}">{$itaj_view}</a></td>
		<td>[ <a class="te" href="{$setfavita_url}{$itaj_q}&amp;setfavita=top" title="��ԏ�Ɉړ�">��</a></td>
		<td><a class="te" href="{$setfavita_url}{$itaj_q}&amp;setfavita=up" title="���Ɉړ�">��</a></td>
		<td><a class="te" href="{$setfavita_url}{$itaj_q}&amp;setfavita=down" title="����Ɉړ�">��</a></td>
		<td><a class="te" href="{$setfavita_url}{$itaj_q}&amp;setfavita=bottom" title="��ԉ��Ɉړ�">��</a> ]</td>
		<td>[<a href="{$setfavita_url}&amp;setfavita=0">�폜</a>]</td>
	</tr>
EOP;
			}
		}
	}
	echo '</table>';
}

if (!$_conf['ktai']) {
	echo $sync_favita_form_ht;
}

//================================================================
// �t�b�^HTML�\��
//================================================================
if ($_conf['ktai']) {
	echo '<hr>'.$_conf['k_to_index_ht'];
}

echo '</body></html>';

?>
