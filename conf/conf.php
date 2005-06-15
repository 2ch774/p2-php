<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 - ��{�ݒ�t�@�C��

    ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
*/

$_conf['p2version'] = "1.5.x";
$_conf['p2expack'] = "050610.0100";

$_conf['p2name'] = "WaterWeasel";   // p2�̖��O
//$_conf['p2name'] = "p2";
//$_conf['p2name'] = "P2";
//$_conf['p2name'] = "p++";
$_conf['p2name_ua'] = $_conf['p2name'];
//$_conf['p2version_ua'] = $_conf['p2version'];
$_conf['p2version_ua'] = $_conf['p2expack'];

//======================================================================
// ��{�ݒ菈��
//======================================================================

$_info_msg_ht = '';

// {{{ ��������m�F

if (version_compare(phpversion(), '4.3.0', 'lt')) {
    die('<html><body><h1>p2 info: PHP�o�[�W����4.3.0�����ł͎g���܂���B</h1></body></html>');
}
if (ini_get('safe_mode')) {
    die('<html><body><h1>p2 info: �Z�[�t���[�h�œ��삷��PHP�ł͎g���܂���B</h1></body></html>');
}
if (!extension_loaded('mbstring')) {
    die('<html><body><h1>p2 info: mbstring�g�����W���[�������[�h����Ă��܂���B</h1></body></html>');
}

// }}}
// {{{ ���ݒ�

// �G���[�o�͂�ݒ�
error_reporting(E_ALL ^ E_NOTICE);

// �^�C���]�[�����Z�b�g
putenv('TZ=JST-9');

// �X�N���v�g���s��������(�b)
set_time_limit(60);

// �����t���b�V�����I�t�ɂ���
ob_implicit_flush(0);

// �N���C�A���g����ڑ���؂��Ă������𑱍s����
ignore_user_abort(1);

// session.trans_sid�L���� �� output_add_rewrite_var(), http_build_query() ���Ő����E�ύX�����
// URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
ini_set('arg_separator.output', '&amp;');

// ���N�G�X�gID��ݒ�
define('P2_REQUEST_ID', substr($_SERVER['REQUEST_METHOD'], 0, 1) . md5(serialize($_REQUEST)));

// OS����
if (strstr(PHP_OS, 'WIN')) {
    // Windows
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ';');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '\\');
} else {
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ':');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '/');
}

// ���������ɂ����镶���R�[�h�w��
mb_internal_encoding('SJIS-win');
mb_http_output('pass');
mb_substitute_character(63); // �����R�[�h�ϊ��Ɏ��s���������� "?" �ɂȂ�

if (function_exists('mb_ereg_replace')) {
    define('P2_MBREGEX_AVAILABLE', 1);
    mb_regex_encoding('SJIS-win');
} else {
    define('P2_MBREGEX_AVAILABLE', 0);
}

// DB_DataObject��PHP 4.3.10�̃o�O���
if (phpversion() == '4.3.10' && !defined('DB_DATAOBJECT_NO_OVERLOAD')) {
    define('DB_DATAOBJECT_NO_OVERLOAD', TRUE);
}

// }}}
// {{{ ���C�u�����ނ̃p�X�ݒ�

// ��{�I�ȋ@�\��񋟂��邷�郉�C�u����
define('P2_LIBRARY_DIR', './lib');

// ���܂��I�ȋ@�\��񋟂��邷�郉�C�u����
define('P2EX_LIBRARY_DIR', './lib/expack');

// �X�^�C���V�[�g
define('P2_STYLE_DIR', './style');

// PEAR�C���X�g�[���f�B���N�g���A�����p�X�ɒǉ������
define('P2_PEAR_DIR', './includes');

// PEAR���n�b�N�����t�@�C���p�f�B���N�g���A�ʏ��PEAR���D��I�Ɍ����p�X�ɒǉ������
// Cache/Container/db.php(PEAR::Cache)��MySQL���肾�����̂ŁA�ėp�I�ɂ������̂�u���Ă���
define('P2_PEAR_HACK_DIR', './lib/pear_hack');

