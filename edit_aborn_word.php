<?php
/*
    p2 - ���ځ[�񃏁[�h�ҏW�C���^�t�F�[�X
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�
require_once P2_LIBRARY_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

$path = isset($_REQUEST['path']) ? $_REQUEST['path'] : '';
$path_ht = htmlspecialchars($path, ENT_QUOTES);

if (!empty($_POST['submit_save']) || !empty($_POST['submit_default'])) {
    if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
        die('p2 error: �s���ȃ|�X�g�ł�');
    }
}

$writable_files = array(
    "p2_aborn_name.txt", "p2_aborn_mail.txt", "p2_aborn_msg.txt", "p2_aborn_id.txt",
    "p2_ng_name.txt", "p2_ng_mail.txt", "p2_ng_msg.txt", "p2_ng_id.txt",
    //"p2_aborn_res.txt",
);

if ($writable_files and (!in_array(basename($path), $writable_files))) {
    $i = 0;
    foreach ($writable_files as $afile) {
        if ($i != 0) {
            $files_st .= "��";
        }
        $files_st .= "�u".$afile."�v";
        $i++;
    }
    die("Error: ".basename($_SERVER['PHP_SELF'])." �搶�̏������߂�t�@�C���́A".$files_st."�����I");
}

//=====================================================================
// �O����
//=====================================================================

// {{{ ���ۑ��{�^����������Ă�����A�ݒ��ۑ�

if (!empty($_POST['submit_save'])) {

    $newdata = '';
    foreach ($_POST['nga'] as $na_info) {
        $a_word = strtr(trim($na_info['word'], "\t\r\n"), "\t\r\n", "   ");
        $a_time = strtr(trim($na_info['ht']), "\t\r\n", "   ");
        if ($a_time === '') {
            $a_time = '--';
        }
        $a_hits = $na_info['hn'];
        if ($a_word === '') {
            continue;
        }
        if (!empty($na_info['re'])) {
            $a_mode = !empty($na_info['ic']) ? '<regex:i>' : '<regex>';
        } elseif (!empty($na_info['ic'])) {
            $a_mode = '<i>';
        } else {
            $a_mode = '';
        }
        $newdata .= $a_mode . $a_word . "\t" . $a_time . "\t" . $a_hits . "\n";
    }
    if (FileCtl::file_write_contents($path, $newdata) !== FALSE) {
        $_info_msg_ht .= "<p>���ݒ���X�V�ۑ����܂���</p>";
    } else {
        $_info_msg_ht .= "<p>�~�ݒ���X�V�ۑ��ł��܂���ł���</p>";
    }

// }}}
// {{{ ���f�t�H���g�ɖ߂��{�^����������Ă�����

} elseif (!empty($_POST['submit_default'])) {
    if (@unlink($path)) {
        $_info_msg_ht .= "<p>�����X�g����ɂ��܂���</p>";
    } else {
        $_info_msg_ht .= "<p>�~���X�g����ɂł��܂���ł���</p>";
    }
}

// }}}
// {{{ ���X�g�ǂݍ���

$formdata = array();
if (file_exists($path)) {
    $lines = file($path);
    $i = 0;
    foreach ($lines as $line) {
        $lar = explode("\t", rtrim($line, "\r\n"));
        $formdata[$i] = array('word' => '', 're' => '', 'ic' => '', 'ht' => '', 'hn' => 0);
        isset($lar[1]) && $formdata[$i]['ht'] = $lar[1];
        isset($lar[2]) && $formdata[$i]['hn'] = $lar[2];
        if (preg_match('/^<(mb_ereg|preg_match|regex)(:[imsxeADSUXu]+)?>(.+)$/', $lar[0], $m)) {
            if ($m[3] === '') {
                continue;
            }
            $formdata[$i]['word'] = $m[3];
            $formdata[$i]['re'] = ' checked';
            if ($m[2] && strstr($m[2], 'i')) {
                $formdata[$i]['ic'] = ' checked';
            }
        } elseif (preg_match('/^<i>(.+)$/', $lar[0], $m)) {
            if ($m[1] === '') {
                continue;
            }
            $formdata[$i]['word'] = $m[1];
            $formdata[$i]['ic'] = ' checked';
        } else {
            if ($lar[0] === '') {
                continue;
            }
            $formdata[$i]['word'] = $lar[0];
        }
        $i++;
    }
}

// }}}

//=====================================================================
// �v�����g�ݒ�
//=====================================================================
$ptitle_top = sprintf('���ځ[��/NG���[�h�ҏW &gt; <a href="%s?path=%s">%s</a>',
    $_SERVER['PHP_SELF'], rawurlencode($path), basename($path));
$ptitle = strip_tags($ptitle_top);

$csrfid = P2Util::getCsrfId();

//=====================================================================
// �v�����g
//=====================================================================
// �w�b�_HTML���v�����g
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>\n
EOP;

if (empty($_conf['ktai'])) {
    echo <<<EOP
    <script type="text/javascript" src="js/basic.js?{$_conf['p2expack']}"></script>
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=edit_conf_user&amp;skin={$skin_en}" type="text/css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : ' onLoad="top.document.title=self.document.title;"';
echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

// PC�p�\��
if (empty($_conf['ktai'])) {
    echo <<<EOP
<p id="pan_menu"><a href="editpref.php">�ݒ�Ǘ�</a> &gt; {$ptitle_top}</p>\n
EOP;
} else {
    echo basename($path) . "<br>";
}

// PC�p�\��
if (empty($_conf['ktai'])) {
    $htm['form_submit'] = <<<EOP
        <tr class="group">
            <td colspan="3" align="center">
                <input type="submit" name="submit_save" value="�ύX��ۑ�����">
                <input type="submit" name="submit_default" value="���X�g����ɂ���" onClick="if (!window.confirm('���X�g����ɂ��Ă���낵���ł����H�i��蒼���͂ł��܂���j')) {return false;}"><br>
            </td>
        </tr>\n
EOP;
// �g�їp�\��
} else {
    $htm['form_submit'] = <<<EOP
<input type="submit" name="submit_save" value="�ύX��ۑ�����"><br>\n
EOP;
}

// ��񃁃b�Z�[�W�\��
if (!empty($_info_msg_ht)) {
    echo $_info_msg_ht;
    $_info_msg_ht = "";
}

echo <<<EOP
<div>���ji: �啶���������𖳎�, r: ���K�\��</div>
<form method="POST" action="{$_SERVER['PHP_SELF']}" target="_self" accept-charset="{$_conf['accept_charset']}">
    {$_conf['k_input_ht']}
    <input type="hidden" name="detect_hint" value="����">
    <input type="hidden" name="path" value="{$path_ht}">
    <input type="hidden" name="csrfid" value="{$csrfid}">\n
EOP;

// PC�p�\���itable�j
if (empty($_conf['ktai'])) {
    echo <<<EOP
    <table id="edit_conf_user" cellspacing="0">
        <tr>
            <td align="center">���ځ[�񃏁[�h</td>
            <td align="center">i / r</td>
            <td align="center">�ŏI�q�b�g�����Ɖ�</td>
        </tr>
        <tr class="group">
            <td colspan="3">�V�K�o�^</td>
        </tr>\n
EOP;
    $row_format = <<<EOP
        <tr>
            <td><input type="text" size="35" name="nga[%1\$d][word]" value="%2\$s"></td>
            <td>
                <label><input type="checkbox" name="nga[%1\$d][ic]" value="1"%3\$s>i</label>
                <label><input type="checkbox" name="nga[%1\$d][re]" value="1"%4\$s>r</label>
            </td>
            <td align="right">
                <input type="hidden" name="nga[%1\$d][ht]" value="%5\$s">%5\$s
                <input type="hidden" name="nga[%1\$d][hn]" value="%6\$d">(%6\$d)
            </td>
        </tr>\n
EOP;
// �g�їp�\��
} else {
    echo "�V�K�o�^<br>\n";
    $row_format = <<<EOP
<input type="text" name="nga[%1\$d][word]" value="%2\$s">
<input type="checkbox" name="nga[%1\$d][ic]" value="1"%3\$s>i
<input type="checkbox" name="nga[%1\$d][re]" value="1"%4\$s>r
<input type="hidden" name="nga[%1\$d][ht]" value="%5\$s">
<input type="hidden" name="nga[%1\$d][hn]" value="%6\$d">(%6\$d)<br>\n
EOP;
}

printf($row_format, -1, '', '', '', '--', 0);

echo $htm['form_submit'];

if (!empty($formdata)) {
    foreach ($formdata as $k => $v) {
        printf($row_format,
            $k,
            htmlspecialchars($v['word'], ENT_QUOTES),
            $v['ic'],
            $v['re'],
            htmlspecialchars($v['ht'], ENT_QUOTES),
            $v['hn']
        );
    }
    echo $htm['form_submit'];
}

// PC�Ȃ�
if (empty($_conf['ktai'])) {
    echo '</table>'."\n";
}

echo '</form>'."\n";


// �g�тȂ�
if ($_conf['ktai']) {
    echo <<<EOP
<hr>
<a {$_conf['accesskey']}="{$_conf['k_accesskey']['up']}" href="editpref.php{$_conf['k_at_q']}">{$_conf['k_accesskey']['up']}.�ݒ�ҏW</a>
{$_conf['k_to_index_ht']}
EOP;
}

echo '</body></html>';

// �������܂�
exit;

?>
