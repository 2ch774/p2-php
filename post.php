<?php
/**
 * rep2 - ���X��������
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

if (!empty($_conf['disable_res'])) {
    p2die('�������݋@�\�͖����ł��B');
}

// �����G���[
if (empty($_POST['host'])) {
    p2die('�����̎w�肪�ςł�');
}

if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
    p2die('�s���ȃ|�X�g�ł�');
}

if ($_conf['expack.aas.enabled'] && !empty($_POST['PREVIEW_AAS'])) {
    include P2_BASE_DIR . '/aas.php';
    exit;
}

//================================================================
// �ϐ�
//================================================================
$newtime = date('gis');

$post_param_keys    = array('bbs', 'key', 'time', 'FROM', 'mail', 'MESSAGE', 'subject', 'submit');
$post_internal_keys = array('host', 'sub', 'popup', 'rescount', 'ttitle_en');
$post_optional_keys = array('newthread', 'submit_beres', 'from_read_new', 'maru', 'csrfid');
$post_p2_flag_keys  = array('b', 'p2_post_confirm_cookie');

foreach ($post_param_keys as $pk) {
    ${$pk} = (isset($_POST[$pk])) ? $_POST[$pk] : '';
}
foreach ($post_internal_keys as $pk) {
    ${$pk} = (isset($_POST[$pk])) ? $_POST[$pk] : '';
}

if (!isset($ttitle)) {
    if ($ttitle_en) {
        $ttitle = base64_decode($ttitle_en);
    } elseif ($subject) {
        $ttitle = $subject;
    } else {
        $ttitle = '';
    }
}

//$MESSAGE = rtrim($MESSAGE);

// {{{ �\�[�X�R�[�h�����ꂢ�ɍČ������悤�ɕϊ�

if (!empty($_POST['fix_source'])) {
    // �^�u���X�y�[�X��
    $MESSAGE = tab2space($MESSAGE);
    // ���ꕶ�������̎Q�Ƃ�
    $MESSAGE = htmlspecialchars($MESSAGE, ENT_QUOTES);
    // ����URL�����N���
    $MESSAGE = str_replace('tp://', 't&#112;://', $MESSAGE);
    // �s���̃X�y�[�X�����̎Q�Ƃ�
    $MESSAGE = preg_replace('/^ /m', '&nbsp;', $MESSAGE);
    // ������X�y�[�X�̈�ڂ����̎Q�Ƃ�
    $MESSAGE = preg_replace('/(?<!&nbsp;)  /', '&nbsp; ', $MESSAGE);
    // ���X�y�[�X������Ԃ��Ƃ��̎d�グ
    $MESSAGE = preg_replace('/(?<=&nbsp;)  /', ' &nbsp;', $MESSAGE);
}

// }}}
// {{{ �N�b�L�[�̓ǂݍ���

$cookie_file = P2Util::cachePathForCookie($host);
if ($cookie_cont = FileCtl::file_read_contents($cookie_file)) {
    $p2cookies = unserialize($cookie_cont);
    if ($p2cookies['expires']) {
        if (time() > strtotime($p2cookies['expires'])) { // �����؂�Ȃ�j��
            // echo "<p>�����؂�̃N�b�L�[���폜���܂���</p>";
            unlink($cookie_file);
            unset($cookie_cont, $p2cookies);
        }
    }
}

// }}}

// ������΂�livedoor�ړ]�ɑΉ��Bpost���livedoor�Ƃ���B
$host = P2Util::adjustHostJbbs($host);

// machibbs�AJBBS@������� �Ȃ�
if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
    $bbs_cgi = '/bbs/write.cgi';

    // JBBS@������� �Ȃ�
    if (P2Util::isHostJbbsShitaraba($host)) {
        $bbs_cgi = '../../bbs/write.cgi';
        preg_match('/\\/(\\w+)$/', $host, $ar);
        $dir = $ar[1];
        $dir_k = 'DIR';
    }

    /* compact() �� array_combine() ��POST����l�̔z������̂ŁA
       $post_param_keys �� $post_send_keys �̒l�̏����͑�����I */
    //$post_param_keys  = array('bbs', 'key', 'time', 'FROM', 'mail', 'MESSAGE', 'subject', 'submit');
    $post_send_keys     = array('BBS', 'KEY', 'TIME', 'NAME', 'MAIL', 'MESSAGE', 'SUBJECT', 'submit');
    $key_k     = 'KEY';
    $subject_k = 'SUBJECT';