// �����p�X���Z�b�g
if (is_dir(P2_PEAR_DIR) || is_dir(P2_PEAR_HACK_DIR)) {
    $_include_path = '.';
    if (is_dir(P2_PEAR_HACK_DIR)) {
        $_include_path .= PATH_SEPARATOR . realpath(P2_PEAR_HACK_DIR);
    }
    if (is_dir(P2_PEAR_DIR)) {
        $_include_path .= PATH_SEPARATOR . realpath(P2_PEAR_DIR);
    }
    $_include_path .= PATH_SEPARATOR . ini_get('include_path');
    set_include_path($_include_path);
}

// ���[�e�B���e�B�N���X��ǂݍ���
require_once (P2_LIBRARY_DIR . '/p2util.class.php');

// }}}
// {{{ PEAR::PHP_Compat��PHP5�݊��̊֐���ǂݍ���

if (version_compare(phpversion(), '5.0.0', '<')) {
    require_once 'PHP/Compat.php';
    PHP_Compat::loadFunction('clone');
    PHP_Compat::loadFunction('scandir');
    PHP_Compat::loadFunction('http_build_query');
    PHP_Compat::loadFunction('array_walk_recursive');
}

// }}}
// {{{ �t�H�[������̓��͂��ꊇ�ŃT�j�^�C�Y

// ���������h�~�̂��߃t�H�[����accept-encoding������UTF-8(Safari�n) or Shift_JIS(���̑�)�ɂ��A
// �딻��h�~�̂���hidden�v�f�̐擪�ɔ����e�[�u���̕������d���ށB
// �ϊ�������eucJP-win������̂�HTTP���͂̕����R�[�h��EUC�Ɏ����ϊ������T�[�o�̂��߁B
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_REQUEST = &$_POST;
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $_REQUEST = &$_GET;
}
if (!empty($_REQUEST)) {
    if (get_magic_quotes_gpc()) {
        array_walk_recursive($_REQUEST, 'stripslashes_cb');
    }
    mb_convert_variables('SJIS-win', 'UTF-8,eucJP-win,SJIS-win', $_REQUEST);
    array_walk_recursive($_REQUEST, 'nullfilter_cb');
}

// }}}
// {{{ �[������

require_once 'Net/UserAgent/Mobile.php';
$mobile = &Net_UserAgent_Mobile::singleton();

// PC
if ($mobile->isNonMobile()) {
    $_conf['ktai'] = FALSE;
    $_conf['enable_cookie'] = TRUE;

    if (P2Util::isBrowserSafariGroup()) {
        $_conf['accept_charset'] = 'UTF-8';
    } else {
        $_conf['accept_charset'] = 'Shift_JIS';
    }

// �g��
} else {
    $_conf['ktai'] = TRUE;
    $_conf['accept_charset'] = 'Shift_JIS';

    // �x���_����
    // DoCoMo i-Mode
    if ($mobile->isDoCoMo()) {
        $_conf['enable_cookie'] = FALSE;
    // EZweb (au or Tu-Ka)
    } elseif ($mobile->isEZweb()) {
        $_conf['enable_cookie'] = TRUE;
    // Vodafone Live!
    } elseif ($mobile->isVodafone()) {
        $_conf['accesskey'] = 'DIRECTKEY';
        // W�^�[����3GC�^�[����Cookie���g����
        if ($mobile->isTypeW() || $mobile->isType3GC()) {
            $_conf['enable_cookie'] = TRUE;
        } else {
            $_conf['enable_cookie'] = FALSE;
        }
    // AirH" Phone
    } elseif ($mobile->isAirHPhone()) {
        $_conf['enable_cookie'] = TRUE;
    // ���̑�
    } else {
        $_conf['enable_cookie'] = FALSE;
    }
}

