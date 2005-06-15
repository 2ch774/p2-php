<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once (P2_LIBRARY_DIR . '/dataphp.class.php');
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

/**
* p2 - p2�p�̃��[�e�B���e�B�N���X
* �C���X�^���X����炸�ɃN���X���\�b�h�ŗ��p����
*
* @create  2004/07/15
*/
class P2Util{

    /**
     * �� �t�@�C�����_�E�����[�h���ĕۑ�����
     */
    function &fileDownload($url, $localfile, $disp_error = 1)
    {
        global $_conf, $_info_msg_ht;
        global $expack_ua;

        $perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;

        if (file_exists($localfile)) {
            $modified = gmdate('D, d M Y H:i:s', filemtime($localfile)).' GMT';
        } else {
            $modified = false;
        }

        // DL
        include_once (P2_LIBRARY_DIR . '/wap.class.php');
        $wap_ua = &new UserAgent;
        if ($expack_ua != "") {
            $wap_ua->setAgent($expack_ua);
        } else {
            $wap_ua->setAgent($_SERVER['HTTP_USER_AGENT']);
        }
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        $wap_req = &new Request;
        $wap_req->setUrl($url);
        $wap_req->setModified($modified);
        if ($_conf['proxy_use']) {
            $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
        }
        $wap_res = &$wap_ua->request($wap_req);

        if ($wap_res->is_error() && $disp_error) {
            $url_t = P2Util::throughIme($wap_req->url);
            $_info_msg_ht .= "<div>Error: {$wap_res->code} {$wap_res->message}<br>";
            $_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>";
        }

        // �X�V����Ă�����
        if ($wap_res->is_success() && $wap_res->code != '304') {
            if (FileCtl::file_write_contents($localfile, $wap_res->content) === FALSE) {
                die("Error: {$localfile} ���X�V�ł��܂���ł���");
            }
            chmod($localfile, $perm);
        }

        return $wap_res;
    }

 	/**
     * ���p�[�~�b�V�����̒��ӂ����N����
     */
    function checkDirWritable($aDir)
    {
        global $_info_msg_ht, $_conf;

        // �}���`���[�U���[�h���́A��񃁃b�Z�[�W��}�����Ă���B

        if (!is_dir($aDir)) {
            /*
            $_info_msg_ht .= '<p class="infomsg">';
            $_info_msg_ht .= '����: �f�[�^�ۑ��p�f�B���N�g��������܂���B<br>';
            $_info_msg_ht .= $aDir."<br>";
            */
            if (is_dir(dirname(realpath($aDir))) && is_writable(dirname(realpath($aDir)))) {
                //$_info_msg_ht .= "�f�B���N�g���̎����쐬�����݂܂�...<br>";
                if (mkdir($aDir, $_conf['data_dir_perm'])) {
                    //$_info_msg_ht .= "�f�B���N�g���̎����쐬���������܂����B";
                    chmod($aDir, $_conf['data_dir_perm']);
                } else {
                    //$_info_msg_ht .= "�f�B���N�g���������쐬�ł��܂���ł����B<br>�蓮�Ńf�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B";
                }
            } else {
                    //$_info_msg_ht .= "�f�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B";
            }
            //$_info_msg_ht .= '</p>';

        } elseif (!is_writable($aDir)) {
            $_info_msg_ht .= '<p class="infomsg">����: �f�[�^�ۑ��p�f�B���N�g���ɏ������݌���������܂���B<br>';
            //$_info_msg_ht .= $aDir.'<br>';
            $_info_msg_ht .= '�f�B���N�g���̃p�[�~�b�V�������������ĉ������B</p>';
        }
    }

    /**
     * ���_�E�����[�hURL����L���b�V���t�@�C���p�X��Ԃ�
     */
    function cacheFileForDL($url)
    {
        global $_conf;

        $parsed = parse_url($url); // URL����

        $save_uri = $parsed['host'] ? $parsed['host'] : '';
        $save_uri .= $parsed['port'] ? ':'.$parsed['port'] : '';
        $save_uri .= $parsed['path'] ? $parsed['path'] : '';
        $save_uri .= $parsed['query'] ? '?'.$parsed['query'] : '';

        $cachefile = $_conf['cache_dir'] . "/".$save_uri;

        FileCtl::mkdir_for($cachefile);

        return $cachefile;
    }

