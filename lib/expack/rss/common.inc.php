<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2�@�\�g���p�b�N - RSS���[�e�B���e�B�֐�

/**
 * RSS��URL���烍�[�J���ɕۑ�����t�@�C���̃p�X��ݒ肷��
 */
function rss_get_save_path($remotefile)
{
	global $datdir;
	static $done = array();

	if (isset($done[$remotefile])) {
		return $done[$remotefile];
	}

	$pURL = @parse_url($remotefile);
	if (!$pURL || !isset($pURL['scheme']) || $pURL['scheme'] != 'http' || !isset($pURL['host'])) {
		$_info_msg_ht = ' <p>p2 error: �s����RSS��URL (' . htmlspecialchars($remotefile) . ')</p>';
		return ($done[$remotefile] = FALSE);
	}

	$localname = '';
	if (isset($pURL['path']) && $pURL['path'] !== '') {
		$localname .= preg_replace('/[^\w.]/', '_', substr($pURL['path'], 1));
	}
	if (isset($pURL['query']) && $pURL['query'] !== '') {
		$localname .= '_' . preg_replace('/[^\w.%]/', '_', $pURL['query']) . '.rdf';
	}

	// �g���q.cgi��.php���ŕۑ�����̂�h��
	if (!preg_match('/\.(rdf|rss|xml)$/i', $localname)) {
		// �Â��o�[�W�����Ŏ擾����RSS���폜
		if (file_exists($localname)) {
			@unlink($localname);
		}
		// �g���q.rdf��t��
		$localname .= '.rdf';
	}
	// dotFile�������̂�h��
	if (substr($localname, 0, 1) == '.') {
		$localname = '_' . $localname;
	}

	$done[$remotefile] = $localpath = $datdir . '/p2_rss/' . $pURL['host'] . '/' . $localname;

	return $localpath;
}

?>