// }}}
// {{{ �N�G���[�ɂ�鋭���r���[�w��

// b=pc �͂܂������N�悪���S�łȂ�
// output_add_rewrite_var() �͕֗������A�o�͂��o�b�t�@����đ̊����x��������̂���_�B�B
// �̊����x�𗎂Ƃ��Ȃ��ǂ����@�Ȃ����ȁH

$_conf['b'] = NULL;
$_conf['b_force_view'] = FALSE;

if (isset($_GET['b'])) {
    $_conf['b'] = $_GET['b'];
} elseif (isset($_POST['b'])) {
    $_conf['b'] = $_POST['b'];
} elseif (!empty($_GET['k']) || !empty($_POST['k'])) {
    $_conf['b'] = 'k';
}

// PC�i�g�т�b=pc�j
if ($_conf['ktai'] && $_conf['b'] == 'pc') {
    $_conf['ktai'] = FALSE;
    $_conf['b_force_view'] = TRUE;

// �g�сiPC��b=k�Bk=1�͉ߋ��݊��p�j
} elseif (!$_conf['ktai'] && $_conf['b'] == 'k') {
    $_conf['ktai'] = TRUE;
    $_conf['b_force_view'] = TRUE;
}

// }}}
// {{{ �r���[�ϐ��ݒ�

// �g��
if ($_conf['ktai']) {
    $_conf['accesskey'] = 'accesskey';
    $_conf['k_accesskey']['matome'] = '3';  // �V�܂Ƃ� // 3
    $_conf['k_accesskey']['latest'] = '3';  // �V // 9
    $_conf['k_accesskey']['res'] = '7';     // ڽ
    $_conf['k_accesskey']['above'] = '2';   // �� // 2
    $_conf['k_accesskey']['up'] = '5';      // �i�j // 5
    $_conf['k_accesskey']['prev'] = '4';    // �O // 4
    $_conf['k_accesskey']['bottom'] = '8';  // �� // 8
    $_conf['k_accesskey']['next'] = '6';    // �� // 6
    $_conf['k_accesskey']['info'] = '9';    // ��
    $_conf['k_accesskey']['dele'] = '*';    // ��
    $_conf['k_accesskey']['filter'] = '#';  // ��

    $_conf['k_to_index_ht'] = "<a {$_conf['accesskey']}=\"0\" href=\"index.php\">0.TOP</a>";

    $_conf['meta_charset_ht'] = '';
    $_conf['doctype'] = '';
/*
    if ($mobile->isWAP2()) {
        $_conf['doctype'] = <<<EOP
<?xml version="1.0" encoding="Shift_JIS"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"
 "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">\n
EOP;
    }
*/

// PC
} else {
    $_conf['meta_charset_ht'] = '<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">';
    // DOCTYPE HTML �錾
    $ie_strict = false;
    if ($ie_strict) {
        $_conf['doctype'] = <<<EOP
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">\n
EOP;
    } else {
        $_conf['doctype'] = <<<EOP
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">\n
EOP;
    }
}

// }}}
// {{{ ���[�U�ݒ�

include_once 'conf/conf_user.php'; // ���[�U�ݒ� �Ǎ�
include_once 'conf/conf_user_ex.php'; // �g���p�b�N���[�U�ݒ� �Ǎ�

$_conf['display_threads_num'] = 150; // (150) �X���b�h�T�u�W�F�N�g�ꗗ�̃f�t�H���g�\����
$_conf['posted_rec_num'] = 1000; // (1000) �������񂾃��X�̍ő�L�^�� //���݂͋@�\���Ă��Ȃ�

$_conf['p2status_dl_interval'] = 360;   // (360) p2status�i�A�b�v�f�[�g�`�F�b�N�j�̃L���b�V�����X�V�����ɕێ����鎞�� (��)

