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
        $id = '';
        if ($_GET['id']) {
            $id = $_GET['id'];
        } else if ($_GET['key'] && $_GET['resnum']) {
            $id = '';
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
            $stalker->id = $id;
        } else {
            P2Util::printSimpleHtml('����������Ȃ��悤�ł��B');
            exit();
        }
        $_ime = new P2Ime();
        header('Location: ' . $_ime->through($stalker->getIDURL(), null, false));
    } else {
        P2Util::printSimpleHtml('���̔͑Ή����Ă��܂���B');
    }
}
