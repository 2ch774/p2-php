<?php
/*
    rep2 - ��{�ݒ�t�@�C��

    ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
*/

$_conf['p2version'] = '1.7.26';     // rep2�̃o�[�W����
$_conf['p2expack'] = '060518.2348'; // ASAP�̃o�[�W����
$_conf['p2name'] = 'REP2EX-ASAP';   // rep2�̖��O�B

//======================================================================
// ��{�ݒ菈��
//======================================================================
error_reporting(E_ALL & ~E_NOTICE); // �G���[�o�͐ݒ�

// {{{ ��{�ϐ�

$_conf['p2web_url']             = 'http://akid.s17.xrea.com/';
$_conf['p2ime_url']             = 'http://akid.s17.xrea.com/p2ime.php';
$_conf['favrank_url']           = 'http://akid.s17.xrea.com:8080/favrank/favrank.php';
$_conf['expack.web_url']        = 'http://page2.xrea.jp/expack/';
$_conf['expack.download_url']   = 'http://page2.xrea.jp/expack/index.php/download';
$_conf['expack.history_url']    = 'http://page2.xrea.jp/expack/index.php/history#ASAP';
$_conf['expack.tgrep_url']      = 'http://page2.xrea.jp/tgrep/tgrep2-test.cgi';
$_conf['expack.ime_url']        = 'http://page2.xrea.jp/r.p';
$_conf['menu_php']              = 'menu.php';
$_conf['subject_php']           = 'subject.php';
$_conf['read_php']              = 'read.php';
$_conf['read_new_php']          = 'read_new.php';
$_conf['read_new_k_php']        = 'read_new_k.php';
$_conf['cookie_file_name']      = 'p2_cookie.txt';

$_info_msg_ht = ''; // ���[�U�ʒm�p ��񃁃b�Z�[�WHTML

// }}}
// {{{ �f�o�b�O

$debug = 0;
isset($_GET['debug']) and $debug = $_GET['debug'];

// }}}
// {{{ ��������m�F

$_php_version = phpversion();
$_required_version = '4.3.3';
$_recommended_version = (substr(zend_version(), 0, 1) == '1') ? '4.4.2' : '5.1.2';
if (version_compare($_php_version, $_required_version, '<')) {
    p2die('PHP ' . $_required_version . ' �����ł͎g���܂���B');
}
if (!extension_loaded('mbstring')) {
    p2die('PHP�̃C���X�g�[�����s�\���ł��Bmbstring�g�����W���[�������[�h����Ă��܂���B');
}
if (ini_get('safe_mode')) {
    p2die('�Z�[�t���[�h�œ��삷��PHP�ł͎g���܂���B');
}
if (ini_get('register_globals')) {
    $msg = <<<EOP
�\�����Ȃ����������邽�߂� php.ini �� register_globals �� Off �ɂ��Ă��������B
magic_quotes_gpc �� mbstring.encoding_translation �� Off �ɂ���邱�Ƃ��������߂��܂��B
EOP;
    p2die('register_globals �� On �ł��B', $msg);
}
if (true && version_compare($_php_version, $_recommended_version, '<')) {
    $_info_msg_ht .= '<p><b>�Â��o�[�W������PHP�œ��삵�Ă��܂��B</b> <i>(PHP ' . $_php_version . ')</i><br>';
    $_info_msg_ht .= 'PHP ' . $_recommended_version . ' �ȍ~�ɃA�b�v�f�[�g���邱�Ƃ��������߂��܂��B<br>';
    $_info_msg_ht .= '<small>�i���̃��b�Z�[�W��\�����Ȃ��悤�ɂ���ɂ� ' . htmlspecialchars(__FILE__, ENT_QUOTES) . ' �� ';
    $_info_msg_ht .= (__LINE__ - 4) . ' �s�ڂ� &quot;true&quot; �� &quot;false&quot; �ɏ������Ă��������j</small></p>';
}
if (version_compare($_php_version, '5.1.0', '>=')) {
    define('P2_PHP50', true);
    define('P2_PHP51', true);
} elseif (version_compare($_php_version, '5.0.0', '>=')) {
    define('P2_PHP50', true);
    define('P2_PHP51', false);
} else {
    define('P2_PHP50', false);
    define('P2_PHP51', false);
}

// }}}
// {{{ ���ݒ�

// �^�C���]�[�����Z�b�g
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Tokyo');
} else {
    putenv('TZ=JST-9');
}

@set_time_limit(60); // (60) �X�N���v�g���s��������(�b)

// �����t���b�V�����I�t�ɂ���
ob_implicit_flush(0);

// �N���C�A���g����ڑ���؂��Ă������𑱍s����
// ignore_user_abort(1);

// session.trans_sid�L���� �� output_add_rewrite_var(), http_build_query() ���Ő����E�ύX�����
// URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
ini_set('arg_separator.output', '&amp;');

// ���N�G�X�gID��ݒ�
define('P2_REQUEST_ID', substr($_SERVER['REQUEST_METHOD'], 0, 1) . md5(serialize($_REQUEST)));

