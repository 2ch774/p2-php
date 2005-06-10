<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 - �X���b�h���E�B���h�E

require_once 'conf/conf.php';   // ��{�ݒ�t�@�C��
require_once (P2_LIBRARY_DIR . '/thread.class.php');    // �X���b�h�N���X
require_once (P2_LIBRARY_DIR . '/filectl.class.php');
require_once (P2_LIBRARY_DIR . '/dele.inc.php');    // �폜�����p�̊֐��S

authorize(); // ���[�U�F��

//================================================================
// �ϐ��ݒ�
//================================================================
isset($_GET['host']) && $host = $_GET['host'];  // "pc.2ch.net"
isset($_GET['bbs'])  && $bbs  = $_GET['bbs'];   // "php"
isset($_GET['key'])  && $key  = $_GET['key'];   // "1022999539"
$ttitle_en = isset($_GET['ttitle_en']) ? $_GET['ttitle_en'] : '';

//popup 0(false),1(true),2(true,�N���[�Y�^�C�}�[�t)
$popup_ht = !empty($_GET['popup']) ? '&amp;popup=1' : '';

// �ȉ��ǂꂩ����Ȃ��Ă��_���o��
if (empty($host) || empty($bbs) || empty($key)) {
    die('p2 error: ����������������܂���B');
}

//================================================================
// ����ȑO�u����
//================================================================

// ���폜
if (!empty($_GET['dele'])) {
    $r = deleteLogs($host, $bbs, array($key));
    //echo $r;
    if (empty($r)) {
        $title_msg = '�~ ���O�폜���s';
        $info_msg = '�~ ���O�폜���s';
    } elseif ($r == 1) {
        $title_msg = '�� ���O�폜����';
        $info_msg = '�� ���O�폜����';
    } elseif ($r == 2) {
        $title_msg = '- ���O�͂���܂���ł���';
        $info_msg = '- ���O�͂���܂���ł���';
    }
}

// �����폜
if (!empty($_GET['offrec'])) {
    $r1 = offRecent($host, $bbs, $key);
    $r2 = offResHist($host, $bbs, $key);
    if (empty($r1) || empty($r2)) {
        $title_msg = '�~ �����������s';
        $info_msg = '�~ �����������s';
    } elseif ($r1 == 1 || $r2 == 1) {
        $title_msg = '�� ������������';
        $info_msg = '�� ������������';
    } elseif ($r1 == 2 && $r2 == 2) {
        $title_msg = '- �����ɂ͂���܂���ł���';
        $info_msg = '- �����ɂ͂���܂���ł���';
    }

// ���C�ɓ���X���b�h
} elseif (isset($_GET['setfav'])) {
    require_once (P2_LIBRARY_DIR . '/setfav.inc.php');
    setFav($host, $bbs, $key, $_GET['setfav']);

// �a������
} elseif (isset($_GET['setpal'])) {
    require_once (P2_LIBRARY_DIR . '/setpalace.inc.php');
    setPal($host, $bbs, $key, $_GET['setpal']);

// �X���b�h���ځ[��
} elseif (isset($_GET['taborn'])) {
    require_once (P2_LIBRARY_DIR . '/settaborn.inc.php');
    settaborn($host, $bbs, $key, $_GET['taborn']);
}

//=================================================================
// �����C��
//=================================================================

$aThread = &new Thread;

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
$aThread->setThreadPathInfo($host, $bbs, $key);
$key_line = $aThread->getThreadInfoFromIdx();
$aThread->getDatBytesFromLocalDat(); // $aThread->length ��set

if (!$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs)) {
    $aThread->itaj = $aThread->bbs;
}
$hc['itaj'] = $aThread->itaj;

if (!$aThread->ttitle) {
    if ($ttitle_en) {
        $aThread->setTtitle(base64_decode($ttitle_en));
    } else {
        $aThread->setTitleFromLocal();
    }
}
if (!$ttitle_en) {
    if ($aThread->ttitle) {
        $ttitle_en = base64_encode($aThread->ttitle);
        //$ttitle_urlen = rawurlencode($ttitle_en);
    }
}
$ttitle_en_ht = ($ttitle_en) ? "&amp;ttitle_en={$ttitle_en}" : '';

if ($aThread->ttitle) {
    $hc['ttitle_name'] = $aThread->ttitle_hc;
} else {
    $hc['ttitle_name'] = '�X���b�h�^�C�g�����擾';
}

