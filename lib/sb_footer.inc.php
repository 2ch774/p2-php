<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
	p2 - �T�u�W�F�N�g - �t�b�^�\��
	for subject.php
*/

$bbs_q = '&amp;bbs='.$aThreadList->bbs;

// dat�q�� =======================
$dat_soko_ht = '';
if (!$aThreadList->spmode or $aThreadList->spmode == 'taborn') { //�X�y�V�������[�h�łȂ���΁A�܂��͂��ځ[�񃊃X�g�Ȃ�
	$dat_soko_ht = "\t<a href=\"{$_conf['subject_php']}?host={$aThreadList->host}{$bbs_q}{$norefresh_q}&amp;spmode=soko\" target=\"_self\">dat�q��</a> |";
}

// ���ځ[�񒆂̃X���b�h =================
$taborn_link_ht = '';
if ($ta_num) {
	$taborn_link_ht = "\t<a href=\"{$_conf['subject_php']}?host={$aThreadList->host}{$bbs_q}{$norefresh_q}&amp;spmode=taborn\" target=\"_self\">���ځ[�񒆂̃X���b�h ({$ta_num})</a> |";
}

// �V�K�X���b�h�쐬 =======
$buildnewthread_ht = '';
if (!$aThreadList->spmode) {
	$buildnewthread_ht ="\t<a href=\"post_form.php?host={$aThreadList->host}{$bbs_q}&amp;newthread=true\" target=\"_self\" onclick=\"return OpenSubWin('post_form.php?host={$aThreadList->host}{$bbs_q}&amp;newthread=true&amp;popup=1',{$STYLE['post_pop_size']},0,0)\">�V�K�X���b�h�쐬</a>";
}

// HTML�v�����g ==============================================

echo "</table>\n";

// �`�F�b�N�t�H�[�� =====================================
echo $check_form_ht;

//�t�H�[���t�b�^
echo <<<EOP
		<input type="hidden" name="host" value="{$aThreadList->host}">
		<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
		<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
	</form>\n
EOP;

// sbject �c�[���o�[ =====================================
include (P2_LIBRARY_DIR . '/sb_toolbar.inc.php');

echo '<p>';
echo $dat_soko_ht;
echo $taborn_link_ht;
echo $buildnewthread_ht;
echo '</p>';

// �X�y�V�������[�h�łȂ���΃t�H�[�����͕⊮ ========================
if (!$aThreadList->spmode) {
	if (P2Util::isHostJbbsShitaraba($aThreadList->host)) { // �������
		$ini_url_text = "http://{$aThreadList->host}/bbs/read.cgi?BBS={$aThreadList->bbs}&KEY=";
	} elseif (P2Util::isHostMachiBbs($aThreadList->host)) { // �܂�BBS
		$ini_url_text = "http://{$aThreadList->host}/bbs/read.pl?BBS={$aThreadList->bbs}&KEY=";
	} elseif (P2Util::isHostMachiBbsNet($aThreadList->host)) { // �܂��r�˂���
		$ini_url_text = "http://{$aThreadList->host}/test/read.cgi?bbs={$aThreadList->bbs}&key=";
	} else {
		$ini_url_text = "http://{$aThreadList->host}/test/read.cgi/{$aThreadList->bbs}/";
	}
} else {
	$ini_url_text = '';
}

//if (!$aThreadList->spmode || $aThreadList->spmode == 'fav' || $aThreadList->spmode == 'recent' || $aThreadList->spmode == 'res_hist') {
$onclick_ht =<<<EOP
var url_v=document.forms["urlform"].elements["url_text"].value;
if (url_v == "" || url_v == "{$ini_url_text}") {
	alert("�������X���b�h��URL����͂��ĉ������B ��Fhttp://pc.2ch.net/test/read.cgi/mac/1034199997/");
	return false;
}
EOP;
echo "<div>\n";
echo <<<EOP
	<form id="urlform" method="GET" action="{$_conf['read_php']}" target="read">
		<div>
			�X��URL�𒼐ڎw��
			<input id="url_text" type="text" value="{$ini_url_text}" name="nama_url" size="54">
			<input type="submit" name="btnG" value="�\��" onclick='{$onclick_ht}'>
		</div>
	</form>\n
EOP;
if ($aThreadList->spmode == 'fav' && $_exconf['etc']['multi_favs']) {
	echo "\t<div style=\"margin:8px 8px;\">\n";
	echo FavSetManager::makeFavSetSwitchForm('m_favlist_set', '���C�ɃX��', NULL, NULL, FALSE, array('spmode' => 'fav', 'norefresh' => 1));
	echo "\t</div>\n";
}
echo "</div>\n";

//}

//================
echo '</body></html>';

?>
