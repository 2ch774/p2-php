<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/*
	cmd �������ŃR�}���h����
	�Ԃ�l�́A�e�L�X�g�ŕԂ�
*/

include_once 'conf/conf.php';  // ��{�ݒ�t�@�C��

authorize(); // ���[�U�F��

$r_msg = '';

// cmd���w�肳��Ă��Ȃ���΁A�����Ԃ����ɏI��
if (!isset($_GET['cmd']) && !isset($_POST['cmd'])) {
	die('');
}

// �R�}���h�擾
if (isset($_GET['cmd'])) {
	$cmd = $_GET['cmd'];
} elseif (isset($_POST['cmd'])) {
	$cmd = $_POST['cmd'];
}

// �����O�폜
if ($cmd == 'delelog') { 
	if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key'])) {
		include_once (P2_LIBRARY_DIR . '/dele.inc.php');
		$r = deleteLogs($_REQUEST['host'], $_REQUEST['bbs'], array($_REQUEST['key']));
		if (empty($r)) {
			$r_msg = '0'; // ���s
		} elseif ($r == 1) {
			$r_msg = '1'; // ����
		} elseif ($r == 2) {
			$r_msg = '2'; // �Ȃ�
		}
	}

// �����C�ɃX��
} elseif ($cmd == 'setfav') {
	if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key']) && isset($_REQUEST['setfav'])) {
		include_once (P2_LIBRARY_DIR . '/setfav.inc.php');
		$r = setFav($_REQUEST['host'], $_REQUEST['bbs'], $_REQUEST['key'], $_REQUEST['setfav']);
		if (empty($r)) {
			$r_msg = '0'; // ���s
		} elseif ($r == 1) {
			$r_msg = '1'; // ����
		}
	}
}


// ���ʃv�����g

//$r_msg = mb_convert_encoding($r_msg, 'UTF-8', 'SJIS-win');

echo $r_msg;

?>
