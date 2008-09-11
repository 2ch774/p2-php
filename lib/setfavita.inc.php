<?php
/**
 * rep2 - ���C�ɔ̏���
 */

require_once P2_LIB_DIR . '/FileCtl.php';

// {{{ setFavIta()

/**
 * ���C�ɔ��Z�b�g����
 *
 * $set �́A0(����), 1(�ǉ�), top, up, down, bottom
 */
function setFavIta()
{
    global $_conf, $_info_msg_ht;

    if (isset($_GET['setfavita'])) {
        $setfavita = $_GET['setfavita'];
    } elseif (isset($_POST['setfavita'])) {
        $setfavita = $_POST['setfavita'];
    }

    $host = isset($_GET['host']) ? $_GET['host'] : NULL;
    $bbs = isset($_GET['bbs']) ? $_GET['bbs'] : NULL;

    if ($_POST['url']) {
        if (preg_match("/http:\/\/(.+)\/([^\/]+)\/([^\/]+\.html?)?/", $_POST['url'], $matches)) {
            $host = $matches[1];
            $host = preg_replace('{/test/read\.cgi$}', '', $host);
            $bbs = $matches[2];
        } else {
            $_info_msg_ht .= "<p>p2 info: �u{$_POST['url']}�v�͔�URL�Ƃ��Ė����ł��B</p>";
        }
    }

    $list = $_POST['list'];

    if (!$host && !$bbs and (!(!empty($_POST['submit_setfavita']) && $list))) {
        $_info_msg_ht .= "<p>p2 info: �̎w�肪�ςł�</p>";
        return false;
    }

    if (isset($_POST['itaj'])) {
        $itaj = $_POST['itaj'];
    }
    if (!isset($itaj) && isset($_GET['itaj_en'])) {
        $itaj = base64_decode($_GET['itaj_en']);
    }
    if (empty($itaj)) { $itaj = $bbs; }

    //================================================
    // �ǂݍ���
    //================================================
    // p2_favita.brd �t�@�C�����Ȃ���ΐ���
    FileCtl::make_datafile($_conf['favita_brd'], $_conf['favita_perm']);

    // p2_favita.brd �ǂݍ���;
    $lines = FileCtl::file_read_lines($_conf['favita_brd'], FILE_IGNORE_NEW_LINES);

    //================================================
    // ����
    //================================================
    $neolines = array();
    $before_line_num = 0;

    // �ŏ��ɏd���v�f������
    if (!empty($lines)) {
        $i = -1;
        foreach ($lines as $l) {
            $i++;

            // {{{ ���f�[�^�iver0.6.0�ȉ��j�ڍs�[�u
            if ($l[0] != "\t") {
                $l = "\t".$l;
            }
            // }}}

            $lar = explode("\t", $l);

            if ($lar[1] == $host and $lar[2] == $bbs) { // �d�����
                $before_line_num = $i;
                continue;
            } elseif (!$lar[1] || !$lar[2]) { // �s���f�[�^�ihost, bbs�Ȃ��j���A�E�g
                continue;
            } else {
                $neolines[] = $l;
            }
        }
    }

    // �L�^�f�[�^�ݒ�
    if (!empty($_POST['submit_setfavita']) && $list) {
        $rec_lines = array();
        foreach (explode(',', $list) as $aList) {
            list($host, $bbs, $itaj_en) = explode('@', $aList);
            $rec_lines[] = "\t{$host}\t{$bbs}\t" . base64_decode($itaj_en);
        }

        $_info_msg_ht .= <<<EOJS
<script type="text/javascript">
//<![CDATA[
if (parent.menu) {
    parent.menu.location.href = '{$_conf['menu_php']}?nr=1';
}
//]]>
</script>\n
EOJS;

    } elseif ($setfavita and $host && $bbs && $itaj) {
        $newdata = "\t{$host}\t{$bbs}\t{$itaj}";
        require_once P2_LIB_DIR . '/getsetposlines.inc.php';
        $rec_lines = getSetPosLines($neolines, $newdata, $before_line_num, $setfavita);

    // ����
    } else {
        $rec_lines = $neolines;
    }

    $cont = '';
    if (!empty($rec_lines)) {
        foreach ($rec_lines as $l) {
            $cont .= $l . "\n";
        }
    }

    // ��������
    if (FileCtl::file_write_contents($_conf['favita_brd'], $cont) === false) {
        p2die('cannot write file.');
    }

    return true;
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