// 2ch
} else {
    if ($sub) {
        $bbs_cgi = "/test/{$sub}bbs.cgi";
    } else {
        $bbs_cgi = '/test/bbs.cgi';
    }
    $post_send_keys = $post_param_keys;
    $key_k     = 'key';
    $subject_k = 'subject';
}

// submit �͏������ނŌŒ肵�Ă��܂��iBe�ŏ������ނ̏ꍇ�����邽�߁j
$submit = '��������';

$post = array_combine($post_send_keys, compact($post_param_keys));
$post_cache = $post;
unset($post_cache['submit']);

if (!empty($_POST['newthread'])) {
    unset($post[$key_k]);
    $location_ht = "{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}";
} else {
    unset($post[$subject_k]);
    $location_ht = "{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}&amp;ls={$rescount}-&amp;refresh=1&amp;nt={$newtime}{$_conf['k_at_a']}#r{$rescount}";
}

if (P2Util::isHostJbbsShitaraba($host)) {
    $post[$dir_k] = $dir;
}

// {{{ 2ch�Ł����O�C�����Ȃ�sid�ǉ�

if (!empty($_POST['maru']) and P2Util::isHost2chs($host) && file_exists($_conf['sid2ch_php'])) {

    // ���O�C����A24���Ԉȏ�o�߂��Ă����玩���ă��O�C��
    if (file_exists($_conf['idpw2ch_php']) && filemtime($_conf['sid2ch_php']) < time() - 60*60*24) {
        require_once P2_LIB_DIR . '/login2ch.inc.php';
        login2ch();
    }

    include $_conf['sid2ch_php'];
    $post['sid'] = $SID2ch;
}

// }}}

if (!empty($_POST['p2_post_confirm_cookie'])) {
    $post_ignore_keys = array_merge($post_param_keys, $post_internal_keys, $post_optional_keys, $post_p2_flag_keys);
    foreach ($_POST as $k => $v) {
        if (!array_key_exists($k, $post) && !in_array($k, $post_ignore_keys)) {
            $post[$k] = $v;
        }
    }
}

if (!empty($_POST['newthread'])) {
    $ptitle = 'rep2 - �V�K�X���b�h�쐬';
} else {
    $ptitle = 'rep2 - ���X��������';
}

//================================================================
// �������ݏ���
//================================================================

//=============================================
// �|�X�g���s
//=============================================
$posted = postIt($host, $bbs, $key, $post);

//=============================================
// cookie �ۑ�
//=============================================
FileCtl::make_datafile($cookie_file, $_conf['p2_perm']); // �Ȃ���ΐ���
if ($p2cookies) {$cookie_cont = serialize($p2cookies);}
if ($cookie_cont) {
    if (FileCtl::file_write_contents($cookie_file, $cookie_cont) === false) {
        p2die('cannot write file.');
    }
}

//=============================================
// �X�����Đ����Ȃ�Asubject����key���擾
//=============================================
if (!empty($_POST['newthread']) && $posted) {
    sleep(1);
    $key = getKeyInSubject();
}

//=============================================
// key.idx �ۑ�
//=============================================
// <> ���O���B�B
$tag_rec['FROM'] = str_replace('<>', '', $FROM);
$tag_rec['mail'] = str_replace('<>', '', $mail);

// ���O�ƃ��[���A�󔒎��� P2NULL ���L�^
$tag_rec_n['FROM'] = ($tag_rec['FROM'] == '') ? 'P2NULL' : $tag_rec['FROM'];
$tag_rec_n['mail'] = ($tag_rec['mail'] == '') ? 'P2NULL' : $tag_rec['mail'];

if ($host && $bbs && $key) {
    $keyidx = P2Util::idxDirOfHostBbs($host, $bbs) . $key . '.idx';

    // �ǂݍ���
    if ($keylines = FileCtl::file_read_lines($keyidx, FILE_IGNORE_NEW_LINES)) {
        $akeyline = explode('<>', $keylines[0]);
    }
    $sar = array($akeyline[0], $akeyline[1], $akeyline[2], $akeyline[3], $akeyline[4],
                 $akeyline[5], $akeyline[6], $tag_rec_n['FROM'], $tag_rec_n['mail'], $akeyline[9],
                 $akeyline[10], $akeyline[11], $akeyline[12]);
    P2Util::recKeyIdx($keyidx, $sar); // key.idx�ɋL�^
}

