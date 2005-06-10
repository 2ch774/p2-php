<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 - �X�^�C���V�[�g���O���X�^�C���V�[�g�Ƃ��ďo�͂���

// �����ݒ�ǂݍ��� & ���[�U�F��
require_once 'conf/conf.php';
authorize();

// �Ó��ȃt�@�C��������
if (isset($_GET['css']) && preg_match('/^\w+$/', $_GET['css'])) {
    $css = P2_STYLE_DIR . '/' . $_GET['css'] . '_css.php';
}
if (!isset($css) || !file_exists($css)) {
    exit;
}

// �w�b�_
header('Content-Type: text/css; charset=Shift_JIS');

// �X�^�C���V�[�g�Ǎ�
$stylesheet = '';
include_once $css;

// �\��
echo "@charset \"Shift_JIS\";\n\n";
echo $stylesheet;

?>
