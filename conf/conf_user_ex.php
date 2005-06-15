<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/**
 * p2expack - ���[�U��`�p �ݒ�t�@�C��
 * �ݒ�l�̌�̃R�����g�̓f�t�H���g�l
 * ���ݒ�����ׂăf�t�H���g�ɖ߂����K�\����
 * �����F^(\$.+?) = .+?(; // \((.+?)\))$
 * �u���F\1 = \3\2
 */

/* ---------------------------------------------------------------------- */
// {{{ init

/**
 * �z���������
 */
$_exconf = array();

// }}}
/* ---------------------------------------------------------------------- */
// {{{ common

/**
 * ���ʐݒ�
 */

// �g���p�b�N�̊e�@�\��L���ɂ���i����:1, ���Ȃ�:0�j
// �����0�ɂ���ƑS�@�\��OFF�ɂȂ�B
$enable_expack = 1; // (1)

// dat�t�@�C���Asubject.txt�ȊO�̃t�@�C���_�E�����[�h���ɑ���UA�A��̎��̓u���E�U��UA�𑗐M
// �i�ŔARSS�A�C���[�W�L���b�V���Ȃǂŗ��p�j
$expack_ua= ""; // ("")

// }}}
/* ---------------------------------------------------------------------- */
// {{{ security

/**
 * �Z�L�����e�B�@�\
 *
 * �z�X�g�`�F�b�N�̏ڍאݒ�� conf/conf_hostcheck.php �ŁB
 * �������t�@�C�A�E�H�[����httpd.conf/.htaccess�̕����_��ɐݒ�ł��邵
 * �摜��conf.php�����[�h���Ȃ�php�X�N���v�g���A�N�Z�X������
 * �Ώۂɂł���̂ŁA�\�Ȃ炻�������g���ق��������B
 */

// �������݂�s�\�ɂ���i����:1, ���Ȃ�:0�j
$_exconf['secure']['read_only'] = 0; // (0)

// �������ݎ��Ƀ��t�@�����m�F����i����:1, ���Ȃ�:0�j
$_exconf['secure']['check_referer'] = 0; // (0)

// �z�X�g�`�F�b�N������ (0:���Ȃ�; 1:�w�肳�ꂽ�z�X�g�̂݋���; 2:�w�肳�ꂽ�z�X�g�̂݋���;)
$_exconf['secure']['auth_host'] = 0; // (0)

// BBQ�𗘗p���ăv���L�V���ۂ����� (0:���Ȃ�; 1:����;)
$_exconf['secure']['auth_bbq'] = 0; // (0)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ ubiquitous

/**
 * ���r�L�^�X���[�h��p�ݒ�
 *
 * �F�ݒ�� # + 6����16�i�� (��:#ff9900) �Ŏw�肷��
 */

// �C���f���g�����ƑS�p�p���E�J�i�𔼊p�ɕϊ����A�p�P�b�g�ʂ����炷�ioff:0, on:1�j
$_exconf['ubiq']['save_packet'] = 0; // (0)

// �w�i�F
$_exconf['ubiq']['c_bgcolor'] = ""; // ("")

// �����F
$_exconf['ubiq']['c_text'] = ""; // ("")

// �����N�F
$_exconf['ubiq']['c_link'] = ""; // ("")

// �K��σ����N�F
$_exconf['ubiq']['c_vlink'] = ""; // ("")

// �X���b�h�ꗗ - �V�K�X���F
$_exconf['ubiq']['c_newthre'] = ""; // ("")

// �X���b�h�ꗗ - �V�����X���F
$_exconf['ubiq']['c_unum'] = ""; // ("")

// �X���b�h���e - ���X�ԍ��F�i�V���j
$_exconf['ubiq']['c_newres'] = ""; // ("")

// �X���b�h���e - ���X�ԍ��F�i�I���U�t���C�j
$_exconf['ubiq']['c_onthefly'] = ""; // ("")

// NG���[�h�̕����F
$_exconf['ubiq']['c_ngword'] = ""; // ("")

// �t�B���^�Ƀ}�b�`���������̕����F
$_exconf['ubiq']['c_match'] = ""; // ("")