//=============================================
// �������ݗ���
//=============================================
if (empty($posted)) {
    exit;
}

if ($host && $bbs && $key) {

    $lock = new P2Lock($_conf['res_hist_idx'], false);

    FileCtl::make_datafile($_conf['res_hist_idx'], $_conf['res_write_perm']); // �Ȃ���ΐ���

    $lines = FileCtl::file_read_lines($_conf['res_hist_idx'], FILE_IGNORE_NEW_LINES);

    $neolines = array();

    // {{{ �ŏ��ɏd���v�f���폜���Ă���

    if (is_array($lines)) {
        foreach ($lines as $line) {
            $lar = explode('<>', $line);
            // �d�����, key�̂Ȃ����͕̂s���f�[�^
            if (!$lar[1] || $lar[1] == $key) {
                continue;
            } 
            $neolines[] = $line;
        }
    }

    // }}}

    // �V�K�f�[�^�ǉ�
    $newdata = "{$ttitle}<>{$key}<><><><><><>{$tag_rec['FROM']}<>{$tag_rec['mail']}<><>{$host}<>{$bbs}";
    array_unshift($neolines, $newdata);
    while (sizeof($neolines) > $_conf['res_hist_rec_num']) {
        array_pop($neolines);
    }

    // {{{ ��������

    if ($neolines) {
        $cont = '';
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }

        if (FileCtl::file_write_contents($_conf['res_hist_idx'], $cont) === false) {
            p2die('cannot write file.');
        }
    }

    // }}}

    $lock->free();
}

//=============================================
// �������݃��O�L�^
//=============================================
if ($_conf['res_write_rec']) {

    // �f�[�^PHP�`���ip2_res_hist.dat.php, �^�u��؂�j�̏������ݗ������Adat�`���ip2_res_hist.dat, <>��؂�j�ɕϊ�����
    P2Util::transResHistLogPhpToDat();

    $date_and_id = date('y/m/d H:i');
    $message = htmlspecialchars($MESSAGE, ENT_NOQUOTES);
    $message = preg_replace('/\\r?\\n/', '<br>', $message);

    FileCtl::make_datafile($_conf['res_hist_dat'], $_conf['res_write_perm']); // �Ȃ���ΐ���

    $resnum = '';
    if (!empty($_POST['newthread'])) {
        $resnum = 1;
    } else {
        if ($rescount) {
            $resnum = $rescount + 1;
        }
    }

    // �V�K�f�[�^
    $newdata = "{$tag_rec['FROM']}<>{$tag_rec['mail']}<>{$date_and_id}<>{$message}<>{$ttitle}<>{$host}<>{$bbs}<>{$key}<>{$resnum}";

    // �܂��^�u��S�ĊO���āi2ch�̏������݂ł̓^�u�͍폜����� 2004/12/13�j
    $newdata = str_replace("\t", '', $newdata);
    // <>���^�u�ɕϊ�����
    //$newdata = str_replace('<>', "\t", $newdata);

    $cont = $newdata."\n";

    // �������ݏ���
    if (FileCtl::file_write_contents($_conf['res_hist_dat'], $cont, FILE_APPEND) === false) {
        trigger_error('p2 error: �������݃��O�̕ۑ��Ɏ��s���܂���', E_USER_WARNING);
        // ����͎��ۂ͕\������Ȃ�����ǂ�
        //$_info_msg_ht .= "<p>p2 error: �������݃��O�̕ۑ��Ɏ��s���܂���</p>";
    }
}

//===========================================================
// �֐�
//===========================================================
// {{{ postIt()

/**
 * ���X����������
 *
 * @return boolean �������ݐ����Ȃ� true�A���s�Ȃ� false
 */
