<?php
/*
ID�X�g�[�J�[��URL��n���B

����:
host:$host��n�� (�C��)
bbs:$bbs��n��
id:ID��n��
img:�����܂܂�Ă���Ή摜��\��
*/

include_once './conf/conf.inc.php';

$_login->authorize(); //���[�U�F��

require_once './plugin/stalker/stalker.class.php';

$stalker = new stalker();
$stalker->host = $_GET['host'];
$stalker->bbs  = $_GET['bbs'];
// �摜��\������ꍇ
if ($_GET['img']) {
    if ($stalker->isEnable()) {
        header("Content-Type: image/png");
        readfile('./plugin/stalker/stalker.png');
    } else {
        header("Content-Type: image/gif");
        readfile('./img/spacer.gif');
    }
    exit;
} else {
    if ($stalker->isEnable()) {
        $stalker->id = $_GET['id'];
        $_ime = new P2Ime();
        header('Location: ' . $_ime->through($stalker->getIDURL(), null, false));
    } else {
        P2Util::printSimpleHtml('���̔͑Ή����Ă��܂���B');
    }
}