// �t�B���^�Ƀ}�b�`���������𑾎��ɂ���i���Ȃ�:0, ����:1�j
$_exconf['ubiq']['b_match'] = 0; // (0)

// ���������N���ꂽURL���ȗ��\�L����i���Ȃ�:0, ����:1�j
$_exconf['ubiq']['shortcut'] = 0; // (0)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ kanban

/**
 * �Ŕ|�b�v�A�b�v
 *
 * kb_disp_* ��3�Ƃ�0�ɂ���ƊŔ̓��X�|�b�v�A�b�v�A����ȊO��HTML�|�b�v�A�b�v
 * ���X�|�b�v�A�b�v�ł� $_exconf['kanban']['popup_delay'] �����f����Ȃ��B�i��showResPopUp()�̎d�l�j
 */

// �Ŕ|�b�v�A�b�v�ioff:0, on:1, 2ch.net/bbspink.com�̂�:2�j
$_exconf['kanban']['*'] = 0; // (0)

// �摜�L���b�V���f�B���N�g��
// $datdir �� ./data �ȊO�A���Ƀh�L�������g���[�g�O�ɐݒ肵�Ă���Ƃ��͗v�ύX�B
$_exconf['kanban']['savedir'] = "$datdir/p2_kanban"; // ("$datdir/p2_kanban")

// SETTING.TXT�������Ƃ��AHTML����Ŕ摜���擾�i���Ȃ�:0, ����:1�j
$_exconf['kanban']['nosetting'] = 1; // (1)

// �摜�E�f�[�^���L���b�V���i���Ȃ�:0, ����:1, ����X�V:2�j
$_exconf['kanban']['cache'] = 1; // (1)

// �Ŕ�HTML�|�b�v�A�b�v�\���x�����ԁi�b�j
$_exconf['kanban']['popup_delay'] = 0.5; // (0.5)

// �L���b�V���̍폜�E�X�V�{�^����\���i���Ȃ�:0, ����:1�j
$_exconf['kanban']['manage'] = 0; // (0)

// �̃��[�J�����[����\���i���Ȃ�:0, ����:1�j
$_exconf['kanban']['disp_rule'] = 0; // (0)

// �摜�L���b�V���X�V�̌��ʂ�\���i���Ȃ�:0, ����:1�j
$_exconf['kanban']['disp_img_result'] = 0; // (0)

// �ݒ�t�@�C���X�V�̌��ʂ�\���i���Ȃ�:0, ����:1�j
$_exconf['kanban']['disp_file_result'] = 0; // (0)

// ���[�J�����[�����̊O�������N�ŊJ���t���[���܂��̓E�C���h�E
$_exconf['kanban']['target_frame'] = "read"; // ("read")

// }}}
/* ---------------------------------------------------------------------- */
// {{{ skin

/**
 * �X�L��
 */

// �X�L���ioff:0, on:1�j
$_exconf['skin']['*'] = 1;	// (0)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ filter

/**
 * expack: �t�B���^�����O����
 */

// �t�B���^�����O�����𑝂₵�A�g�т̃��X�t�B���^�����O���\�ɂ���ioff:0, ���X�̂�:1, �T�u�W�F�N�g��:2�j
$_exconf['flex']['*'] = 0; // (0)

// ID:xxxxxxxx��ID�t�B���^�����O�̃����N�ɕϊ��ioff:0, on:1, �{�����̂�:2�j
$_exconf['flex']['idpopup'] = 0; // (0)

// ���r�L�^�X��ID:xxxxxxxx��ID�t�B���^�����O�̃����N�ɕϊ��ioff:0, on:1, �{�����̂�:2�j
$_exconf['flex']['idlink_k'] = 0; // (0)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ bookmark

/**
 * ������
 */

// �X���b�h���Ƃɓǂ񂾉ӏ����L������ioff:0, on:1�j
$_exconf['bookmark']['*'] = 0; // (0)

// ������Ƃ��Ďg�������Ȃǁ^PC�p
// ex)"��", "<img src=\"img/readhere.png\">"
$_exconf['bkmk']['marker'] = "�����܂œǂ�"; // ("�����܂œǂ�")