function postIt($host, $bbs, $key, $post)
{
    global $_conf, $post_result, $post_error2ch, $p2cookies, $popup, $rescount, $ttitle_en;
    global $STYLE, $skin_en;
    global $bbs_cgi, $post_cache;

    $method = 'POST';
    $bbs_cgi_url = 'http://' . $host . $bbs_cgi;

    $URL = parse_url($bbs_cgi_url); // URL����
    if (isset($URL['query'])) { // �N�G���[
        $URL['query'] = '?' . $URL['query'];
    } else {
        $URL['query'] = '';
    }

    // �v���L�V
    if ($_conf['proxy_use']) {
        $send_host = $_conf['proxy_host'];
        $send_port = $_conf['proxy_port'];
        $send_path = $bbs_cgi_url;
    } else {
        $send_host = $URL['host'];
        $send_port = $URL['port'];
        $send_path = $URL['path'] . $URL['query'];
    }

    if (!$send_port) { $send_port = 80; }    // �f�t�H���g��80

    $request = "{$method} {$send_path} HTTP/1.0\r\n";
    $request .= "Host: {$URL['host']}\r\n";
    $request .= "User-Agent: Monazilla/1.00 ({$_conf['p2ua']})\r\n";
    $request .= "Referer: http://{$URL['host']}/\r\n";

    // �N�b�L�[
    $cookies_to_send = '';
    if ($p2cookies) {
        foreach ($p2cookies as $cname => $cvalue) {
            if ($cname != 'expires') {
                $cookies_to_send .= " {$cname}={$cvalue};";
            }
        }
    }

    // be.2ch.net �F�؃N�b�L�[
    if (P2Util::isHostBe2chNet($host) || !empty($_REQUEST['submit_beres'])) {
        $cookies_to_send .= ' MDMD='.$_conf['be_2ch_code'].';';    // be.2ch.net�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)
        $cookies_to_send .= ' DMDM='.$_conf['be_2ch_mail'].';';    // be.2ch.net�̓o�^���[���A�h���X
    }

    if (!$cookies_to_send) { $cookies_to_send = ' ;'; }
    $request .= 'Cookie:'.$cookies_to_send."\r\n";
    //$request .= 'Cookie: PON='.$SPID.'; NAME='.$FROM.'; MAIL='.$mail."\r\n";

    $request .= "Connection: Close\r\n";

    // {{{ POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t

    if (strcasecmp($method, 'POST') == 0) {
        $post_enc = array();
        while (list($name, $value) = each($post)) {

            // ������� or be.2ch.net�Ȃ�AEUC�ɕϊ�
            if (P2Util::isHostJbbsShitaraba($host) || P2Util::isHostBe2chNet($host)) {
                $value = mb_convert_encoding($value, 'CP51932', 'CP932');
            }

            $post_enc[] = $name . '=' . rawurlencode($value);
        }
        $postdata = implode("&", $post_enc);
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: ".strlen($postdata)."\r\n";
        $request .= "\r\n";
        $request .= $postdata;

    } else {
        $request .= "\r\n";
    }
    // }}}

    // �������݂��ꎞ�I�ɕۑ�
    $failed_post_file = P2Util::getFailedPostFilePath($host, $bbs, $key);
    $cont = serialize($post_cache);
    DataPhp::writeDataPhp($failed_post_file, $cont, $_conf['res_write_perm']);

    // WEB�T�[�o�֐ڑ�
    $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
    if (!$fp) {
        showPostMsg(false, "�T�[�o�ڑ��G���[: $errstr ($errno)<br>p2 Error: �T�[�o�ւ̐ڑ��Ɏ��s���܂���", false);
        return false;
    }

    //echo '<h4>$request</h4><p>' . $request . "</p>"; //for debug
    fputs($fp, $request);

    while (!feof($fp)) {

        if ($start_here) {

            while (!feof($fp)) {
                $wr .= fread($fp, 164000);
            }
            $response = $wr;
            break;

        } else {
            $l = fgets($fp, 164000);
            //echo $l ."<br>"; // for debug
            $response_header_ht .= $l."<br>";
            // �N�b�L�[�L�^
            if (preg_match("/Set-Cookie: (.+?)\r\n/", $l, $matches)) {
                //echo "<p>".$matches[0]."</p>"; //
                $cgroups = explode(";", $matches[1]);
                if ($cgroups) {
                    foreach ($cgroups as $v) {
                        if (preg_match("/(.+)=(.*)/", $v, $m)) {
                            $k = ltrim($m[1]);
                            if ($k != "path") {
                                $p2cookies[$k] = $m[2];
                            }
                        }
                    }
                }
                if ($p2cookies) {
                    unset($cookies_to_send);
                    foreach ($p2cookies as $cname => $cvalue) {
                        if ($cname != "expires") {
                            $cookies_to_send .= " {$cname}={$cvalue};";
                        }
                    }
                    $newcokkies = "Cookie:{$cookies_to_send}\r\n";

                    $request = preg_replace("/Cookie: .*?\r\n/", $newcokkies, $request);
                }

            // �]���͏������ݐ����Ɣ��f
            } elseif (preg_match("/^Location: /", $l, $matches)) {
                $post_seikou = true;
            }
            if ($l == "\r\n") {
                $start_here = true;
            }
        }

    }
    fclose($fp);

    // be.2ch.net or JBBS������� �����R�[�h�ϊ� EUC��SJIS
    if (P2Util::isHostBe2chNet($host) || P2Util::isHostJbbsShitaraba($host)) {
        $response = mb_convert_encoding($response, 'CP932', 'CP51932');

        //<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
        $response = preg_replace("{(<head>.*<META http-equiv=\"Content-Type\" content=\"text/html; charset=)EUC-JP(\">.*</head>)}is", "$1Shift_JIS$2", $response);
    }

    $kakikonda_match = "/<title>.*(�������݂܂���|�� �������݂܂��� ��|�������ݏI�� - SubAll BBS).*<\/title>/is";
    $cookie_kakunin_match = "/<!-- 2ch_X:cookie -->|<title>�� �������݊m�F ��<\/title>|>�������݊m�F�B</";

    if (eregi("(<.+>)", $response, $matches)) {
        $response = $matches[1];
    }

    // �J�L�R�~����
    if (preg_match($kakikonda_match, $response, $matches) or $post_seikou) {
        $reload = empty($_POST['from_read_new']);
        showPostMsg(true, '�������݂��I���܂����B', $reload);

        // ���e���s�L�^���폜
        if (file_exists($failed_post_file)) {
            unlink($failed_post_file);
        }

        return true;
        //$response_ht = htmlspecialchars($response, ENT_QUOTES);
        //echo "<pre>{$response_ht}</pre>";

    // cookie�m�F�ipost�ă`�������W�j
    } elseif (preg_match($cookie_kakunin_match, $response, $matches)) {

        $GLOBALS['_post_form_hidden_values'] = <<<EOFORM
<input type="hidden" name="host" value="{$host}">
<input type="hidden" name="popup" value="{$popup}">
<input type="hidden" name="rescount" value="{$rescount}">
<input type="hidden" name="ttitle_en" value="{$ttitle_en}">
EOFORM;

        foreach ($GLOBALS['post_optional_keys'] as $hk) {
            if (isset($_POST[$hk])) {
                $value_hd = htmlspecialchars($_POST[$hk], ENT_QUOTES);
                $GLOBALS['_post_form_hidden_values'] .= "\n<input type=\"hidden\" name=\"{$hk}\" value=\"{$value_hd}\">";
            }
        }

        $replaced = preg_replace_callback('{<form method="?POST"? action="?\\.\\./test/(sub)?bbs\\.cgi(?:\\?guid=ON)?"?>(.+?)</form>}i', 'replacePostFormCb', $response, -1, $count);

        if ($count != 1) {
            echo '<html><head><title>p2 ERROR</title></head><body>';
            echo '<h1>p2 ERROR</h1><p>�T�[�o����̃��X�|���X���ςł��B</p><pre>';
            echo htmlspecialchars($response, ENT_QUOTES);
            echo '</pre></body></html>';
            return false;
        }

        $h_b = explode('</head>', $replaced, 2);

        // HTML�v�����g
        echo $h_b[0];
        if (!$_conf['ktai']) {
            echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=post&amp;skin={$skin_en}">\n
EOP;
        }
        if ($popup) {
            $mado_okisa = explode(',', $STYLE['post_pop_size']);
            $mado_okisa_x = $mado_okisa[0];
            $mado_okisa_y = $mado_okisa[1] + 200;
            echo <<<EOSCRIPT
            <script type="text/javascript">
            //<![CDATA[
                resizeTo({$mado_okisa_x},{$mado_okisa_y});
            //]]>
            </script>
EOSCRIPT;
        }

        echo "</head>";
        echo $h_b[1];

        return false;

    // ���̑��̓��X�|���X�����̂܂ܕ\��
    } else {
        echo preg_replace('@������Ń����[�h���Ă��������B<a href="\\.\\./[a-z]+/index\\.html"> GO! </a><br>@', '', $response);
        return false;
    }
}