// favlist �`�F�b�N =====================================
//���C�ɃX�����X�g �Ǎ�
if (file_exists($_conf['favlist_file']) && ($favlines = file($_conf['favlist_file']))) {
    foreach ($favlines as $l) {
        $l = rtrim($l);
        $favarray = explode('<>', $l);
        if ($aThread->key == $favarray[1]) {
            $aThread->fav = '1';
            if ($favarray[0]) {
                $aThread->setTtitle($favarray[0]);
            }
            break;
        }
    }
}

if ($aThread->fav) {
    $favmark = '<span class="fav">��</span>';
    $favdo = 0;
} else {
    $favmark = '<span class="fav">+</span>';
    $favdo = 1;
}

$fav_ht = <<<EOP
<a href="info.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;setfav={$favdo}{$popup_ht}{$ttitle_en_ht}">{$favmark}</a>
EOP;

// palace �`�F�b�N =========================================
$palace_idx = $_conf['pref_dir']. '/p2_palace.idx';

//�a������X�����X�g �Ǎ�
$isPalace = false;
if (file_exists($palace_idx) && ($pallines = file($palace_idx))) {
    foreach ($pallines as $l) {
        $l = rtrim($l);
        $palarray = explode('<>', $l);
        if ($aThread->key == $palarray[1]) {
            $isPalace = true;
            if ($palarray[0]) {
                $aThread->ttitle = $palarray[0];
            }
            break;
        }
    }
}
$paldo = ($isPalace) ? 0 : 1;
$pal_a_ht = "info.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;setpal={$paldo}{$popup_ht}{$ttitle_en_ht}";

if ($isPalace) {
    $pal_ht = "<a href=\"{$pal_a_ht}\">��</a>";
} else {
    $pal_ht = "<a href=\"{$pal_a_ht}\">+</a>";
}

// ���X���b�h���ځ[��`�F�b�N =====================================
// �X���b�h���ځ[�񃊃X�g�Ǎ�
$datdir_host = P2Util::datdirOfHost($host);
$taborn_idx = "{$datdir_host}/{$bbs}/p2_threads_aborn.idx";

//�X���b�h���ځ[�񃊃X�g�Ǎ�

if (file_exists($taborn_idx) && ($tabornlist = file($taborn_idx))) {
    foreach ($tabornlist as $l) {
        $l = rtrim($l);
        $tarray = explode('<>', $l);
        if ($aThread->key == $tarray[1]) {
            $isTaborn = true;
            break;
        }
    }
}

$taborndo_title_at = '';
if (!empty($isTaborn)) {
    $tastr1 = '���ځ[��';
    $tastr2 = '���ځ[���������';
    $taborndo = 0;
} else {
    $tastr1 = '�ʏ�';
    $tastr2 = '���ځ[�񂷂�';
    $taborndo = 1;
    if (!$_conf['ktai']) {
        $taborndo_title_at = ' title="�X���b�h�ꗗ�Ŕ�\���ɂ��܂�"';
    }
}

$taborn_ht = <<<EOP
{$tastr1} [<a href="info.php?host={$aThread->host}&bbs={$aThread->bbs}&key={$aThread->key}&amp;taborn={$taborndo}{$popup_ht}{$ttitle_en_ht}"{$taborndo_title_at}>{$tastr2}</a>]
EOP;


// ���O����Ȃ��t���O�Z�b�g ===========
if (file_exists($aThread->keydat) or file_exists($aThread->keyidx) ) { $existLog = true; }

//=================================================================
// HTML�v�����g
//=================================================================
if (!$_conf['ktai']) {
    $target_read_at = ' target="read"';
    $target_sb_at = ' target="sbject"';
}

$motothre_url = $aThread->getMotoThread();
if ($_conf['motothre_ime']) {
    $motothre_url_ime = P2Util::throughIme($motothre_url, TRUE);
} else {
    $motothre_url_ime = htmlspecialchars($motothre_url);
}
if (isset($title_msg)) {
    $hc['title'] = $title_msg;
} else {
    $hc['title'] = "info - {$hc['ttitle_name']}";
}

$hd = array_map('htmlspecialchars', $hc);


P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOHEADER
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$hd['title']}</title>\n
EOHEADER;

if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=info&amp;skin={$skin_en}" type="text/css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">\n
EOP;
}