// ������Ƃ��Ďg�������Ȃǁ^�g�їp
// ex)"��", "<img src=\"img/readhere_k.png\">"
$_exconf['bkmk']['marker_k'] = "�����܂œǂ�"; // ("�����܂œǂ�")

// }}}
/* ---------------------------------------------------------------------- */
// {{{ active mona

/**
 * �A�N�e�B�u���i�[
 *
 * �}���`�o�C�g�Ή��̐��K�\���֐����g���Ȃ��T�[�o�ɂ͔�Ή��B
 * �N���C�A���g��Macintosh��Safari�n�ȊO�̃u���E�U�Ȃ疳���ɂȂ�B
 * �i�S�p�v���|�[�V���i���t�H���g�̃����_�����O�ɖ�肪���邽�߁j
 *
 * ��������ɂ�mb_ereg�n�̃}���`�o�C�g�Ή����K�\���֐����K�v
 * ��������A�̓p�^�[���}�b�`���O�Ŕ���A
 * ��������B�̓p�^�[���}�b�`���O�ɉ����A�{�����̒P��\�������̔䗦���l������
 */

// �A�N�e�B�u���i�[�ioff:0, on:1, ��������A:2, ��������B:3�j
$_exconf['aMona']['*'] = 0; // (0)

// AA�n�̔A�J���}��؂�
$_exconf['aMona']['aaita'] = "aasaloon,mona,aastory,kao"; // ("aasaloon,mona,aastory,kao")

// AA�p�̃t�H���g�A�J���}��؂�
$_exconf['aMona']['aafont'] = "MS-PGothic,�l�r �o�S�V�b�N,Mona,���i�["; // ("MS-PGothic,�l�r �o�S�V�b�N,Mona,���i�[")

// AA�n�̔��������ʂ̂Ƃ������Ń��i�[�t�H���g�\���ioff:0, AA�n�̔̂�:1, �������ʂ�:2�j
$_exconf['aMona']['auto_monafont'] = 0; // (0)

// �������i�[�t�H���g�\���̂Ƃ��̑傫�� ex)"12px", "14px", "16px"
$_exconf['aMona']['auto_fontsize'] = "16px"; // ("16px")

// ��O�I�Ɏ������i�[�t�H���g�\�����Ȃ��A�J���}��؂� ex)"mac,php"
$_exconf['aMona']['auto_noaaita'] = ""; // ("")

// ��������B��AA�炵���p�^�[���Ƀ}�b�`����Ƃ���臒l
$_exconf['aMona']['thresholdA'] = 50; // (50)

// ��������B��AA���ۂ��p�f�B���O����Ă���Ƃ���臒l
$_exconf['aMona']['thresholdB'] = 50; // (50)

// ��������B�ł̒P��\�������䗦�̍Œ჉�C��
// �����؂�ƃp�^�[���Ƀ}�b�`���Ȃ��Ă�AA�ƌ��Ȃ����
$_exconf['aMona']['thresholdC'] = 20; // (20)

// PC�Ŏ�������̂Ƃ�AA���܂ރ��X���ȗ�����ioff:0, NG���[�h����:1, �������ځ[��:2�j
$_exconf['aMona']['aaryaku'] = 0; // (0)

// �g�тŎ�������̂Ƃ�AA���܂ރ��X���ȗ�����ioff:0, NG���[�h����:1, �������ځ[��:2�j
$_exconf['aMona']['aaryaku_k'] = 0; // (0)

// �������[�h�Ŏ�������̂Ƃ�AA���܂ރ��X���ȗ�����ioff:0, NG���[�h����:1, �������ځ[��:2�j
$_exconf['aMona']['aaryaku_l'] = 0; // (0)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ handle

/**
 * �R�e�n��/�g���b�v�L���x��
 *
 * ���O���ɋL���ł����^����ǉ��ł���B
 */

// �x���@�\��L���ɂ���ioff:0, on:1�j
$_exconf['handle']['*'] = 0; // (0)