// }}}
// {{{ showPostMsg()

/**
 * �������ݏ������ʕ\������
 *
 * @return void
 */
function showPostMsg($isDone, $result_msg, $reload)
{
    global $_conf, $location_ht, $popup, $ttitle;
    global $STYLE, $skin_en;
    global $_info_msg_ht;

    // �v�����g�p�ϐ� ===============
    if (!$_conf['ktai']) {
        $class_ttitle = ' class="thre_title"';
    }
    $ttitle_ht = "<b{$class_ttitle}>{$ttitle}</b>";
    // 2005/03/01 aki: jig�u���E�U�ɑΉ����邽�߁A&amp; �ł͂Ȃ� & ��
    // 2005/04/25 rsk: <script>�^�O����CDATA�Ƃ��Ĉ����邽�߁A&amp;�ɂ��Ă͂����Ȃ�
    $location_noenc = preg_replace("/&amp;/", "&", $location_ht);
    if ($popup) {
        $popup_ht = <<<EOJS
<script type="text/javascript">
//<![CDATA[
    opener.location.href="{$location_noenc}";
    var delay= 3*1000;
    setTimeout("window.close()", delay);
//]]>
</script>
EOJS;

    } else {
        $_conf['extra_headers_ht'] .= <<<EOP
<meta http-equiv="refresh" content="1;URL={$location_noenc}">
EOP;
    }

    // �v�����g ==============
    echo $_conf['doctype'];
    echo <<<EOHEADER
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
EOHEADER;

    if ($isDone) {
        echo "    <title>p2 - �������݂܂����B</title>";
    } else {
        echo "    <title>{$ptitle}</title>";
    }

    if (!$_conf['ktai']) {
        echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=post&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">\n
EOP;
        if ($popup) {
            echo <<<EOSCRIPT
            <script type="text/javascript">
            //<![CDATA[
                resizeTo({$STYLE['post_pop_size']});
            //]]>
            </script>
EOSCRIPT;
        }
        if ($reload) {
            echo $popup_ht;
        }
    } else {
        $kakunin_ht = <<<EOP
<p><a href="{$location_ht}">�m�F</a></p>
EOP;
    }

    echo "</head>\n";
    echo "<body{$_conf['k_colors']}>\n";

    echo $_info_msg_ht;
    $_info_msg_ht = "";

    echo <<<EOP
<p>{$ttitle_ht}</p>
<p>{$result_msg}</p>
{$kakunin_ht}
</body>
</html>
EOP;
}

