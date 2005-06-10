<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 - �Ȉ�RSS���[�_�i�L���ꗗ�j

    RSS�n�t�@�C����UTF-8�ŏ����āA�g�тɏo�͂���Ƃ�����SJIS�ɂ���������
    mbstring.script_encoding = SJIS-win �Ƃ̐��������l�����SJIS�̂܂܂�����ȁH
*/

require_once 'conf/conf.php';

authorize(); // ���[�U�F��


//============================================================
// �ϐ��̏�����
//============================================================

$_info_msg_ht = '';
$channel = array();
$items = array();

$num = trim($_REQUEST['num']);
$xml = trim($_REQUEST['xml']);
$atom = empty($_REQUEST['atom']) ? 0 : 1;
$site_en = trim($_REQUEST['site_en']);

$xml_en = rawurlencode($xml);
$xml_ht = P2Util::re_htmlspecialchars($xml);


//============================================================
// RSS�ǂݍ���
//============================================================

if ($xml) {
    require_once (P2EX_LIBRARY_DIR . '/rss/parser.inc.php');
    $rss = &p2GetRSS($xml, $atom);
    if (is_a($rss, 'XML_Parser')) {
        clearstatcache();
        $rss_parse_success = TRUE;
        $xml_path = rss_get_save_path($xml);
        $mtime    = filemtime($xml_path);
        $channel  = $rss->getChannelInfo();
        $items    = $rss->getItems();

        $fp = fopen($xml_path, 'rb');
        $xmldec = fgets($fp, 1024);
        fclose($fp);
        if (preg_match('/^<\\?xml version="1.0" encoding="((?i:iso)-8859-(?:[1-9]|1[0-5]))" ?\\?>/', $xmldec, $matches)) {
            $encoding = $matches[1];
        } else {
            $encoding = 'ASCII,JIS,UTF-8,eucJP-win,SJIS-win';
        }
        mb_convert_variables('SJIS-win', $encoding, $channel, $items);
    } else {
        $rss_parse_success = FALSE;
    }
} else {
    $rss_parse_success = FALSE;
}


//===================================================================
// HTML�\���p�ϐ��̐ݒ�
//===================================================================

//�^�C�g��
$title = isset($channel['title']) ? P2Util::re_htmlspecialchars($channel['title']) : '';

//�X�V����
$reloaded_time = date('m/d G:i:s');


//============================================================
// HTML�v�����g
//============================================================

if ($_conf['doctype']) { echo $_conf['doctype']; }
if ($_conf['ktai']) {
    include_once (P2EX_LIBRARY_DIR . '/rss/subject_k.inc.php');
} else {
    include_once (P2EX_LIBRARY_DIR . '/rss/subject.inc.php');
}

//============================================================
// 2ch bbspink �������N
//============================================================
function rss_link2ch_callback($s)
{
    global $_conf;
    $read_url = "{$_conf['read_php']}?host={$s[1]}&amp;bbs={$s[3]}&amp;key={$s[4]}&amp;ls={$s[6]}";
    return $read_url;
}

?>
