<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
	p2 - BrdCtl -- ���X�g�R���g���[���N���X for menu.php
*/

require_once (P2_LIBRARY_DIR . '/p2util.class.php');
require_once (P2_LIBRARY_DIR . '/filectl.class.php');
require_once (P2_LIBRARY_DIR . '/brdmenu.class.php');

/**
* ���X�g �R���g���[�� �N���X
*/
class BrdCtl{

	/**
	 * board��S�ēǂݍ���
	 */
	function read_brds()
	{
		$brd_menus_dir = BrdCtl::read_brd_dir();
		$brd_menus_online = BrdCtl::read_brd_online();
		if (is_array($brd_menus_dir) && is_array($brd_menus_online)) {
			$brd_menus = array_merge($brd_menus_dir, $brd_menus_online);
		} elseif (is_array($brd_menus_dir)) {
			$brd_menus = $brd_menus_dir;
		} elseif (is_array($brd_menus_online)) {
			$brd_menus = $brd_menus_online;
		} else {
			$brd_menus = array();
		}
		return $brd_menus;
	}

	/**
	 * board�f�B���N�g���𑖍����ēǂݍ���
	 */
	function read_brd_dir()
	{
		global $_info_msg_ht;

		$brd_menus = array();
		$brd_dir = './board';

		if (is_dir($brd_dir) && ($cdir = dir($brd_dir))) {
			// �f�B���N�g������
			while ($entry = $cdir->read()) {
				if (preg_match('/^\./', $entry)) {
					continue;
				}
				$filepath = $brd_dir.'/'.$entry;
				if ($data = file($filepath)) {
					$aBrdMenu = &new BrdMenu;	// �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
					$aBrdMenu->setBrdMatch($filepath);	// �p�^�[���}�b�`�`����o�^
					$aBrdMenu->setBrdList($data);	// �J�e�S���[�Ɣ��Z�b�g
					$brd_menus[] = &$aBrdMenu;
				} else {
					$_info_msg_ht .= "<p>p2 error: ���X�g {$entry} ���ǂݍ��߂܂���ł����B</p>\n";
				}
			}
			$cdir->close();
		}

		return $brd_menus;
	}

	/**
	 * �I�����C�����X�g��Ǎ���
	 */
	function read_brd_online()
	{
		global $_conf, $_info_msg_ht;

		$brd_menus = array();

		if ($_conf['brdfile_online']) {
			$cachefile = P2Util::cacheFileForDL($_conf['brdfile_online']);

			$noDL = false;
			$isNewDL = false;
			$read_html_flag = false;

			// �L���b�V��������ꍇ
			if (file_exists($cachefile.'.p2.brd')) {
				// norefresh�Ȃ�DL���Ȃ�
				if (!empty($_GET['nr'])) {
					$noDL = true;
				// �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�DL���Ȃ�
				} elseif (@filemtime($cachefile.'.p2.brd') > time() - 60 * 60 * $_conf['menu_dl_interval']) {
					$noDL = true;
				}
			}

			// DL���Ȃ�
			if ($noDL ) {
				;
			// DL����
			} else {
				//echo 'DL!<br>';//
				$brdfile_online_res = &P2Util::fileDownload($_conf['brdfile_online'], $cachefile);
				if ($brdfile_online_res->is_success() && $brdfile_online_res->code != '304') {
					$isNewDL = true;
				}
			}

			// html�`���Ȃ�
			if (preg_match('/html?$/', $_conf['brdfile_online'])) {

				// �X�V����Ă�����V�K�L���b�V���쐬
				if ($isNewDL) {
					//echo 'NEW!<br>';//
					$aBrdMenu = &new BrdMenu; // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
					$aBrdMenu->makeBrdFile($cachefile); // .p2.brd�t�@�C���𐶐�
					$brd_menus[] = &$aBrdMenu;
					$read_html_flag = true;
				}

				if (file_exists($cachefile.'.p2.brd')) {
					$cashe_brd = $cachefile.'.p2.brd';
				} else {
					$cashe_brd = $cachefile;
				}

			} else {
				$cashe_brd = $cachefile;
			}

			if (!$read_html_flag) {
				if ($data = @file($cashe_brd)) {
					$aBrdMenu = &new BrdMenu; // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
					$aBrdMenu->setBrdMatch($cashe_brd); // �p�^�[���}�b�`�`����o�^
					$aBrdMenu->setBrdList($data); // �J�e�S���[�Ɣ��Z�b�g
					if ($aBrdMenu->num) {
						$brd_menus[] = &$aBrdMenu;
					} else {
						$_info_msg_ht .=  "<p>p2 �G���[: {$cashe_brd} ������j���[�𐶐����邱�Ƃ͂ł��܂���ł����B</p>\n";
					}
				} else {
					$_info_msg_ht .=  "<p>p2 �G���[: {$cachefile} �͓ǂݍ��߂܂���ł����B</p>\n";
				}
			}

		}

		return $brd_menus;
	}

}

?>