// }}}
// {{{ getKeyInSubject()

/**
 *  subject����key���擾����
 *
 * @return string|false
 */
function getKeyInSubject()
{
    global $host, $bbs, $ttitle;

    require_once P2_LIB_DIR . '/SubjectTxt.php';
    $aSubjectTxt = new SubjectTxt($host, $bbs);

    foreach ($aSubjectTxt->subject_lines as $l) {
        if (strpos($l, $ttitle) !== false) {
            if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $l, $matches)) {
                return $key = $matches[1];
            }
        }
    }
    return false;
}

// }}}
// {{{ tab2space()

/**
 * ���`���ێ����Ȃ���A�^�u���X�y�[�X�ɒu��������
 *
 * @return string
 */
function tab2space($in_str, $tabwidth = 4, $crlf = "\n")
{
    $out_str = '';
    $lines = preg_split('/\r\n|\r|\n/', $in_str);
    $ln = count($lines);

    for ($i = 0; $i < $ln; $i++) {
        $parts = explode("\t", rtrim($lines[$i]));
        $pn = count($parts);

        for ($j = 0; $j < $pn; $j++) {
            if ($j == 0) {
                $l = $parts[$j];
            } else {
                //$t = $tabwidth - (strlen($l) % $tabwidth);
                $sn = $tabwidth - (mb_strwidth($l) % $tabwidth); // UTF-8�ł��S�p��������2�ƃJ�E���g����
                for ($k = 0; $k < $sn; $k++) {
                    $l .= ' ';
                }
                $l .= $parts[$j];
            }
        }

        $out_str .= $l;
        if ($i + 1 < $ln) {
            $out_str .= $crlf;
        }
    }

    return $out_str;
}

// }}}
// {{{ replacePostFormCb()

/**
 * COOKIE�̊m�F�t�H�[��������������R�[���o�b�N�֐�
 *
 * @param array $m
 * @return string
 */
function replacePostFormCb($m)
{
    global $_conf, $_post_form_hidden_values;

    return <<<EOFORM
<form method="POST" action="./post.php" accept-charset="{$_conf['accept_charset']}">
{$m[2]}<input type="hidden" name="sub" value="{$m[1]}">
{$_post_form_hidden_values}{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
<input type="hidden" name="p2_post_confirm_cookie" value="1">
</form>
EOFORM;
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