    /**
     * �� host��bbs�������Ԃ�
     */
    function getItaName($host, $bbs)
    {
        global $_conf, $ita_names;

        $id = $host . '/' . $bbs;

        if (isset($ita_names[$id])) {
            return $ita_names[$id];
        }

        $datdir_host = P2Util::datdirOfHost($host);
        $p2_setting_txt = $datdir_host."/".$bbs."/p2_setting.txt";

        if (file_exists($p2_setting_txt)) {
            $p2_setting_cont = @file_get_contents($p2_setting_txt);
            if ($p2_setting_cont) {
                $p2_setting = unserialize($p2_setting_cont);
                if (isset($p2_setting['itaj'])) {
                    $ita_names[$id] = $p2_setting['itaj'];
                    return $ita_names[$id];
                }
            }
        }

        // ��Long�̎擾
        // itaj���Z�b�g�ŊŔ|�b�v�A�b�v�Ŏ擾����SETTING.TXT������΃Z�b�g
        if (!isset($p2_setting['itaj'])) {
            $setting_txt = $datdir_host."/".$bbs."/SETTING.TXT";
            if (file_exists($setting_txt)) {
                $setting = file($setting_txt);
                if ($setting && ($found = preg_grep('/^BBS_TITLE=(.+)/', $setting))) {
                    $bbs_title = explode('=', array_shift($found), 2);
                    $ita_names[$id] = $p2_setting['itaj'] = rtrim($bbs_title[1]);

                    FileCtl::make_datafile($p2_setting_txt, $_conf['p2_perm']);
                    $p2_setting_cont = serialize($p2_setting);
                    if (FileCtl::file_write_contents($p2_setting_txt, $p2_setting_cont) === FALSE) {
                        die("Error: {$p2_setting_txt} ���X�V�ł��܂���ł���");
                    }
                    return $ita_names[$id];
                }
            }
        }
        /*
        // itaj���Z�b�g��2ch pink �Ȃ�SETTING.TXT��ǂ�ŃZ�b�g
        if (!isset($p2_setting['itaj'])) {
            if (P2Util::isHost2chs($host)) {
                $tempfile = $_conf['pref_dir']."/SETTING.TXT.temp";
                P2Util::fileDownload("http://{$host}/{$bbs}/SETTING.TXT", $tempfile);
                // $setting = getHttpContents("http://{$host}/{$bbs}/SETTING.TXT", "", "GET", "", array(""), $httpua="p2");
                $setting = file($tempfile);
                if (file_exists($tempfile)) { unlink($tempfile); }
                if ($setting) {
                    foreach ($setting as $sl) {
                        $sl = trim($sl);
                        if (preg_match("/^BBS_TITLE=(.+)/", $sl, $matches)) {
                            $ita_names[$id] = $p2_setting['itaj'] = $matches[1];
                        }
                    }
                    if ($p2_setting['itaj']) {
                        FileCtl::make_datafile($p2_setting_txt, $_conf['p2_perm']);
                        if ($p2_setting) {$p2_setting_cont = serialize($p2_setting);}
                        if ($p2_setting_cont) {
                            if (FileCtl::file_write_contents($p2_setting_txt, $p2_setting_cont) === FALSE) {
                                die("Error: {$p2_setting_txt} ���X�V�ł��܂���ł���");
                            }
                        }
                        return $ita_names[$id];
                    }
                }
            }
        }
        */

        return null;
    }

    /**
     * �� host����dat�̕ۑ��f�B���N�g����Ԃ�
     */
    function datdirOfHost($host)
    {
        global $datdir;
        static $datdirs = array();
        if (!isset($datdirs[$host])) {
            // 2channel or bbspink
            if (P2Util::isHost2chs($host)) {
                $datdirs[$host] = $datdir.'/2channel';
            // machibbs.com
            } elseif (P2Util::isHostMachiBbs($host)) {
                $datdirs[$host] = $datdir.'/machibbs.com';
            // JBBS�������
            } elseif (P2Util::isHostJbbsShitaraba($host)) {
                if ($host2 = strstr($host, '/')) {
                    $datdirs[$host] = $datdir.'/jbbs.livedoor.jp'.$host2;
                } else {
                    $datdirs[$host] = $datdir.'/jbbs.livedoor.jp';
                }
            } else {
                $datdirs[$host] = $datdir.'/'.$host;
            }
        }
        return $datdirs[$host];
    }

    /**
     * �� failed_post_file �̃p�X�𓾂�֐�
     */
    function getFailedPostFilePath($host, $bbs, $key = false)
    {
        if ($key) {
            $filename = $key.'.failed.data.php';
        } else {
            $filename = 'failed.data.php';
        }
        return $failed_post_file = P2Util::datdirOfHost($host).'/'.$bbs.'/'.$filename;
    }


    /**
     * �����X�g�̃i�r�͈͂�Ԃ�
     */
    function getListNaviRange($disp_from, $disp_range, $disp_all_num)
    {
        $disp_end = 0;
        $disp_navi = array();

        if (!$disp_all_num) {
            $disp_navi['from'] = 0;
            $disp_navi['end'] = 0;
            $disp_navi['all_once'] = true;
            $disp_navi['mae_from'] = 1;
            $disp_navi['tugi_from'] = 1;
            return $disp_navi;
        }

        $disp_navi['from'] = $disp_from;

        $disp_range = $disp_range-1;

        // from���z����
        if ($disp_navi['from'] > $disp_all_num) {
            $disp_navi['from'] = $disp_all_num - $disp_range;
            if ($disp_navi['from'] < 1) {
                $disp_navi['from'] = 1;
            }
            $disp_navi['end'] = $disp_all_num;

        // from �z���Ȃ�
        } else {
            // end �z����
            if ($disp_navi['from'] + $disp_range > $disp_all_num) {
                $disp_navi['end'] = $disp_all_num;
                if ($disp_navi['from'] == 1) {
                    $disp_navi['all_once'] = true;
                }
            // end �z���Ȃ�
            } else {
                $disp_navi['end'] = $disp_from + $disp_range;
            }
        }

        $disp_navi['mae_from'] = $disp_from -1 -$disp_range;
        if ($disp_navi['mae_from'] < 1) {
            $disp_navi['mae_from'] = 1;
        }
        $disp_navi['tugi_from'] = $disp_navi['end'] +1;


        if ($disp_navi['from'] == $disp_navi['end']) {
            $range_on_st = $disp_navi['from'];
        } else {
            $range_on_st = "{$disp_navi['from']}-{$disp_navi['end']}";
        }
        $disp_navi['range_st'] = "{$range_on_st}/{$disp_all_num} ";


        return $disp_navi;

    }


