<?php
/**
 * rep2 - ��{�ݒ�t�@�C��
 * ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
 */

// �o�[�W�������
$_conf = array(
    'p2version' => '1.7.29+1.8.x',  // rep2�̃o�[�W����
    'p2expack'  => '100105.2345',   // �g���p�b�N�̃o�[�W����
    'p2name'    => 'expack',        // rep2�̖��O
);

$_conf['p2ua'] = "{$_conf['p2name']}/{$_conf['p2version']}+{$_conf['p2expack']}";

define('P2_VERSION_ID', sprintf('%u', crc32($_conf['p2ua'])));

/*
 * �ʏ�̓Z�b�V�����t�@�C���̃��b�N�҂����ɗ͒Z�����邽��
 * ���[�U�[�F�،シ���ɃZ�b�V�����ϐ��̕ύX���R�~�b�g����B
 * �F�،���Z�b�V�����ϐ���ύX����X�N���v�g�ł�
 * ���̃t�@�C����ǂݍ��ޑO��
 *  define('P2_SESSION_CLOSE_AFTER_AUTHENTICATION', 0);
 * �Ƃ���B
 */
if (!defined('P2_SESSION_CLOSE_AFTER_AUTHENTICATION')) {
    define('P2_SESSION_CLOSE_AFTER_AUTHENTICATION', 1);
}

// {{{ �O���[�o���ϐ���������

$_info_msg_ht = ''; // ���[�U�ʒm�p ��񃁃b�Z�[�WHTML

$MYSTYLE    = array();
$STYLE      = array();
$debug      = false;
$skin       = null;
$skin_en    = null;
$skin_name  = null;
$skin_uniq  = null;
$_login     = null;
$_p2session = null;

$conf_user_def   = array();
$conf_user_rules = array();
$conf_user_rad   = array();
$conf_user_sel   = array();

// }}}

// ��{�ݒ菈�������s
p2configure();

// �N���[���A�b�v
if (basename($_SERVER['SCRIPT_NAME']) != 'edit_conf_user.php') {
    unset($conf_user_def, $conf_user_rules, $conf_user_rad, $conf_user_sel);
}

// E_NOTICE ����шÖق̔z�񏉊�������
$_conf['filtering'] = false;
$hd = array('word' => null);
$htm = array();
$word = null;

// {{{ p2configure()

/**
 * �ꎞ�ϐ��ŃO���[�o���ϐ����������Ȃ��悤�ɐݒ菈�����֐���
 */
