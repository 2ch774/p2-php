<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
	p2 - �ŔE�X���b�h���\���X�N���v�g
*/

require_once 'conf/conf.php';
require_once (P2EX_LIBRARY_DIR . '/kanban.inc.php');

//�ϐ��̑O����
$datdir_host = $_GET['datdir_host'];
$bbs = $_GET['bbs'];
$ptitle_url = $_GET['ptitle_url'];
$datdir_bbs = $datdir_host . '/' . $bbs;
$o_link = 'kanban.php?mode={#mode#}';
$o_link .= '&amp;datdir_host=' . rawurlencode($datdir_host);
$o_link .= '&amp;bbs=' . rawurlencode($bbs);
$o_link .= '&amp;ptitle_url=' . rawurlencode($ptitle_url);
$info_ab = '';
$info_ae = '';

//�{�^���̂���
$button_tpl = '<input type="button" id="%s" value="%s" onclick="%s">';
$button = '';

//�|�b�v�A�b�v�̂Ƃ�
if (isset($_GET['popup'])) {
	
	$kanban = unserialize(base64_decode($_GET['popup']));
	//�L���b�V�����폜��������̓L���b�V���Ȃ��ōĎ擾�B
	if ((strstr($kanban['image'], $datdir_bbs) && !file_exists($kanban['image'])) ||
		(strstr($kanban['background'], $datdir_bbs) && !file_exists($kanban['background']))) {
		$kanban = getSignboard($ptitle_url, 0, 0);
	}
	//�ڍ׏���\������Ƃ�
	elseif ($_exconf['kanban']['disp_rule'] || $_exconf['kanban']['disp_img_result'] || $_exconf['kanban']['disp_file_result']) {
		//GET�Ŏ󂯓n���ł����Ƃ�
		if ($kanban['info']) {
			$kanban_info = $kanban['info'];
		}
		//GET�Ŏ󂯓n���ł��Ȃ������Ƃ�
		else {
			$kb_url = $kanban['image'];
			if (substr($kb_url, 0, 7) == 'http://') { $kb_src = $kanban['image']; }
			$bg_url = $kanban['background'];
			if (substr($bg_url, 0, 7) == 'http://') { $bg_src = $kanban['background']; }
			$setting_file = $datdir_bbs . '/p2_kb_setting.txt';
			$rule_file = $datdir_bbs . '/p2_kb_head.txt';
			$return_popup = 0;
			@include (P2EX_LIBRARY_DIR . '/kanban_info.inc.php');
		}
	}
	
	//�ҏW�{�^���\�����L���̂Ƃ�
	if ($_exconf['kanban']['manage']) {
		//�L���b�V������Ă���Ƃ��A�L���b�V���폜�{�^����\��
		if (file_exists($kanban['image']) || file_exists($kanban['background'])) {
			$onclick_mode = 'delete';
			$button_id = 'deletebutton';
			$button_value = '�L���b�V�����폜';
		}
		//�L���b�V������Ă��Ȃ��Ƃ��A�L���b�V���X�V�{�^����\��
		else {
			$onclick_mode = 'reload';
			$button_id = 'reloadbutton';
			$button_value = '�摜���L���b�V��';
		}
		//�{�^���̐ݒ�
		$link = str_replace('{#mode#}', $onclick_mode, $o_link);
		$onclick_action = "return OpenSubWin('{$link}',600,380,0,0);";
		$button = sprintf($button_tpl, $button_id, $button_value, $onclick_action);
	}
	//�ŔN���b�N�Ŕ��E�C���h�E���J�������N�̍쐬
	$onclick_mode = 'info';
	$link = str_replace('{#mode#}', $onclick_mode, $o_link);
	$info_ab = "<a href=\"javascript:void(OpenSubWin('{$link}',600,570,1,0));\">";
	$info_ae = '</a>';
}