/* �f�t�H���g�ݒ� */
if (!isset($login['use'])) { $login['use'] = 1; }
if (!is_dir($_conf['pref_dir'])) { $_conf['pref_dir'] = "./data"; }
if (!is_dir($datdir)) { $datdir = "./data"; }
if (!isset($_conf['rct_rec_num'])) { $_conf['rct_rec_num'] = 20; }
if (!isset($_conf['res_hist_rec_num'])) { $_conf['res_hist_rec_num'] = 20; }
if (!isset($_conf['posted_rec_num'])) { $_conf['posted_rec_num'] = 1000; }
if (!isset($_conf['before_respointer'])) { $_conf['before_respointer'] = 20; }
if (!isset($_conf['sort_zero_adjust'])) { $_conf['sort_zero_adjust'] = 0.1; }
if (!isset($_conf['display_threads_num'])) { $_conf['display_threads_num'] = 150; }
if (!isset($_conf['cmp_dayres_midoku'])) { $_conf['cmp_dayres_midoku'] = 1; }
if (!isset($_conf['k_sb_disp_range'])) { $_conf['k_sb_disp_range'] = 30; }
if (!isset($_conf['k_rnum_range'])) { $_conf['k_rnum_range'] = 10; }
if (!isset($_conf['pre_thumb_height'])) { $_conf['pre_thumb_height'] = "32"; }
if (!isset($_conf['quote_res_view'])) { $_conf['quote_res_view'] = 1; }
if (!isset($_conf['res_write_rec'])) { $_conf['res_write_rec'] = 1; }
if (!isset($_conf['frame_type'])) { $_conf['frame_type'] = 0; }
if (!isset($_conf['frame_cols'])) { $_conf['frame_cols'] = "156,*"; }
if (!isset($_conf['frame_rows'])) { $_conf['frame_rows'] = "40%,60%"; }

if (!isset($STYLE['post_pop_size'])) { $STYLE['post_pop_size'] = "610,350"; }
if (!isset($STYLE['post_msg_rows'])) { $STYLE['post_msg_rows'] = 10; }
if (!isset($STYLE['post_msg_cols'])) { $STYLE['post_msg_cols'] = 70; }
if (!isset($STYLE['info_pop_size'])) { $STYLE['info_pop_size'] = "600,380"; }

/* ���[�U�ݒ�̒������� */
$_conf['ext_win_target_at'] = '';
$_conf['bbs_win_target_at'] = '';
$_conf['accept_charset_at'] = '';
if (!$_conf['ktai']) {
    $_conf['ext_win_target'] && $_conf['ext_win_target_at'] = " target=\"{$_conf['ext_win_target']}\"";
    $_conf['bbs_win_target'] && $_conf['bbs_win_target_at'] = " target=\"{$_conf['bbs_win_target']}\"";
    $_conf['accept_charset'] && $_conf['accept_charset_at'] = " accept-charset=\"{$_conf['accept_charset']}\"";
}

if ($_conf['get_new_res']) {
    if ($_conf['get_new_res'] == 'all') {
        $_conf['get_new_res_l'] = $_conf['get_new_res'];
    } else {
        $_conf['get_new_res_l'] = 'l'.$_conf['get_new_res'];
    }
} else {
    $_conf['get_new_res_l'] = 'l200';
}

// }}}
// {{{ �ϐ��ݒ�

$_conf['login_log_rec']       = 1;  // ���O�C�����O�̋L�^��
$_conf['login_log_rec_num']   = 100;    // ���O�C�����O�̋L�^��
$_conf['last_login_log_show'] = 1;  // �O�񃍃O�C�����\����

$_conf['p2web_url']         = 'http://akid.s17.xrea.com/';
$_conf['p2ime_url']         = 'http://akid.s17.xrea.com/p2ime.php';
$_conf['favrank_url']       = 'http://akid.s17.xrea.com:8080/favrank/favrank.php';
$_conf['expack_url']        = 'http://moonshine.s32.xrea.com/';
$_conf['tgrep_url']         = 'http://moonshine.s32.xrea.com/test/tgrep.cgi';
$_conf['menu_php']          = 'menu.php';
$_conf['subject_php']       = 'subject.php';
$_conf['subject_rss_php']   = 'subject_rss.php';
$_conf['read_php']          = 'read.php';
$_conf['read_new_php']      = 'read_new.php';
$_conf['read_new_k_php']    = 'read_new_k.php';
$_conf['rct_file']          = $_conf['pref_dir'] . '/p2_recent.idx';
$_conf['cache_dir']         = $_conf['pref_dir'] . '/p2_cache';
$_conf['cookie_dir']        = $_conf['pref_dir'] . '/p2_cookie';    // cookie �ۑ��f�B���N�g��
$_conf['cookie_file_name']  = 'p2_cookie.txt';
$_conf['favlist_file']      = $_conf['pref_dir'] . '/p2_favlist.idx';
$_conf['favita_path']       = $_conf['pref_dir'] . '/p2_favita.brd';
$_conf['idpw2ch_php']       = $_conf['pref_dir'] . '/p2_idpw2ch.php';
$_conf['sid2ch_php']        = $_conf['pref_dir'] . '/p2_sid2ch.php';
$_conf['auth_user_file']    = $_conf['pref_dir'] . '/p2_auth_user.php';
$_conf['auth_ez_file']      = $_conf['pref_dir'] . '/p2_auth_ez.php';
$_conf['auth_jp_file']      = $_conf['pref_dir'] . '/p2_auth_jp.php';
$_conf['login_log_file']    = $_conf['pref_dir'] . '/p2_login.log.php';

// saveMatomeCache() �̂��߂� $_conf['pref_dir'] ���΃p�X�ɕϊ�����
// �����ɂ���ẮArealpath() �Œl���擾�ł��Ȃ��ꍇ������H
if ($rp = realpath($_conf['pref_dir'])) {
    $_conf['matome_cache_path'] = $rp.'/matome_cache';
} else {
    if (substr($_conf['pref_dir'], 0, 1) == '/') {
        $_conf['matome_cache_path'] = $_conf['pref_dir'] . '/matome_cache';
    } else {
        $GLOBALS['pref_dir_realpath_failed_msg'] = 'p2 error: realpath()�̎擾���ł��܂���ł����B�t�@�C�� conf.inc.php �� $_conf[\'pref_dir\'] �����[�g����̐�΃p�X�w��Őݒ肵�Ă��������B';
    }
}

$_conf['matome_cache_ext']  = '.htm';
$_conf['matome_cache_max']  = 3;    // �\���L���b�V���̐�

$_conf['md5_crypt_key']        = $_SERVER['SERVER_NAME'].$_SERVER['SERVER_SOFTWARE'];
$_conf['menu_dl_interval']     = 1; // menu�̃L���b�V�����X�V�����ɕێ����鎞��(hour)
$_conf['fsockopen_time_limit'] = 10;    // (10) �l�b�g���[�N�ڑ��^�C���A�E�g����(�b)

$_conf['data_dir_perm']  = 0707;
$_conf['dat_perm']       = 0606;
$_conf['key_perm']       = 0606;
$_conf['pass_perm']      = 0604;
$_conf['p2_perm']        = 0606;    // �����Ă����܂�Ӗ��̂Ȃ����������f�[�^�t�@�C��
$_conf['palace_perm']    = 0606;
$_conf['favita_perm']    = 0606;
$_conf['favlist_perm']   = 0606;
$_conf['rct_perm']       = 0606;
$_conf['res_write_perm'] = 0606;

// }}}
// {{{ �g���p�b�N �ϐ��ݒ�

$_conf['favset_num'] = 5;
$_conf['favset_file'] = $_conf['pref_dir'] . '/p2_favset.txt';

