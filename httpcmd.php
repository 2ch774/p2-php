<?php
/*
    cmd �������ŃR�}���h����
    �Ԃ�l�́A�e�L�X�g�ŕԂ�
*/

include_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// {{{ HTTP�w�b�_��XML�錾

P2Util::header_nocache();
header('Content-Type: text/html; charset=Shift_JIS');

// }}}

$r_msg = "";

// cmd���w�肳��Ă��Ȃ���΁A�����Ԃ����ɏI��
if (!isset($_GET['cmd']) && !isset($_POST['cmd'])) {
    die('');
}

// �R�}���h�擾
if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
} elseif (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
}

// {{{ ���O�폜

if ($cmd == 'delelog') {
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key'])) {
        include_once P2_LIBRARY_DIR . '/dele.inc.php';
        $r = deleteLogs($_REQUEST['host'], $_REQUEST['bbs'], array($_REQUEST['key']));
        if (empty($r)) {
            $r_msg = "0"; // ���s
        } elseif ($r == 1) {
            $r_msg = "1"; // ����
        } elseif ($r == 2) {
            $r_msg = "2"; // �Ȃ�
        }
    }

// }}}
// {{{ ���C�ɃX��

} elseif ($cmd == 'setfav') {
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key']) && isset($_REQUEST['setfav'])) {
        include_once P2_LIBRARY_DIR . '/setfav.inc.php';
        if (isset($_REQUEST['setnum'])) {
            $r = setFav($_REQUEST['host'], $_REQUEST['bbs'], $_REQUEST['key'], $_REQUEST['setfav'], $_REQUEST['setnum']);
        } else {
            $r = setFav($_REQUEST['host'], $_REQUEST['bbs'], $_REQUEST['key'], $_REQUEST['setfav']);
        }
        if (empty($r)) {
            $r_msg = "0"; // ���s
        } elseif ($r == 1) {
            $r_msg = "1"; // ����
        }
    }

// }}}
// {{{ �X���b�h���ځ[��

} elseif ($cmd == 'taborn') {
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key']) && isset($_REQUEST['taborn'])) {
        include_once P2_LIBRARY_DIR . '/settaborn.inc.php';
        $r = settaborn($_REQUEST['host'], $_REQUEST['bbs'], $_REQUEST['key'], $_REQUEST['taborn']);
        if (empty($r)) {
            $r_msg = "0"; // ���s
        } elseif ($r == 1) {
            $r_msg = "1"; // ����
        }
    }
}
// }}}
// {{{ ���ʏo��

if (P2Util::isBrowserSafariGroup()) {
    $r_msg = P2Util::encodeResponseTextForSafari($r_msg);
}
echo $r_msg;

// }}}
