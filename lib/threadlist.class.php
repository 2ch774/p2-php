<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 - �X���b�h���X�g�N���X

//=============================================================================
// ThreadList �N���X
//=============================================================================

class ThreadList{
	var $threads; //�N���XThread�̃I�u�W�F�N�g���i�[����z��
	var $num; //�i�[���ꂽThread�I�u�W�F�N�g�̐�
	var $host; // ex)pc.2ch.net
	var $bbs; // ex)mac
	var $itaj; // �� ex)�V�Emac��
	var $itaj_hd;	// HTML�\���p�ɁA���� htmlspecialchars() ��������
	var $spmode; //���ʔȊO�̃X�y�V�������[�h
	var $ptitle; //�y�[�W�^�C�g��

	/**
	 * �R���X�g���N�^
	 */
	function ThreadList()
	{
		$this->threads = array();
		$this->num = 0;
	}

	//==============================================
	function setSpMode($name)
	{
		global $_conf;
		if ($name == 'recent') {
			$this->spmode = $name;
			$this->ptitle = $_conf['ktai'] ? '�ŋߓǂ񂾽�' : '�ŋߓǂ񂾃X��';
		} elseif ($name == 'res_hist') {
			$this->spmode = $name;
			$this->ptitle = '�������ݗ���';
		} elseif ($name == 'fav') {
			$this->spmode = $name;
			$this->ptitle = $_conf['ktai'] ? '���C�ɽ�' : '���C�ɃX��';
		} elseif ($name == 'taborn') {
			$this->spmode = $name;
			$this->ptitle = $_conf['ktai'] ? "$this->itaj (���ݒ�)" : "$this->itaj (���ځ[��)";
		} elseif ($name == 'soko') {
			$this->spmode = $name;
			$this->ptitle = $this->itaj.' (dat�q��)';
		} elseif ($name == 'palace') {
			$this->spmode = $name;
			$this->ptitle = $_conf['ktai'] ? '�ڂ̓a��' : '�X���̓a��';
		} elseif ($name == 'news') {
			$this->spmode = $name;
			$this->ptitle = $_conf['ktai'] ? 'ƭ������' : '�j���[�X�`�F�b�N';
		}
	}

	/**
	 * �� �����I�ɔ��ihost, bbs, ���j���Z�b�g����
	 */
	function setIta($host, $bbs, $itaj = "")
	{
		$this->host = $host;
		$this->bbs = $bbs;
		$this->setItaj($itaj);

		return true;
	}

	/**
	 * �������Z�b�g����
	 */
	function setItaj($itaj)
	{
		if ($itaj) {
			$this->itaj = $itaj;
		} else {
			$this->itaj = $this->bbs;
		}
		$this->itaj_hd = htmlspecialchars($this->itaj);
		$this->ptitle = $this->itaj;

		return true;
	}

