<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 -  �g�їp�C���f�b�N�X�v�����g�֐�
*/

require_once (P2_LIBRARY_DIR . '/p2util.class.php');    // p2�p�̃��[�e�B���e�B�N���X

function index_print_k()
{
    global $_conf, $login;
    global $_info_msg_ht;
    global $_exconf, $k_color_settings;

    $p_htm = array();

    $newtime = date('gis');

    $body = '';
    $autho_user_ht = '';
    $ptitle = '��޷��p2';

    // �F��հ�ޏ��
    $autho_user_ht = '';
    if ($login['use']) {
        $autho_user_ht = "<p>۸޲�հ��: {$login['user']} - ".date('Y/m/d (D) G:i:s')."</p>\n";
    }
    $user_at_a = '';

    // �O��̃��O�C�����
    if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
        if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
            $log_hd = array_map('htmlspecialchars', $log);
            $p_htm['last_login'] =<<<EOP
�O���۸޲ݏ�� - {$log_hd['date']}<br>
հ��: {$log_hd['user']}<br>
IP: {$log_hd['ip']}<br>
HOST: {$log_hd['host']}<br>
UA: {$log_hd['ua']}<br>
REFERER: {$log_hd['referer']}
EOP;
        }
    }

    if ($_exconf['etc']['multi_favs']) {
        $m_favlist_set_a = '&amp;m_favlist_set=' . ((isset($_SESSION['m_favlist_set'])) ? $_SESSION['m_favlist_set'] : 0);
        $m_favita_set_a = '&amp;m_favita_set=' . ((isset($_SESSION['m_favita_set'])) ? $_SESSION['m_favita_set'] : 0);
        $m_rss_set_a = '&amp;m_rss_set=' . ((isset($_SESSION['m_rss_set'])) ? $_SESSION['m_rss_set'] : 0);
    } else {
        $m_favlist_set_a = '';
        $m_favita_set_a = '';
        $m_rss_set_a = '';
    }

    if ($_exconf['rss']['*']) {
        $rss_k_ht = "#.<a {$_conf['accesskey']}=\"#\" href=\"menu_k.php?view=rss{$m_rss_set_a}\">RSS</a><br>";
    }

    //=========================================================
    // �g�їp HTML �v�����g
    //=========================================================
    P2Util::header_content_type();
    if ($_conf['doctype']) {
        echo $_conf['doctype'];
    }
    echo <<<EOP
<html>
<head>
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<title>{$ptitle}</title>
</head>
<body{$k_color_settings}>
<h1>{$ptitle}</h1>
{$_info_msg_ht}
1.<a {$_conf['accesskey']}="1" href="subject.php?spmode=fav&amp;sb_view=shinchaku{$m_favlist_set_a}">���C�ɽڂ̐V��</a><br>
2.<a {$_conf['accesskey']}="2" href="subject.php?spmode=fav{$m_favlist_set_a}">���C�ɽڂ̑S��</a><br>
3.<a {$_conf['accesskey']}="3" href="menu_k.php?view=favita{$m_favita_set_a}">���C�ɔ�</a><br>
4.<a {$_conf['accesskey']}="4" href="menu_k.php?view=cate">��ؽ�</a><br>
5.<a {$_conf['accesskey']}="5" href="subject.php?spmode=recent&amp;sb_view=shinchaku">�ŋߓǂ񂾽ڂ̐V��</a><br>
6.<a {$_conf['accesskey']}="6" href="subject.php?spmode=recent">�ŋߓǂ񂾽ڂ̑S��</a><br>
7.<a {$_conf['accesskey']}="7" href="subject.php?spmode=res_hist">�������ݗ���</a> <a href="read_res_hist.php?nt={$newtime}#footer">*</a><br>
8.<a {$_conf['accesskey']}="8" href="subject.php?spmode=palace&amp;norefresh=true">�ڂ̓a��</a><br>
9.<a {$_conf['accesskey']}="9" href="setting.php?dummy=1{$user_at_a}">۸޲݊Ǘ�</a><br>
0.<a {$_conf['accesskey']}="0" href="editpref.php?dummy=1{$user_at_a}">�ݒ�Ǘ�</a><br>
{$rss_k_ht}
*.<a {$_conf['accesskey']}="*" href="subject.php?spmode=news">ƭ������</a><br>
?.<a href="tgrepc.php">��������</a>
</ol>
<hr>
{$autho_user_ht}
{$p_htm['last_login']}
</body>
</html>
EOP;

}
?>
