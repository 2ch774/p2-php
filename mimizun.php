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
        $id = '';
        if ($_GET['id']) {
            $id = $_GET['id'];
        } else if ($_GET['key'] && $_GET['resnum']) {
            $aThread = new ThreadRead;
            $aThread->setThreadPathInfo($_GET['host'], $_GET['bbs'], $_GET['key']);
            $aThread->readDat();
            $resnum = $_GET['resnum'];
            if (isset($aThread->datlines[$resnum - 1])) {
                $ares = $aThread->datlines[$resnum - 1];
                $resar = $aThread->explodeDatLine($ares);
                $m = array();
                if (preg_match('<(ID: ?| )([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$)>', $resar[2], $m)) {
                    $id = $m[2];
                }
            }
        }
        if ($id) {
            $mimizun->id = $id;
        } else {
            P2Util::printSimpleHtml('����������Ȃ��悤�ł��B');
            exit();
        }
        $_ime = new P2Ime();
        $url = $_ime->through($mimizun->getIDURL(), null, false);
        header('Location: ' . $url);
    } else {
        P2Util::printSimpleHtml('���̔͑Ή����Ă��܂���B');
    }
}