//�R�e�n��&�g���b�v�̃��X�g�ݒ�(�K�v�ɉ����Ēǉ��A�ύX�A�폜���ĉ�����)
// �����F$_exconf['handle']['���X�g�ɕ\�����閼�O'] = "��^��";
// HTML�̓��ꕶ�������̎Q�ƂŋL�q���Ȃ��Ă��悢�B(�o�͎��Ɏ����ŕϊ�)
$_exconf['handle']['����������ł���'] = "����������ł���#abcdefg";
$_exconf['handle']['��ʒm����ł���'] = "��ʒm����ł���#hijklmn";
$_exconf['handle']['�ʂ�ۂ���ł���'] = "�ʂ�ۂ���ł���#opqrstu";
$_exconf['handle']['�f�t�H���g������'] = ""; // �W��(���O�����󗓂ɂ���)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ editor

/**
 * ���͎x��
 *
 * conf/conf_constant.php ���e�L�X�g�G�f�B�^�ŕҏW���Ē�^����ǉ��ł���B
 */

// ���͎x���@�\��L���ɂ���ioff:0, on:1�j
$_exconf['editor']['*'] = 0; // (0)

// ���e���e�̓��I�v���r���[�ioff:0, on(�t�H�[���̏�):1, on(�t�H�[���̉�):2, �ŏ�����\��:+4�j
$_exconf['editor']['dpreview'] = 0; // (0)

// ���e�t�H�[���ɒ�^�����j���[��ǉ��ioff:0, on:1�j
$_exconf['editor']['constant'] = 0; // (0)

// �\�[�X�R�[�h�̃R�s�y�ɓK�����␳������`�F�b�N�{�b�N�X��\���i���Ȃ�:0, ����:1, pc�I�̂�:2�j
$_exconf['editor']['srcfix'] = 0; // (0)

// sage�ĂȂ��Ƃ��x������i����:1, ���Ȃ�:0�j
$_exconf['editor']['check_sage'] = 0; // (0)

// ���b�Z�[�W������̂Ƃ��x������i����:1, ���Ȃ�:0�j
$_exconf['editor']['check_message'] = 0; // (0)

