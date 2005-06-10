<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 - ���X�������݃t�H�[��

require_once 'conf/conf.php';   // ��{�ݒ�
require_once (P2_LIBRARY_DIR . '/dataphp.class.php');

authorize(); // ���[�U�F��

//==================================================
// ���ϐ�
//==================================================
if (empty($_GET['host'])) {
    // �����G���[
    die('p2 error: host ���w�肳��Ă��܂���');
} else {
    $host = $_GET['host'];
}

$bbs  = isset($_GET['bbs'])  ? $_GET['bbs']  : '';
$key  = isset($_GET['key'])  ? $_GET['key']  : '';

$rescount = isset($_GET['rc']) ? $_GET['rc'] : 1;
$popup = isset($_GET['popup']) ? $_GET['popup'] : 0;

$itaj = P2Util::getItaName($host, $bbs);
if (!$itaj) { $itaj = $bbs; }

$ttitle_en = isset($_GET['ttitle_en']) ? $_GET['ttitle_en'] : '';
$ttitle = (strlen($ttitle_en) > 0) ? base64_decode($ttitle_en) : '';
$ttitle_hd = htmlspecialchars($ttitle);

$datdir_host = P2Util::datdirOfHost($host);
$key_idx = $datdir_host.'/'.$bbs.'/'.$key.'.idx';

// �t�H�[���̃I�v�V�����ǂݍ���
include (P2_LIBRARY_DIR . '/post_options_loader.inc.php');

// �\���w��
if (!$_conf['ktai']) {
    $class_ttitle = ' class="thre_title"';
    $target_read = ' target="read"';
    $sub_size_at = ' size="40"';
}

// {{{ �X�����ĂȂ�
if (!empty($_GET['newthread'])) {
    $ptitle = "{$itaj} - �V�K�X���b�h�쐬";

    // machibbs�AJBBS@������� �Ȃ�
    if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
        $submit_value = '�V�K��������';
    // 2ch�Ȃ�
    } else {
        $submit_value = '�V�K�X���b�h�쐬';
    }

    $htm['subject'] = "<b><span{$class_ttitle}>�^�C�g��</span></b>�F<input type=\"text\" name=\"subject\"{$sub_size_at}><br>";
    if ($_conf['ktai']) {
        $htm['subject'] = "<a href=\"{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}\">{$itaj}</a><br>".$htm['subject'];
    }
    $htm['newthread_hidden'] = '<input type="hidden" name="newthread" value="1">';
// }}}

// {{{ �������݂Ȃ�
} else {
    $ptitle = "{$itaj} - ���X��������";

    $submit_value = "��������";

    $htm['resform_ttitle'] = "<p><b><a{$class_ttitle} href=\"{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}\"{$target_read}>{$ttitle}</a></b></p>";
    $htm['newthread_hidden'] = '';
}
// }}}

$htm['readnew_hidden'] = !empty($_GET['from_read_new']) ? '<input type="hidden" name="from_read_new" value="1">' : '';


//==========================================================
// ��HTML�v�����g
//==========================================================
$body_on_load = '';
if (!$_conf['ktai']) {
    $body_on_load = " onload=\"setFocus('MESSAGE'); checkSage();\"";
}

P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOHEADER
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle}</title>\n
EOHEADER;
if (!$_conf['ktai']) {
echo <<<EOP
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=post&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=mona&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=prvw&amp;skin={$skin_en}" type="text/css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="js/basic.js"></script>
    <script type="text/javascript" src="js/post_form.js"></script>\n
    <script type="text/javascript" src="js/showhide.js"></script>
    <script type="text/javascript" src="js/strutil.js"></script>
    <script type="text/javascript" src="js/dpreview.js"></script>
    <script type="text/javascript">
        var dpreview_ok = {$_exconf['editor']['dpreview']};
    </script>\n
EOP;
    if ($_exconf['editor']['with_aMona']) {
        $am_aafont = str_replace(",", "','", $_exconf['aMona']['aafont']);
        $am_normalfont = str_replace('","', ",", $STYLE['fontfamily']);
        $am_read_fontsize = ($_exconf['editor']['dpreview']) ? $STYLE['respop_fontsize'] : $STYLE['read_fontsize'];
        echo <<<EOJS
    <script type="text/javascript" src="js/asciiart.js"></script>
    <script type="text/javascript">
        var am_aa_fontFamily = "{$am_aafont}";
        var am_fontFamily = "{$am_normalfont}";
        var am_read_fontSize = "{$am_read_fontsize}";
    </script>\n
EOJS;
    }
}
echo <<<EOP
</head>
<body{$k_color_settings}{$body_on_load}>\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht = '';

include (P2_LIBRARY_DIR . '/post_form.inc.php');

echo $htm['dpreview'];
echo $htm['post_form'];
echo $htm['dpreview2'];

echo '</body></html>';

?>
