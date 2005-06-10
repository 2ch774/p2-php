<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

//�ݒ�t�@�C��
if (file_exists($setting_file)) {
	$setting_lastmod = date('Y/m/d H:i:s', filemtime($setting_file));
	if (isset($setting_res)) {
		$setting_exists = "{$setting_res->code} {$setting_res->message}";
	} elseif (!isset($setting_exists) || $setting_exists === TRUE) {
		$setting_exists = 'No Renewal';
	}
	if (!isset($setting)) {
		//�ݒ�����擾
		$setting = parse_setting_txt($setting_file, $setting_cache, $_exconf['kanban']['cache']);
	}
} else {
	$setting_exists = 'Not Found';
	$setting_lastmod = NULL;
}

//���[�J�����[��
if (file_exists($rule_file)) {
	$rule_lastmod = date('Y/m/d H:i:s', filemtime($rule_file));
	if (isset($rule_res)) {
		$rule_exists = "{$rule_res->code} {$rule_res->message}";
	} elseif (!isset($rule_exists) || $rule_exists === TRUE) {
		$rule_exists = 'No Renewal';
	}
	if (!$local_rule) {
		//���[�J�����[�����擾
		$local_rule = parse_head_txt($rule_file, $rule_cache, $_exconf['kanban']['cache']);
	}
} else {
	$rule_exists = 'Not Found';
	$rule_lastmod = NULL;
}
if (!$local_rule) { $_exconf['kanban']['disp_rule'] = 0; } //head.txt���󂩁A���݂��Ȃ��ꍇ�̓��[�J�����[����\�����Ȃ�

//�ŔƔw�i�摜
if ($kb_src == $kb_url) {
	$kb_exists = "���擾�i{$kb_src}�j";
	$kb_lastmod = NULL;
} else {
	$kb_exists = '�擾�ρi' . basename($kb_path) . '�j';
	$kb_lastmod = date('Y/m/d H:i:s', filemtime($kb_path));
}
if ($bg_src == $bg_url) {
	$bg_exists = "���擾�i{$bg_src}�j";
	$bg_lastmod = NULL;
} else {
	$bg_exists = '�擾�ρi' . basename($bg_path) . '�j';
	$bg_lastmod = date('Y/m/d H:i:s', filemtime($bg_path));
}