function p2configure()
{
    global $MYSTYLE, $STYLE, $debug;
    global $skin, $skin_en, $skin_name, $skin_uniq;
    global $_conf, $_info_msg_ht, $_login, $_p2session;
    global $conf_user_def, $conf_user_rules, $conf_user_rad, $conf_user_sel;

    // �G���[�o�͐ݒ�
    if (defined('E_DEPRECATED')) {
        error_reporting(E_ALL & ~(E_NOTICE | E_STRICT | E_DEPRECATED));
    } else {
        error_reporting(E_ALL & ~(E_NOTICE | E_STRICT));
    }

    // {{{ ��{�ϐ�

    $_conf['p2web_url']             = 'http://akid.s17.xrea.com/';
    $_conf['p2ime_url']             = 'http://akid.s17.xrea.com/p2ime.php';
    $_conf['favrank_url']           = 'http://akid.s17.xrea.com/favrank/favrank.php';
    $_conf['expack.web_url']        = 'http://page2.skr.jp/rep2/';
    $_conf['expack.download_url']   = 'http://page2.skr.jp/rep2/downloads.html';
    $_conf['expack.history_url']    = 'http://page2.skr.jp/rep2/history.html';
    $_conf['expack.tgrep_url']      = 'http://page2.xrea.jp/tgrep/search';
    $_conf['expack.ime_url']        = 'http://page2.skr.jp/gate.php';
    $_conf['menu_php']              = 'menu.php';
    $_conf['subject_php']           = 'subject.php';
    $_conf['read_php']              = 'read.php';
    $_conf['read_new_php']          = 'read_new.php';
    $_conf['read_new_k_php']        = 'read_new_k.php';

    // }}}
    // {{{ ���ݒ�

    // �f�o�b�O
    //$debug = !empty($_GET['debug']);

    putenv('LC_CTYPE=C');

    // �^�C���]�[�����Z�b�g
    date_default_timezone_set('Asia/Tokyo');

    // �X�N���v�g���s�������� (�b)
    if (!defined('P2_CLI_RUN')) {
        set_time_limit(60); // (60)
    }

    // �����t���b�V�����I�t�ɂ���
    ob_implicit_flush(0);

    // �N���C�A���g����ڑ���؂��Ă������𑱍s����
    // ignore_user_abort(1);

    // file($filename, FILE_IGNORE_NEW_LINES) �� CR/LF/CR+LF �̂�������s���Ƃ��Ĉ���
    ini_set('auto_detect_line_endings', 1);

    // session.trans_sid�L���� �� output_add_rewrite_var(), http_build_query() ���Ő����E�ύX�����
    // URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
    ini_set('arg_separator.output', '&amp;');

    // ���N�G�X�gID��ݒ� (�R�X�g���傫�����Ɏg���Ă��Ȃ��̂Ŕp�~)
    //define('P2_REQUEST_ID', substr($_SERVER['REQUEST_METHOD'], 0, 1) . md5(serialize($_REQUEST)));

    // Windows �Ȃ�
    if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
        // Windows
        defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ';');
        defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '\\');
        define('P2_OS_WINDOWS', 1);
    } else {
        defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ':');
        defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '/');
        define('P2_OS_WINDOWS', 0);
    }

    $DIR_SEP = DIRECTORY_SEPARATOR;
    $PATH_SEP = PATH_SEPARATOR;

    // mbstring.script_encoding = SJIS-win ���� "\0", "\x00" �ȍ~���J�b�g�����̂�
    define('P2_NULLBYTE', chr(0));

    // }}}
    // {{{ P2Util::header_content_type() ��s�v�ɂ��邨�܂��Ȃ�

    ini_set('default_mimetype', 'text/html');
    ini_set('default_charset', 'Shift_JIS');

    // }}}
    // {{{ ���C�u�����ނ̃p�X�ݒ�

    define('P2_CONF_DIR', dirname(__FILE__)); // __DIR__ @php-5.3

    define('P2_BASE_DIR', dirname(P2_CONF_DIR));
    $P2_BASE_DIR_S = P2_BASE_DIR . $DIR_SEP;

    // ��{�I�ȋ@�\��񋟂��邷�郉�C�u����
    define('P2_LIB_DIR', $P2_BASE_DIR_S . 'lib');

    // ���܂��I�ȋ@�\��񋟂��邷�郉�C�u����
    define('P2EX_LIB_DIR', $P2_BASE_DIR_S . 'lib' . $DIR_SEP . 'expack');

    // �X�^�C���V�[�g
    define('P2_STYLE_DIR', $P2_BASE_DIR_S . 'style');

    // �X�L��
    define('P2_SKIN_DIR', $P2_BASE_DIR_S . 'skin');
    define('P2_USER_SKIN_DIR', $P2_BASE_DIR_S . 'user_skin');

    // PEAR�C���X�g�[���f�B���N�g���A�����p�X�ɒǉ������
    define('P2_PEAR_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'includes');

    // PEAR���n�b�N�����t�@�C���p�f�B���N�g���A�ʏ��PEAR���D��I�Ɍ����p�X�ɒǉ������
    // Cache/Container/db.php(PEAR::Cache)��MySQL���肾�����̂ŁA�ėp�I�ɂ������̂�u���Ă���
    // include_path��ǉ�����̂̓p�t�H�[�}���X�ɉe�����y�ڂ����߁A�{���ɕK�v�ȏꍇ�̂ݒ�`
    if (defined('P2_USE_PEAR_HACK')) {
        define('P2_PEAR_HACK_DIR', $P2_BASE_DIR_S . 'lib' . $DIR_SEP . 'pear_hack');
    }

    // �R�}���h���C���c�[��
    define('P2_CLI_DIR', $P2_BASE_DIR_S . 'cli');

    // �����p�X���Z�b�g
    $include_path = '';
    if (defined('P2_PEAR_HACK_DIR')) {
        $include_path .= P2_PEAR_HACK_DIR . $PATH_SEP;
    }
    if (is_dir(P2_PEAR_DIR)) {
        $include_path .= P2_PEAR_DIR . $PATH_SEP;
    } else {
        $paths = array();
        foreach (explode($PATH_SEP, get_include_path()) as $dir) {
            if (is_dir($dir)) {
                $dir = realpath($dir);
                if ($dir != P2_BASE_DIR) {
                    $paths[] = $dir;
                }
            }
        }
        if (count($paths)) {
            $include_path .= implode($PATH_SEP, array_unique($paths)) . $PATH_SEP;
        }
    }
    $include_path .= P2_BASE_DIR; // fallback
    set_include_path($include_path);

    $P2_CONF_DIR_S = P2_CONF_DIR . $DIR_SEP;
    $P2_LIB_DIR_S = P2_LIB_DIR . $DIR_SEP;

    // }}}
    // {{{ ���`�F�b�N�ƃf�o�b�O

    // ���[�e�B���e�B��ǂݍ���
    include $P2_LIB_DIR_S . 'P2Util.php';
    include $P2_LIB_DIR_S . 'p2util.inc.php';

    // ��������m�F (�v���𖞂����Ă���Ȃ�R�����g�A�E�g��)
    p2checkenv(__LINE__);

    if ($debug) {
        if (!class_exists('Benchmark_Profiler', false)) {
            require 'Benchmark/Profiler.php';
        }
        $profiler = new Benchmark_Profiler(true);
        // print_memory_usage();
        register_shutdown_function('print_memory_usage');
    }

    // }}}
    // {{{ �����R�[�h�̎w��

    //mb_detect_order("CP932,CP51932,ASCII");
    mb_internal_encoding('CP932');
    mb_http_output('pass');
    mb_substitute_character(63); // �����R�[�h�ϊ��Ɏ��s���������� "?" �ɂȂ�
    //mb_substitute_character(0x3013); // ��
    //ob_start('mb_output_handler');

    if (function_exists('mb_ereg_replace')) {
        define('P2_MBREGEX_AVAILABLE', 1);
        mb_regex_encoding('CP932');
    } else {
        define('P2_MBREGEX_AVAILABLE', 0);
    }

    // }}}
    // {{{ �Ǘ��җp�ݒ�etc.

    // �Ǘ��җp�ݒ��ǂݍ���
    include $P2_CONF_DIR_S . 'conf_admin.inc.php';

    // �f�B���N�g���̐�΃p�X��
    $_conf['data_dir'] = p2_realpath($_conf['data_dir']);
    $_conf['dat_dir']  = p2_realpath($_conf['dat_dir']);
    $_conf['idx_dir']  = p2_realpath($_conf['idx_dir']);
    $_conf['pref_dir'] = p2_realpath($_conf['pref_dir']);

    // �Ǘ��p�ۑ��f�B���N�g��
    $_conf['admin_dir'] = $_conf['data_dir'] . $DIR_SEP . 'admin';

    // cache �ۑ��f�B���N�g��
    // 2005/06/29 $_conf['pref_dir'] . '/p2_cache' ���ύX
    $_conf['cache_dir'] = $_conf['data_dir'] . $DIR_SEP . 'cache';

    // Cookie �ۑ��f�B���N�g��
    // 2008/09/09 $_conf['pref_dir'] . '/p2_cookie' ���ύX
    $_conf['cookie_dir'] = $_conf['data_dir'] . $DIR_SEP . 'cookie';

    // �R���p�C�����ꂽ�e���v���[�g�̕ۑ��f�B���N�g��
    $_conf['compile_dir'] = $_conf['data_dir'] . $DIR_SEP . 'compile';

    // �Z�b�V�����f�[�^�ۑ��f�B���N�g��
    $_conf['session_dir'] = $_conf['data_dir'] . $DIR_SEP . 'session';

    // �e���|�����f�B���N�g��
    $_conf['tmp_dir'] = $_conf['data_dir'] . $DIR_SEP . 'tmp';

    // �o�[�W����ID���d���p����q�A�h�L�������g���ɖ��ߍ��ނ��߂̕ϐ�
    $_conf['p2_version_id'] = P2_VERSION_ID;

    // �����R�[�h��������p�̃q���g������
    $_conf['detect_hint'] = '����';
    $_conf['detect_hint_input_ht'] = '<input type="hidden" name="_hint" value="����">';
    $_conf['detect_hint_input_xht'] = '<input type="hidden" name="_hint" value="����" />';
    //$_conf['detect_hint_utf8'] = mb_convert_encoding('����', 'UTF-8', 'CP932');
    $_conf['detect_hint_q'] = '_hint=%81%9D%81%9E'; // rawurlencode($_conf['detect_hint'])
    $_conf['detect_hint_q_utf8'] = '_hint=%E2%97%8E%E2%97%87'; // rawurlencode($_conf['detect_hint_utf8'])

    // }}}
    // {{{ �ϐ��ݒ�

    $pref_dir_s = $_conf['pref_dir'] . $DIR_SEP;

    $_conf['favita_brd']        = $pref_dir_s . 'p2_favita.brd';        // ���C�ɔ� (brd)
    $_conf['favlist_idx']       = $pref_dir_s . 'p2_favlist.idx';       // ���C�ɃX�� (idx)
    $_conf['recent_idx']        = $pref_dir_s . 'p2_recent.idx';        // �ŋߓǂ񂾃X�� (idx)
    $_conf['palace_idx']        = $pref_dir_s . 'p2_palace.idx';        // �X���̓a�� (idx)
    $_conf['res_hist_idx']      = $pref_dir_s . 'p2_res_hist.idx';      // �������݃��O (idx)
    $_conf['res_hist_dat']      = $pref_dir_s . 'p2_res_hist.dat';      // �������݃��O�t�@�C�� (dat)
    $_conf['res_hist_dat_php']  = $pref_dir_s . 'p2_res_hist.dat.php';  // �������݃��O�t�@�C�� (�f�[�^PHP)
    $_conf['idpw2ch_php']       = $pref_dir_s . 'p2_idpw2ch.php';       // 2ch ID�F�ؐݒ�t�@�C�� (�f�[�^PHP)
    $_conf['sid2ch_php']        = $pref_dir_s . 'p2_sid2ch.php';        // 2ch ID�F�؃Z�b�V����ID�L�^�t�@�C�� (�f�[�^PHP)
    $_conf['auth_user_file']    = $pref_dir_s . 'p2_auth_user.php';     // �F�؃��[�U�ݒ�t�@�C��(�f�[�^PHP)
    $_conf['auth_imodeid_file'] = $pref_dir_s . 'p2_auth_imodeid.php';  // docomo i���[�hID�F�؃t�@�C�� (�f�[�^PHP)
    $_conf['auth_docomo_file']  = $pref_dir_s . 'p2_auth_docomo.php';   // docomo �[�������ԍ��F�؃t�@�C�� (�f�[�^PHP)
    $_conf['auth_ez_file']      = $pref_dir_s . 'p2_auth_ez.php';       // EZweb �T�u�X�N���C�oID�F�؃t�@�C�� (�f�[�^PHP)
    $_conf['auth_jp_file']      = $pref_dir_s . 'p2_auth_jp.php';       // SoftBank �[���V���A���ԍ��F�؃t�@�C�� (�f�[�^PHP)
    $_conf['login_log_file']    = $pref_dir_s . 'p2_login.log.php';     // ���O�C������ (�f�[�^PHP)
    $_conf['login_failed_log_file'] = $pref_dir_s . 'p2_login_failed.dat.php';  // ���O�C�����s���� (�f�[�^PHP)

    $_conf['matome_cache_path'] = $pref_dir_s . 'matome_cache';
    $_conf['matome_cache_ext']  = '.htm';
    $_conf['matome_cache_max']  = 3; // �\���L���b�V���̐�

    $_conf['orig_favita_brd']   = $_conf['favita_brd'];
    $_conf['orig_favlist_idx']  = $_conf['favlist_idx'];

    $_conf['cookie_file_path']  = $_conf['cookie_dir'] . $DIR_SEP . 'p2_cookies.sqlite3';

    // �␳
    if ($_conf['expack.use_pecl_http'] && !extension_loaded('http')) {
        if (!($_conf['expack.use_pecl_http'] == 2 && $_conf['expack.dl_pecl_http'])) {
            $_conf['expack.use_pecl_http'] = 0;
        }
    }

    // �R�}���h���C�����[�h�ł͂����܂�
    if (defined('P2_CLI_RUN')) {
        return;
    }

    // }}}

    include $P2_LIB_DIR_S . 'bootstrap.php';
}

