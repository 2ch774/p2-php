<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 -  �N�b�L�[�F�؏���

    ���������G���R�[�f�B���O: Shift_JIS
*/

require_once 'conf/conf.php'; // ��{�ݒ�

authorize(); // ���[�U�F��


// �����o���p�ϐ�

$return_path = 'login.php';

$next_url = $return_path . '?check_regist_cookie=1&amp;regist_cookie=' . $_REQUEST['regist_cookie'];

$next_url = str_replace('&amp;', '&', $next_url);

header('Location: '.$next_url);
exit;

?>
