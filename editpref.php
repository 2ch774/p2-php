<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 -  �ݒ�ҏW
*/

require_once 'conf/conf.php';  //��{�ݒ�
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

authorize(); // ���[�U�F��

// {{{ �z�X�g�̓����p�ݒ�

if (!isset($rh_idx))     { $rh_idx     = $_conf['pref_dir'] . '/p2_res_hist.idx'; }
if (!isset($palace_idx)) { $palace_idx = $_conf['pref_dir'] . '/p2_palace.idx'; }

$synctitle = array(
    $_conf['favita_path']  => '���C�ɔ�',
    $_conf['favlist_file'] => '���C�ɃX��',
    $_conf['rct_file']     => '�ŋߓǂ񂾃X��',
    $rh_idx     => '�������ݗ���',
    $palace_idx => '�X���̓a��',
);

// }}}
// {{{ �ݒ�ύX����

// �X�L���ύX������΁A�ݒ�t�@�C�������������ă����[�h
if (isset($_POST['skin'])) {
    updateSkinSetting();

// ���C�ɓ���Z�b�g�ύX������΁A�ݒ�t�@�C��������������
} elseif (isset($_POST['favsetlist'])) {
    updateFavSetList();

// �z�X�g�̓���
} elseif (isset($_POST['sync'])) {
    $syncfile = $_POST['sync'];
    if ($syncfile == $_conf['favita_path']) {
        include_once (P2_LIBRARY_DIR . '/syncfavita.inc.php');
    } elseif (in_array($syncfile, array($_conf['favlist_file'], $_conf['rct_file'], $rh_idx, $palace_idx))) {
        include_once (P2_LIBRARY_DIR . '/syncindex.inc.php');
    }
    if ($sync_ok) {
        $_info_msg_ht .= "<p>{$synctitle[$syncfile]}�𓯊����܂����B</p>";
    } else {
        $_info_msg_ht .= "<p>{$synctitle[$syncfile]}�͕ύX����܂���ł����B</p>";
    }
    unset($syncfile);
}

$parent_reload = '';
if (isset($_GET['reload_skin'])) {
    $parent_reload = 'onload="parent.menu.location.href=\'./menu.php\'; parent.read.location.href=\'./first_cont.php\';"';
}

// }}}
// {{{ �����o���p�ϐ�

$ptitle = '�ݒ�Ǘ�';

if ($_conf['ktai']) {
    $status_st = '�ð��';
    $autho_user_st = '�F��հ��';
    $client_host_st = '�[��ν�';
    $client_ip_st = '�[��IP���ڽ';
    $browser_ua_st = '��׳��UA';
    $p2error_st = 'p2 �װ';
} else {
    $status_st = '�X�e�[�^�X';
    $autho_user_st = '�F�؃��[�U';
    $client_host_st = '�[���z�X�g';
    $client_ip_st = '�[��IP�A�h���X';
    $browser_ua_st = '�u���E�UUA';
    $p2error_st = 'p2 �G���[';
}

$autho_user_ht = '';

// }}}

//=========================================================
// {{{ HTML�v�����g
//=========================================================
P2Util::header_nocache();
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

EOP;
if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=editpref&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=editpref&amp;skin={$skin_en}">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

EOP;
}
echo <<<EOP
</head>
<body {$parent_reload}{$k_color_settings}>\n
EOP;

if (!$_conf['ktai']) {
    //echo "<p id=\"pan_menu\"><a href=\"setting.php\">�ݒ�</a> &gt; {$ptitle}</p>\n";
    echo "<p id=\"pan_menu\">{$ptitle}</p>\n";
}


echo $_info_msg_ht;
$_info_msg_ht = '';

// �ݒ�v�����g =====================
$aborn_res_txt  = $_conf['pref_dir'] . '/p2_aborn_res.txt';
$aborn_name_txt = $_conf['pref_dir'] . '/p2_aborn_name.txt';
$aborn_mail_txt = $_conf['pref_dir'] . '/p2_aborn_mail.txt';
$aborn_msg_txt  = $_conf['pref_dir'] . '/p2_aborn_msg.txt';
$aborn_id_txt   = $_conf['pref_dir'] . '/p2_aborn_id.txt';
$ng_name_txt    = $_conf['pref_dir'] . '/p2_ng_name.txt';
$ng_mail_txt    = $_conf['pref_dir'] . '/p2_ng_mail.txt';
$ng_msg_txt     = $_conf['pref_dir'] . '/p2_ng_msg.txt';
$ng_id_txt      = $_conf['pref_dir'] . '/p2_ng_id.txt';