//�L���b�V���폜�܂��̓L���b�V���X�V�̎�
elseif (isset($_GET['mode'])) {
	
	$result = array();
	
	//�L���b�V�����ꂽ�摜���폜
	if ($_GET['mode'] == 'delete') {
		$dirObj = dir($datdir_bbs);
		while (($ent = $dirObj->read()) !== FALSE) {
			if (preg_match('/\.(gif|jpe?g|png)$/i', $ent)) {
				$file = $datdir_bbs . '/' . $ent;
				if (@unlink($file)) {
					$tmpmsg = '<td class="tdleft"><b>�� �L���b�V���폜����</b></td>';
					$tmpmsg .= '<td class="tdcont">' . realpath($file) . '</td>';
				} else {
					$tmpmsg = '<td class="tdleft"><b>�~ �L���b�V���폜���s</b></td>';
					$tmpmsg .= '<td class="tdcont">' . realpath($file) . '</td>';
				}
				array_push($result, $tmpmsg);
			}
		}
		$kanban = getSignboard($ptitle_url, 0, 0);
		if (count($result) > 0) {
			$msg = '<table border="0" cellspacing="1" cellpadding="0"><tr>';
			$msg .= implode('</tr><tr>', $result) . '</tr></table>';
		} else {
			$msg = '�L���b�V������';
		}
	}
	
	//�摜�L���b�V�����X�V
	elseif ($_GET['mode'] == 'reload') {
		$kanban = getSignboard($ptitle_url, 2, 0);
		//�Ŕ̃`�F�b�N
		if (strstr($kanban['image'], $datdir_bbs.'/')) {
			$tmpmsg = '<td class="tdleft"><b>�� �ŔL���b�V������</b></td>';
			$tmpmsg .= '<td class="tdcont">' . realpath($kanban['image']) . '</td>';
		} elseif (substr($kanban['image'], 0, 7) == 'http://') {
			$tmpmsg = '<td class="tdleft"><b>�� �Ŕ̓I�����C��</b></td>';
			$tmpmsg .= '<td class="tdcont">' . $kanban['image'] . '</td>';
		} else {
			$tmpmsg = '<td class="tdleft"><b>�~ �ŔȂ�</b></td><td class="stabus"></td>';
		}
		array_push($result, $tmpmsg);
		//�w�i�̃`�F�b�N
		if (strstr($kanban['background'], $datdir_bbs.'/')) {
			$tmpmsg = '<td class="tdleft"><b>�� �w�i�L���b�V������</b></td>';
			$tmpmsg .= '<td class="tdcont">' . realpath($kanban['background']) . '</td>';
		} elseif (substr($kanban['background'], 0, 7) == 'http://') {
			$tmpmsg = '<td class="tdleft"><b>�� �w�i�̓I�����C��</b></td>';
			$tmpmsg .= '<td class="tdcont">' . $kanban['background'] . '</td>';
		} else {
			$tmpmsg = '<td class="tdleft"><b>�~ �w�i�Ȃ�</b></td><td class="tdcont"></td>';
		}
		array_push($result, $tmpmsg);
		$msg = '<table border="0" cellspacing="1" cellpadding="0"><tr>';
		$msg .= implode('</tr><tr>', $result) . '</tr></table>';
	}
	
	//�����擾
	elseif ($_GET['mode'] == 'info') {
		$kanban = getSignboard($ptitle_url, $_exconf['kanban']['cache'], 0);
	}
	
	//�{�^���̍쐬
	if ($_GET['mode'] == 'info') {
		$closetimer_js = '';
		$body_onload = '';
		$button = sprintf($button_tpl, 'clisebutton', '�E�C���h�E�����', "window.close()");
	} else {
		$closetimer_js = '<script type="text/javascript" src="./js/closetimer.js"></script>';
		$body_onload = " onload=\"startTimer(document.getElementById('timerbutton'))\"";
		$button = sprintf($button_tpl, 'timerbutton', 'Close Timer', "stopTimer(document.getElementById('timerbutton'))");
	}

}


//�w�i�̐ݒ�
if ($kanban['background']) {
	$background = 'background-image: url("' . $kanban['background'] . '") !important;';
}
if ($kanban['bgcolor']) {
	$bgcolor = 'background-color: ' . $kanban['bgcolor'] . ' !important;';
} else {
	$bgcolor = 'background-color: #FFFFFF !important;';
}

//�����e�[�u���ɓW�J
$msg = '';
if (is_array($kanban_info) && count($kanban_info) > 0) {
	$msg .= P2Util::Info_Dump($kanban_info, 1);
}

//���b�Z�[�W���̍쐬
if ($msg) { $msg = "<div class=\"info\">{$msg}</div>"; }

//�{�^�����̍쐬
if ($button) { $button = "<div class=\"button\">{$button}</div>"; }

//HTML���o��
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOH
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$kanban['title']}</title>
	<base target="{$_exconf['kanban']['target_frame']}">
	<link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
	<link rel="stylesheet" href="css.php?css=info&amp;skin={$skin_en}" type="text/css">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<style type="text/css" media="all">
	body {
		color: #000000;
		{$bgcolor}
		{$background}
		text-align: center;
	}
	a { text-decoration: none; }
	a:link { color: #0000FF; }
	a:visited { color: #0000FF; }
	a:active { color: #FF0000; }
	a:hover { color: #FF0000; }
	p { margin: 0; }
	pre { margin: 0; text-align: left; }
	table { margin: 0 auto; border-width: 0; padding: 0; }
	table.child {
		margin: 0;
		padding: 0;
		border-top: 1px solid #DDDDDD;
		border-right: 1px solid #555555;
		border-bottom: 1px solid #555555;
		border-left: 1px solid #DDDDDD;
	}
	td { marign 1px; text-align: left; }
	table.child tr.setting td { font-size: 9px; }
	td.tdleft {
		text-align: right;
		vertical-align: top;
	}
	td.tdcont, td#rule {
		color: #000000;
		text-align: left;
		vertical-align: middle;
	}
	div.info {
		margin: 10px auto;
		padding: 5px;
		border-top: 1px solid #DDDDDD;
		border-right: 1px solid #555555;
		border-bottom: 1px solid #555555;
		border-left: 1px solid #DDDDDD;
		color: #000000;
		background-color: #FFFFFF;
	}
	span.colorset { border:1px #808080 solid; }
	div.button { margin: 10px auto; }
	</style>\n
EOH;

if (isset($MYSTYLE) && is_array($MYSTYLE)) {
	include_once (P2_STYLE_DIR . '/mystyle_css.php');
	disp_mystyle(array('info', 'kanban'));
}

echo <<<EOF
	<script type="text/javascript" src="js/basic.js"></script>
	{$closetimer_js}
</head>
<body{$body_onload}>
<h1>{$info_ab}<img src="{$kanban['image']}" alt="{$kanban['title']}">{$info_ae}</h1>
{$msg}
{$button}
</body>
</html>
EOF;
?>
