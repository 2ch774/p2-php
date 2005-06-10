<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2�@�\�g���p�b�N - �A�N�e�B�u���i�[�E�N���X

$GLOBALS['_ACTIVEMONA_INSTANCES'] = array();

class ActiveMona
{
	var $activemona;
	var $automona;

	var $aaita;
	var $noaaita;

	var $mona;

	var $thresholdA;
	var $thresholdB;
	var $thresholdC;

	var $regexA;
	var $regexB;
	var $regexC;

	var $noAAchars;
	var $noalnum;
	var $keisen;
	var $kigou;

	/**
	 * �R���X�g���N�^
	 */
	function ActiveMona($config)
	{
		$this->activemona = $config['*'];
		$this->automona = $config['auto_monafont'];

		$this->aaita = preg_quote($config['aaita'], '/');
		$this->noaaita = preg_quote($config['auto_noaaita'], '/');

		// ��������ɗ��p�����P��\�������䗦��臒l
		$this->thresholdA = $config['thresholdA']; // AA�炵���p�^�[���Ƀ}�b�`����Ƃ�
		$this->thresholdB = $config['thresholdB']; // AA���ۂ��p�f�B���O����Ă���Ƃ�
		$this->thresholdC = $config['thresholdC']; // �Œ჉�C��

		// ��AA�\�������i�������K���j
		$this->noAAchars = ' 0-9A-Za-z/.,:;+\-\'=!?';
		$this->noAAchars .= '\xa1-\xdd'; // ���p�J�i�E�-�
		$this->noAAchars .= '�@�O-�X�`-�y��-����-��@-���^�E�B�A�F�G�{�[���I�H����';

		// ASCII�͈̔͂ŃA���t�@�x�b�g�E�����ȊO
		$this->noalnum = '\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f';
		// �r��
		$this->keisen = '����������������������������������������������������������������';
		// �L����
		$this->kigou = '�@�A�B�C�D�E�F�G�H�I�J�K�L�M�N�O�P�Q�R�S�T�U�V�W�X�Y�Z�[�\�]�^�_';
		$this->kigou .= '�`�a�b�c�d�e�f�g�h�i�j�k�l�m�n�o�p�q�r�s�t�u�v�w�x�y�z�{�|�}�~';
		$this->kigou .= '����������������������������������������������������������������';
		$this->kigou .= '�������������������������������������������ȁɁʁˁ́́΁ځہ܁݁ށ�';
		$this->kigou .= '������������������������';

		// 1~3������AA�\�������̃p�^�[����3��A������
		$this->regexA = '([^' . $this->noAAchars . ']{1,3})\\1\\1';
		// AA�̃p�f�B���O�ɂ悭�p������p�^�[��
		$this->regexB = '�@�@�@|�@ �@| �@ ';
		// ���{��Ή��̔�P��\�������i���Ȃ肢��������j
		$this->regexC = '[' . $this->noalnum . $this->keisen . $this->kigou . ']';

		// ���i�[�t�H���g�\���X�C�b�`
		// "%1\$s"��sprintf�Œu�������
		$this->mona = "<span class=\"aMonaSW\">�i";
		$this->mona .= "<span onclick=\"activeMona('%1\$s','12px');\">�L</span>";
		$this->mona .= "<span onclick=\"activeMona('%1\$s','14px');\">��</span>";
		$this->mona .= "<span onclick=\"activeMona('%1\$s','16px');\">�M</span>";
		$this->mona .= "�j</span>";
	}

	/**
	 * �V���O���g���p�^�[�����g��
	 *
	 * @return object
	 */
	function &singleton($config)
	{
		$key = md5(serialize($config));
		if (!isset($GLOBALS['_ACTIVEMONA_INSTANCES'][$key]) ||
			!is_object($GLOBALS['_ACTIVEMONA_INSTANCES'][$key]) ||
			!is_a($GLOBALS['_ACTIVEMONA_INSTANCES'][$key], 'ActiveMmona')
		) {
			$GLOBALS['_ACTIVEMONA_INSTANCES'][$key] = &new ActiveMona($config);
		}
		return $GLOBALS['_ACTIVEMONA_INSTANCES'][$key];
	}

	/**
	 * �ݒ�ɉ�����AA����ƃ��i�[�t�H���g�\���X�C�b�`�������s��
	 *
	 * @return string
	 */
	function transAM(&$msg, $id, $bbs)
	{
		// ������
		$bbsregexp = '/(^|,)' . preg_quote($bbs, '/') . '(,|$)/';
		$returnMona = FALSE;
		$autoMona = FALSE;
		//�ꕔAA�̕��������␳
		$msg = str_replace('�A�A', '�@', $msg);
		// �s���s���̋󔒕���������
		$msg = preg_replace('/(^|\s+)(<div id="\w+">|<br ?\/?>)\s+/i', '$2', $msg);

		// ���(�L�́M)��\������
		if ($this->activemona == 1) {
			$returnMona = TRUE;
			// AA�n�̔��H
			if ($this->automona && preg_match($bbsregexp, $this->aaita)) {
				$autoMona = TRUE;
			}
		// AA�Ɣ��肳�ꂽ�Ƃ�����(�L�́M)��\������
		} elseif ($this->activemona >= 2 && preg_match('/<br( \/)?>/i', $msg)) {
			$returnMona = $this->detectAA($msg);
			// �������i�[�t�H���g�\��������H
			if ($returnMona && $this->automona == 2 && !preg_match($bbsregexp, $this->noaaita)) {
				$autoMona = TRUE;
			}
		}

		if ($autoMona) {
			$this->autoMona($msg);
		}

		if ($returnMona) {
			return $this->getMona($id);
		} else {
			return "";
		}
	}

	/**
	 * ���i�[�t�H���g�\���X�C�b�`�𐶐�
	 *
	 * @return string
	 */
	function getMona($id)
	{
		return sprintf($this->mona, $id);
	}

	/**
	 * �������i�[�t�H���g�\��
	 *
	 * @return void
	 */
	function autoMona(&$msg)
	{
		$msg = preg_replace('/^\s*<div/', '<div class="AutoMona"', $msg);
	}

	/**
	 * AA����
	 *
	 * @return boolean
	 */
	function detectAA(&$msg)
	{
		if ($this->activemona == 3) {
			return $this->detectAAbyThreshold($msg);
		} else {
			return $this->detectAAbyPattern($msg);
		}
	}

	/**
	 * �p�^�[���}�b�`�ɂ��AA����
	 *
	 * @return boolean
	 */
	function detectAAbyPattern(&$msg)
	{
		if (mb_ereg($this->regexA, $msg) || mb_ereg($this->regexB, $msg)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * �p�^�[���}�b�`�ɉ����A�P��\�������䗦���l������AA����
	 *
	 * @return boolean
	 */
	function detectAAbyThreshold(&$msg)
	{
		// $msg����^�O�Ǝ��̎Q�ƁE���l�����Q�Ƃ�����
		$rawmsg = mb_ereg_replace('&#?[0-9A-Za-z]+;', '', strip_tags($msg));
		// $rawmsg�����P��\������������
		$wcm = mb_ereg_replace($this->regexC, '', $rawmsg);
		// �P��\��������
		$wcc = mb_strlen($wcm);
		// ������
		$len = mb_strlen($rawmsg);
		// �P��\�������䗦
		$ratio = ($len > 0) ? round($wcc / $len * 100) : 100;

		if ($ratio < $this->thresholdC ||
			($ratio < $this->thresholdA && mb_ereg($this->regexA, $msg)) ||
			($ratio < $this->thresholdB && mb_ereg($this->regexB, $msg))
		) {
			return TRUE;
		}
		return FALSE;
	}

}

?>
