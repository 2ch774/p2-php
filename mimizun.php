<?php
/*
�݂݂���ID������URL��n���B

����:
host:$host��n�� (�C��)
bbs:$bbs��n��
id:ID��n��
img:�����܂܂�Ă���Ή摜��\��
*/

include_once './conf/conf.inc.php';

$_login->authorize(); //���[�U�F��

require_once './plugin/mimizun/mimizun.class.php';

$mimizun = new mimizun();
$mimizun->host = $_GET['host'];
$mimizun->bbs  = $_GET['bbs'];

// �摜��\������ꍇ
if ($_GET['img']) {
    if ($mimizun->isEnable()) {
        header("Content-Type: image/png");
        readfile('./plugin/mimizun/mimizun.png');
    } else {
        header("Content-Type: image/gif");
        readfile('./img/spacer.gif');
    }
    exit;
} else {
    if ($mimizun->isEnable()) {
        $mimizun->id = $_GET['id'];
        header('Location: ' . P2Util::throughIme($mimizun->getIDURL()));
    } else {
        P2Util::printSimpleHtml('���̔͑Ή����Ă��܂���B');
    }
}
