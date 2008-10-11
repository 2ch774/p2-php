<?php
/**
 * rep2 - Ajax
 * cmd �������ŃR�}���h����
 * �Ԃ�l�́A�e�L�X�g�ŕԂ�
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// {{{ HTTP�w�b�_��XML�錾

P2Util::header_nocache();
header('Content-Type: text/html; charset=Shift_JIS');

// }}}

$r_msg = '';

// �R�}���h�擾 (�w�肳��Ă��Ȃ���΁A�����Ԃ����ɏI��)
if (!isset($_REQUEST['cmd'])) {
    exit;
} else {
    $cmd = $_REQUEST['cmd'];
}

switch ($cmd) {
// {{{ ���O�폜

case 'delelog':
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key'])) {
        require_once P2_LIB_DIR . '/dele.inc.php';
        $r = deleteLogs($_REQUEST['host'], $_REQUEST['bbs'], array($_REQUEST['key']));
        if (empty($r)) {
            $r_msg = "0"; // ���s
        } elseif ($r == 1) {
            $r_msg = "1"; // ����
        } elseif ($r == 2) {
            $r_msg = "2"; // �Ȃ�
        }
    }
    break;

// }}}
// {{{ ���C�ɃX��

case 'setfav':
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key']) && isset($_REQUEST['setfav'])) {
        require_once P2_LIB_DIR . '/setfav.inc.php';
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
    break;

// }}}
// {{{ �X���b�h���ځ[��

case 'taborn':
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key']) && isset($_REQUEST['taborn'])) {
        require_once P2_LIB_DIR . '/settaborn.inc.php';
        $r = settaborn($_REQUEST['host'], $_REQUEST['bbs'], $_REQUEST['key'], $_REQUEST['taborn']);
        if (empty($r)) {
            $r_msg = "0"; // ���s
        } elseif ($r == 1) {
            $r_msg = "1"; // ����
        }
    }
    break;

// }}}
// {{{ ImageCaceh2 ON/OFF

case 'ic2':
    if (isset($_REQUEST['switch'])) {
        require_once P2EX_LIB_DIR . '/ic2/Switch.php';
        $switch = (bool)$_REQUEST['switch'];
        if (IC2_Switch::set($switch, !empty($_REQUEST['mobile']))) {
            if ($switch) {
                $r_msg = '1'; // ON�ɂ���
            } else {
                $r_msg = '2'; // OFF�ɂ���
            }
        } else {
            $r_msg = '0'; // ���s
        }
    }
    break;

// }}}
}
// {{{ ���ʏo��

if (P2Util::isBrowserSafariGroup()) {
    $r_msg = P2Util::encodeResponseTextForSafari($r_msg);
}
echo $r_msg;

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