// }}}
// {{{ p2checkenv()

/**
 * ��������m�F����
 *
 * @return bool
 */
function p2checkenv($check_recommended)
{
    global $_info_msg_ht;

    $php_version = phpversion();
    $required_version = '5.2.8';
    $recommended_version52 = '5.2.12';
    $recommended_version53 = '5.3.1';
    $required_extensions = array(
        'dom',
        'json',
        'libxml',
        'mbstring',
        'pcre',
        'pdo',
        'pdo_sqlite',
        'session',
        'spl',
        //'xsl',
        'zlib',
    );

    // PHP�̃o�[�W����
    if (version_compare($php_version, $required_version, '<')) {
        p2die("PHP {$required_version} �����ł͎g���܂���B");
    }

    // �K�{�g�����W���[��
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            p2die("{$ext} �g�����W���[�������[�h����Ă��܂���B");
        }
    }

    // �Z�[�t���[�h
    if (ini_get('safe_mode')) {
        p2die('�Z�[�t���[�h�œ��삷��PHP�ł͎g���܂���B');
    }

    // register_globals
    if (ini_get('register_globals')) {
        $msg = <<<EOP
�\�����Ȃ����������邽�߂� php.ini �� register_globals �� Off �ɂ��Ă��������B
magic_quotes_gpc �� mbstring.encoding_translation �� Off �ɂ���邱�Ƃ��������߂��܂��B
EOP;
        p2die('register_globals �� On �ł��B', $msg);
    }

    // eAccelerator
    if (extension_loaded('eaccelerator') &&
        version_compare(EACCELERATOR_VERSION, '0.9.5.2', '<'))
    {
        $err = 'eAccelerator���X�V���Ă��������B';
        $ev = EACCELERATOR_VERSION;
        $msg = <<<EOP
<p>PHP 5.2�ŗ�O��ߑ��ł��Ȃ����̂���eAccelerator ({$ev})���C���X�g�[������Ă��܂��B<br>
eAccelerator�𖳌��ɂ��邩�A���̖�肪�C�����ꂽeAccelerator 0.9.5.2�ȍ~���g�p���Ă��������B<br>
<a href="http://eaccelerator.net/">http://eaccelerator.net/</a></p>
EOP;
        p2die($err, $msg, true);
    }

    // �����o�[�W����
    if ($check_recommended) {
        if (version_compare($php_version, '5.3.0-dev', '>=')) {
            $recommended_version = $recommended_version53;
        } else {
            $recommended_version = $recommended_version52;
        }
        if (version_compare($php_version, $recommended_version, '<')) {
            // title.php �̂݃��b�Z�[�W��\��
            if (basename($_SERVER['PHP_SELF'], '.php') == 'title') {
                $_info_msg_ht .= <<<EOP
<p><strong>�����o�[�W�������Â�PHP�œ��삵�Ă��܂��B</strong><em>(PHP {$php_version})</em><br>
PHP {$recommended_version} �ȍ~�ɃA�b�v�f�[�g���邱�Ƃ��������߂��܂��B</p>
<p style="font-size:smaller">���̃��b�Z�[�W��\�����Ȃ��悤�ɂ���ɂ� <em>{\$rep2_directory}</em>/conf/conf.inc.php �� {$check_recommended} �s�ځA<br>
<samp>p2checkenv(__LINE__);</samp> �� <samp>p2checkenv(false);</samp> �ɏ��������Ă��������B</p>
EOP;
            }
            return false;
        }
    }

    return true;
}

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