// �A�N�e�B�u���i�[�E���j���[��ǉ��ioff:0, on:1)
$_exconf['editor']['with_aMona'] = 0; // (0)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ smart popup menu

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[ (SPM)
 *
 * ���X�ԍ�����|�b�v�A�b�v���j���[��\��
 * DynamicHTML�Ŏ������Ă��邽�߃u���E�U�Ɉˑ�����@�\������B
 */

// SPM�ioff:0, mouseover�C�x���g�Ń|�b�v�A�b�v:1, click�C�x���g�Ń|�b�v�A�b�v:2�j
$_exconf['spm']['*'] = 0; // (0)

// ����Ƀ��X�ioff:0, on:1, �������݃t�H�[���̏�Ɍ����X��\��:2�j
$_exconf['spm']['kokores'] = 1; // (1)

// (1) �X�}�[�g���ځ[��̎��Ɋm�F����ioff:0, on:1�j
$_exconf['spm']['confirm'] = 1;	//

// �w�背�X���ځ[��E���ځ[�񃏁[�h�o�^�ioff:0, on:1�j
$_exconf['spm']['aborn'] = 1; // (1)

// NG���[�h�o�^�ioff:0, on:1�j
$_exconf['spm']['ng'] = 1; // (1)

// �_���肢�ioff:0, on:1�j
$_exconf['spm']['fortune'] = 0; // (0)

// �t�H���g�ݒ��ǉ��ioff:0, on:1,�i�L�́M�j�̂�:2�j
$_exconf['spm']['with_aMona'] = 0; // (0)

// �t�B���^�����O���j���[��ǉ��ioff:0, on:1�j
$_exconf['spm']['with_flex'] = 1; // (1)

// �t�B���^�����O���ʂ��J���t���[���܂��̓E�C���h�E
$_exconf['spm']['flex_target'] = "read"; // ("read")

// ���j���[�擪�̕����Ȃ�
// ex)"��", "�i'�x'�j", "<img src='img/spm.gif'>"
// ex2) "resnum"�̓��X�ԍ��ɒu�� ("&gt;&gt;resnum" => ">>����")
$_exconf['spm']['header'] = ""; // ("")

// ���ڂɃJ�[�\�����d�Ȃ����Ƃ������ɕ\�����镶���Ȃ�
// ex)"[", "url('img/before.gif')"
$_exconf['spm']['before'] = ""; // ("")

// ���ڂɃJ�[�\�����d�Ȃ����Ƃ��E���ɕ\�����镶���Ȃ�
// ex)"]", "url('img/after.gif')"
$_exconf['spm']['after'] = ""; // ("")

// }}}
/* ---------------------------------------------------------------------- */
// {{{ rss

/**
 * RSS���[�_
 *
 * �������@�d�@�v�@������
 * PEAR��XML_RSS���K�v�B
 * �ڂ����� http://moonshine.s32.xrea.com/install.html#rss ��
 * http://moonshine.s32.xrea.com/install.html#pear ������ꂽ���B
 *
 * RSS�o�[�W����0.9.x, 1.0, 2.0�ɑΉ��B�iRSS 2.0�̐V�v�f�ւ̑Ή��͕s�\���j
 * PHP��XSLT�܂���XSL�@�\�g�����L���Ȃ�Atom 0.3��RSS 1.0�ɕϊ����đΉ��B
 */

// �ꗗ�ɁgRSS�h�J�e�S����ǉ��ioff:0, on:1�j
$_exconf['rss']['*'] = 0; // (0)

// RSS���X�V���ꂽ���ǂ����m�F����Ԋu�i���w��j
$_exconf['rss']['check_interval'] = 30; // (30)

// RSS�̊O�������N���J���t���[���܂��̓E�C���h�E
$_exconf['rss']['target_frame'] = "read"; // ("read")

// �T�v���J���t���[���܂��̓E�C���h�E
$_exconf['rss']['desc_target_frame'] = "read"; // ("read")

// �C���[�W�L���b�V��1or2���g���ă����N���ꂽ�摜���L���b�V������ioff:0, on:1�j
// ���C���[�W�L���b�V�����L���ɂȂ��Ă��Ȃ��Ǝg���Ȃ�
$_exconf['rss']['with_imgcache'] = 0; // (0)

// RSS�̃^�C�g���摜�i�H�j���L���b�V������ioff:0, on:1�j
// ���Ŕ|�b�v�A�b�v�ƃC���[�W�L���b�V�����L���ɂȂ��Ă��Ȃ��Ǝg���Ȃ�
$_exconf['rss']['with_kanban'] = 0; // (0)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ fit image

/**
 * �t�B�b�g�C���[�W
 *
 * �u���E�U�ɂ���Ă͐��������삵�Ȃ��B
 * Safari���ƍ����ɂ��킹�ă��T�C�Y�����Ƃ��AMozilla�n���ƕ��ɍ��킹�ă��T�C�Y�����Ƃ���
 * �X�N���[���o�[������Ȃ����ۂ��B�i�c���̂ǂ��炩���E�C���h�E����͂ݏo��ꍇ�������j
 */

// �|�b�v�A�b�v�摜���E�C���h�E�ɂ��킹�ă��T�C�Y�ioff:0, on:1�j
$_exconf['fitImage']['*'] = 0; // (0)

// �����t�B�b�g�C���[�W�ioff:0, �E�C���h�E����:1, �E�C���h�E��:2, ����:3�j
// �摜���L���b�V������Ă��炸�A�摜��񂪓����Ȃ��Ƃ��͖����B
// �����ɍ��킹��Ƃ��́A�摜�̍������E�C���h�E�̍������傫���Ƃ��������T�C�Y����B
// ���ɍ��킹��Ƃ������l�ŁA�����͑S�̂�\������悤�Ƀ��T�C�Y����B
$_exconf['fitImage']['auto'] = 0; // (0)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ image cache

/**
 * ImageCaceh2
 *
 * �������@�d�@�v�@������
 * ���̋@�\���g���ɂ�PHP��GD�@�\�g���܂���ImageMagick��
 * SQLite, PostgreSQL, MySQL�̂����ꂩ���K�v�B
 * �ڂ�����ImageCache2�̃y�[�W (http://moonshine.s32.xrea.com/ic2.html) �ŁB
 */

// �摜���L���b�V�����T���l�C���쐬����ioff:0, PC�̂�:1, �g�т̂�:2, ����:3�j
$_exconf['imgCache']['*'] = 0; // (0)

// ime�o�R�Ń\�[�X�ւ̃����N���쐬����iNO:0, YES:1�j
$_exconf['imgCache']['through_ime'] = 0; // (0)

// �V�����X�̉摜�� $_conf['pre_thumb_limit(_k)'] �𖳎����đS�ĕ\������iNO:0, YES:1�j
$_exconf['imgCache']['newres_ignore_limit'] = 0; // (0)

// ImageCache2�̐ݒ�͂����܂�
// �ȉ��̍��ڂ� ���C���[�W�L���b�V������ ����ImageCache2 �Ɉڍs���邽�߂����Ɏc���Ă���

// �摜�L���b�V���f�B���N�g��
// $datdir �� ./data �ȊO�A���Ƀh�L�������g���[�g�O�ɐݒ肵�Ă���Ƃ��͗v�ύX�B
$_exconf['imgCache']['cachedir'] = "$datdir/p2_image_cache"; // ("$datdir/p2_image_cache")

// �T���l�C���쐬�f�B���N�g��
// $datdir �� ./data �ȊO�A���Ƀh�L�������g���[�g�O�ɐݒ肵�Ă���Ƃ��͗v�ύX�B
$_exconf['imgCache']['thumbdir'] = "$datdir/p2_image_thumb"; // ("$datdir/p2_image_thumb")

// }}}
/* ---------------------------------------------------------------------- */
// {{{ live

/**
 * �������[�h
 * �X���b�h�\����Live2ch���ɂ���B
 */

// �������[�h�ioff:0, on:1, live�n�̔̂�:2�j
$_exconf['liveView']['*'] = 0; // (0)

// �\������ő�̍s��
$_exconf['liveView']['rowlimit'] = 5; // (5)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ aborn

/**
 * �g�����ځ[��
 */

// 1���X������̍s�������A����𒴂���Ƃ��ځ[��A0���Ɩ����B
$_exconf['aborn']['break_aborn'] = 0; // (0)

// �s�������ɂЂ������������X��NG���[�h�����ɂ���ioff:0, on:1�j
$_exconf['aborn']['break_aborn_ng'] = 0; // (0)

// �A�����ځ[��ioff:0, ���ڎQ�Ƃ��Ă��郌�X�̂�:1, �֘A���X���ׂ�:2�j
// �������傫�����X�ԍ��ɂ͘A�����Ȃ�
$_exconf['aborn']['chain_aborn'] = 0; // (0)

// �A�����ځ[��ɂЂ������������X��NG���[�h�����ɂ���ioff:0, on:1�j
$_exconf['aborn']['chain_aborn_ng'] = 0; // (0)

// NG���[�h�A���ioff:0, ���ڎQ�Ƃ��Ă��郌�X�̂�:1, �֘A���X���ׂ�:2�j
// �������傫�����X�ԍ��ɂ͘A�����Ȃ�
$_exconf['aborn']['chain_ng'] = 0; // (0)

// >>1-100�Ȃǂ̃��X�͈͎w����A�����ځ[��/NG�̑Ώۂɂ���ioff:0, on:1�j
$_exconf['aborn']['chain_range'] = 0; // (0)

// }}}
/* ---------------------------------------------------------------------- */
// {{{ soap

/**
 * SOAP�x�[�X��Web�T�[�r�X�E�N���C�A���g
 *
 * Google Web APIs �𗘗p�����X���b�h�����ȂǁB
 *
 * ���̋@�\���g���̂ɕK�v�Ȃ��́F
 * �EPHP4�Ȃ�PEAR::SOAP�APHP5�Ȃ�SOAP�@�\�g��
 *
 * Google�����ɕK�v�Ȃ��́F
 * �EGoogle �A�J�E���g
 * �EGoogle Web APIs �̃y�[�W�������ł��� Developer's Kit �Ɋ܂܂��WSDL�t�@�C��
 * �EPEAR::Pager (2.x)
 * �EPEAR::Var_Dump (1.x)
 */

// SOAP�N���C�A���g�ioff:0, on:1�j
$_exconf['soap']['*'] = 0; // (0)

// Google Search WSDL �̃p�X
$_exconf['soap']['google_wsdl'] = ""; // ("")

// Google Key
$_exconf['soap']['google_key'] = ""; // ("")

// }}}
/* ---------------------------------------------------------------------- */
// {{{ status

/**
 * �X�e�[�^�X�\��
 * �e���ɃX�e�[�^�X�\����ǉ�����B
 */

// �X����\������ۂɁA�������Ԃ�\������(����:1, ���Ȃ�:0)
$_exconf['status']['processtime'] = 1; // (1)

// �X����\������ۂɁAdat�̃T�C�Y��\������(����:1, ���Ȃ�:0)
$_exconf['status']['datsize'] = 1; // (1)

// �X����P�ƕ\������ۂɁAdat�ɉ�����datdir�̃T�C�Y��\������(����:1, ���Ȃ�:0)
$_exconf['status']['datdirsize'] = 0; // (0)

// �X���b�h�ꗗ��Birthday�̑����dat�T�C�Y��\�� (����:1, ���Ȃ�:0)
$_exconf['status']['sb_show_datsize'] = 1; // (0)

// }}}
/*
----------------------------------------------------------------------
*/
// {{{ etc.

/**
 * ���̑�
 */

// ���C�ɔA���C�ɃX���ARSS�̃��X�g�𕡐��I�ׂ�悤�ɂ���ioff:0, on:1�j
$_exconf['etc']['multi_favs'] = 0; // (0)

// ���X�|�b�v�A�b�v��񓯊��Ɂi�J�[�\����>>n�ɏd�Ȃ������_�Łj�ǂݍ��ށioff:0, on:1�j
$_exconf['etc']['async_respop'] = 0; // (0)

// �񓯊����X�|�b�v�A�b�v���L���̂Ƃ��A�ʏ��HTML�|�b�v�A�b�v��>>1-10��񓯊����X�|�b�v�A�b�v�ɂ���B
//�ioff:0, 1~:�L���ɂ��郌�X���̏���A10�Ȃ�>>1-10(10)��>>56-64(9)�͗L���ɂȂ邪�A>>100-200(101)�͖����j
$_exconf['etc']['async_rangepop'] = 10; // (10)

// ���t������������ioff:0, on:1�j
$_exconf['etc']['datetime_rewrite'] = 0; // (0)

// ���t�̃t�H�[�}�b�g������
// @link http://jp.php.net/manual/ja/function.date.php
// %w% �� $_exconf['etc']['datetime_weekday'] �̑Ή�����v�f�ɕϊ������B
// ��SJIS�ł̓t�H�[�}�b�g������ɑS�p�������g���ƁA�����邱�Ƃ�����B
$_exconf['etc']['datetime_format'] = "Y-m-d H:i:s"; // ("Y-m-d H:i:s")

// �j����\��������
$_exconf['etc']['datetime_weekday'] = array("��","��","��","��","��","��","�y"); // (array("��","��","��","��","��","��","�y"))

// ���r�L�^�X���[�h�œ��t������������ioff:0, on:1�j
$_exconf['etc']['datetime_rewrite_k'] = 0; // (0)

// ���r�L�^�X���[�h�ł̓��t�̃t�H�[�}�b�g������
$_exconf['etc']['datetime_format_k']= "y/m/d H:i"; // ("y/m/d H:i")

// ���r�L�^�X���[�h�ł̗j����\��������
$_exconf['etc']['datetime_weekday_k'] = array("��","��","��","��","��","��","�y"); // (array("��","��","��","��","��","��","�y"))

// }}}
/* ---------------------------------------------------------------------- */

?>
