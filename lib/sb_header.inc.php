<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
	p2 -  �T�u�W�F�N�g - �w�b�_�\��
	for subject.php
*/

//===================================================================
// �ϐ�
//===================================================================
$newtime = date('gis');
$reloaded_time = date('m/d G:i:s'); // �X�V����

// �X�����ځ[��`�F�b�N�A�q�� =============================================
$taborn_check_ht = '';
if ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko' and $aThreadList->threads) {
	$offline_num = $aThreadList->num - $online_num;
	$taborn_check_ht = <<<EOP
	<form class="check" method="POST" action="{$_SERVER['PHP_SELF']}" target="_self">\n
EOP;
	if ($offline_num > 0) {
		if ($aThreadList->spmode == 'taborn') {
			$taborn_check_ht .= <<<EOP
		<p>{$aThreadList->num}�����A{$offline_num}���̃X���b�h�����ɔT�[�o�̃X���b�h�ꗗ����O��Ă���悤�ł��i�����Ń`�F�b�N�����܂��j</p>\n
EOP;
		}
		/*
		elseif ($aThreadList->spmode == 'soko') {
			$taborn_check_ht .= <<<EOP
		<p>{$aThreadList->num}����dat�����X���b�h���ۊǂ���Ă��܂��B</p>\n
EOP;
		}*/
	}
}

//===============================================================
// HTML�\���p�ϐ� for �c�[���o�[(sb_toolbar.inc.php)
//===============================================================

$norefresh_q = '&amp;norefresh=true';

// �y�[�W�^�C�g������URL�ݒ� ====================================
if ($aThreadList->spmode == 'taborn' or $aThreadList->spmode == 'soko') {
	$ptitle_url = "{$_conf['subject_php']}?host={$aThreadList->host}&bbs={$aThreadList->bbs}";
} elseif ($aThreadList->spmode == 'res_hist') {
	$ptitle_url = './read_res_hist.php#footer';
} elseif (!$aThreadList->spmode) {
	$ptitle_url = 'http://'.$aThreadList->host.'/'.$aThreadList->bbs.'/';
	if (preg_match('/www\.onpuch\.jp/', $aThreadList->host)) {
		$ptitle_url = $ptitle_url.'index2.html';
	} elseif (preg_match('/livesoccer\.net/', $aThreadList->host)) {
		$ptitle_url = $ptitle_url.'index2.html';
	}
	// match�o�^���head�Ȃ��ĕ������ق����悳���������A�������X�|���X������̂�����
}
if (isset($ptitle_url)) {
	if ($_conf['motothre_ime'] && !$aThreadList->spmode) {
		$ptitle_url_ime = P2Util::throughIme($ptitle_url, TRUE);
	} else {
		$ptitle_url_ime = htmlspecialchars($ptitle_url);
	}
} else {
	$ptitle_url_ime = '';
}

// �y�[�W�^�C�g������HTML�ݒ� ====================================
if ($aThreadList->spmode == 'fav' && $_exconf['etc']['multi_favs']) {
	$ptitle_hd = FavSetManager::getFavSetPageTitleHt('m_favlist_set', $aThreadList->ptitle);
} else {
	$ptitle_hd = htmlspecialchars($aThreadList->ptitle);
}

$kanban_popup = false;

if ($aThreadList->spmode == 'taborn') {
	$ptitle_ht = "\t<span class=\"itatitle\"><a class=\"aitatitle\" href=\"{$ptitle_url_ime}\" target=\"_self\"><b>{$aThreadList->itaj_hd}</b></a>�i���ځ[�񒆁j</span>";
} elseif ($aThreadList->spmode == 'soko') {
	$ptitle_ht = "\t<span class=\"itatitle\"><a class=\"aitatitle\" href=\"{$ptitle_url_ime}\" target=\"_self\"><b>{$aThreadList->itaj_hd}</b></a>�idat�q�Ɂj</span>";
} elseif (!empty($ptitle_url)) {
	$onmouse_popup = '';
	if ($_exconf['kanban']['*'] == 2) {
		if (strstr($ptitle_url, '2ch.net') || strstr($ptitle_url, 'bbspink.com')) {
			$_exconf['kanban']['*'] = 1;
		} else {
			$_exconf['kanban']['*'] = 0;
		}
	}
	if (!$aThreadList->spmode && $_exconf['kanban']['*']) { //�Ŕ|�b�v�A�b�v
		include_once (P2EX_LIBRARY_DIR . '/kanban.inc.php');
		if ($_exconf['kanban']['disp_rule'] || $_exconf['kanban']['disp_img_result'] || $_exconf['kanban']['disp_file_result']) { //HTML�|�b�v�A�b�v�\��
			//NOTICE���o���Ȃ��悤�ɏ�����
			$kanban_info = array();
			//�|�b�v�A�b�vURI
			$kanban_popup = getSignboard($ptitle_url, $_exconf['kanban']['cache'], 1);
			//�C�x���g�n���h��
			if (!empty($kanban_popup)) {
				if (is_array($kanban_popup)) {
					$kanban_info = $kanban_popup;
					$kb_popup_mode_respopup = true;
				} else {
					$onmouse_popup = " onmouseover=\"showHtmlPopUp('{$kanban_popup}',event,{$_exconf['kanban']['popup_delay']})\" onmouseout=\"offHtmlPopUp()\"";
					$kb_popup_mode_respopup = false;
				}
			}
		} else { //���X�|�b�v�A�b�v�\��
			//�Ŕ��
			$kanban_info = getSignboard($ptitle_url, $_exconf['kanban']['cache'], 0);
			$kb_popup_mode_respopup = true;
		}
		if ($kb_popup_mode_respopup) {
			//�|�b�v�A�b�vURI
			$_exconf['kanban']['disp_rule'] = 0;
			$_exconf['kanban']['disp_img_result'] = 0;
			$_exconf['kanban']['disp_file_result'] = 0;
			$kanban_popup = 'kanban.php?mode=info';
			$kanban_popup .= '&amp;datdir_host=' . rawurlencode($datdir_host);
			$kanban_popup .= '&amp;bbs=' . rawurlencode($bbs);
			$kanban_popup .= '&amp;ptitle_url=' . rawurlencode($ptitle_url);
			//�C�x���g�n���h��
			$onmouse_popup = " onmouseover=\"showResPopUp('kanbanImage',event)\" onmouseout=\"hideResPopUp('kanbanImage')\"";
		}
	}
	$ptitle_ht = "\t<span class=\"itatitle\"><a class=\"aitatitle\" href=\"{$ptitle_url_ime}\"{$onmouse_popup}><b>{$ptitle_hd}</b></a></span>";
} else {
	$ptitle_ht = "\t<span class=\"itatitle\"><b>{$ptitle_hd}</b></span>";
}

// �r���[�����ݒ� ==============================================
$edit_ht = '';
if ($aThreadList->spmode) {	// �X�y�V�������[�h��
	if ($aThreadList->spmode == 'fav' or $aThreadList->spmode == 'palace') {	// ���C�ɃX�� or �a���Ȃ�
		if ($sb_view == 'edit') {
			$edit_ht = "<a class=\"narabi\" href=\"{$_conf['subject_php']}?spmode={$aThreadList->spmode}{$norefresh_q}\" target=\"_self\">����</a>";
		} else {
			$edit_ht = "<a class=\"narabi\" href=\"{$_conf['subject_php']}?spmode={$aThreadList->spmode}&amp;sb_view=edit{$norefresh_q}\" target=\"_self\">����</a>";

		}
	}
}

// �t�H�[��hidden ==================================================
$sb_form_hidden_ht = <<<EOP
	<input type="hidden" name="detect_hint" value="����">
	<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
	<input type="hidden" name="host" value="{$aThreadList->host}">
	<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
EOP;

// �\������ ==================================================
$sb_disp_num_ht = '';
if (!$aThreadList->spmode || $aThreadList->spmode == 'news') {
	$vnchecks = array(100, 150, 200, 250, 300, 400, 500, 'all');
	if (!isset($p2_setting['viewnum'])) {
		$p2_setting['viewnum'] = '150';
	} elseif (!in_array($p2_setting['viewnum'], $vnchecks) && is_numeric($p2_setting['viewnum'])) {
		array_unshift($vnchecks, (int)$p2_setting['viewnum']);
	}

	$sb_disp_num_ht = '<select name="viewnum">';
	foreach ($vnchecks as $vncheck) {
		$vnselected = ($p2_setting['viewnum'] == $vncheck) ? 'selected' : '';
		$vntitle = ($vncheck == 'all') ? '�S��' : ($vncheck . '��');
		$sb_disp_num_ht .= "<option value=\"{$vncheck}\"{$vnselected}>{$vntitle}</option>";
	}
	$sb_disp_num_ht .= '</select>';
}

// �t�B���^���� ==================================================
if ($_exconf['flex']['*'] == 2) {
	$filter_method_checked = array(' checked', '', '');
	if ($sb_filter_method == 'or') {
		$filter_method_checked[0] = '';
		$filter_method_checked[1] = ' checked';
	} elseif ($sb_filter_method == 'regex') {
		$filter_method_checked[0] = '';
		$filter_method_checked[2] = ' checked';
	}
	$sb_form_method_ht = <<<EOP
			<label><input type="radio" name="method" value="and"{$filter_method_checked[0]}>AND</label>
			<label><input type="radio" name="method" value="or"{$filter_method_checked[1]}>OR</label>
			<label><input type="radio" name="method" value="regex"{$filter_method_checked[2]}>���K�\��</label>
EOP;
}

$filter_form_ht = <<<EOP
		<form class="toolbar" method="GET" action="subject.php" accept-charset="{$_conf['accept_charset']}" target="_self">
			{$sb_form_hidden_ht}
			<input type="text" id="word" name="word" value="{$word_ht}" size="16">
			{$sb_form_method_ht}
			<input type="submit" name="submit_kensaku" value="����">
		</form>
EOP;



// �`�F�b�N�t�H�[�� =====================================
$abornoff_ht = '';
$check_form_ht = '';
if ($aThreadList->spmode == 'taborn') {
	$abornoff_ht = "<input type=\"submit\" name=\"submit\" value=\"{$abornoff_st}\">";
}
if (($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') && $aThreadList->threads) {
	$check_form_ht = <<<EOP
	<p>
		�`�F�b�N�������ڂ�
		<input type="submit" name="submit" value="{$deletelog_st}">
		{$abornoff_ht}
	</p>
EOP;
}

//===================================================================
// HTML�v�����g
//===================================================================

P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">\n
EOP;

if ($_conf['refresh_time']) {
	$refresh_time_s = $_conf['refresh_time'] * 60;
	$refresh_url = "{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}";
	echo <<<EOP
	<meta http-equiv="refresh" content="{$refresh_time_s};URL={$refresh_url}">\n
EOP;
}

echo <<<EOP
	<title>{$ptitle_hd}</title>
	<base target="read">\n
EOP;

//�Ŕ|�b�v�A�b�v
$kanban_img_ht = '';
$sb_popup_js = '';
if ($kanban_popup) {
	if ($kb_popup_mode_respopup) { //���X�|�b�v�A�b�v�\��
		//���X�|�b�v�A�b�v�pJavaScript�t�@�C��
		$sb_popup_js = "\n\t" . '<script type="text/javascript" src="js/respopup.js"></script>';
		//�Ŕu���b�N�̃X�^�C���V�[�g
		$kb_bgcolor_q = (empty($kanban_info['bgcolor']))
			? '' : '&amp;bgcolor=' . rawurlencode($kanban_info['bgcolor']);
		$kb_bgimage_q = (empty($kanban_info['background']))
			? '' : '&amp;bgimage=' . rawurlencode($kanban_info['background']);
		echo <<<EOCSS
	<link rel="stylesheet" href="css.php?css=kanban&amp;skin={$skin_en}{$kb_bgcolor_q}{$kb_bgimage_q}" type="text/css">\n
EOCSS;
		//���X�|�b�v�A�b�vHTML�^�O
		$kanban_img_ht = <<<EOP
<div id="kanbanImage"{$onmouse_popup}><a href="javascript:void(OpenSubWin('{$kanban_popup}',600,570,1,0));"><img src="{$kanban_info['image']}" alt="{$kanban_info['title']}"></a></div>
EOP;
	} else { //HTML�|�b�v�A�b�v�\��
		//HTML�|�b�v�A�b�v�pJavaScript�t�@�C��
		$sb_popup_js = <<<EOJS
	<script type="text/javascript">
	gIsPageLoaded = false;
	</script>
	<script type="text/javascript" src="js/htmlpopup.js"></script>\n
EOJS;
		//HTML�|�b�v�A�b�v�̃X�^�C���V�[�g
		echo <<<EOCSS
	<link rel="stylesheet" href="css.php?css=read&amp;skin={$skin_en}" type="text/css">\n
EOCSS;
	}
}

echo <<<EOCSS
	<link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
	<link rel="stylesheet" href="css.php?css=subject&amp;skin={$skin_en}" type="text/css">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">\n
EOCSS;
echo $sb_popup_js;
echo <<<EOJS
	<script type="text/javascript" src="js/basic.js"></script>
	<script type="text/javascript">
	<!--
	function setWinTitle(){
		var shinchaku_ari = "$shinchaku_attayo";
		if (shinchaku_ari) {
			window.top.document.title="��{$ptitle_hd}";
		} else {
			if (top != self) {top.document.title=self.document.title;}
		}
	}

	function chNewAllColor()
	{
		var smynum1 = document.getElementById('smynum1');
		if (smynum1) {
			smynum1.style.color="{$STYLE['sb_ttcolor']}";
		}
		var smynum2 = document.getElementById('smynum2')
		if (smynum2) {
			smynum2.style.color="{$STYLE['sb_ttcolor']}";
		}
		var a = document.getElementsByTagName('a');
		for (var i = 0; i < a.length; i++) {
			if (a[i].className == 'un_a') {
				a[i].style.color = "{$STYLE['sb_ttcolor']}";
			}
		}
	}
	
	function chUnColor(idnum){
		var unid = 'un'+idnum;
		var unid_obj = document.getElementById(unid);
		if (unid_obj) {
			unid_obj.style.color="{$STYLE['sb_ttcolor']}";
		}
	}
	
	function chTtColor(idnum){
		var ttid = "tt"+idnum;
		var toid = "to"+idnum;
		var ttid_obj = document.getElementById(ttid);
		if (ttid_obj) {
			ttid_obj.style.color="{$STYLE['thre_title_color_v']}";
		}
		var toid_obj = document.getElementById(toid);
		if (toid_obj) {
			toid_obj.style.color="{$STYLE['thre_title_color_v']}";
		}
	}
	// -->
	</script>
EOJS;

if ($aThreadList->spmode == 'taborn' or $aThreadList->spmode == 'soko') {
	echo <<<EOJS
	<script language="javascript">
	<!--
	function checkAll(){
		var trk = 0;
		var inp = document.getElementsByTagName('input');
		for (var i=0; i<inp.length; i++){
			var e = inp[i];
			if ((e.name != 'allbox') && (e.type == 'checkbox')){
				trk++;
				e.checked = document.getElementById('allbox').checked;
			}
		}
	}
	// -->
	</script>
EOJS;
}

echo <<<EOP
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onload="setWinTitle();gIsPageLoaded=true;">
{$kanban_img_ht}
EOP;

include (P2_LIBRARY_DIR . '/sb_toolbar.inc.php');

echo $_info_msg_ht;
$_info_msg_ht = '';

echo <<<EOP
	$taborn_check_ht
	$check_form_ht
	<table cellspacing="0" width="100%">\n
EOP;

?>