// {{{ PC�p�\��
if (!$_conf['ktai']) {

    echo "<table id=\"editpref\">\n";

    // {{{ PC - NG���[�h�ҏW
    echo "<tr><td>\n\n";

    echo <<<EOP
<fieldset>
<legend><a href="http://akid.s17.xrea.com:8080/p2puki/pukiwiki.php?%5B%5BNG%A5%EF%A1%BC%A5%C9%A4%CE%C0%DF%C4%EA%CA%FD%CB%A1%5D%5D" target="read">NG���[�h</a>�ҏW</legend>\n
EOP;
    printEditFileForm($ng_name_txt, '���O');
    printEditFileForm($ng_mail_txt, '���[��');
    printEditFileForm($ng_msg_txt, '���b�Z�[�W');
    printEditFileForm($ng_id_txt, ' I D ');
    echo <<<EOP
</fieldset>\n\n
EOP;

    echo "</td>";
    // }}}
    // {{{ PC - ���ځ[�񃏁[�h�ҏW
    echo "<td>\n\n";

    echo <<<EOP
<fieldset>
<legend>���ځ[�񃏁[�h�ҏW</legend>\n
EOP;
    printEditFileForm($aborn_name_txt, '���O');
    printEditFileForm($aborn_mail_txt, '���[��');
    printEditFileForm($aborn_msg_txt, '���b�Z�[�W');
    printEditFileForm($aborn_id_txt, ' I D ');
    echo <<<EOP
</fieldset>\n\n
EOP;

    echo "</td></tr>";
    // }}}
    // {{{ PC - �X�L�� �̐ݒ�
    echo "<tr><td>\n\n";

    echo <<<EOP
<fieldset>
<legend>�X�L��</legend>\n
EOP;
    printSkinSelectForm($_conf['skin_file'], '�ύX');
//  printEditFileForm('conf/conf_skin.php', '�X�L���ݒ�');
    printEditFileForm($skin, '���̃X�L����ҏW');
    echo <<<EOP
</fieldset>\n\n
EOP;

    echo "</td>";
    // }}}
    // {{{ PC - ���̑� �̐ݒ�
    echo "<td>\n\n";

    echo <<<EOP
<fieldset>
<legend>���̑�</legend>\n
EOP;
    printEditFileForm('conf/conf.php', '��{�ݒ�');
    printEditFileForm('conf/conf_user.php', '���[�U�ݒ�');
    printEditFileForm('conf/conf_user_ex.php', '�g���p�b�N�ݒ�');
    echo "<br>\n";
    printEditFileForm('conf/conf_user_style.php', '�f�U�C���ݒ�');
    printEditFileForm('conf/conf_constant.php', '��^��');
    printEditFileForm($aborn_res_txt, '���ځ[�񃌃X');
    echo <<<EOP
</fieldset>\n
EOP;

    echo "</td></tr>\n\n";
    // }}}
    // {{{ PC - �z�X�g�̓��� HTML�̃Z�b�g
    $htm['sync'] = "<tr><td colspan=\"2\">\n\n";

    $htm['sync'] .= <<<EOP
<fieldset>
<legend>�z�X�g�̓����i2ch�̔ړ]�ɑΉ����܂��j</legend>\n
EOP;
    $exist_sync_flag = false;
    foreach ($synctitle as $syncpath => $syncname) {
        if (is_writable($syncpath)) {
            $exist_sync_flag = true;
            $htm['sync'] .= getSyncFavoritesFormHt($syncpath, $syncname);
        }
    }
    $htm['sync'] .= <<<EOP
</fieldset>\n\n
EOP;

    $htm['sync'] .= "</td></tr>\n\n";

    if ($exist_sync_flag) {
        echo $htm['sync'];
    } else {
        // echo "<p>νĂ̓����͕K�v����܂���</p>";
    }
    // }}}
    // {{{ PC - �Z�b�g�؂�ւ��E���̕ύX
    if ($_exconf['etc']['multi_favs']) {
        echo "<tr><td colspan=\"2\">\n\n";

        echo <<<EOP
<form action="editpref.php" method="post" accept-charset="{$_conf['accept_charset']}" target="_self" style="margin:0">
    <input type="hidden" name="detect_hint" value="����">
    <input type="hidden" name="favsetlist" value="1">
    <fieldset>
        <legend>�Z�b�g�؂�ւ��E���̕ύX�i�Z�b�g������ɂ���ƃf�t�H���g�̖��O�ɖ߂�܂��j</legend>
        <table>
            <tr>\n
EOP;
        echo "<td>\n";
        echo getFavSetListFormHt('m_favlist_set', '���C�ɃX��');
        echo "</td><td>\n";
        echo getFavSetListFormHt('m_favita_set', '���C�ɔ�');
        echo "</td><td>\n";
        echo getFavSetListFormHt('m_rss_set', 'RSS');
        echo "</td>\n";
        echo <<<EOP
            </tr>
        </table>
        <div>
            <input type="submit" value="�ύX">
        </div>
    </fieldset>
</form>\n\n
EOP;

        echo "</td></tr>\n\n";
    }
    // }}}

    echo "</table>\n";

// }}}
// {{{ �g�їp�\��
} else {
    // {{{ �g�� - �Z�b�g�؂�ւ�
    if ($_exconf['etc']['multi_favs']) {
        echo <<<EOP
<hr>
<p>���C�ɽڥ���C�ɔ¥RSS�̾�Ă�I��</p>
<form action="editpref.php" method="post" accept-charset="{$_conf['accept_charset']}" target="_self">
EOP;
        echo getFavSetListFormHtK('m_favlist_set', '���C�ɽ�'), '<br>';
        echo getFavSetListFormHtK('m_favita_set', '���C�ɔ�'), '<br>';
        echo getFavSetListFormHtK('m_rss_set', 'RSS'), '<br>';
        echo <<<EOP
<input type="submit" value="�ύX">
</form>
EOP;
    }
}
// }}}

// {{{ �V���܂Ƃߓǂ݂̃L���b�V���\��
$max = $_conf['matome_cache_max'];
for ($i = 0; $i <= $max; $i++) {
    $dnum = ($i) ? '.'.$i : '';
    $ai = '&amp;cnum='.$i;
    $file = $_conf['matome_cache_path'].$dnum.$_conf['matome_cache_ext'];
    //echo '<!-- '.$file.' -->';
    if (file_exists($file)) {
        $date = date('Y/m/d G:i:s', filemtime($file));
        $b = filesize($file)/1024;
        $kb = round($b, 0);
        $url = 'read_new.php?cview=1'.$ai;
        if ($i == 0) {
            $links[] = '<a href="'.$url.'" target="read">'.$date.'</a> '.$kb.'KB';
        } else {
            $links[] = '<a href="'.$url.'" target="read">'.$date.'</a> '.$kb.'KB';
        }
    }
}
if (!empty($links)) {
    if ($_conf['ktai']) {
        echo '<hr>'."\n";
    }
    echo $htm['matome'] = '<p>�V���܂Ƃߓǂ݂̑O��L���b�V����\��<br>' . implode('<br>', $links) . '</p>';
}
// }}}

// �g�їp�t�b�^
if ($_conf['ktai']) {
    echo "<p>νĂ̓����i2ch�̔ړ]�ɑΉ����܂��j</p>\n";
    foreach ($synctitle as $syncpath => $syncname) {
        if (is_writable($syncpath)) {
            echo getSyncFavoritesFormHt($syncpath, $syncname);
        }
    }
    echo '<hr>';
    echo $_conf['k_to_index_ht'];
}

echo '</body></html>';

// }}}
// =====================================================
// {{{ �֐�
// =====================================================

/**
 * �ݒ�t�@�C���ҏW�E�C���h�E���J���t�H�[�����v�����g����
 */
function printEditFileForm($path_value, $submit_value)
{
    if ((file_exists($path_value) && is_writable($path_value)) ||
        (!file_exists($path_value) && is_writable(dirname($path_value)))
    ) {
        $onsubmit = '';
        $disabled = '';
    } else {
        $onsubmit = ' onsubmit="return false;"';
        $disabled = ' disabled';
    }
    $rows = 36; //18
    $cols = 92; //90

    $ht = <<<EOFORM
<form action="editfile.php" method="POST" target="editfile" class="inline-form"{$onsubmit}>
    <input type="hidden" name="path" value="{$path_value}">
    <input type="hidden" name="encode" value="Shift_JIS">
    <input type="hidden" name="rows" value="{$rows}">
    <input type="hidden" name="cols" value="{$cols}">
    <input type="submit" value="{$submit_value}"{$disabled}>
</form>\n
EOFORM;

    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    echo $ht;
}

/**
 * �X�L���̑I��p�t�H�[�����v�����g����
 */
function printSkinSelectForm($path_value, $submit_value)
{
    global $skin;

    if ((file_exists($path_value) && is_writable($path_value)) ||
        (!file_exists($path_value) && is_writable(dirname($path_value)))
    ) {
        $onsubmit = '';
        $disabled = '';
    } else {
        $onsubmit = ' onsubmit="return false;"';
        $disabled = ' disabled';
    }

    $skindir = dir('./skin');
    $skins = array();
    $spskin = array();
    @include 'conf/conf_skin.php';

    while (($ent = $skindir->read()) !== FALSE) {
        if (preg_match('/^(\w+)\.php$/', $ent, $name) && !isset($spskin[$name[1]])) {
            $skins[$name[1]] = $name[1];
        }
    }
    $skins = array_merge($skins, $spskin);
    asort($skins);

    echo <<<EOFORM
<form action="editpref.php" method="POST" target="_self" class="inline-form"{$onsubmit}>
    <input type="hidden" name="path" value="{$path_value}"{$disabled}>
    <select name="skin"{$disabled}>\n
EOFORM;
    if (file_exists('conf/conf_user_style.php')) {
        $selected = ($skin == 'conf/conf_user_style.php') ? ' selected' : '';
        echo "\t\t<option value=\"conf_style\"{$selected}>�W��</option>\n";
    }
    foreach ($skins as $file => $name) {
        $path = 'skin/' . $file . '.php';
        if (file_exists($path)) {
            $selected = ($skin == $path) ? ' selected' : '';
            echo "\t\t<option value=\"{$file}\"{$selected}>{$name}</option>\n";
        }
    }
    echo <<<EOFORM
    </select>
    <input type="submit" value="{$submit_value}"{$disabled}>
</form>\n
EOFORM;
}

/**
 * �z�X�g�̓����p�t�H�[����HTML���擾����
 */
function getSyncFavoritesFormHt($path_value, $submit_value)
{
    $ht = <<<EOFORM
<form action="editpref.php" method="POST" target="_self" class="inline-form">
    <input type="hidden" name="sync" value="{$path_value}">
    <input type="submit" value="{$submit_value}">
</form>\n
EOFORM;

    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    return $ht;
}

/**
 * ���C�ɓ���Z�b�g�؂�ւ��E�Z�b�g���ύX�p�t�H�[����HTML���擾����iPC�p�j
 */
function getFavSetListFormHt($set_name, $set_title)
{
    global $_conf;

    if (!($titles = FavSetManager::getFavSetTitles($set_name))) {
        $titles = array();
    }

    $radio_checked = array_fill(0, $_conf['favset_num'] + 1, '');
    $i = (isset($_SESSION[$set_name])) ? (int)$_SESSION[$set_name] : 0;
    $radio_checked[$i] = ' checked';
    $ht = <<<EOFORM
<fieldset>
    <legend>{$set_title}</legend>\n
EOFORM;
    for ($j = 0; $j <= $_conf['favset_num']; $j++) {
        if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
            $titles[$j] = ($j == 0) ? $set_title : $set_title . $j;
        }
        $ht .= <<<EOFORM
    <input type="radio" name="{$set_name}" value="{$j}"{$radio_checked[$j]}>
    <input type="text" name="{$set_name}_titles[{$j}]" size="18" value="{$titles[$j]}">
    <br>\n
EOFORM;
    }
    $ht .= <<<EOFORM
</fieldset>\n
EOFORM;

    return $ht;
}

/**
 * ���C�ɓ���Z�b�g�؂�ւ��p�t�H�[����HTML���擾����i�g�їp�j
 */
function getFavSetListFormHtK($set_name, $set_title)
{
    global $_conf;

    if (!($titles = FavSetManager::getFavSetTitles($set_name))) {
        $titles = array();
    }

    $selected = array_fill(0, $_conf['favset_num'] + 1, '');
    $i = (isset($_SESSION[$set_name])) ? (int)$_SESSION[$set_name] : 0;
    $selected[$i] = ' selected';
    $ht = "<select name=\"{$set_name}\">";
    for ($j = 0; $j <= $_conf['favset_num']; $j++) {
        if ($j == 0) {
            if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
                $titles[$j] = $set_title;
            }
            $titles[$j] .= ' (��̫��)';
        } else {
            if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
                $titles[$j] = $set_title . $j;
            }
        }
        $ht .= "<option value=\"{$j}\"{$selected[$j]}>{$titles[$j]}</option>";
    }
    $ht .= "</select>\n";

    return $ht;
}


