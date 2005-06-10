<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 - txt �� �\��
*/

require_once 'conf/conf.php';   //��{�ݒ�t�@�C���Ǎ�

authorize(); // ���[�U�F��

// �����G���[
if (!isset($_GET['file'])) {
    die('Error: file ���w�肳��Ă��܂���');
}

//=========================================================
// �ϐ�
//=========================================================

$file = (isset($_GET['file'])) ? $_GET['file'] : NULL;
$encode = 'Shift_JIS';

//=========================================================
// �O����
//=========================================================
// �ǂݍ��߂�t�@�C�������肷��
$readable_files = array('doc/README.txt', 'doc/ChangeLog.txt', 'doc/Bookmarklet.txt', 'doc/README-EX.txt');

if ($readable_files && $file && (!in_array($file, $readable_files))) {
    $i = 0;
    foreach ($readable_files as $afile) {
        if ($i != 0) {
            $files_st .= '��';
        }
        $files_st .= '�u'.$afile.'�v';
        $i++;
    }
    die('Error: '.basename($_SERVER['PHP_SELF']).' �搶�̓ǂ߂�t�@�C���́A'.$files_st.'�����I');
}

//=========================================================
// HTML�v�����g
//=========================================================
// �ǂݍ��ރt�@�C���͊g���q.txt����
if (preg_match('/\.txt$/i', $file)) {
    viewTxtFile($file, $encode);
} else {
    die("error: cannot view \"{$file}\"");
}

/**
 * �t�@�C�����e��ǂݍ���ŕ\������
 */
function viewTxtFile($file, $encode)
{
    global $_info_msg_ht;

    if ($file == '') {
        die('Error: file ���w�肳��Ă��܂���');
    }

    $filename = basename($file);
    $ptitle = $filename;

    // �t�@�C�����e�ǂݍ���
    $cont = @file_get_contents($file);

    if ($encode == 'EUC-JP') {
        $cont = mb_convert_encoding($cont, 'SJIS-win', 'eucJP-win');
    }

    $cont_area = htmlspecialchars($cont);

    // �v�����g
    echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle}</title>
</head>
<body onload="top.document.title=self.document.title;">
{$_info_msg_ht}
<pre>{$cont_area}</pre>
</body>
</html>
EOF;

    return TRUE;
}

?>