    /**
     * �� key.idx �� data ���L�^����
     */
    function recKeyIdx($keyidx, $data)
    {
        global $_conf;

        $data = rtrim($data) . "\n";

        FileCtl::make_datafile($keyidx, $_conf['key_perm']);
        if (FileCtl::file_write_contents($keyidx, $data) === FALSE) {
            die("Error: {$subjectfile} ���X�V�ł��܂���ł���");
        }

        return true;
    }

    /**
     * �� �������L�^����
     */
    function recRecent($data)
    {
        global $_conf;

        // $_conf['rct_file']�t�@�C�����Ȃ���ΐ���
        require_once (P2_LIBRARY_DIR . '/filectl.class.php');
        FileCtl::make_datafile($_conf['rct_file'], $_conf['rct_perm']);

        $lines = @file($_conf['rct_file']); //�ǂݍ���

        // �ŏ��ɏd���v�f���폜
        $data = rtrim($data);
        $neolines = array();
        if ($lines) {
            $data_ar = explode('<>', $data);
            foreach ($lines as $line) {
                $line = rtrim($line);
                $lar = explode('<>', $line);
                if ($lar[1] == $data_ar[1]) { continue; } // key�ŏd�����
                if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
                $neolines[] = $line;
            }
        }

        // �V�K�f�[�^�ǉ�
        $neolines ? array_unshift($neolines, $data) : $neolines = array($data);

        while (count($neolines) > $_conf['rct_rec_num']) {
            array_pop($neolines);
        }

        // ��������
        $fp = @fopen($_conf['rct_file'], 'wb') or die("Error: {$_conf['rct_file']} ���X�V�ł��܂���ł���");
        if ($neolines) {
            @flock($fp, LOCK_EX);
            foreach ($neolines as $l) {
                fputs($fp, $l."\n");
            }
            @flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    /**
     * �� subject.txt���_�E�����[�h����
     */
    function &subjectDownload($in_url, $subjectfile)
    {
        global $_conf, $datdir, $_info_msg_ht;

        $perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;

        if (file_exists($subjectfile)) {
            if (!empty($_GET['norefresh']) || isset($_REQUEST['word'])) {
                return;	// �X�V���Ȃ��ꍇ�́A���̏�Ŕ����Ă��܂�
            } elseif ((!empty($_POST['newthread']) ) && P2Util::isSubjectFresh($subjectfile)) {
                return;	// �V�K�X�����Ď��łȂ��A�X�V���V�����ꍇ��������
            }
            $modified = gmdate('D, d M Y H:i:s', filemtime($subjectfile))." GMT";
        } else {
            $modified = false;
        }

        if (extension_loaded('zlib') and strstr($in_url, ".2ch.net")) {
            $headers = 'Accept-Encoding: gzip'."\r\n";
        } else {
            $headers = '';
        }

        // ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B
        $url = P2Util::adjustHostJbbs($in_url);

        // ��DL
        include_once (P2_LIBRARY_DIR . '/wap.class.php');
        $wap_ua = &new UserAgent;
        $wap_ua->setAgent('Monazilla/1.00 ('.$_conf['p2name_ua'].'/'.$_conf['p2version_ua'].')');
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        $wap_req = &new Request;
        $wap_req->setUrl($url);
        $wap_req->setModified($modified);
        $wap_req->setHeaders($headers);
        if ($_conf['proxy_use']) {
            $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
        }
        $wap_res = &$wap_ua->request($wap_req);

        if ($wap_res->is_error()) {
            $url_t = P2Util::throughIme($wap_req->url);
            $_info_msg_ht .= "<div>Error: {$wap_res->code} {$wap_res->message}<br>";
            $_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>";
        } else {
            $body = $wap_res->content;
        }

        // �� DL�������� ���� �X�V����Ă�����
        if ($wap_res->is_success() && $wap_res->code != '304') {

            // ������΂Ȃ�EUC��SJIS�ɕϊ�
            if (strstr($subjectfile, $datdir."/jbbs.shitaraba.com") || strstr($subjectfile, $datdir."/jbbs.livedoor.com") || strstr($subjectfile, $datdir."/jbbs.livedoor.jp")) {
                $body = mb_convert_encoding($body, 'SJIS', 'EUC-JP');
            }

            // �t�@�C���ɕۑ�����
            if (FileCtl::file_write_contents($subjectfile, $body) === FALSE) {
                die("Error: {$subjectfile} ���X�V�ł��܂���ł���");
            }
            chmod($subjectfile, $perm);

        } else {
            // touch���邱�ƂōX�V�C���^�[�o���������̂ŁA���΂炭�ă`�F�b�N����Ȃ��Ȃ�
            // �i�ύX���Ȃ��̂ɏC�����Ԃ��X�V����̂́A�����C���i�܂Ȃ����A�����ł͓��ɖ��Ȃ����낤�j
            touch($subjectfile);
        }

        return $wap_res;
    }

    /**
     * �� subject.txt ���V�N�Ȃ� true ��Ԃ�
     */
    function isSubjectFresh($subjectfile)
    {
        global $_conf;

        // �L���b�V��������ꍇ
        if (file_exists($subjectfile)) {
            // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
            // clearstatcache();
            if (@filemtime($subjectfile) > time() - $_conf['sb_dl_interval']) {
                return true;
            }
        }
        return false;
    }

    /**
     * ���z�X�g����N�b�L�[�t�@�C���p�X��Ԃ�
     */
    function cachePathForCookie($host)
    {
        global $_conf;

        $cachefile = $_conf['cookie_dir']."/{$host}/".$_conf['cookie_file_name'];

        FileCtl::mkdir_for($cachefile);

        return $cachefile;
    }

    /**
     * �����p�Q�[�g��ʂ����߂�URL�ϊ�
     */
    function throughIme($url, $htmlize = FALSE)
    {
        global $_conf;

        // p2ime�́Aenc, m, url �̈����������Œ肳��Ă���̂Œ���

        if (!$url) {
            return '';
        }

        switch ($_conf['through_ime']) {
            case '2ch':
                $url_r = preg_replace('|^(https?)://(.+)$|', '$1://ime.nu/$2', $url);
                break;
            case 'p2':
            case 'p2pm':
                $url_r = $_conf['p2ime_url'] . '?enc=1&url=' . rawurlencode(str_replace('&amp;', '&', $url));
                break;
            case 'p2m':
                $url_r = $_conf['p2ime_url'] . '?enc=1&m=1&url=' . rawurlencode(str_replace('&amp;', '&', $url));
                break;
            case 'ex':
                $url_r = 'http://moonshine.s32.xrea.com/moonshime.php?url=' . rawurlencode(str_replace('&amp;', '&', $url));
                break;
            default:
                $url_r = $url;
        }

        if ($htmlize) {
            $url_r = htmlspecialchars($url_r);
        }

        return $url_r;
    }

    /**
     * �� host �� 2ch or bbspink �Ȃ� true ��Ԃ�
     */
    function isHost2chs($host)
    {
        return (boolean)preg_match('/\.(2ch\.net|bbspink\.com)/', $host);
    }

    /**
     * �� host �� be.2ch.net �Ȃ� true ��Ԃ�
     */
    function isHostBe2chNet($host)
    {
        return (boolean)preg_match('/^be\.2ch\.net/', $host);
    }

    /**
     * �� host �� bbspink �Ȃ� true ��Ԃ�
     */
    function isHostBbsPink($host)
    {
        return (boolean)preg_match('/\.bbspink\.com/', $host);
    }

    /**
     * �� host �� machibbs �Ȃ� true ��Ԃ�
     */
    function isHostMachiBbs($host)
    {
        return (boolean)preg_match('/\.(machibbs\.com|machi\.to)/', $host);
    }

    /**
     * �� host �� machibbs.net �܂��r�˂��� �Ȃ� true ��Ԃ�
     */
    function isHostMachiBbsNet($host)
    {
        return (boolean)preg_match('/\.machibbs\.net/', $host);
    }

    /**
     * �� host �� JBBS@������� �Ȃ� true ��Ԃ�
     */
    function isHostJbbsShitaraba($host)
    {
        return (boolean)preg_match('/jbbs\.shitaraba\.com|jbbs\.livedoor\.com|jbbs\.livedoor\.jp/', $host);
    }

    /**
     * ��JBBS@������΂̃z�X�g���ύX�ɑΉ����ĕύX����
     *
     * @param	string	$in_str	�z�X�g���ł�URL�ł��Ȃ�ł��ǂ�
     */
    function adjustHostJbbs($in_str)
    {
        return preg_replace('/jbbs\.shitaraba\.com|jbbs\.livedoor\.com/', 'jbbs.livedoor.jp', $in_str, 1);
    }

    /**
     * �� dat ���c���Ȃ� host �Ȃ� true ��Ԃ�
     */
    function isHostNoCacheData($host)
    {
        return (boolean)preg_match('/^epg\.2ch\.net/', $host);
    }

    /**
     * �� �X���b�h���w�肷��
     */
    function detectThread()
    {
        global $_conf;

        // �X��URL�̒��ڎw��
        if (($nama_url = $_GET['nama_url']) || ($nama_url = $_GET['url'])) {

            // 2ch or pink - http://choco.2ch.net/test/read.cgi/event/1027770702/
            if (preg_match("{http://([^/]+\.(2ch\.net|bbspink\.com|mmobbs\.com))/test/read\.cgi/([^/]+)/([0-9]+)(/)?([^/]+)?}", $nama_url, $matches)) {
                $host = $matches[1];
                $bbs = $matches[3];
                $key = $matches[4];
                $ls = $matches[6];

            // 2ch or pink �ߋ����Ohtml - http://pc.2ch.net/mac/kako/1015/10153/1015358199.html
            } elseif (preg_match("{(http://([^/]+\.(2ch\.net|bbspink\.com))(/[^/]+)?/([^/]+)/kako/\d+(/\d+)?/(\d+)).html}", $nama_url, $matches) ){
                $host = $matches[2];
                $bbs = $matches[5];
                $key = $matches[7];
                $kakolog_uri = $matches[1];
                $_GET['kakolog'] = urlencode($kakolog_uri);

            // �܂����������JBBS - http://kanto.machibbs.com/bbs/read.pl?BBS=kana&KEY=1034515019
            } elseif (preg_match("{http://([^/]+\.machibbs\.com|[^/]+\.machi\.to)/bbs/read\.(pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(&START=([0-9]+))?(&END=([0-9]+))?[^\"]*}", $nama_url, $matches) ){
                $host = $matches[1];
                $bbs = $matches[3];
                $key = $matches[4];
                $ls = $matches[6] ."-". $matches[8];
            } elseif (preg_match("{http://(jbbs\.(?:shitaraba\.com|livedoor\.(?:com|jp))/[^/]+?)/bbs/read\.(?:pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(?:&START=([0-9]+))?(&END=([0-9]+))?[^\"]*}", $nama_url, $matches) ){
                $host = $matches[1];
                $bbs = $matches[2];
                $key = $matches[3];
                $ls = $matches[4] ."-". $matches[5];

            // �������JBBS http://jbbs.livedoor.com/bbs/read.cgi/computer/2999/1081177036/-100
            } elseif (preg_match("{http://(jbbs\.(?:shitaraba\.com|livedoor\.(?:com|jp)))/bbs/read\.cgi/(\w+)/(\d+)/(\d+)/((\d+)?-(\d+)?)?[^\"]*}", $nama_url, $matches) ){
                $host = $matches[1] ."/". $matches[2];
                $bbs = $matches[3];
                $key = $matches[4];
                $ls = $matches[5];
            }

        } else {
            isset($_REQUEST['host']) && $host = $_REQUEST['host']; // "pc.2ch.net"
            isset($_REQUEST['bbs'])  && $bbs  = $_REQUEST['bbs'];  // "php"
            isset($_REQUEST['key'])  && $key  = $_REQUEST['key'];  // "1022999539"
            isset($_REQUEST['ls'])   && $ls   = $_REQUEST['ls'];   // "all"

            // �z�X�g����
            $hostMapCache = $_conf['pref_dir'] . '/p2_host_bbs_map.txt';
            if ((!$host || $host == '2channel' || $host == 'machibbs.com') && $bbs && file_exists($hostMapCache)) {
                $regexp = '/^';
                if ($host == 'machibbs.com') {
                    $regexp .= '[a-z]+\.(?:machibbs\.com|machi\.to)';
                } else {
                    $regexp .= '[a-z]+[0-9]*\.(?:2ch\.net|bbspink\.com)';
                }
                $regexp .= '<>'.preg_quote($bbs, '/').'<>/';
                $host = '';
                $filter = create_function('$line', 'return (boolean)preg_match(\''.$regexp.'\', $line);');
                $hostMap = file($hostMapCache);
                if ($found = array_filter($hostMap, $filter)) {
                    list($host,) = explode('<>', array_shift($found));
                }
            }
        }

        if (!($host && $bbs && $key)) {
            $htm['nama_url'] = htmlspecialchars($nama_url);
            $msg = "p2 - {$_conf['read_php']}: �X���b�h�̎w�肪�ςł��B<br>"
                . "<a href=\"{$htm['nama_url']}\">" .$htm['nama_url']."</a>";
            die($msg);
        }

        return array($host, $bbs, $key, $ls);
    }

    /**
     * �� http header no cache ���o��
     */
    function header_nocache()
    {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');	// ���t���ߋ�
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');	// ��ɏC������Ă���
        header('Cache-Control: no-store, no-cache, must-revalidate');	// HTTP/1.1
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache'); // HTTP/1.0
    }

    /**
     * �� http header Content-Type �o��
     */
    function header_content_type()
    {
        header('Content-Type: text/html; charset=Shift_JIS');
    }

    /**
     * �� http header Content-Length �o��
     * �g�����F
     * ob_start(array('P2Util', 'header_content_length'));
     */
    function header_content_length($buf)
    {
        header('Content-Length: ' . strlen($buf));
        return $buf;
    }

    /**
     * �����`���̏������ݗ�����V�`���ɕϊ�����
     */
    function transResHistLog()
    {
        global $_conf;

        $rh_dat_php = $_conf['pref_dir'].'/p2_res_hist.dat.php';
        $rh_dat = $_conf['pref_dir'].'/p2_res_hist.dat';

        // �������ݗ������L�^���Ȃ��ݒ�̏ꍇ�͉������Ȃ�
        if ($_conf['res_write_rec'] == 0) {
            return true;
        }

        // p2_res_hist.dat.php�i�V�j ���Ȃ��āAp2_res_hist.dat�i���j ���ǂݍ��݉\�ł�������
        if ((!file_exists($rh_dat_php)) and is_readable($rh_dat)) {
            // �ǂݍ����
            if ($cont = @file_get_contents($rh_dat)) {
                // <>��؂肩��^�u��؂�ɕύX����
                // �܂��^�u��S�ĊO����
                $cont = str_replace("\t", "", $cont);
                // <>���^�u�ɕϊ�����
                $cont = str_replace("<>", "\t", $cont);

                // �f�[�^PHP�`���ŕۑ�
                DataPhp::writeDataPhp($cont, $rh_dat_php, $_conf['res_write_perm']);
            }
        }
        return true;
    }

    /**
     * ���O��̃A�N�Z�X�����擾
     */
    function getLastAccessLog($logfile)
    {
        // �ǂݍ����
        if (!$lines = DataPhp::fileDataPhp($logfile)) {
            return false;
        }
        if (!isset($lines[1])) {
            return false;
        }
        $line = rtrim($lines[1]);
        $lar = explode("\t", $line);

        $alog['user'] = $lar[6];
        $alog['date'] = $lar[0];
        $alog['ip'] = $lar[1];
        $alog['host'] = $lar[2];
        $alog['ua'] = $lar[3];
        $alog['referer'] = $lar[4];

        return $alog;
    }


    /**
     * ���A�N�Z�X�������O�ɋL�^����
     */
    function recAccessLog($logfile, $maxline = 100)
    {
        global $_conf, $login;

        // ���O�t�@�C���̒��g���擾����
        if ($lines = DataPhp::fileDataPhp($logfile)) {
            // �����s����
            while (sizeof($lines) > $maxline -1) {
                array_pop($lines);
            }
        } else {
            $lines = array();
        }
        $lines = array_map('rtrim', $lines);

        // �ϐ��ݒ�
        $date = date('Y/m/d (D) G:i:s');

        // HOST���擾
        if (!$remoto_host = $_SERVER['REMOTE_HOST']) {
            $remoto_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        }
        if ($remoto_host == $_SERVER['REMOTE_ADDR']) {
            $remoto_host = '';
        }

        if (isset($login['user'])) {
            $user = $login['user'];
        } else {
            $user = '';
        }

        // �V�������O�s��ݒ�
        $newdata = $date."<>".$_SERVER['REMOTE_ADDR']."<>".$remoto_host."<>".$_SERVER['HTTP_USER_AGENT']."<>".$_SERVER['HTTP_REFERER']."<>".""."<>".$user;
        //$newdata = htmlspecialchars($newdata);


        // �܂��^�u��S�ĊO����
        $newdata = str_replace("\t", "", $newdata);
        // <>���^�u�ɕϊ�����
        $newdata = str_replace("<>", "\t", $newdata);

        // �V�����f�[�^����ԏ�ɒǉ�
        @array_unshift($lines, $newdata);

        $cont = implode("\n", $lines) . "\n";

        // �������ݏ���
        DataPhp::writeDataPhp($cont, $logfile, $_conf['res_write_perm']);

        return true;
    }

    /**
     * ���u���E�U��Safari�n�Ȃ�true��Ԃ�
     */
    function isBrowserSafariGroup()
    {
        return (boolean)preg_match('/Safari|AppleWebKit|Konqueror/', $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * 2ch�����O�C����ID��PASS�Ǝ������O�C���ݒ��ۑ�����
     */
    function saveIdPw2ch($login2chID, $login2chPW, $autoLogin2ch = '')
    {
        global $_conf;

        include_once (P2_LIBRARY_DIR . '/md5_crypt.inc.php');

        $crypted_login2chPW = md5_encrypt($login2chPW, $_conf['md5_crypt_key']);
    $idpw2ch_cont = <<<EOP
<?php
\$rec_login2chID = '{$login2chID}';
\$rec_login2chPW = '{$crypted_login2chPW}';
\$rec_autoLogin2ch = '{$autoLogin2ch}';
?>
EOP;
        FileCtl::make_datafile($_conf['idpw2ch_php'], $_conf['pass_perm']);	// �t�@�C�����Ȃ���ΐ���
        $fp = @fopen($_conf['idpw2ch_php'], 'wb') or die("p2 Error: {$_conf['idpw2ch_php']} ���X�V�ł��܂���ł���");
        @flock($fp, LOCK_EX);
        fputs($fp, $idpw2ch_cont);
        @flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }

    /**
     * 2ch�����O�C���̕ۑ��ς�ID��PASS�Ǝ������O�C���ݒ��ǂݍ���
     */
    function readIdPw2ch()
    {
        global $_conf;

        include_once (P2_LIBRARY_DIR . '/md5_crypt.inc.php');

        if (!file_exists($_conf['idpw2ch_php'])) {
            return false;
        }

        $rec_login2chID = NULL;
        $login2chPW = NULL;
        $rec_autoLogin2ch = NULL;

        include $_conf['idpw2ch_php'];

        // �p�X�𕡍���
        if (!empty($rec_login2chPW)) {
            $login2chPW = md5_decrypt($rec_login2chPW, $_conf['md5_crypt_key']);
        }

        return array($rec_login2chID, $login2chPW, $rec_autoLogin2ch);
    }

    /**
     * getCsrfId
     */
    function getCsrfId()
    {
        global $login;

        return md5($login['user'] . $login['pass'] . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_SOFTWARE']);
    }

    /**
     * checkCsrfId
     */
    function checkCsrfId($str)
    {
        $csrfid = P2Util::getCsrfId();

        if ($str != $csrfid) {
            die('p2 error: �s���ȃ|�X�g�ł�');
        }
    }

    // {{{ getmicrotime()

    /**
     * �}�C�N���b�܂ł̃^�C���X�^���v��Ԃ�
     *
     * @access  public
     * @return  real
     */
    function getmicrotime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$sec + (float)$usec);
    }

    // }}}
    // {{{ p2diskused()

    /**
     * du�R�}���h���g���ăf�B�X�N�g�p�ʂ��擾����
     */
    function p2diskused($root = NULL, $symlink = FALSE)
    {
        $root = (is_string($root) && is_dir($root)) ? realpath($root) :dirname(__FILE__);
        $root = escapeshellarg($root);
        $opt = ($symlink) ? '-L' : '';
        $result = exec("du -c -h $opt $root | tail -n 1");
        $used = preg_replace('/^ *([\d.]+[KMGTPE]?).+$/', '$1', $result);
        return $used;
    }

    // }}}
    // {{{ p2dumpinfo()

    /**
     * �X�N���v�g�J�n������̌o�ߎ��ԁi�ƃf�B�X�N�g�p�ʁj��\������
     */
    function p2dumpinfo()
    {
        global $p2_start_time, $_dump_diskused, $_dump_changeroot;
        if (!$p2_start_time) {
            $p2_start_time = P2Util::getmicrotime();
        }
        switch ($_dump_diskused) {
            case 2: $p2_disk_used = ' | ' . P2Util::p2diskused($_dump_changeroot, TRUE) . ' used.'; break;
            case 1: $p2_disk_used = ' | ' . P2Util::p2diskused($_dump_changeroot) . ' used.'; break;
            default: $p2_disk_used = '';
        }
        $p2_end_time = P2Util::getmicrotime();
        $p2_process_time = number_format($p2_end_time - $p2_start_time, 3);
        echo "<div style=\"text-align:right\">{$p2_process_time}sec{$p2_disk_used}</div>";
    }

    // }}}
    // {{{ scandir_r()

    /**
     * �ċA�I�Ƀf�B���N�g���𑖍�����
     *
     * ���X�g���t�@�C���ƃf�B���N�g���ɕ����ĕԂ��B���ꂻ��̃��X�g�͒P���Ȕz��
     */
    function scandir_r($dir)
    {
        $dir = realpath($dir);
        $list = array('files' => array(), 'dirs' => array());
        $files = scandir($dir);
        foreach ($files as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $filename = $dir . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($filename)) {
                $child = P2Util::scandir_r($filename);
                if ($child) {
                    $list['dirs'] = array_merge($list['dirs'], $child['dirs']);
                    $list['files'] = array_merge($list['files'], $child['files']);
                }
                $list['dirs'][] = $filename;
            } else {
                $list['files'][] = $filename;
            }
        }
        return $list;
    }

    // }}}
    // {{{ garbageCollection()

    /**
     * ������ЂƂ̃K�x�R��
     *
     * $targetDir����ŏI�X�V���$lifeTime�b�ȏソ�����t�@�C�����폜
     *
     * @access  public
     * @param   string   $targetDir  �K�[�x�b�W�R���N�V�����Ώۃf�B���N�g��
     * @param   integer  $lifeTime   �t�@�C���̗L�������i�b�j
     * @param   string   $prefix     �Ώۃt�@�C�����̐ړ����i�I�v�V�����j
     * @param   string   $suffix     �Ώۃt�@�C�����̐ڔ����i�I�v�V�����j
     * @param   boolean  $recurive   �ċA�I�ɃK�[�x�b�W�R���N�V�������邩�ۂ��i�f�t�H���g�ł�FALSE�j
     * @return  array    �폜�ɐ��������t�@�C���Ǝ��s�����t�@�C����ʁX�ɋL�^�����񎟌��̔z��
     */
    function garbageCollection($targetDir, $lifeTime, $prefix = '', $suffix = '', $recursive = FALSE)
    {
        $result = array('successed' => array(), 'failed' => array(), 'skipped' => array());
        $expire = time() - $lifeTime;
        //�t�@�C�����X�g�擾
        if ($recursive) {
            $list = P2Util::scandir_r($targetDir);
            $files = &$list['files'];
        } else {
            $list = scandir($targetDir);
            $files = array();
            $targetDir = realpath($targetDir) . DIRECTORY_SEPARATOR;
            foreach ($list as $filename) {
                if ($filename == '.' || $filename == '..') { continue; }
                $files[] = $targetDir . $filename;
            }
        }
        //�����p�^�[���ݒ�i$prefix��$suffix�ɃX���b�V�����܂܂Ȃ��悤�Ɂj
        if ($prefix || $suffix) {
            $prefix = (is_array($prefix)) ? implode('|', array_map('preg_quote', $prefix)) : preg_quote($prefix);
            $suffix = (is_array($suffix)) ? implode('|', array_map('preg_quote', $suffix)) : preg_quote($suffix);
            $pattern = '/^' . $prefix . '.+' . $suffix . '$/';
        } else {
            $pattern = '';
        }
        //�K�x�R���J�n
        foreach ($files as $filename) {
            if ($pattern && !preg_match($pattern, basename($filename))) {
                //$result['skipped'][] = $filename;
                continue;
            }
            if (filemtime($filename) < $expire) {
                if (@unlink($filename)) {
                    $result['successed'][] = $filename;
                } else {
                    $result['failed'][] = $filename;
                }
            }
        }
        return $result;
    }

    // }}}
    // {{{ session_gc()

    /**
     * �Z�b�V�����t�@�C���̃K�[�x�b�W�R���N�V����
     *
     * session.save_path�̃p�X�̐[����2���傫���ꍇ�A�K�[�x�b�W�R���N�V�����͍s���Ȃ�����
     * �����ŃK�[�x�b�W�R���N�V�������Ȃ��Ƃ����Ȃ��B
     *
     * @access  public
     * @return  void
     *
     * @link http://jp.php.net/manual/ja/ref.session.php#ini.session.save-path
     */
    function session_gc()
    {
        global $_conf;

        if (session_module_name() != 'files') {
            return;
        }

        $d = (int)ini_get('session.gc_divisor');
        $p = (int)ini_get('session.gc_probability');
        mt_srand();
        if (mt_rand(1, $d) <= $p) {
            $m = (int)ini_get('session.gc_maxlifetime');
            P2Util::garbageCollection($_conf['session_dir'], $m);
        }
    }

    // }}}
    // {{{ Info_Dump()

    /**
     * �������z����ċA�I�Ƀe�[�u���ɕϊ�����
     *
     * �Q�����˂��setting.txt���p�[�X�����z��p�̏������򂠂�
     * ���ʂɃ_���v����Ȃ� Var_Dump::display($value, TRUE) ��������
     * (�o�[�W����1.0.0�ȍ~�AVar_Dump::display() �̑��������^�̂Ƃ�
     *  ���ڕ\���������ɁA�_���v���ʂ�������Ƃ��ĕԂ�B)
     *
     * @access  public
     * @param   array    $info    �e�[�u���ɂ������z��
     * @param   integer  $indent  ���ʂ�HTML�����₷�����邽�߂̃C���f���g��
     * @return  string   <table>~</table>
     */
    function Info_Dump($info, $indent = 0)
    {
        $table = '<table border="0" cellspacing="1" cellpadding="0">' . "\n";
        $n = count($info);
        foreach ($info as $key => $value) {
            if (!is_object($value) && !is_resource($value)) {
                for ($i = 0; $i < $indent; $i++) { $table .= "\t"; }
                if ($n == 1 && $key === 0) {
                    $table .= '<tr><td class="tdcont">';
                /*} elseif (preg_match('/^\w+$/', $key)) {
                    $table .= '<tr class="setting"><td class="tdleft"><b>' . $key . '</b></td><td class="tdcont">';*/
                } else {
                    $table .= '<tr><td class="tdleft"><b>' . $key . '</b></td><td class="tdcont">';
                }
                if (is_array($value)) {
                    $table .= P2Util::Info_Dump($value, $indent+1); //�z��̏ꍇ�͍ċA�Ăяo���œW�J
                } elseif ($value === true) {
                    $table .= '<i>TRUE</i>';
                } elseif ($value === false) {
                    $table .= '<i>FALSE</i>';
                } elseif (is_null($value)) {
                    $table .= '<i>NULL</i>';
                } elseif (is_scalar($value)) {
                    if ($value === '') { //��O:�󕶎���B0���܂߂Ȃ��悤�Ɍ^���l�����Ĕ�r
                        $table .= '<i>(no value)</i>';
                    } elseif ($key == '���O�擾��<br>�X���b�h��') { //���O�폜��p
                        $table .= $value;
                    } elseif ($key == '���[�J�����[��') { //���[�J�����[����p
                        $table .= '<table border="0" cellspacing="1" cellpadding="0" class="child">';
                        $table .= "\n\t\t<tr><td id=\"rule\">{$value}</tr></td>\n\t</table>";
                    } elseif (preg_match('/^(https?|ftp):\/\/[\w\/\.\+\-\?=~@#%&:;]+$/i', $value)) { //�����N
                        $table .= '<a href="' . P2Util::throughIme($value) . '" target="_blank">' . $value . '</a>';
                    } elseif ($key == '�w�i�F' || substr($key, -6) == '_COLOR') { //�J���[�T���v��
                        $table .= "<span class=\"colorset\" style=\"color:{$value};\">��</span>�i{$value}�j";
                    } else {
                        $table .= htmlspecialchars($value);
                    }
                }
                $table .= '</td></tr>' . "\n";
            }
        }
        for ($i = 1; $i < $indent; $i++) { $table .= "\t"; }
        $table .= '</table>';
        $table = str_replace('<td class="tdcont"><table border="0" cellspacing="1" cellpadding="0">',
            '<td class="tdcont"><table border="0" cellspacing="1" cellpadding="0" class="child">', $table);

        return $table;
    }

    // }}}
    // {{{ re_htmlspecialchars()

    /**
     * ["&<>]�����̎Q�ƂɂȂ��Ă��邩�ǂ����s���ȕ�����ɑ΂���htmlspecialchars()��������
     */
    function re_htmlspecialchars($str)
    {
        // e�C���q��t�����Ƃ��A"�͎����ŃG�X�P�[�v�����悤��
        return preg_replace('/["<>]|&(?!#?\w+;)/e', 'htmlspecialchars("$0")', $str);
    }

    // }}}
    // {{{ mkTrip()

    /**
     * �g���b�v�𐶐�����
     */
    function mkTrip($key, $length = 10)
    {
        $salt = substr($key . 'H.', 1, 2);
        $salt = preg_replace('/[^\.-z]/', '.', $salt);
        $salt = str_replace(
            array(':',';','<','=','>','?','@','[','\\',']','^','_','`'),
            array('A','B','C','D','E','F','G','a','b','c','d','e','f'),
            $salt);

        return substr(crypt($key, $salt), -$length);
    }

    // }}}
}

?>