$body_onload = '';
if (isset($_GET['popup']) && $_GET['popup'] == 2) {
    echo "\t<script type=\"text/javascript\" src=\"js/closetimer.js\"></script>\n";
    $body_onload = " onload=\"startTimer(document.getElementById('timerbutton'))\"";
}

echo <<<EOP
</head>
<body{$k_color_settings}{$body_onload}>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = '';

echo "<p>\n";
echo "<b><a class=\"thre_title\" href=\"{$_conf['read_php']}?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}\"{$target_read_at}>{$hd['ttitle_name']}</a></b>\n";
echo "</p>\n";

if ($_conf['ktai']) {
    if (isset($info_msg)) {
        echo "<p>".$info_msg."</p>\n";
    }
}

if (checkRecent($aThread->host, $aThread->bbs, $aThread->key) or checkResHist($aThread->host, $aThread->bbs, $aThread->key)) {
    $offrec_ht = " / [<a href=\"info.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;offrec=true{$popup_ht}{$ttitle_en_ht}\" title=\"���̃X�����u�ŋߓǂ񂾃X���v�Ɓu�������ݗ����v����O���܂�\">��������O��</a>]";
}

if (!$_conf['ktai']) {
    echo "<table cellspacing=\"0\">\n";
}
print_info_line("���X��", "<a href=\"{$motothre_url_ime}\"{$target_read_at}>{$motothre_url}</a>");
if (!$_conf['ktai']) {
    print_info_line("�z�X�g", $aThread->host);
}
print_info_line("��", "<a href=\"{$_conf['subject_php']}?host={$aThread->host}&amp;bbs={$aThread->bbs}\"{$target_sb_at}>{$hd['itaj']}</a>");
if (!$_conf['ktai']) {
    print_info_line("key", $aThread->key);
}
if ($existLog) {
    print_info_line("���O", "���� [<a href=\"info.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;dele=true{$popup_ht}{$ttitle_en_ht}\">�폜����</a>]{$offrec_ht}");
} else {
    print_info_line("���O", "���擾{$offrec_ht}");
}
if ($aThread->gotnum) {
    print_info_line("�������X��", $aThread->gotnum);
} elseif (!$aThread->gotnum and $existLog) {
    print_info_line("�������X��", "0");
} else {
    print_info_line("�������X��", "-");
}
if ($aThread->length) {print_info_line("�擾�T�C�Y", $aThread->length);}

if (!$_conf['ktai']) {
    if (file_exists($aThread->keydat)) {
        if ($aThread->length) {
            print_info_line("dat�T�C�Y", $aThread->length.' �o�C�g');
        }
        print_info_line("dat", $aThread->keydat);
    } else {
        print_info_line("dat", "-");
    }
    if (file_exists($aThread->keyidx)) {
        print_info_line("idx", $aThread->keyidx);
    } else {
        print_info_line("idx", "-");
    }
}

print_info_line("���C�ɃX��", $fav_ht);
print_info_line("�a������", $pal_ht);
print_info_line("�\��", $taborn_ht);

if (!$_conf['ktai']) {
    echo "</table>\n";
}

if (!$_conf['ktai']) {
    if (isset($info_msg)) {
        echo "<span class=\"infomsg\">".$info_msg."</span>\n";
    } else {
        echo "�@\n";
    }
}

// ����{�^��
if (!empty($_GET['popup'])) {
    echo '<div align="center">';
    if ($_GET['popup'] == 1) {
        echo '<form action=""><input type="button" value="�E�B���h�E�����" onclick="window.close();"></form>';
    } elseif ($_GET['popup'] == 2) {
        echo <<<EOP
    <form action=""><input id="timerbutton" type="button" value="Close Timer" onclick="stopTimer(document.getElementById('timerbutton'))"></form>
EOP;
    }
    echo "</div>\n";
}

if ($_conf['ktai']) {
    echo "<hr>".$_conf['k_to_index_ht'];
}

echo '</body></html>';

//===============================================
// ���֐�
//===============================================
function print_info_line($s, $c)
{
    global $_conf;
    if ($_conf['ktai']) {
        echo "{$s}: {$c}<br>";
    } else {
        echo "<tr><td class=\"tdleft\" nowrap><b>{$s}</b>&nbsp;</td><td class=\"tdcont\">{$c}</td></tr>\n";
    }
}

?>
