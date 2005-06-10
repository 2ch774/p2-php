<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    expack - �X���b�h���c���[�\������
    �c���[�\���ȊO�̃��[�`����read.php����q��
*/

require_once 'conf/conf.php';
require_once (P2_LIBRARY_DIR . '/thread.class.php');    //�X���b�h�N���X�Ǎ�
require_once (P2_LIBRARY_DIR . '/threadread.class.php');    //�X���b�h���[�h�N���X�Ǎ�
require_once (P2_LIBRARY_DIR . '/filectl.class.php');
require_once (P2_LIBRARY_DIR . '/ngabornctl.class.php');
require_once (P2_LIBRARY_DIR . '/showthread.class.php');    //HTML�\���N���X
require_once (P2_LIBRARY_DIR . '/showthreadpc.class.php');  //HTML�\���N���X
require_once (P2_LIBRARY_DIR . '/showthreadtree.class.php'); // �c���[�\���N���X

authorize(); // ���[�U�F��

//================================================================
// �ϐ�
//================================================================

$newtime = date('gis'); // ���������N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[
//$_today = date('y/m/d');

$_info_msg_ht = '';

if (empty($_GET['host']) || empty($_GET['bbs']) || empty($_GET['key']) || empty($_GET['ls'])) {
    die("p2 - read_async.php: ���X�̎w�肪�ςł��B");
}

$host = $_GET['host'];
$bbs  = $_GET['bbs'];
$key  = $_GET['key'];
$mode = isset($_GET['q']) ? (int)$_GET['q'] : 0;

$_conf['ktai'] = FALSE;

//==================================================================
// �����C��
//==================================================================
$aThread = &new ThreadRead;


//==========================================================
// idx�̓ǂݍ���
//==========================================================

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
if (!isset($aThread->keyidx)) {
    $aThread->setThreadPathInfo($host, $bbs, $key);
}

// �f�B���N�g����������΍��
//FileCtl::mkdir_for($aThread->keyidx);

$aThread->itaj = P2Util::getItaName($host, $bbs);
if (!$aThread->itaj) {
    $aThread->itaj = $aThread->bbs;
}

// idx�t�@�C��������Γǂݍ���
if (is_readable($aThread->keyidx)) {
    $lines = @file($aThread->keyidx);
    $l = rtrim($lines[0]);
    $data = explode('<>', $l);
} else {
    $data = array_fill(0, 10, '');
}
$aThread->getThreadInfoFromIdx();


//===========================================================
// DAT�̃_�E�����[�h
//===========================================================
if (empty($_GET['offline'])) {
    $aThread->downloadDat();
}

// ��DAT��ǂݍ���
$aThread->readDat();

// �I�t���C���w��ł����O���Ȃ���΁A���߂ċ����ǂݍ���
if (empty($aThread->datlines) && !empty($_GET['offline'])) {
    $aThread->downloadDat();
    $aThread->readDat();
}


$aThread->setTitleFromLocal(); // �^�C�g�����擾���Đݒ�


//===========================================================
// �\�����X�Ԃ͈̔͂�ݒ�
//===========================================================
$aThread->ls = $_GET['ls'];
$rn = (int)$aThread->ls; // string "256n" => integer 256
$aThread->lsToPoint();


//===============================================================
// ���v�����g
//===============================================================
$ptitle_ht = htmlspecialchars($aThread->itaj).' / '.$aThread->ttitle_hd;

// {{{ HTTP�w�b�_��XML�錾

if (P2Util::isBrowserSafariGroup()) {
    header('Content-Type: application/xml; charset=UTF-8');
    $xmldec = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
} else {
    header('Content-Type: text/html; charset=Shift_JIS');
    // ���p�Łu�H���v�������Ă镶������R�����g�ɂ���ƃp�[�X�G���[
    //$xmldec = '<' . '?xml version="1.0" encoding="Shift_JIS" ?' . '>' . "\n";
    $xmldec = '';
}

// }}}
// {{{ �{�̐���

$node = '�Ȃ��ہB';

if ($aThread->rescount) {

    $aShowThread = &new ShowThreadTree($aThread);

    if (isset($aShowThread->pDatLines[$rn])) {
        switch ($mode) {
            // ���X�|�b�v�A�b�v
            case 1:
                $node = $aShowThread->qRes($rn);
                break;
            // �R�s�y
            case 2:
                $part = $aShowThread->pDatLines[$rn];
                $node = $rn;
                $node .= ' �F' . $part['name'];
                $node .= ' �F' . $part['mail'];
                $node .= ' �F' . $part['date_id'] . "\n";
                $node .= trim($part['msg']);
                $node = strip_tags($node, '<br>');
                $node = preg_replace('/ *<br.*?> */i', "\n", $node);
                break;
            default:
                $node = $aShowThread->transMsg($aShowThread->pDatLines[$rn]['msg'], $rn);
        }
    }

}

// }}}
// {{{ �{�̏o��

if (P2Util::isBrowserSafariGroup()) {
    $node = mb_convert_encoding($node, 'UTF-8', 'SJIS-win');
}
echo $xmldec;
echo $node;

// }}}

// idx�E����ݒ�t���O���Ȃ���ΏI��
if (empty($_GET['rec'])) {
    exit;
}


// �e���r�ԑg����2ch�Ȃǂ̓��O�Eidx�E������ۑ����Ȃ�
if (P2Util::isHostNoCacheData($aThread->host)) {
    //@unlink($aThread->keydat); // ThreadRead::readDat()�ō폜����
    exit;
}


//===========================================================
// idx�̒l��ݒ�A�L�^
//===========================================================
if ($aThread->rescount) {
    $aThread->readnum = min($aThread->rescount, max(0, $data[5], $aThread->resrange['to']));

    $newline = $aThread->readnum + 1;   // $newline�͔p�~�\�肾���A���݊��p�ɔO�̂���

    $sar = array($aThread->ttitle, $aThread->key, $data[2], $aThread->rescount, $aThread->modified,
                 $aThread->readnum, $data[6], $data[7], $data[8], $newline);
    $s = implode('<>', $sar);
    P2Util::recKeyIdx($aThread->keyidx, $s); // key.idx�ɋL�^
}

//===========================================================
// �������L�^
//===========================================================
$newdata_ar = array($aThread->ttitle, $aThread->key, $data[2], '', '', $aThread->readnum,
                    $data[6], $data[7], $data[8], $newline, $aThread->host, $aThread->bbs);
$newdata = implode('<>', $newdata_ar);
P2Util::recRecent($newdata);

// ��NG���ځ[����L�^
NgAbornCtl::saveNgAborns();

?>
