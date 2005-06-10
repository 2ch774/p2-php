<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
	p2 -  �X���b�h�T�u�W�F�N�g�\���X�N���v�g
	�t���[��������ʁA�E�㕔��

	�V������m�邽�߂Ɏg�p���Ă���	// $shinchaku_num, $_newthre_num ���Z�b�g

	subject.php �ƌZ��Ȃ̂ňꏏ�ɖʓ|���݂�
*/

require_once 'conf/conf.php';	// �ݒ�
require_once (P2_LIBRARY_DIR . '/p2util.class.php');	// p2�p�̃��[�e�B���e�B�N���X
require_once (P2_LIBRARY_DIR . '/threadlist.class.php');	// �X���b�h���X�g �N���X
require_once (P2_LIBRARY_DIR . '/thread.class.php');	// �X���b�h �N���X
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

$shinchaku_attayo = false;
$shinokini_attayo = false;
$shinchaku_num = 0;
$shinokini_num = 0;

if (isset($aThreadList)) { unset($aThreadList); }

/*
$debug = false;
$debug && require_once 'Benchmark/Profiler.php';
$debug && $prof = &new Benchmark_Profiler(true);
*/

authorize(); // ���[�U�F��

//============================================================
// ���ϐ��ݒ�
//============================================================

// ���[�h�ݒ� =================================
$sb_disp_from = isset($_REQUEST['from']) ? $_REQUEST['from'] : 1;

// �� p2_setting �ݒ� ======================================
if ($spmode) {
	$p2_setting_txt = $GLOBALS['prefdir'].'/p2_setting_'.$spmode.'.txt';
} else {
	$datdir_host = P2Util::datdirOfHost($host);
	$p2_setting_txt = $datdir_host.'/'.$bbs.'/p2_setting.txt';
	$sb_keys_b_txt = $datdir_host.'/'.$bbs.'/p2_sb_keys_b.txt';
	$sb_keys_txt = $datdir_host.'/'.$bbs.'/p2_sb_keys.txt';

	if (!empty($_REQUEST['norefresh']) || !empty($_REQUEST['word'])) {
		if (file_exists($sb_keys_b_txt) && ($prepre_sb_cont = file_get_contents($sb_keys_b_txt))) {
			$prepre_sb_keys = unserialize($prepre_sb_cont);
		}
	} else {
		if (file_exists($sb_keys_txt) && ($pre_sb_cont = file_get_contents($sb_keys_txt))) {
			$pre_sb_keys = unserialize($pre_sb_cont);
		}
	}

}

// ��p2_setting �ǂݍ���
if (file_exists($p2_setting_txt) && ($p2_setting_cont = file_get_contents($p2_setting_txt))) {
	$p2_setting = unserialize($p2_setting_cont);
	foreach ($p2_setting as $pre_key => $pre_value) {
		${$pre_key.'_pre'} = $pre_value;
	}
}

if (isset($_REQUEST['sb_view'])) { $sb_view = $_REQUEST['sb_view']; }
if (empty($sb_view)) { $sb_view = 'normal'; }

if (isset($_REQUEST['viewnum'])) { $p2_setting['viewnum'] = $_REQUEST['viewnum']; }
if (empty($p2_setting['viewnum'])) { $p2_setting['viewnum'] = $GLOBALS['_conf']['display_threads_num']; } // �f�t�H���g�l

if (isset($_REQUEST['sort'])) { $p2_setting['sort']  = $_REQUEST['sort']; }

// �\�[�g�̃f�t�H���g�w��
if (empty($p2_setting['sort'])) {
	if ($spmode == 'news') {
		$p2_setting['sort'] = 'ikioi';
	} else {
		$p2_setting['sort'] = 'midoku';
	}
}

if (isset($_GET['itaj_en'])) { $p2_setting['itaj'] = base64_decode($_GET['itaj_en']); }

// ���ݎ��� ====================================
$nowtime = time();

//============================================================
// �����C��
//============================================================

$aThreadList = &new ThreadList;

// ���ƃ��[�h�̃Z�b�g ===================================
if ($spmode) {
	if ($spmode == 'taborn' || $spmode == 'soko') {
		$aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
	}
	$aThreadList->setSpMode($spmode);
} else {
	//if (!$p2_setting['itaj']) { $p2_setting['itaj'] = P2Util::getItaName($host, $bbs); }
	$aThreadList->setIta($host, $bbs, $p2_setting['itaj']);

	// ���X���b�h���ځ[�񃊃X�g�Ǎ� ===================================
	$datdir_host = P2Util::datdirOfHost($aThreadList->host);
	$taborn_idx = $datdir_host.'/'.$aThreadList->bbs.'/p2_threads_aborn.idx';

	if (file_exists($taborn_idx) && ($tabornlines = file($taborn_idx))) {
		$ta_num = sizeof($tabornlines);
		foreach ($tabornlines as $l) {
			$l = rtrim($l);
			$data = explode('<>', $l);
			$ta_keys[ $data[1] ] = true;
		}
	} else {
		$ta_num = 0;
	}

}

// ���\�[�X���X�g�Ǎ� ==================================
$lines = $aThreadList->readList();

// �����C�ɃX�����X�g �Ǎ�
if (file_exists($GLOBALS['favlistfile']) && ($favlines = file($GLOBALS['favlistfile']))) {
	foreach ($favlines as $l) {
		$l = rtrim($l);
		$data = explode('<>', $l);
		$fav_keys[ $data[1] ] = true;
	}
}

//============================================================
// �����ꂼ��̍s���
//============================================================

$linesize = sizeof($lines);

for ($x = 0; $x < $linesize ; $x++) {

	$l = rtrim($lines[$x]);

	$aThread = &new Thread;

	if ($aThreadList->spmode != 'taborn' and $aThreadList->spmode != 'soko') {
		$aThread->torder = $x + 1;
	}

	// ���f�[�^�ǂݍ���
	// spmode
	if ($aThreadList->spmode) {
		switch ($aThreadList->spmode) {
		case 'recent':	// ����
			$aThread->getThreadInfoFromExtIdxLine($l);
			$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
			if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }
			break;
		case 'res_hist':	// �������ݗ���
			$aThread->getThreadInfoFromExtIdxLine($l);
			$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
			if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }
			break;
		case 'fav':	// ���C��
			$aThread->getThreadInfoFromExtIdxLine($l);
			$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
			if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }
			break;
		case 'taborn':	// �X���b�h���ځ[��
			$la = explode('<>', $l);
			$aThread->key = $la[1];
			$aThread->host = $aThreadList->host;
			$aThread->bbs = $aThreadList->bbs;
			break;
		case 'soko':	// dat�q��
			$la = explode('<>', $l);
			$aThread->key = $la[1];
			$aThread->host = $aThreadList->host;
			$aThread->bbs = $aThreadList->bbs;
			break;
		case 'palace':	// �X���̓a��
			$aThread->getThreadInfoFromExtIdxLine($l);
			$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
			if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }
			break;
		case 'news':	// �j���[�X�`�F�b�N
			$aThread->isonline = true;
			$aThread->key = $l['key'];
			$aThread->setTtitle($l['ttitle']);
			$aThread->rescount = $l['rescount'];
			$aThread->host = $l['host'];
			$aThread->bbs = $l['bbs'];

			$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
			if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }
			break;
		}

	// subject (not spmode)
	} else {
		$aThread->getThreadInfoFromSubjectTxtLine($l);
		$aThread->host = $aThreadList->host;
		$aThread->bbs = $aThreadList->bbs;
		if (!empty($_REQUEST['norefresh']) || !empty($_REQUEST['word'])) {
			if (!$prepre_sb_keys[$aThread->key]) { $aThread->new = true; }
		} else {
			if (!$pre_sb_keys[$aThread->key]) { $aThread->new = true; }
			$subject_keys[$aThread->key] = true;
		}
	}

	//host��bbs��key���s���Ȃ�X�L�b�v
	if (!($aThread->host && $aThread->bbs && $aThread->key)) {
		unset($aThread);
		continue;
	}

	// ���X���b�h���ځ[��`�F�b�N =====================================
	//$debug && $prof->enterSection('taborn_check_continue');
	if ($aThreadList->spmode != 'taborn' && isset($ta_keys[$aThread->key])) {
		unset($ta_keys[$aThread->key]);
		//$debug && $prof->leaveSection('taborn_check_continue');
		continue; // ���ځ[��X���̓X�L�b�v
	}
	//$debug && $prof->leaveSection('taborn_check_continue');

	$aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
	// �����X���b�h�f�[�^��idx����擾
	$aThread->getThreadInfoFromIdx($aThread->keyidx);


	//$debug && $prof->enterSection('favlist_check');
	// favlist�`�F�b�N =====================================
	//if ($x <= $threads_num) {
		if ($aThreadList->spmode != 'taborn' && isset($fav_keys[$aThread->key])) {
			$aThread->fav = 1;
			unset($fav_keys[$aThread->key]);
		}
	//}
	//$debug && $prof->leaveSection('favlist_check');

	// �� spmode(�a������Anews������)�Ȃ� ====================================
	if ($aThreadList->spmode && $aThreadList->spmode != 'news' && $sb_view != 'edit') {

		// subject.txt����DL�Ȃ痎�Ƃ��ăf�[�^��z��Ɋi�[
		if (!$subject_txts["$aThread->host/$aThread->bbs"]) {
			$datdir_host = P2Util::datdirOfHost($aThread->host);
			$subject_url = "http://{$aThread->host}/{$aThread->bbs}/subject.txt";
			$subjectfile = "{$datdir_host}/{$aThread->bbs}/subject.txt";
			FileCtl::mkdir_for($subjectfile); //�f�B���N�g����������΍��
			P2Util::subjectDownload($subject_url, $subjectfile);

			//$debug && $prof->enterSection('subthre_read');
			if ($aThreadList->spmode == 'soko' || $aThreadList->spmode == 'taborn') {

				if (extension_loaded('zlib') && strstr($aThread->host, ".2ch.net")) {
					$sblines = gzfile($subjectfile);
				} else {
					$sblines = file($subjectfile);
				}
				if ($sblines) {
					$it = 1;
					foreach ($sblines as $asbl) {
						if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $asbl, $matches)) {
							$akey = $matches[1];
							$subject_txts["$aThread->host/$aThread->bbs"][$akey]['ttitle'] = rtrim($matches[4]);
							$subject_txts["$aThread->host/$aThread->bbs"][$akey]['rescount'] = $matches[6];
							$subject_txts["$aThread->host/$aThread->bbs"][$akey]['torder'] = $it;
						}
						$it++;
					}
				}

			} else {

				if (extension_loaded('zlib') && strstr($aThread->host, ".2ch.net")) {
					$subject_txts["$aThread->host/$aThread->bbs"] = gzfile($subjectfile);
				} else {
					$subject_txts["$aThread->host/$aThread->bbs"] = file($subjectfile);
				}

			}
			//$debug && $prof->leaveSection('subthre_read');
		}

		//$debug && $prof->enterSection('subthre_check');
		// ���X�����擾 =============================
		if ($aThreadList->spmode == 'soko' || $aThreadList->spmode == 'taborn') {

			if ($subject_txts[$aThread->host."/".$aThread->bbs][$aThread->key]) {

				// �q�ɂ̓I�����C�����܂܂Ȃ�
				if ($aThreadList->spmode == 'soko') {
					unset($aThread);
					continue;
				} elseif ($aThreadList->spmode == 'taborn') {
					// subject.txt ����X�����擾
					//$aThread->getThreadInfoFromSubjectTxtLine($l);
					$aThread->isonline = true;
					$aThread->setTtitle($ttitle);
					$aThread->rescount = $subject_txts["$aThread->host/$aThread->bbs"][$aThread->key]['rescount'];
					$aThread->rescount = $subject_txts["$aThread->host/$aThread->bbs"][$aThread->key]['rescount'];
					if ($aThread->readnum) {
						$aThread->unum = $aThread->rescount - $aThread->readnum;
						// machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
						if ($aThread->unum < 0) { $aThread->unum = 0; }
					}
					$aThread->torder = $subject_txts["$aThread->host/$aThread->bbs"][$aThread->key]['torder'];
				}

			}

		} else {

			if ($subject_txts[$aThread->host."/".$aThread->bbs]) {
				$it = 1;
				foreach ($subject_txts[$aThread->host."/".$aThread->bbs] as $l) {
					if (@preg_match("/^{$aThread->key}/",$l)) {
						// subject.txt ����X�����擾
						$aThread->getThreadInfoFromSubjectTxtLine($l);
						break;
					}
					$it++;
				}
			}

		}
		//$debug && $prof->leaveSection('subthre_check');

		if ($aThreadList->spmode == 'taborn') {
			if (!$aThread->torder) { $aThread->torder = '-'; }
		}


		// ���V���̂�(for spmode) ===============================
		if ($sb_view == 'shinchaku' && ! $_GET['word']) {
			if ($aThread->unum < 1) {
				unset($aThread);
				continue;
			}
		}

	}

	// subject����rescount�����Ȃ������ꍇ�́Agotnum�𗘗p����B
	if ((!$aThread->rescount) and $aThread->gotnum) {
		$aThread->rescount = $aThread->gotnum;
	}
	if (!$aThread->ttitle_ht) { $aThread->ttitle_ht = $aThread->ttitle_hd; }

	// �V������
	if ($aThread->unum > 0) {
		$shinchaku_attayo = true;
		$shinchaku_num += $aThread->unum; // �V����set
		if ($aThread->fav) {
			$shinokini_attayo = true;
			$shinokini_num += $aThread->unum; // ���C�ɃX���̐V����set
		}
	// ���C�ɃX��
	} elseif ($aThread->fav) {
		;
	// �V�K�X��
	} elseif ($aThread->new) {
		$_newthre_num++; // ��showbrdmenupc.class.php
	}

	// �����̃Z�b�g
	$aThread->setDayRes($nowtime);

	unset($aThread);
}

//$shinchaku_num

?>
