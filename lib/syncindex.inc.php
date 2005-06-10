<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
	p2 -  ���C�ɃX���̓���
		�ŋߓǂ񂾃X���A�������ݗ����A�X���̓a��
*/

require_once (P2_LIBRARY_DIR . '/brdctl.class.php');

//================================================
// �ǂݍ���
//================================================
//favlistfile�t�@�C�����Ȃ���ΏI��
if (!file_exists($syncfile)) {
	return;
}

//favlistfile�ǂݍ���;
$lines = @file($syncfile);

//board�ǂݍ���
$_current = BrdCtl::read_brds();

//================================================
// ����
//================================================

//���X�g��P���z��ɕϊ�
$current = array();
foreach ($_current as $brdmenu) {
	foreach ($brdmenu->categories as $category) {
		foreach ($category->menuitas as $ita) {
			$current[] = "{$ita->host}<>{$ita->bbs}";
		}
	}
}

// ���f�[�^�̓���
// 2ch/bbspink�̏ꍇ�A���X�g�ƌ��f�[�^��bbs�i���j�ŏƍ����āA���X�g�f�[�^�Ō��f�[�^���㏑������B
$neolines = array();
$lines = array_map('rtrim', $lines);
foreach ($lines as $line) {
	$data = explode('<>', $line);
	if (preg_match('/^\w+\.(2ch\.net|bbspink\.com)$/', $data[10], $matches)) {
		$grep_pattern = '/^\w+\.' . preg_quote($matches[1], '/') . '<>' . preg_quote($data[11], '/') . '$/';
	} else {
		if (preg_match('/jbbs\.(shitaraba\.com|livedoor\.(com|jp))/', $data[10])) {
			$data[10] = preg_replace('/jbbs\.(shitaraba|livedoor)\.com/', 'jbbs.livedoor.jp', $data[10]);
			$neolines[] = implode('<>', $data);
		} else {
			$neolines[] = $line;
		}
		continue;
	}
	if ($findline = preg_grep($grep_pattern, $current)) {
		// $findline�͍ŏ��Ɍ����������̂𗘗p�B
		$newdata = explode('<>', rtrim(array_shift($findline)));
		$data[10] = $newdata[0];
		$data[11] = $newdata[1];
		$neolines[] = implode('<>', $data);
	} else {
		$neolines[] = $line;
	}
}

//================================================
// �X�V������΁A��������
//================================================
if (serialize($lines) != serialize($neolines)) {
	$fp = @fopen($syncfile, 'wb') or die("Error: {$syncfile} ���X�V�ł��܂���ł���");
	@flock($fp, LOCK_EX);
	foreach ($neolines as $l) {
		fputs($fp, $l);
		fputs($fp, "\n");
	}
	@flock($fp, LOCK_UN);
	fclose($fp);
	$sync_ok = true;
} else {
	$sync_ok = false;
}

?>