if (isset($_GET['mode']) && $_GET['mode'] == 'info') {
	//���O(dat)�̐����J�E���g����B�܂��w�����������ꍇ�A���O(dat,idx)�̍폜���B
	$dats = 0;
	$dirObj = dir($datdir_bbs);
	while (($ent = $dirObj->read()) !== FALSE) {
		$file = $datdir_bbs . '/' . $ent;
		if (preg_match('/^(\d+)\.(dat(\.gz)?|idx)$/i', $ent, $matches)) {
			$pdat = $datdir_bbs . '/p2_parsed_dat/' . $matches[1] . '.pdat';
			if (!empty($_GET['remove_all_dat'])) {
				unlink($file);
				if (file_exists($pdat)) {
					unlink($pdat);
				}
			} elseif (!empty($_GET['remove_old_dat']) && ((time() - filemtime($file)) > (30 * 86400))) {
				unlink($file);
				if (file_exists($pdat)) {
					unlink($pdat);
				}
			} elseif (preg_match('/^\d+\.dat(\.gz)?$/i', $ent)) {
				$dats++;
			}
		} elseif (preg_match('/\.(gif|jpe?g|png)$/i', $ent)) {
			unlink($file); //���Ŕ|�b�v�A�b�v�E�L���b�V�����폜
		}
	}

	//dat�폜�̊m�F�E�C���h�E���쐬
	global $o_link;
	$onclick_mode = 'info';
	$link = str_replace('{#mode#}', $onclick_mode, $o_link);
	$onclick_rmall = "if (confirm('�{���� &quot;{$kanban['title']}&quot; �̑S���O���폜���Ă�낵���ł����H')) location.href='{$link}&amp;remove_all_dat=1';";
	$remove_all = "<a href=\"javascript:;\" onclick=\"{$onclick_rmall}\">�S�Ẵ��O���폜</a>";
	$onclick_rmold = "if (confirm('�{���� &quot;{$kanban['title']}&quot; �̌Â����O���폜���Ă�낵���ł����H')) location.href='{$link}&amp;remove_old_dat=1';";
	$remove_old = "<a href=\"javascript:;\" onclick=\"{$onclick_rmold}\">�Â����O���폜</a>";

	//dat���ƍ폜�����N
	$dats = strval($dats) . " <small>[{$remove_all}] [{$remove_old}]</small>";

	//�ڍ׏��
	$kanban_info = array(
		'���' => array('��' => $kanban['title'], '��URL' => $ptitle_url,
			'���O�ۑ���' => realpath($datdir_bbs) . '/', '���O�擾��<br>�X���b�h��' => $dats,
			'�Ŕ�' => $kb_exists, '�w�i�摜' => $bg_exists, '�w�i�F' => $kanban['bgcolor']),
		'���[�J�����[��' => $local_rule, '�̐ݒ�' => $setting,
		'�Ŕ\�[�XURL' => $kb_src, '�Ŕ����NURL' => $kb_url,
		'�w�i�\�[�XURL' => $bg_src, '�w�i�����NURL' => $bg_url,
		'�Ŕ�Cache�X�V' => $wap_res_kb, '�V �m�F����' => $kb_lastmod,
		'�w�iCache�X�V' => $wap_res_bg, ' �V �m�F����' => $bg_lastmod,
		'SETTING.TXT�X�V' => $setting_exists, '  �V �m�F����' => $setting_lastmod,
		'head.txt�X�V' => $rule_exists, '   �V �m�F����' => $rule_lastmod);
} else {
	if ($_GET['mode'] == 'delete' || $_GET['mode'] == 'reload') {
		$_exconf['kanban']['disp_rule'] = 0;
	}
	$kanban_info = array();
	//���[�J�����[��
	if ($_exconf['kanban']['disp_rule']) {
		if ($_exconf['kanban']['disp_img_result'] || $_exconf['kanban']['disp_file_result']) {
			$kanban_info['���[�J�����[��'] = $local_rule;
		} else {
			$kanban_info = array($local_rule); //�P�ƕ\���̂Ƃ�
		}
	}
	//�摜�L���b�V���̍X�V�Ɋւ����
	if ($_exconf['kanban']['disp_img_result']) {
		$kanban_info['�Ŕ\�[�XURL'] = $kb_src;
		$kanban_info['�Ŕ����NURL'] = $kb_url;
		$kanban_info['�w�i�\�[�XURL'] = $bg_src;
		$kanban_info['�w�i�����NURL'] = $bg_url;
		$kanban_info['�Ŕ�Cache�X�V'] = $wap_res_kb;
		$kanban_info['�V �m�F����'] = $kb_lastmod;
		$kanban_info['�w�iCache�X�V'] = $wap_res_bg;
		$kanban_info[' �V �m�F����'] = $bg_lastmod;
	}
	//�ݒ�t�@�C���̍X�V�Ɋւ����
	if ($_exconf['kanban']['disp_file_result']) {
		$kanban_info['SETTING.TXT�X�V'] = $setting_exists;
		$kanban_info['  �V �m�F����'] = $setting_lastmod;
		$kanban_info['head.txt�X�V'] = $rule_exists;
		$kanban_info['   �V �m�F����'] = $rule_lastmod;
	}
}

// GET�œn���钷���� Apache 1.3.9 �ł� 8190 �����܂ŁB
// ������z����� 414 Request-URI Too Large �ƂȂ�̂Ń`�F�b�N����B
if ($return_popup && count($kanban_info) > 0) {
	$popup_test_array = $kanban;
	$popup_test_array['info'] = $kanban_info;
	$popup = makePopUpURL($popup_test_array, $datdir_host, $bbs, $ptitle_url);
	if (strlen($popup) > 8000) {
		$popup = makePopUpURL($kanban, $datdir_host, $bbs, $ptitle_url);
	}
}
?>