// Windows �Ȃ�
if (strstr(PHP_OS, 'WIN')) {
    // Windows
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ';');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '\\');
} else {
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ':');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '/');
}

// }}}
// {{{ �����R�[�h�̎w��

// mb_detect_order("SJIS-win,eucJP-win,ASCII");
mb_internal_encoding('SJIS-win');
mb_http_output('pass');
mb_substitute_character(63); // �����R�[�h�ϊ��Ɏ��s���������� "?" �ɂȂ�
//mb_substitute_character(0x3013); // ��
// ob_start('mb_output_handler');

if (function_exists('mb_ereg_replace')) {
    define('P2_MBREGEX_AVAILABLE', 1);
    @mb_regex_encoding('SJIS-win');
} else {
    define('P2_MBREGEX_AVAILABLE', 0);
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
    set_include_path($_include_path . PATH_SEPARATOR . get_include_path());
}

// ���C�u������ǂݍ���
$_pear_required = array(
    'File/Util.php'             => 'File',
    'HTTP/Request.php'          => 'HTTP_Request',
    'Net/UserAgent/Mobile.php'  => 'Net_UserAgent_Mobile',
    'PHP/Compat.php'            => 'PHP_Compat',
);
if (!empty($debug)) {
    $_pear_required['Benchmark/Profiler.php'] = 'Benchmark';
}
foreach ($_pear_required as $_pear_file => $_pear_pkg) {
    if (!include_once($_pear_file)) {
        $url1 = 'http://akid.s17.xrea.com:8080/p2puki/pukiwiki.php?PEAR%A4%CE%A5%A4%A5%F3%A5%B9%A5%C8%A1%BC%A5%EB';
        $url2 = 'http://page2.xrea.jp/p2pear/index.php';
        $url1_t = P2Util::throughIme($url1);
        $url2_t = P2Util::throughIme($url2);
        $msg = <<<EOP
<ul>
    <li><a href="{$url1_t}" target="_blank">p2Wiki: PEAR�̃C���X�g�[��</a></li>
    <li><a href="{$url2_t}" target="_blank">p2pear (PEAR�l�ߍ��킹)</a></li>
</ul>
EOP;
        p2die('PEAR �� ' . $_pear_pkg . ' ���C���X�g�[������Ă��܂���B', $msg, true);
    }
}
require_once P2_LIBRARY_DIR . '/p2util.class.php';
require_once P2_LIBRARY_DIR . '/dataphp.class.php';
require_once P2_LIBRARY_DIR . '/session.class.php';
require_once P2_LIBRARY_DIR . '/login.class.php';

// }}}
// {{{ �f�o�b�O

if (!empty($debug)) {
    $profiler =& new Benchmark_Profiler(true);
    // printMemoryUsage();
    register_shutdown_function('printMemoryUsage');
}

// }}}
// {{{ PEAR::PHP_Compat��PHP5�݊��̊֐���ǂݍ���

if (!P2_PHP50) {
    PHP_Compat::loadFunction('array_walk_recursive');
    PHP_Compat::loadFunction('clone');
    PHP_Compat::loadFunction('file_put_contents');
    PHP_Compat::loadFunction('http_build_query');
    PHP_Compat::loadFunction('scandir');
}

// }}}
// {{{ �t�H�[������̓��͂��ꊇ�ŃT�j�^�C�Y

/**
 * �t�H�[������̓��͂��ꊇ�ŃN�H�[�g�����������R�[�h�ϊ�
 * �t�H�[����accept-encoding������UTF-8(Safari�n) or Shift_JIS(���̑�)�ɂ��A
 * �����hidden�v�f�Ŕ����e�[�u���̕������d���ނ��ƂŌ딻������炷
 * �ϊ�������eucJP-win������̂�HTTP���͂̕����R�[�h��EUC�Ɏ����ϊ������T�[�o�̂���
 */
if (!empty($_GET)) {
    if (get_magic_quotes_gpc()) {
        $_GET = array_map('stripslashes_r', $_GET);
    }
    mb_convert_variables('SJIS-win', 'UTF-8,eucJP-win,SJIS-win', $_GET);
    $_GET = array_map('nullfilter_r', $_GET);
}
if (!empty($_POST)) {
    if (get_magic_quotes_gpc()) {
        $_POST = array_map('stripslashes_r', $_POST);
    }
    mb_convert_variables('SJIS-win', 'UTF-8,eucJP-win,SJIS-win', $_POST);
    $_POST = array_map('nullfilter_r', $_POST);
    $_REQUEST = array_merge($_GET, $_POST);
} else {
    $_REQUEST = $_GET;
}

// }}}

// ���Ǘ��җp�ݒ��ǂݍ���
if (!include_once './conf/conf_admin.inc.php') {
    p2die('�Ǘ��җp�ݒ�t�@�C����ǂݍ��߂܂���ł����B');
}

// �Ǘ��p�ۑ��f�B���N�g�� (�p�[�~�b�V������707)
$_conf['admin_dir'] = $_conf['data_dir'] . '/admin';

// cache �ۑ��f�B���N�g�� (�p�[�~�b�V������707)
$_conf['cache_dir'] = $_conf['data_dir'] . '/cache'; // 2005/6/29 $_conf['pref_dir'] . '/p2_cache' ���ύX

$_conf['doctype'] = '';
$_conf['accesskey'] = 'accesskey';

$_conf['meta_charset_ht'] = '<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">';

// {{{ �[������

$_conf['login_check_ip']  = 1; // ���O�C������IP�A�h���X�����؂���
$_conf['input_type_search'] = FALSE;

$mobile = &Net_UserAgent_Mobile::singleton();

// PC
if ($mobile->isNonMobile()) {
    $_conf['ktai'] = FALSE;
    $_conf['disable_cookie'] = FALSE;

    if (P2Util::isBrowserSafariGroup()) {
        $_conf['accept_charset'] = 'UTF-8';
        $_conf['input_type_search'] = TRUE;
    } else {
        $_conf['accept_charset'] = 'Shift_JIS';
    }

// �g��
} else {
    require_once P2_LIBRARY_DIR . '/hostcheck.class.php';
    
    $_conf['ktai'] = TRUE;
    $_conf['accept_charset'] = 'Shift_JIS';

    // �x���_����
    // DoCoMo i-Mode
    if ($mobile->isDoCoMo()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrDocomo()) {
            p2die("UA��DoCoMo�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
        }
        $_conf['disable_cookie'] = TRUE;
    // EZweb (au or Tu-Ka)
    } elseif ($mobile->isEZweb()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrAu()) {
            p2die("UA��EZweb�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
        }
        $_conf['disable_cookie'] = FALSE;
    // Vodafone Live!
    } elseif ($mobile->isVodafone()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrVodafone()) {
            p2die("UA��Vodafone�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
        }
        //$_conf['accesskey'] = 'DIRECTKEY';
        // W�^�[����3GC�^�[����Cookie���g����
        if ($mobile->isTypeW() || $mobile->isType3GC()) {
            $_conf['disable_cookie'] = FALSE;
        } else {
            $_conf['disable_cookie'] = TRUE;
        }
    // AirH" Phone
    } elseif ($mobile->isAirHPhone()) {
        /*
        // AirH"�ł͒[��ID�F�؂��s��Ȃ��̂ŁA�R�����g�A�E�g
        if ($_conf['login_check_ip'] && !HostCheck::isAddrAirh()) {
            p2die("UA��AirH\"�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
        }
        */
        $_conf['disable_cookie'] = FALSE;
    // ���̑�
    } else {
        $_conf['disable_cookie'] = TRUE;
    }
}

// }}}
// {{{ �N�G���[�ɂ�鋭���r���[�w��

// b=pc �͂܂������N�悪���S�łȂ�
// output_add_rewrite_var() �͕֗������A�o�͂��o�b�t�@����đ̊����x��������̂���_�B�B
// �̊����x�𗎂Ƃ��Ȃ��ǂ����@�Ȃ����ȁH

$_conf['view_forced_by_query'] = false;
$_conf['k_at_a'] = '';
$_conf['k_at_q'] = '';
$_conf['k_input_ht'] = '';

// ����PC�r���[�w��
if ($_GET['b'] == 'pc' || $_POST['b'] == 'pc') {
    if ($_conf['ktai']) {
        $_conf['view_forced_by_query'] = true;
        $_conf['ktai'] = false;
    }
    $_conf['b'] = 'pc';
    //output_add_rewrite_var('b', 'pc');

    $_conf['k_at_a'] = '&amp;b=pc';
    $_conf['k_at_q'] = '?b=pc';
    $_conf['k_input_ht'] = '<input type="hidden" name="b" value="pc">';

// �����g�уr���[�w��ib=k�Bk=1�͉ߋ��݊��p�j
} elseif (!empty($_GET['k']) || !empty($_POST['k']) || $_GET['b'] == 'k' || $_POST['b'] == 'k') {
    if (!$_conf['ktai']) {
        $_conf['view_forced_by_query'] = true;
        $_conf['ktai'] = true;
    }
    $_conf['b'] = 'k';
    //output_add_rewrite_var('b', 'k');
    
    $_conf['k_at_a'] = '&amp;b=k';
    $_conf['k_at_q'] = '?b=k';
    $_conf['k_input_ht'] = '<input type="hidden" name="b" value="k">';
}
// }}}

$_conf['k_to_index_ht'] = <<<EOP
<a {$_conf['accesskey']}="0" href="index.php{$_conf['k_at_q']}">0.TOP</a>
EOP;

// {{{ DOCTYPE HTML �錾

$ie_strict = false;
if (empty($_conf['ktai'])) {
    if ($ie_strict) {
        $_conf['doctype'] = <<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">\n
EODOC;
    } else {
        $_conf['doctype'] = <<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">\n
EODOC;
    }
}

// }}}

//======================================================================

// {{{ �����[�U�ݒ� �Ǎ�

// �f�t�H���g�ݒ�iconf_user_def.inc.php�j��ǂݍ���
include_once './conf/conf_user_def.inc.php';
$_conf = array_merge($_conf, $conf_user_def);

// ���[�U�ݒ肪����Γǂݍ���
$_conf['conf_user_file'] = $_conf['pref_dir'] . '/conf_user.srd.cgi';

// ���`���t�@�C�����R�s�[
$conf_user_file_old = $_conf['pref_dir'] . '/conf_user.inc.php';
if (!file_exists($_conf['conf_user_file']) && file_exists($conf_user_file_old)) {
    $old_cont = DataPhp::getDataPhpCont($conf_user_file_old);
    FileCtl::make_datafile($_conf['conf_user_file'], $_conf['conf_user_perm']);
    file_put_contents($_conf['conf_user_file'], $old_cont);
}

$conf_user = array();
if (file_exists($_conf['conf_user_file'])) {
    if ($cont = file_get_contents($_conf['conf_user_file'])) {
        $conf_user = unserialize($cont);
        $_conf = array_merge($_conf, $conf_user);
    }
}

// }}}
// {{{ �f�t�H���g�ݒ�

if (!is_dir($_conf['pref_dir']))    { $_conf['pref_dir'] = "./data"; }
if (!is_dir($_conf['dat_dir']))     { $_conf['dat_dir'] = "./data"; }
if (!is_dir($_conf['idx_dir']))     { $_conf['idx_dir'] = "./data"; }
if (!isset($_conf['rct_rec_num']))  { $_conf['rct_rec_num'] = 20; }
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

// }}}
// {{{ ���[�U�ݒ�̒�������

$_conf['ext_win_target_at'] = ($_conf['ext_win_target']) ? " target=\"{$_conf['ext_win_target']}\"" : "";
$_conf['bbs_win_target_at'] = ($_conf['bbs_win_target']) ? " target=\"{$_conf['bbs_win_target']}\"" : "";

if ($_conf['get_new_res']) {
    if ($_conf['get_new_res'] == 'all') {
        $_conf['get_new_res_l'] = $_conf['get_new_res'];
    } else {
        $_conf['get_new_res_l'] = 'l'.$_conf['get_new_res'];
    }
} else {
    $_conf['get_new_res_l'] = 'l200';
}

if ($_conf['expack.user_agent']) {
    ini_set('user_agent', $_conf['expack.user_agent']);
}

// }}}
// {{{ �f�U�C���ݒ� �Ǎ�

$skin_name = 'conf_user_style';
$skin = 'conf/conf_user_style.inc.php';
if (!$_conf['ktai'] && $_conf['expack.skin.enabled']) {
    if (file_exists($_conf['expack.skin.setting_path'])) {
        $skin_name = rtrim(file_get_contents($_conf['expack.skin.setting_path']));
        $skin = 'skin/' . $skin_name . '.php';
    } else {
        require_once P2_LIBRARY_DIR . '/filectl.class.php';
        FileCtl::make_datafile($_conf['expack.skin.setting_path'], $_conf['expack.skin.setting_perm']);
    }
    if (isset($_REQUEST['skin']) && preg_match('/^\w+$/', $_REQUEST['skin']) && $skin_name != $_REQUEST['skin']) {
        $skin_name = $_REQUEST['skin'];
        $skin = 'skin/' . $skin_name . '.php';
        FileCtl::file_write_contents($_conf['expack.skin.setting_path'], $skin_name);
    }
}
if (!file_exists($skin)) {
    $skin_name = 'conf_user_style';
    $skin = 'conf/conf_user_style.inc.php';
}
$skin_en = urlencode($skin_name);
include_once $skin;

// }}}
// {{{ �f�U�C���ݒ�̒�������

if (!is_array($STYLE)) {
    $STYLE = array();
}

if (!isset($STYLE['post_pop_size'])) { $STYLE['post_pop_size'] = "610,350"; }
if (!isset($STYLE['post_msg_rows'])) { $STYLE['post_msg_rows'] = 10; }
if (!isset($STYLE['post_msg_cols'])) { $STYLE['post_msg_cols'] = 70; }
if (!isset($STYLE['info_pop_size'])) { $STYLE['info_pop_size'] = "600,380"; }

if (!isset($STYLE['mobile_subject_newthre_color'])) { $STYLE['mobile_subject_newthre_color'] = "#ff0000"; }
if (!isset($STYLE['mobile_subject_newres_color']))  { $STYLE['mobile_subject_newres_color']  = "#ff6600"; }
if (!isset($STYLE['mobile_read_ttitle_color']))     { $STYLE['mobile_read_ttitle_color']     = "#1144aa"; }
if (!isset($STYLE['mobile_read_newres_color']))     { $STYLE['mobile_read_newres_color']     = "#ff6600"; }
if (!isset($STYLE['mobile_read_ngword_color']))     { $STYLE['mobile_read_ngword_color']     = "#bbbbbb"; }
if (!isset($STYLE['mobile_read_onthefly_color']))   { $STYLE['mobile_read_onthefly_color']   = "#00aa00"; }

$skin_etag = $_conf['p2expack'];
fontconfig_apply_custom();

foreach ($STYLE as $K => $V) {
    if (empty($V)) {
        $STYLE[$K] = '';
    } elseif (strpos($K, 'fontfamily') !== FALSE) {
        $STYLE[$K] = set_css_fonts($V);
    } elseif (strpos($K, 'color') !== FALSE) {
        $STYLE[$K] = set_css_color($V);
    } elseif (strpos($K, 'background') !== FALSE) {
        $STYLE[$K] = 'url("' . addslashes($V) . '")';
    }
}
if (!$_conf['ktai'] && $_conf['expack.am.enabled']) {
    $_conf['expack.am.fontfamily'] = set_css_fonts($_conf['expack.am.fontfamily']);
    if ($STYLE['fontfamily']) {
        $_conf['expack.am.fontfamily'] .= '","' . $STYLE['fontfamily'];
    }
}

$_conf['k_colors'] = '';
if ($_conf['ktai']) {
    if ($_conf['mobile.background_color']) {
        $_conf['k_colors'] .= " bgcolor=\"{$_conf['mobile.background_color']}\"";
    }
    if ($_conf['mobile.text_color']) {
        $_conf['k_colors'] .= " text=\"{$_conf['mobile.text_color']}\"";
    }
    if ($_conf['mobile.link_color']) {
        $_conf['k_colors'] .= " link=\"{$_conf['mobile.link_color']}\"";
    }
    if ($_conf['mobile.vlink_color']) {
        $_conf['k_colors'] .= " vlink=\"{$_conf['mobile.vlink_color']}\"";
    }
    if ($_conf['mobile.newthre_color']) {
        $STYLE['mobile_subject_newthre_color'] = $_conf['mobile.newthre_color'];
    }
    if ($_conf['mobile.newres_color']) {
        $STYLE['mobile_read_newres_color']    = $_conf['mobile.newres_color'];
        $STYLE['mobile_subject_newres_color'] = $_conf['mobile.newres_color'];
    }
    if ($_conf['mobile.ttitle_color']) {
        $STYLE['mobile_read_ttitle_color'] = $_conf['mobile.ttitle_color'];
    }
    if ($_conf['mobile.ngword_color']) {
        $STYLE['mobile_read_ngword_color'] = $_conf['mobile.ngword_color'];
    }
    if ($_conf['mobile.onthefly_color']) {
        $STYLE['mobile_read_onthefly_color'] = $_conf['mobile.onthefly_color'];
    }
    // �g�їp�}�[�J�[
    if ($_conf['mobile.match_color']) {
        $_conf['k_filter_marker'] = "<font color=\"{$_conf['mobile.match_color']}\">\\1</font>";
    } else {
        $_conf['k_filter_marker'] = FALSE;
    }
}

// }}}

//======================================================================
// �ϐ��ݒ�
//======================================================================
$_conf['rct_file'] =            $_conf['pref_dir'] . '/p2_recent.idx';
$_conf['p2_res_hist_dat'] =     $_conf['pref_dir'] . '/p2_res_hist.dat'; // �������݃��O�t�@�C���idat�j
$_conf['p2_res_hist_dat_php'] = $_conf['pref_dir'] . '/p2_res_hist.dat.php'; // �������݃��O�t�@�C���i�f�[�^PHP�j
$_conf['cookie_dir'] =          $_conf['pref_dir'] . '/p2_cookie'; // cookie �ۑ��f�B���N�g��
$_conf['favlist_file'] =        $_conf['pref_dir'] . "/p2_favlist.idx";
$_conf['favita_path'] =         $_conf['pref_dir'] . "/p2_favita.brd";
$_conf['idpw2ch_php'] =         $_conf['pref_dir'] . "/p2_idpw2ch.php";
$_conf['sid2ch_php'] =          $_conf['pref_dir'] . "/p2_sid2ch.php";
$_conf['auth_user_file'] =      $_conf['pref_dir'] . "/p2_auth_user.php";
$_conf['auth_ez_file'] =        $_conf['pref_dir'] . "/p2_auth_ez.php";
$_conf['auth_jp_file'] =        $_conf['pref_dir'] . "/p2_auth_jp.php";
$_conf['auth_docomo_file'] =    $_conf['pref_dir'] . '/p2_auth_docomo.php';
$_conf['login_log_file'] =      $_conf['pref_dir'] . "/p2_login.log.php";
$_conf['login_failed_log_file'] = $_conf['pref_dir'] . '/p2_login_failed.dat.php';

// saveMatomeCache() �̂��߂� $_conf['pref_dir'] ���΃p�X�ɕϊ�����
define('P2_PREF_DIR_REAL_PATH', File_Util::realPath($_conf['pref_dir']));

$_conf['matome_cache_path'] = P2_PREF_DIR_REAL_PATH . DIRECTORY_SEPARATOR . 'matome_cache';
$_conf['matome_cache_ext'] = '.htm';
$_conf['matome_cache_max'] = 3; // �\���L���b�V���̐�

// {{{ ���肦�Ȃ������̃G���[

// �V�K���O�C���ƃ����o�[���O�C���̓����w��͂��肦�Ȃ��̂ŁA�G���[�o��
if (isset($_POST['submit_new']) && isset($_POST['submit_member'])) {
    p2die('������URL�ł��B');
}

// }}}
// {{{ �z�X�g�`�F�b�N

if ($_conf['secure']['auth_host'] || $_conf['secure']['auth_bbq']) {
    require_once P2_LIBRARY_DIR . '/hostcheck.class.php';
    if (($_conf['secure']['auth_host'] && HostCheck::getHostAuth() == FALSE) ||
        ($_conf['secure']['auth_bbq'] && HostCheck::getHostBurned() == TRUE)
    ) {
        HostCheck::forbidden();
    }
}

// }}}
// {{{ ���Z�b�V����

// ���O�́A�Z�b�V�����N�b�L�[��j������Ƃ��̂��߂ɁA�Z�b�V�������p�̗L���Ɋւ�炸�ݒ肷��
session_name('PS');

// eAccelerator�̃Z�b�V�����n���h�����g���Ă݂�
/*if (extension_loaded('eAccelerator')) {
    eaccelerator_set_session_handlers();
}*/

// SQLite�̃Z�b�V�����n���h�����g���Ă݂�
/*if (extension_loaded('sqlite')) {
    ob_start();
    phpinfo(INFO_MODULES);
    $_phpinfo_modules = ob_get_clean();
    $_sh_regex = '!<tr><td class="e">Registered save handlers *</td><td class="v">(.+?)</td></tr>!';
    if (preg_match($_sh_regex, $_phpinfo_modules, $_phpinfo_matches)
        && strstr($_phpinfo_matches[1], 'sqlite'))
    {
        session_module_name('sqlite');
        session_save_path(P2_PREF_DIR_REAL_PATH . DIRECTORY_SEPARATOR . 'p2_session.db');
    }
    unset($_sh_regex, $_phpinfo_modules, $_phpinfo_matches);
}*/

// {{{ �Z�b�V�����f�[�^�ۑ��f�B���N�g�����K��

if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {

    // $_conf['data_dir'] ���΃p�X�ɕϊ�����
    define('P2_DATA_DIR_REAL_PATH', File_Util::realPath($_conf['data_dir']));
    
    $_conf['session_dir'] = P2_DATA_DIR_REAL_PATH . DIRECTORY_SEPARATOR . 'session';
}

// }}}

if (defined('P2_FORCE_USE_SESSION') || $_conf['expack.misc.multi_favs']) {
    $_conf['use_session'] = 1;
}
if ($_conf['use_session'] == 1 or ($_conf['use_session'] == 2 && !$_COOKIE['cid'])) { 

    // {{{ �Z�b�V�����f�[�^�ۑ��f�B���N�g����ݒ�
    
    if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {
    
        if (!is_dir($_conf['session_dir'])) {
            require_once P2_LIBRARY_DIR . '/filectl.class.php';
            FileCtl::mkdir_for($_conf['session_dir'] . '/dummy_filename');
        } elseif (!is_writable($_conf['session_dir'])) {
            p2die("�Z�b�V�����f�[�^�ۑ��f�B���N�g�� ({$_conf['session_dir']}) �ɏ������݌���������܂���B");
        }

        session_save_path($_conf['session_dir']);

        // session.save_path �̃p�X�̐[����2���傫���ƃK�[�x�b�W�R���N�V�������s���Ȃ��̂�
        // ���O�ŃK�[�x�b�W�R���N�V��������
        P2Util::session_gc();
    }
    
    // }}}

    $_p2session =& new Session();
    if ($_conf['disable_cookie'] && !ini_get('session.use_trans_sid')) {
        output_add_rewrite_var(session_name(), session_id());
    }
}

// }}}

// ���C�ɃZ�b�g��؂�ւ���
if ($_conf['expack.misc.multi_favs']) {
    require_once P2_LIBRARY_DIR . '/favsetmng.class.php';
    FavSetManager::switchFavSet();
}

// �����O�C���N���X�̃C���X�^���X�����i���O�C�����[�U���w�肳��Ă��Ȃ���΁A���̎��_�Ń��O�C���t�H�[���\���Ɂj
@require_once P2_LIBRARY_DIR . '/login.class.php';
$_login =& new Login();


//=====================================================================
// �֐�
//=====================================================================
/**
 * �ċA�I��stripslashes��������
 * GET/POST/COOKIE�ϐ��p�Ȃ̂ŃI�u�W�F�N�g�̃v���p�e�B�ɂ͑Ή����Ȃ�
 * (ExUtil)
 */
function stripslashes_r($var, $r = 0)
{
    if (is_array($var)) {
        if ($r < 3) {
            $r++;
            foreach ($var as $key => $value) {
                $var[$key] = stripslashes_r($value, $r);
            }
        } /* else { p2die("too deep multi dimentional array given."); } */
    } elseif (is_string($var)) {
        $var = stripslashes($var);
    }
    return $var;
}

/**
 * �ċA�I��addslashes��������
 * (ExUtil)
 */
function addslashes_r($var, $r = 0)
{
    if (is_array($var)) {
        if ($r < 3) {
            $r++;
            foreach ($var as $key => $value) {
                $var[$key] = addslashes_r($value, $r);
            }
        } /* else { p2die("too deep multi dimentional array given."); } */
    } elseif (is_string($var)) {
        $var = addslashes($var);
    }
    return $var;
}

/**
 * �ċA�I�Ƀk���������폜����
 * mbstring�ŕϊ��e�[�u���ɂȂ�(?)�O����ϊ������
 * NULL(0x00)�ɂȂ��Ă��܂����Ƃ�����̂ŏ�������
 * (ExUtil)
 */
function nullfilter_r($var, $r = 0)
{
    if (is_array($var)) {
        if ($r < 3) {
            $r++;
            foreach ($var as $key => $value) {
                $var[$key] = nullfilter_r($value, $r);
            }
        } /* else { p2die("too deep multi dimentional array given."); } */
    } elseif (is_string($var)) {
        $var = str_replace("\x00", '', $var);
    }
    return $var;
}

/**
 * �������̎g�p�ʂ�\������
 *
 * @return void
 */
function printMemoryUsage()
{
    if (function_exists('memory_get_usage')) {
        $usage = memory_get_usage();
    } elseif (function_exists('xdebug_memory_usage')) {
        $usage = xdebug_memory_usage();
    } else {
        $usage = -1;
    }
    $kb = $usage / 1024;
    $kb = number_format($kb, 2, '.', '');
    
    echo 'Memory Usage: ' . $kb . 'KB';
}

/**
 * SI�P�ʌn�̒l�𐮐��ɕϊ�����
 * �����ɂ�1000�{����̂����������APC�E�G (�L�����u����) �̊���ɏ]����1024�{����
 */
function si2int($num, $kmg)
{
    return si2real($num, $kmg);
}
function si2real($num, $kmg)
{
    $num = (float)$num;
    switch (strtoupper($kmg)) {
        case 'G': $num *= 1024;
        case 'M': $num *= 1024;
        case 'K': $num *= 1024;
    }
    return $num;
}

/**
 * �}���`�o�C�g�Ή���basename()
 */
function mb_basename($path, $encoding = 'SJIS-win')
{
    if (!mb_substr_count($path, '/', $encoding)) {
        return $path;
    }
    $len = mb_strlen($path, $encoding);
    $pos = mb_strrpos($path, '/', $encoding);
    return mb_substr($path, $pos + 1, $len - $pos, $encoding);
}

/**
 * �t�H���g�ݒ�p�Ƀ��[�U�G�[�W�F���g�𔻒肷��
 */
function fontconfig_detect_agent($ua = null)
{
    if ($ua === null) {
        $ua = $_SERVER['HTTP_USER_AGENT'];
    }
    if (preg_match('/\bWindows\b/', $ua)) {
        return 'windows';
    }
    if (preg_match('/\bMac(intoth)?\b/', $ua)) {
        if (preg_match('/\b(Safari|AppleWebKit)\/(\d+(\.\d+)?)\b/', $ua, $matches)) {
            if (400 < (float) $matches[2]) {
                return 'safari2';
            } else {
                return 'safari1';
            }
        } elseif (preg_match('/\b(Mac ?OS ?X)\b/', $ua)) {
            return 'macosx';
        } else {
            return 'macos9';
        }
    }
    return 'other';
}

/**
 * �t�H���g�ݒ��ǂݍ���
 */
function fontconfig_apply_custom()
{
    global $STYLE, $_conf, $skin_en, $skin_etag;
    if ($_conf['expack.skin.enabled']) {
        $_conf['expack.am.fontfamily.orig'] = (isset($_conf['expack.am.fontfamily']))
            ? $_conf['expack.am.fontfamily'] : '';
        $type = fontconfig_detect_agent();
        if (file_exists($_conf['expack.skin.fontconfig_path'])) {
            $fontconfig_data = file_get_contents($_conf['expack.skin.fontconfig_path']);
            $current_fontconfig = unserialize($fontconfig_data);
        }
        if (!is_array($current_fontconfig)) {
            $current_fontconfig = array('enabled' => false, 'custom' => array());
        }
        if ($current_fontconfig['enabled'] && is_array($current_fontconfig['custom'][$type])) {
            $skin_etag = '';
            $sha1 = sha1($fontconfig_data . $_conf['p2expack']);
            for ($i = 0; $i < 40; $i +=5) {
                $skin_etag .= base_convert(substr($sha1, $i, 5), 16, 32);
            }
            foreach ($current_fontconfig['custom'][$type] as $key => $value) {
                if (strstr($key, 'fontfamily') && $value == '-') {
                    if ($key == 'fontfamily_aa') {
                        $_conf['expack.am.fontfamily'] = '';
                    } else {
                        $STYLE["{$key}.orig"] = (isset($STYLE[$key])) ? $STYLE[$key] : '';
                        $STYLE[$key] = '';
                    }
                } elseif ($value) {
                    if ($key == 'fontfamily_aa') {
                        $_conf['expack.am.fontfamily'] = $value;
                    } else {
                        $STYLE["{$key}.orig"] = (isset($STYLE[$key])) ? $STYLE[$key] : '';
                        $STYLE[$key] = $value;
                    }
                }
            }
        }
    }
    $skin_en = preg_replace('/&amp;etag=[^&]*/', '', $skin_en);
    $skin_en .= '&amp;etag=' . urlencode($skin_etag);
}

/**
 * �X�^�C���V�[�g��ǂݍ��ރ^�O��\��
 */
function print_style_tags()
{
    global $skin_name, $skin_etag;
    $style_a = '';
    if ($skin_name) { $style_a .= '&skin=' . urlencode($skin_name); }
    if ($skin_etag) { $style_a .= '&etag=' . urlencode($skin_etag); }
    if ($styles = func_get_args()) {
        echo "\t<style type=\"text/css\">\n";
        echo "\t<!-->\n";
        foreach ($styles as $style) {
            if (file_exists(P2_STYLE_DIR . '/' . $style . '_css.inc')) {
                printf("\t@import 'css.php?css=%s%s';\n", $style, $style_a);
            }
        }
        echo "\t-->\n";
        echo "\t</style>\n";
    }
}

/**
 * �X�^�C���V�[�g�̃t�H���g�w��𒲐�����
 */
function set_css_fonts($fonts)
{
    if (is_string($fonts)) {
        $fonts = preg_split('/(["\'])?\\s*,\\s*(?(1)\\1)/', trim($fonts, " \t\"'"));
    } elseif (!is_array($fonts)) {
        return '';
    }
    $fonts = '"' . implode('","', $fonts) . '"';
    $fonts = preg_replace('/"(serif|sans-serif|cursive|fantasy|monospace)"/', '$1', $fonts);
    return trim($fonts, '"');
}

/**
 * �X�^�C���V�[�g�̐F�w��𒲐�����
 */
function set_css_color($color)
{
    return preg_replace('/^#([0-9A-F])([0-9A-F])([0-9A-F])$/i', '#$1$1$2$2$3$3', $color);
}

/**
 * Safari ����A�b�v���[�h���ꂽ�t�@�C�����̑����E�������Ƀ}�b�`���鐳�K�\��
 */
$GLOBALS['COMBINEHFSKANA_REGEX'] = str_replace(
    array('%u3099%', '%u309A%'),
    array(pack('C*', 0xE3, 0x82, 0x99), pack('C*', 0xE3, 0x82, 0x9A)),
    mb_convert_encoding(
        '/([����-����-����-�Ƃ�-�كE�J-�R�T-�\�^-�g�n-�z�T�R])%u3099%|([��-�كn-�z])%u309A%/u',
        'UTF-8', 'SJIS-win'));

/**
 * Safari ����A�b�v���[�h���ꂽ�t�@�C�����̕���������␳����֐�
 * ����+���_�E����+�����_���ꕶ���ɂ܂Ƃ߂� (NFD �Ő��K�����ꂽ ���� �� NFC �ɂ���)
 * ���o�͂̕����R�[�h��UTF-8
 */
function combinehfskana($str)
{
    return preg_replace_callback($GLOBALS['COMBINEHFSKANA_REGEX'], '_combinehfskana', $str);
}

function _combinehfskana($m)
{
    if ($m[1]) {
        $C = unpack('C*', $m[1]);
        $C[3] += 1;
    } elseif ($m[2]) {
        $C = unpack('C*', $m[2]);
        $C[3] += 2;
    }
    return pack('C*', $C[1], $C[2], $C[3]);
}

/**
 * �������K���ȕ����������p���K�\��
 */
$GLOBALS['WAKATI_REGEX'] = mb_convert_encoding(
    '/(' . implode('|', array(
        //'[��-�]+[��-��]*',
        //'[��-�]+',
        '[���O�l�ܘZ������\]+',
        '[��-�]+',
        '[��-��][��-��[�`�J�K]*',
        '[�@-��][�@-���[�`�J�K]*',
        //'[a-z][a-z_\\-]*',
        //'[0-9][0-9.]*',
        '[0-9a-z][0-9a-z_\\-]*',
    )) . ')/u', 'UTF-8', 'SJIS-win');

/**
 * �������K���Ȑ��K���������������֐�
 */
function wakati($str)
{
    return array_filter(array_map('trim', preg_split($GLOBALS['WAKATI_REGEX'],
        mb_strtolower(mb_convert_kana(mb_convert_encoding(
            $str, 'UTF-8', 'SJIS-win'), 'KVas', 'UTF-8'), 'UTF-8'),
        -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)), 'strlen');
}

/**
 * ���b�Z�[�W��\�����ďI��
 */
function p2die($err, $msg = null, $raw = false)
{
    echo '<html><head><title>p2 error</title></head><body>';
    echo '<h3>p2 error: ', htmlspecialchars($err, ENT_QUOTES), '</h3>';
    if ($msg !== null) {
        if ($raw) {
            echo '<p>', nl2br(htmlspecialchars($msg, ENT_QUOTES)), '</p>';
        } else {
            echo $msg;
        }
    }
    echo '</body></html>';
}

?>
