<?php
/*
    rep2 - �z�X�g�P�ʂł̃A�N�Z�X����/���ۂ̐ݒ�t�@�C��

    ���̃t�@�C���̐ݒ�́A�K�v�ɉ����ĕύX���Ă�������
*/


$GLOBALS['_HOSTCHKCONF'] = array();

// �z�X�g���Ƃ̐ݒ� (0:����; 1:����;)
// $_conf['secure']['auth_host'] == 0 �̂Ƃ��A���R�Ȃ��疳���B
// $_conf['secure']['auth_host'] == 1 �̂Ƃ��A�l��1�i�^�j�̃z�X�g�̂݋��B
// $_conf['secure']['auth_host'] == 2 �̂Ƃ��A�l��0�i�U�j�̃z�X�g�̂݋��ہB
$GLOBALS['_HOSTCHKCONF']['host_type'] = array(
    // p2�����삵�Ă���}�V��
    'localhost' => 1,
    // �N���XA-C�̃v���C�x�[�g�A�h���X
    'private'   => 1,
    // NTT DoCoMo i���[�h
    'docomo'    => 0,
    // au EZweb
    'au'        => 0,
    // SoftBank Mobile
    'softbank'  => 0,
    // WILLCOM AIR-EDGE
    'willcom'   => 0,
    // EMOBILE
    'emobile'   => 0,
    // iPhone 3G
    'iphone'    => 0,
    // ���[�U�[�ݒ�
    'custom'    => 0,
    // ���[�U�[�ݒ� (IPv6)
    'custom_v6' => 0,
);

// �A�N�Z�X��������IP�A�h���X�ш�
// �gIP�A�h���X => �}�X�N�h�`���̘A�z�z��
// $_conf['secure']['auth_host'] == 1 ����
// $GLOBALS['_HOSTCHKCONF']['host_type']['custom'] = 1 �̂Ƃ��g����
$GLOBALS['_HOSTCHKCONF']['custom_allowed_host'] = array(
    //'192.168.0.0' => 24,
);

// �A�N�Z�X�������郊���[�g�z�X�g�̐��K�\��
// preg_match()�֐��̑������Ƃ��Đ�����������ł��邱��
// �g�p���Ȃ��ꍇ��null
// $_conf['secure']['auth_host'] == 1 ����
// $GLOBALS['_HOSTCHKCONF']['host_type']['custom'] = 1 �̂Ƃ��g����
$_HOSTCHKCONF['custom_allowed_host_regex'] = null;

// �A�N�Z�X�����ۂ���IP�A�h���X�ш�
// �gIP�A�h���X => �}�X�N�h�`���̘A�z�z��
// $_conf['secure']['auth_host'] == 2 ����
// $GLOBALS['_HOSTCHKCONF']['host_type']['custom'] = 0 �̂Ƃ��g����
$GLOBALS['_HOSTCHKCONF']['custom_denied_host'] = array(
    //'192.168.0.0' => 24,
);

// �A�N�Z�X�����ۂ��郊���[�g�z�X�g�̐��K�\��
// preg_match()�֐��̑������Ƃ��Đ�����������ł��邱��
// �g�p���Ȃ��ꍇ��null
// $_conf['secure']['auth_host'] == 2 ����
// $GLOBALS['_HOSTCHKCONF']['host_type']['custom'] = 0 �̂Ƃ��g����
$_HOSTCHKCONF['custom_denied_host_regex'] = null;

// BBQ�L���b�V���̗L������ (�b���Ŏw��A0�Ȃ�i�v�Ă�)
$GLOBALS['_HOSTCHKCONF']['auth_bbq_burned_expire'] = 0;

// ��xBBQ�`�F�b�N������ł����z�X�g�ɑ΂���BBQ�F�؃p�X�X���[�̗L������ (�b���Ŏw��A0�Ȃ疈��m�F)
$GLOBALS['_HOSTCHKCONF']['auth_bbq_passed_expire'] = 3600;
