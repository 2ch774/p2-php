<?php
/*
    rep2+Wiki - ���[�U�ݒ� �f�t�H���g
    
    ���̃t�@�C���̓f�t�H���g�l�̐ݒ�Ȃ̂ŁA���ɕύX����K�v�͂���܂���
*/

// {{{ ���摜�u��URL

// �摜�u��URL��EXTRACT�L���b�V������(����L���b�V�����g�p:1, �w��̂��̂͊m�F:2, �u����������URL�͎�����m�F:3, ����m�F:0)
$conf_user_def['wiki.replaceimageurl.extract_cache'] = 1; // (1)
$conf_user_sel['wiki.replaceimageurl.extract_cache'] = array(
    '1' => '����L���b�V�����g�p',
    '2' => '�w��̂��̂͊m�F',
    '3' => '�u����������URL�͎�����m�F',
    '0' => '����m�F',
);

// }}}

// {{{ ��samba

// samba�^�C�}�[�𗘗p (����:1, ���Ȃ�:0)
$conf_user_def['wiki.samba_timer'] = 0; // (0)
$conf_user_rad['wiki.samba_timer'] = array('1' => '����', '0' => '���Ȃ�');
// samba�̃L���b�V������
$conf_user_def['wiki.samba_cache'] = 24; // (24)
$conf_user_rules['wiki.samba_cache'] = array('emptyToDef', 'notIntExceptMinusToDef');

// }}}

// {{{ ��samba

// NG�X���b�h��L���ɂ��� (����:1, ���Ȃ�:0)
$conf_user_def['wiki.ng_thread'] = 0; // (0)
$conf_user_rad['wiki.ng_thread'] = array('1' => '����', '0' => '���Ȃ�');
// �g�щ{�����A���X�ԍ���SPM������ (����:1, ���Ȃ�:0)
$conf_user_def['wiki.spm.mobile'] = 0; // (0)
$conf_user_rad['wiki.spm.mobile'] = array('1' => '����', '0' => '���Ȃ�');

// }}}