	/**
	 * �� readList ���\�b�h
	 */
	function readList()
	{
		global $_conf, $datdir, $word_fm, $debug, $prof, $_info_msg_ht;

		$lines = array();

		if ($this->spmode) {

			// ���[�J���̗����t�@�C�� �ǂݍ���
			if ($this->spmode == 'recent') {
				if ($lines = @file($_conf['rct_file'])) {
					//$_info_msg_ht = '<p>�����͋���ۂł�</p>';
					//return false;
				}

			} elseif ($this->spmode == 'res_hist') { //���[�J���̏������ݗ����t�@�C�� �ǂݍ���
				$rh_idx = $_conf['pref_dir']."/p2_res_hist.idx";
				if (!file_exists($rh_idx) || !($lines = file($rh_idx))) {
					//$_info_msg_ht = '<p>�������ݗ����͋���ۂł�</p>';
					//return false;
				}

			// ���[�J���̂��C�Ƀt�@�C�� �ǂݍ���
			} elseif ($this->spmode == 'fav') {
				if (!file_exists($_conf['favlist_file']) || !($lines = file($_conf['favlist_file']))) {
					//$_info_msg_ht = '<p>���C�ɃX���͋���ۂł�</p>';
					//return false;
				}

			// �j���[�X�n�T�u�W�F�N�g�ǂݍ���
			} elseif ($this->spmode == 'news') {

				$news = array();
				$newsbbslist = array(
					'newsplus' => '�j���[�X����+',
					'liveplus' => '�j���[�X����',
					'bizplus'  => '�r�W�l�Xnews+',
					'news'     => '�j���[�X����',
					'news2'    => '�j���[�X�c�_',
				);

				// �z�X�g����
				$hostMapCache = $_conf['pref_dir'] . '/p2_host_bbs_map.txt';
				if ($newsbbslist && file_exists($hostMapCache)) {
					$hostMap = file($hostMapCache);
					$news_regexp = '/^[a-z]+[0-9]*\.2ch\.net<>(?:';
					$news_regexp .= implode('|', array_keys($newsbbslist));
					$news_regexp .= ')<>/';
					$news_command = 'return (boolean)preg_match(\''.$news_regexp.'\', $line);';
					$news_filter = create_function('$line', $news_command);
					if ($found_lines = array_filter($hostMap, $news_filter)) {
						while (($news_line = array_shift($found_lines)) !== NULL) {
							$news_part = explode('<>', rtrim($news_line));
							$news[] = array('host' => $news_part[0], 'bbs' => $news_part[1]);
						}
					}
				}

				foreach ($news as $n) {
					$subject_url = 'http://'.$n['host'].'/'.$n['bbs'].'/subject.txt';
					$subjectfile = P2Util::datdirOfHost($n['host']).'/'.$n['bbs'].'/subject.txt';

					FileCtl::mkdir_for($subjectfile); // �f�B���N�g����������΍��

					P2Util::subjectDownload($subject_url, $subjectfile);

					if (extension_loaded('zlib') && strstr($n['host'], '.2ch.net')) {
						$slines = gzfile($subjectfile);
					} else {
						$slines = file($subjectfile);
					}

					if ($slines) {
						foreach ($slines as $l) {
							$l = rtrim($l);
							if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $l, $matches)) {
								//$this->isonline = true;
								$al = array();
								$al['key'] = $matches[1];
								$al['ttitle'] = rtrim($matches[4]);
								$al['rescount'] = $matches[6];
								$al['host'] = $n['host'];
								$al['bbs'] = $n['bbs'];
								$al['itaj'] = $newsbbslist[$n['bbs']];
								$lines[] = $al;
							}
						}
					}
				}

			// p2_threads_aborn.idx �ǂݍ���
			} elseif ($this->spmode == 'taborn') {
				$aborn_idx = P2Util::datdirOfHost($this->host).'/'.$this->bbs.'/p2_threads_aborn.idx';
				if (file_exists($aborn_idx)) {
					$lines = file($aborn_idx);
				}

			// dat�q�� ======================
			} elseif ($this->spmode == 'soko') {

				$itadir = P2Util::datdirOfHost($this->host)."/".$this->bbs;
				$dat_pattern = '/([0-9]+)\.dat$/';
				$idx_pattern = '/([0-9]+)\.idx$/';
				$lines = array();

				//$debug && $prof->enterSection('dat');
				// ���O�f�B���N�g���𑖍����ČǗ�dat��idx�t�� =================
				if ($cdir = dir($itadir)) { // or die ("���O�f�B���N�g�����Ȃ���I");
					// �f�B���N�g������
					while ($entry = $cdir->read()) {
						if (preg_match($dat_pattern, $entry, $matches)) {
							$theidx = $itadir."/".$matches[1].".idx";
							if (!file_exists($theidx)) {
								if ($datlines = @file($itadir."/".$entry)) {
									$firstdatline = rtrim($datlines[0]);
									if (strstr($firstdatline, "<>")) {
										$datline_sepa = '<>';
									} else {
										$datline_sepa = ',';
									}
									$d = explode($datline_sepa, $firstdatline);
									$atitle = $d[4];
									$arnum = sizeof($datlines);
									$anewline = $arnum;
									$data = "{$atitle}<>{$matches[1]}<><>{$arnum}<><><><><><>{$anewline}";
									P2Util::recKeyIdx($theidx, $data);
								}
							}
							//array_push($lines, $idl[0]);
						}
					}
					$cdir->close();
				}

				//$debug && $prof->leaveSection('dat');

				//$debug && $prof->enterSection('idx');
				// ���O�f�B���N�g���𑖍�����idx���𒊏o���ă��X�g�� ===========
				if ($cdir = dir($itadir)) { // or die ("���O�f�B���N�g�����Ȃ���I");
					//�f�B���N�g������
					while ($entry = $cdir->read()) {
						if (preg_match($idx_pattern,$entry)) {
							$idl = file($itadir."/".$entry);
							array_push($lines, $idl[0]);
						}
					}
					$cdir->close();
				}
				//$debug && $prof->leaveSection('idx');

			// p2_palace.idx �ǂݍ���
			} elseif ($this->spmode == 'palace') {
				$palace_idx = $_conf['pref_dir']. '/p2_palace.idx';
				if (!file_exists($palace_idx) || !($lines = file($palace_idx))) {
					//$_info_msg_ht = '<p>�a���͂����ǂ��ł�</p>';
					//return false;
				}
			}

		// �I�����C����� subject.txt ��ǂݍ��ށi�m�[�}�����[�h�j
		} else {

			$datdir_host = P2Util::datdirOfHost($this->host);
			$subject_url = "http://".$this->host."/".$this->bbs."/subject.txt";
			$subjectfile = $datdir_host."/".$this->bbs."/subject.txt";

			FileCtl::mkdir_for($subjectfile); // �f�B���N�g����������΍��

			// subject�_�E�����[�h
			P2Util::subjectDownload($subject_url, $subjectfile);

			if (extension_loaded('zlib') && strstr($this->host, '.2ch.net')) {
				$lines = gzfile($subjectfile);
			} else {
				$lines = file($subjectfile);
			}

			// JBBS@������΂Ȃ�d���X���^�C���폜����
			if (P2Util::isHostJbbsShitaraba($this->host)) {
				$lines = array_unique($lines);
			}

			// be.2ch.net �Ȃ�EUC��SJIS�ϊ�
			if (P2Util::isHostBe2chNet($this->host)) {
				mb_convert_variables('SJIS-win', 'eucJP-win', $lines);
			}

		}
		return $lines;
	}

	//==============================================
	/**
	 * �� addThread ���\�b�h
	 */
	function addThread(&$aThread)
	{
		$this->threads[] = &$aThread;
		$this->num++;
		return $this->num;
	}

}

?>