if ($enable_expack) {
    $_conf['skin_file'] = $_conf['pref_dir'].'/p2_user_skin.txt';
    $_conf['skin_perm'] = 0606;
    $_conf['rss_file']  = $_conf['pref_dir'].'/p2_rss.txt';
    $_conf['rss_perm']  = 0606;
    // �A�N�e�B�u���i�[��AA�����mb_ereg()���g������
    if (!function_exists('mb_ereg')) {
        $_exconf['aMona']['*'] = 0;
    }
    // Mac�ł�Safari�n�i�Ƃ�����NSTextView?�j�ȊO�͑S�p�v���|�[�V���i���t�H���g��Ή��Ȃ̂�
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac') && !(P2Util::isBrowserSafariGroup())) {
        $_exconf['aMona']['*'] = 0;
        $_exconf['spm']['with_aMona'] = 0;
        $_exconf['editor']['with_aMona'] = 0;
    }
} else {
    $_exconf['kanban']['*'] = 0;
    $_exconf['skin']['*'] = 0;
    $_exconf['aMona']['*'] = 0;
    $_exconf['fitImage']['*'] = 0;
    $_exconf['editor']['*'] = 0;
    $_exconf['bookmark']['*'] = 0;
    $_exconf['spm']['*'] = 0;
    $_exconf['rss']['*'] = 0;
    $_exconf['flex']['*'] = 0;
    $_exconf['imgCache']['*'] = 0;
    $_exconf['liveView']['*'] = 0;
    $_exconf['soap']['*'] = 0;
}

// }}}
// {{{ �z�X�g�`�F�b�N

if ($_exconf['secure']['auth_host'] || $_exconf['secure']['auth_bbq']) {
    require_once (P2EX_LIBRARY_DIR . '/hostcheck.class.php');
    if (($_exconf['secure']['auth_host'] && HostCheck::getHostAuth() == FALSE) ||
        ($_exconf['secure']['auth_bbq'] && HostCheck::getHostBurned() == TRUE)
    ) {
        HostCheck::forbidden();
    }
}

// }}}
// {{{ �f�U�C���ݒ� �ǂݍ���

if (isset($_GET['skin']) && preg_match('/^\w+$/', $_GET['skin'])) {
    $skin_name = $_GET['skin'];
    $skin = 'skin/' . $skin_name . '.php';
} elseif (isset($_conf['skin_file'])) {
    if (file_exists($_conf['skin_file'])) {
        $skin_name = rtrim(array_shift(file($_conf['skin_file'])));
        $skin = 'skin/' . $skin_name . '.php';
    } else {
        require_once (P2_LIBRARY_DIR . '/filectl.class.php');
        FileCtl::make_datafile($_conf['skin_file'], $_conf['skin_perm']);
    }
}

if (!isset($skin) || !file_exists($skin)) {
    $skin_name = 'conf_user_style';
    $skin = 'conf/conf_user_style.php';
}

$skin_en = rawurlencode($skin_name);

@include_once ($skin);

if (is_array($STYLE)) {
    foreach ($STYLE as $sKey => $sValue) {
        if (strstr($sKey, 'background') && $sValue != "") {
            $STYLE[$sKey] = 'url("' . str_replace("'", "\\'", $sValue) . '")';
        }
        if (strstr($sKey, 'fontfamily') && is_array($sValue)) {
            $STYLE[$sKey] = implode('","', $sValue);
            $STYLE[$sKey] = preg_replace('/"(serif|sans-serif|cursive|fantasy|monospace)"/', "$1", $STYLE[$sKey]);
        }
        if (is_string($sValue) && preg_match('/^#([0-9A-Fa-f])([0-9A-Fa-f])([0-9A-Fa-f])$/', $sValue, $sMatch)) {
            $STYLE[$sKey] = '#'.$sMatch[1].$sMatch[1].$sMatch[2].$sMatch[2].$sMatch[3].$sMatch[3];
        }
    }
}

