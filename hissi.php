<?php
/*
�K���`�F�b�J�[��URL��n���B

����:
host:$host��n�� (�C��)
bbs:$bbs��n��
date:yyyymmdd�`���œ��t��n��
id:ID��n��
img:�����܂܂�Ă���Ή摜��\��

�g�p��:
�u�����t��
Match=(.*?(\d{4})/(\d{2})/(\d{2}).*)
Replace=$1<a href="hissi.php?bbs=$bbs&date=$2$3$4&id=$id" target="_blank"><img src="hissi.php?img=1&bbs=$bbs" height=12px></a>
�Ƃ���΁A�K���`�F�b�J�[�ɑΉ����Ă�ł͉摜���\������A�����łȂ��ł͕\������Ȃ��B
*/

include_once './conf/conf.inc.php';

$_login->authorize(); //���[�U�F��

require_once './plugin/hissi/hissi.class.php';

$hissi = new hissi();
$hissi->host = $_GET['host'];
$hissi->bbs  = $_GET['bbs'];

// �摜��\������ꍇ
if ($_GET['img']) {
    if ($hissi->isEnable()) {
        header("Content-Type: image/png");
        readfile('./plugin/hissi/hissi.png');
    } else {
        header("Content-Type: image/gif");
        readfile('./img/spacer.gif');
    }
    exit;
} else {
    if ($hissi->isEnable()) {
        $hissi->date = $_GET['date'];
        $hissi->id   = $_GET['id'];
        $_ime = new P2Ime();
        header('Location: ' . $_ime->through($hissi->getIDURL(), null, false));
    } else {
        P2Util::printSimpleHtml('���̔͑Ή����Ă��܂���B');
    }
}
