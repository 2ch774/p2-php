<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 -  �^�C�g���y�[�W

require_once 'conf/conf.php';    //��{�ݒ�t�@�C���Ǎ�
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

authorize(); // ���[�U�F��

//=========================================================
// �ϐ�
//=========================================================

if (!empty($GLOBALS['pref_dir_realpath_failed_msg'])) {
    $_info_msg_ht .= '<p>'.$GLOBALS['pref_dir_realpath_failed_msg'].'</p>';
}

$p2web_url_r = P2Util::throughIme($_conf['p2web_url']);
$expack_url_r = P2Util::throughIme($_conf['expack_url']);
$exhist_url_r = P2Util::throughIme($_conf['expack_url'].'history.html');

// �p�[�~�b�V�������ӊ��N ================
if ($_conf['pref_dir'] == $datdir) {
    P2Util::checkDirWritable($_conf['pref_dir']);
} else {
    P2Util::checkDirWritable($_conf['pref_dir']);
    P2Util::checkDirWritable($datdir);
}

//=========================================================
//�O����
//=========================================================
// ��ID 2ch �I�[�g���O�C��
if ($array = P2Util::readIdPw2ch()) {
    list($login2chID, $login2chPW, $autoLogin2ch) = $array;
    if ($autoLogin2ch) {
        include_once (P2_LIBRARY_DIR . '/login2ch.inc.php');
        login2ch();
    }
}

//=========================================================
// �v�����g�ݒ�
//=========================================================
$p_htm = array('last_login' => '');

// �ŐV�Ń`�F�b�N
if ($_conf['updatan_haahaa']) {
    $newversion_found = checkUpdatan();
} else {
    $newversion_found = '';
}

// �F�؃��[�U���
$autho_user_ht = '';
if ($login['use']) {
    $autho_user_ht = "<p>���O�C�����[�U: {$login['user']} - ".date('Y/m/d (D) G:i')."</p>\n";
}

// �O��̃��O�C�����
if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
    if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
        $p_htm['log'] = array_map('htmlspecialchars', $log);
        $p_htm['last_login'] = <<<EOP
�O��̃��O�C����� - {$p_htm['log']['date']}<br>
���[�U: {$p_htm['log']['user']}<br>
IP: {$p_htm['log']['ip']}<br>
HOST: {$p_htm['log']['host']}<br>
UA: {$p_htm['log']['ua']}<br>
REFERER: {$p_htm['log']['referer']}
EOP;
    }
/*
    $p_htm['last_login'] =<<<EOP
<table cellspacing="2" cellpadding="0";>
    <caption>�O��̃��O�C�����</caption>
    <tr><td align="right">����:</td><td>{$p_htm['log']['date']}</td></tr>
    <tr><td align="right">���[�U:</td><td>{$p_htm['log']['user']}</td></tr>
    <tr><td align="right">IP:</td><td>{$p_htm['log']['ip']}</td></tr>
    <tr><td align="right">HOST:</td><td>{$p_htm['log']['host']}</td></tr>
    <tr><td align="right">UA:</td><td>{$p_htm['log']['ua']}</td></tr>
    <tr><td align="right">REFERER:</td><td>{$p_htm['log']['referer']}</td></tr>
</table>
EOP;
*/
}

//=========================================================
// HTML�v�����g
//=========================================================
$ptitle = 'p2 - title';

P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle}</title>
    <base target="read">
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = '';

echo <<<EOP
<br>
<div class="container">
    {$newversion_found}
    <p>p2-expack rev.{$_conf['p2expack']}; based on p2-{$_conf['p2version']}<br>
    �@p2 web: <a href="{$p2web_url_r}" target="_blank">{$_conf['p2web_url']}</a><br>
    �@expack(rsk): <a href="http://moonshine.s32.xrea.com/" target="_blank">http://moonshine.s32.xrea.com/</a><br>
    �@expack(SF): <a href="{$expack_url_r}p2ex.html" target="_blank">{$_conf['expack_url']}p2ex.html</a></p>
    <ul>
        <li><a href="viewtxt.php?file=doc/README.txt">README.txt</a></li>
        <li><a href="viewtxt.php?file=doc/README-EX.txt">README-EX.txt</a></li>
        <li><a href="img/how_to_use.png" target="_blank">�����ȒP�ȑ���@</a></li>
        <li><a href="viewtxt.php?file=doc/Bookmarklet.txt">Bookmarklet</a></li>
        <li><a href="viewtxt.php?file=doc/ChangeLog.txt">ChangeLog�i�X�V�L�^�j</a></li>
        <li><a href="{$exhist_url_r}" target="_blank">�g���p�b�N�X�V�L�^</a></li>
    </ul>
    {$autho_user_ht}
    {$p_htm['last_login']}
</div>
</body>
</html>
EOP;

//==================================================
// ���֐�
//==================================================
/**
 * �I�����C�����p2�ŐV�ł��`�F�b�N����
 */
function checkUpdatan()
{
    global $_conf;

    $ver_txt_url = $_conf['expack_url'] . 'expack-status.txt';
    $cachefile = $_conf['pref_dir'] . '/p2_cache/p2status.txt';
    FileCtl::mkdir_for($cachefile);

    $no_p2status_dl_flag = false;
    if (file_exists($cachefile)) {
        // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
        if (@filemtime($cachefile) > time() - $_conf['p2status_dl_interval'] * 60) {
            $no_p2status_dl_flag = true;
        }
    }

    if (!$no_p2status_dl_flag) {
        P2Util::fileDownload($ver_txt_url, $cachefile);
    }

    $ver_txt = file($cachefile);
    $update_ver = $ver_txt[0];
    $kita = '�����������i߁�߁j��������!!!!!!';
    //$kita = '��*��ߥ*:.�..�.:*��(߁��)ߥ*:.�. .�.:*��ߥ*!!!!!';

    if (preg_match('/^\d{6}\.\d{4}$/', $update_ver) and version_compare($update_ver, $_conf['p2expack'], '>')) {
        $download_url_r = P2Util::throughIme($_conf['expack_url'].'#download');
        $history_url_r = P2Util::throughIme($_conf['expack_url'].'history.html#rev'.str_replace('.', '', $update_ver));
        $expack_filename = 'p2ex-' . str_replace('.', '-', $update_ver);
        $newversion_found =<<<EOP
<div class="kakomi">
    {$kita}<br>
    �I�����C����� �g���p�b�N �̍ŐV�o�[�W�����������܂����B<br>
    rev.{$update_ver}
        �� <a href="{$download_url_r}"{$_conf['ext_win_target_at']}>�_�E�����[�h</a>
        / <a href="{$history_url_r}"{$_conf['ext_win_target_at']}>�X�V�L�^</a>
</div>
<hr class="invisible">
EOP;
    } else {
        $newversion_found = '';
    }

    return $newversion_found;
}

?>