if (isset($k_ngword_color)) {
    $STYLE['read_ngword'] = $k_ngword_color;
}

// }}}
// {{{ �J���[�����O�ݒ�i���r�L�^�X�j

$k_color_settings = '';
if ($_conf['ktai']) {
    if ($_exconf['ubiq']['c_bgcolor']) {
        $k_color_settings .= " bgcolor=\"{$_exconf['ubiq']['c_bgcolor']}\"";
    }
    if ($_exconf['ubiq']['c_text']) {
        $k_color_settings .= " text=\"{$_exconf['ubiq']['c_text']}\"";
    }
    if ($_exconf['ubiq']['c_link']) {
        $k_color_settings .= " link=\"{$_exconf['ubiq']['c_link']}\"";
    }
    if ($_exconf['ubiq']['c_vlink']) {
        $k_color_settings .= " vlink=\"{$_exconf['ubiq']['c_vlink']}\"";
    }
    if ($_exconf['ubiq']['c_ngword']) {
        $k_ngword_color = $_exconf['ubiq']['c_ngword'];
    }
    // �g�їp�}�[�J�[
    if ($_exconf['ubiq']['c_match'] || $_exconf['ubiq']['b_match']) {
        $k_filter_marker = '\\1';
        if ($_exconf['ubiq']['c_match']) {
            $k_filter_marker = "<font color=\"{$_exconf['ubiq']['c_match']}\">" . $k_filter_marker . "</font>";
        }
        if ($_exconf['ubiq']['b_match']) {
            $k_filter_marker = '<b>' . $k_filter_marker . '</b>';
        }
    } else {
        $k_filter_marker = FALSE;
    }
}

// }}}
// {{{ �o�̓o�b�t�@�����O�𔺂��@�\

// KeepAlive�ڑ����g�����߂�Content-Length�w�b�_���o�͂���B
// �L���ɂ���Ə����ł���������\�����邱�Ƃ��ł��Ȃ��Ȃ�A�̊���̑��x�͒x���Ȃ�B
// ���̃o�b�t�@�����O�𔺂��֐����R�[�������O�Ɏ��s���Ȃ��Ɛ�����Contant-Length���擾�ł��Ȃ��̂Œ��ӁB
$_keep_alive = FALSE;
if ($_keep_alive) {
    ob_start(array('P2Util', 'header_content_length'));
}

// �p�P�b�g�ߖ�
if ($_conf['ktai'] && $_exconf['ubiq']['save_packet']) {
    require_once (P2EX_LIBRARY_DIR . '/packetsaver.inc.php');
    ob_start('packet_saver');
    if (extension_loaded('tidy')) {
        define('P2_TIDY_REPAIR_OUTPUT', 1);
    }
}

// �N�G���ɂ�鋭���r���[�ݒ�̂Ƃ�
if ($_conf['b'] && $_conf['b_force_view']) {
    output_add_rewrite_var('b', $_conf['b']);
}

// }}}
// {{{ �Z�b�V����

// ���d�v��
// php.ini �� session.auto_start = 0 (PHP�̃f�t�H���g�̂܂�) �ɂȂ��Ă��邱�ƁB
// �����Ȃ��ƂقƂ�ǂ̃Z�b�V�����֘A�̃p�����[�^���X�N���v�g���ŕύX�ł��Ȃ��B
// .htaccess�ŕύX��������Ă���Ȃ�
/*
<IfModule mod_php4.c>
    php_flag session.auto_start Off
</IfModule>
*/
// �ł�OK�B