/**
 * ���C�ɓ���Z�b�g���X�g���X�V����
 */
function updateFavSetList()
{
    global $_conf, $_info_msg_ht;

    if (file_exists($_conf['favset_file'])) {
        $setlist_titles = FavSetManager::getFavSetTitles();
    } else {
        FileCtl::make_datafile($_conf['favset_file']);
    }
    if (empty($setlist_titles)) {
        $setlist_titles = array();
    }

    $setlist_names = array('m_favlist_set', 'm_favita_set', 'm_rss_set');
    foreach ($setlist_names as $setlist_name) {
        if (isset($_POST["{$setlist_name}_titles"]) && is_array($_POST["{$setlist_name}_titles"])) {
            $setlist_titles[$setlist_name] = array();
            for ($i = 0; $i <= $_conf['favset_num']; $i++) {
                if (!isset($_POST["{$setlist_name}_titles"][$i])) {
                    $setlist_titles[$setlist_name][$i] = '';
                    continue;
                }
                $newname = trim($_POST["{$setlist_name}_titles"][$i]);
                $newname = preg_replace('/\r\n\t/', ' ', $newname);
                $newname = htmlspecialchars($newname);
                $setlist_titles[$setlist_name][$i] = $newname;
            }
        }
    }

    $newdata = serialize($setlist_titles);
    if (FileCtl::file_write_contents($_conf['favset_file'], $newdata) === FALSE) {
        $_info_msg_ht .= "<p>p2 error: {$_conf['favset_file']} �ɂ��C�ɓ���Z�b�g�ݒ���������߂܂���ł����B";
        return FALSE;
    }

    return TRUE;
}

/**
 * �X�L���ݒ���X�V���A�y�[�W�������[�h����
 */
function updateSkinSetting()
{
    global $_conf, $_info_msg_ht;

    if (!preg_match('/^\w+$/', $_POST['skin'])) {
        $_info_msg_ht .= "<p>p2 error: �s���ȃX�L�� ({$_POST['skin']}) ���w�肳��܂����B</p>";
        return FALSE;
    }

    if ($_POST['skin'] == 'conf_style') {
        $newskin = 'conf/conf_user_style.php';
    } else {
        $newskin = 'skin/' . $_POST['skin'] . '.php';
    }

    if (file_exists($newskin)) {
        if (FileCtl::file_write_contents($_conf['skin_file'], $_POST['skin']) !== FALSE) {
            header("Location: {$_SERVER['PHP_SELF']}?reload_skin=1");
            exit;
        } else {
            $_info_msg_ht .= "<p>p2 error: {$_conf['skin_file']} �ɃX�L���ݒ���������߂܂���ł����B</p>";
        }
    } else {
        $_info_msg_ht .= "<p>p2 error: �s���ȃX�L�� ({$_POST['skin']}) ���w�肳��܂����B</p>";
    }

    return FALSE;
}

// }}}

?>