// ���C�ɓ���Z�b�g�̐؂�ւ��@�\���L���Ȃ�Z�b�V�����J�n
if ($_exconf['etc']['multi_favs']) {
    require_once (P2_LIBRARY_DIR . '/favsetmng.class.php');

    // eAccelerator�̃Z�b�V�����n���h�����g���Ă݂�
    /*if (extension_loaded('eAccelerator')) {
        eaccelerator_set_session_handlers();
    }*/

    // �Z�b�V�����f�[�^�ۑ��f�B���N�g����ݒ�
    if (session_module_name() == 'files') {
        $_conf['session_dir'] = $_conf['pref_dir'] . '/p2_session';

        if (!is_dir($_conf['session_dir'])) {
            require_once (P2_LIBRARY_DIR . '/filectl.class.php');
            FileCtl::mkdir_for($_conf['session_dir'] . '/dummy_filename');
        } elseif (!is_writable($_conf['session_dir'])) {
            die("Error: �Z�b�V�����f�[�^�ۑ��f�B���N�g�� ({$_conf['session_dir']}) �ɏ������݌���������܂���B");
        }

        session_save_path($_conf['session_dir']);

        // session.save_path �̃p�X�̐[����2���傫���ƃK�[�x�b�W�R���N�V�������s���Ȃ��̂�
        // ���O�ŃK�[�x�b�W�R���N�V��������
        P2Util::session_gc();
    }

    // �N�b�L�[���g�p�\�Ȓ[���ł̓Z�b�V����ID���N�b�L�[�n���Ɍ��肷��
    if ($_conf['enable_cookie']) {
        ini_set('session.use_only_cookies', '1');
        // �Z�b�V�����N�b�L�[�p�����[�^��ݒ�
        $_scp = session_get_cookie_params();
        if (dirname($_SERVER['PHP_SELF']) != '/') {
            $_scp[1] = dirname($_SERVER['PHP_SELF']) . '/';
            session_set_cookie_params($_scp[0], $_scp[1], $_scp[2], $_scp[3]);
        }
        unset($_scp);
    } else {
        ini_set('session.use_only_cookies', '0');
    }

    // �Z�b�V�����J�n
    session_start();
    // ���C�ɃZ�b�g��؂�ւ���
    FavSetManager::switchFavSet();
    // �Z�b�V�����ϐ��̕ύX���K�v�Ȃ��Ȃ����炷���Z�b�V�������I������
    session_write_close();

    // session.use_trans_sid �̕ύX�̉ۂ� PHP_INI_SYSTEM|PHP_INI_PERDIR �Ȃ̂�
    // php.ini �� .htaccess �ł����ύX�ł��Ȃ��B
    // �[����Cookie��Ή��� session.use_trans_sid = 0 (PHP�̃f�t�H���g) �̃T�[�o��
    // output_add_rewrite_var() ���g���ăZ�b�V����ID���N�G���ɖ��ߍ��ށB
    if (!$_conf['enable_cookie'] && !ini_get('session.use_trans_sid')) {
        output_add_rewrite_var(session_name(), session_id());
//  } elseif (defined('SID') && SID != '') {
//      list($session_name, $session_id) = explode('=', SID);
//      output_add_rewrite_var($session_name, $session_id);
    }
}

// }}}
//======================================================================
// {{{ �֐�

/**
 * �F�؊֐�
 */
function authorize()
{
    global $login;

    if ($login['use']) {

        include_once (P2_LIBRARY_DIR . '/login.inc.php');

        // �F�؃`�F�b�N
        if (!authCheck()) {
            // ���O�C�����s
            include_once (P2_LIBRARY_DIR . '/login_first.inc.php');
            printLoginFirst();
            exit;
        }

        // �v��������΁A�⏕�F�؂�o�^
        registCookie();
        registKtaiId();
     }

    return true;
}

/**
 * array_walk(_recursive)�p�̃R�[���o�b�N�֐�
 */
function addslashes_cb(&$value, $key)
{
    $value = addslashes($value);
}
function stripslashes_cb(&$value, $key)
{
    $value = stripslashes($value);
}
function nullfilter_cb(&$value, $key)
{
    $value = str_replace(chr(0), '', $value);
}

// }}}
?>
