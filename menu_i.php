<?php
/**
 * rep2 - iPhone/iPod Touch��p���j���[ (�viui)
 *
 * @link http://code.google.com/p/iui/
 */

include_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/menu_iphone.inc.php';

$_login->authorize(); //���[�U�F��

if (isset($_GET['cateid'])) {
    xWrap('iShowBrdMenu', (int)$_GET['cateid']);
    exit;
}

if (isset($_POST['word'])) {
    $word = unicode_urldecode($_POST['word']);
    if (preg_match('/^\.+$/', $word)) {
        $word = '';
    }

    if (strlen($word) > 0) {
        // and����
        include_once P2_LIB_DIR . '/strctl.class.php';
        $word = StrCtl::wordForMatch($word, 'and');
        if (P2_MBREGEX_AVAILABLE == 1) {
            $GLOBALS['words_fm'] = @mb_split('\s+', $word);
            $GLOBALS['word_fm'] = @mb_ereg_replace('\s+', '|', $word);
        } else {
            $GLOBALS['words_fm'] = @preg_split('/\s+/', $word);
            $GLOBALS['word_fm'] = @preg_replace('/\s+/', '|', $word);
        }

        xWrap('iShowBrdMatched', $word);
    } else {
        header('Content-Type: application/xml; charset=UTF-8');
        echo mb_convert_encoding('<div class="panel">�����ȃL�[���[�h�ł��B</div>', 'UTF-8', 'CP932');
    }
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=Shift_JIS" />
    <meta name="viewport" content="width=<?php echo $_conf['viewport_width']; ?>, initial-scale=1.0" />
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
    <title>rep2</title>
    <script type="application/x-javascript" src="iui/iui.js"></script>
    <link rel="stylesheet" type="text/css" href="iui/iui.css" />
    <link rel="stylesheet" type="text/css" href="css/menu_i.css" />
</head>
<body>

<div class="toolbar">
    <h1 id="pageTitle"></h1>
    <a id="backButton" class="button" style="z-index:7" href="#"></a>
    <a class="button leftButton" href="#boardSearch">��</a>
    <a class="button" href="#threadSearch">��</a>
</div>

<!-- {{{ �g�b�v���j���[ -->
<ul id="top" title="rep2" selected="true">
<?php if ($_info_msg_ht) { ?>
    <li><a href="#info_msg" style="color:red">�G���[</a></li>
<?php } ?>

    <li class="group">���X�g</li>
<?php if ($_conf['expack.misc.multi_favs']) { ?>
    <li><a href="#fav">���C�ɃX��</a></li>
<?php } else { ?>
    <li><a href="subject.php?spmode=fav&amp;sb_view=shinchaku" target="_self">���C�ɃX���̐V��</a></li>
    <li><a href="subject.php?spmode=fav" target="_self">���C�ɃX��</a></li>
<?php } ?>
    <li><a href="#favita">���C�ɔ�</a></li>
    <li><a href="menu_i.php?cateid=0">���X�g</a></li>
    <li><a href="subject.php?spmode=palace&amp;norefresh=1" target="_self">�X���̓a��</a></li>

    <li class="group">����</li>
    <li><a href="subject.php?spmode=recent&amp;sb_view=shinchaku" target="_self">�ŋߓǂ񂾃X���̐V��</a></li>
    <li><a href="subject.php?spmode=recent" target="_self">�ŋߓǂ񂾃X��</a></li>
    <li><a href="subject.php?spmode=res_hist" target="_self">�������ݗ���</a></li>
    <li><a href="read_res_hist.php" target="_self">�������ݗ����̓��e</a></li>

    <li class="group">expack</li>
<?php if ($_conf['expack.rss.enabled']) { ?>
    <li><a href="#rss">RSS</a></li>
<?php } ?>
    <li><a href="tgrepc.php" target="_self">�X���b�h�^�C�g������</a></li>
<?php if ($_conf['expack.ic2.enabled'] == 2 || $_conf['expack.ic2.enabled'] == 3) { ?>
    <li><a href="iv2.php" target="_self">�摜�L���b�V���ꗗ</a></li>
<?php } ?>

    <li class="group">�Ǘ�</li>
    <li><a href="editpref.php" target="_self">�ݒ�Ǘ�</a></li>
    <li><a href="setting.php" target="_self">���O�C���Ǘ�</a></li>
    <li><a href="#login_info">���O�C�����</a></li>
</ul>
<!-- }}} -->

<?php
// �G���[
if ($_info_msg_ht) { 
    echo '<div id="info_msg" class="panel" title="�G���[">', $_info_msg_ht, '</div>';
}

if ($_conf['expack.misc.multi_favs']) {
    // {{{ ���C�ɃX��

    $favlist = FavSetManager::getFavSetTitles('m_favlist_set');
    $fav_elems = '';
    $fav_new_elems = '';
    $fav_elem_prefix = '';

    foreach ($favlist as $no => $name) {
        $fav_url = "subject.php?spmode=fav&amp;m_favlist_set={$no}";
        $fav_elems .= "<li><a href=\"{$fav_url}\" target=\"_self\">{$name}</a></li>";
        $fav_new_elems .= "<li><a href=\"{$fav_url}&amp;sb_view=shinchaku\" target=\"_self\">{$name}</a></li>";
    }

    echo '<ul id="fav" title="���C�ɃX��">';
    echo '<li class="group">�V��</li>';
    echo $fav_new_elems;
    echo '<li class="group">�S��</li>';
    echo $fav_elems;
    echo "</ul>\n";

    // }}}
    // {{{ ���C�ɔ�

    $favita = FavSetManager::getFavSetTitles('m_favita_set');

    echo '<ul id="favita" title="���C�ɔ�">';

    foreach ($favita as $no => $name) {
        echo "<li><a href=\"#favita{$no}\">{$name}</a></li>";
    }

    echo "</ul>\n";

    $orig_favita_path = $_conf['favita_path'];

    foreach ($favita as $no => $name) {
        $_conf['favita_path'] = $_conf['pref_dir'] . '/'
            . ($no ? "p2_favita{$no}.brd" : 'p2_favita.brd');
        iShowFavIta($name, $no);
    }

    $_conf['favita_path'] = $orig_favita_path;

    // }}}
    // {{{ RSS

    if ($_conf['expack.rss.enabled']) { 
        $rss = FavSetManager::getFavSetTitles('m_rss_set');

        echo '<ul id="rss" title="RSS">';

        foreach ($rss as $no => $name) {
            echo "<li><a href=\"#rss{$no}\">{$name}</a></li>";
        }

        echo "</ul>\n";

        $orig_rss_setting_path = $_conf['expack.rss.setting_path'];

        foreach ($rss as $no => $name) {
            $_conf['expack.rss.setting_path'] = $_conf['pref_dir'] . '/'
                    . ($no ? "p2_rss{$no}.txt" : 'p2_rss.txt');
            iShowRSS($name, $no);
        }

        $_conf['expack.rss.setting_pat'] = $orig_rss_setting_path;
    }

    // }}}
} else {
    iShowFavIta('���C�ɔ�');

    if ($_conf['expack.rss.enabled']) { 
        iShowRSS('RSS');
    }
}
?>

<!-- {{{ ���O�C����� -->
<div id="login_info" class="panel" title="���O�C�����">
<h2>�F�؃��[�U</h2>
<p><strong><?php echo $_login->user; ?></strong> - <?php echo date('Y/m/d (D) G:i:s'); ?></p>
<?php if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) { ?>
<h2>�O��̃��O�C��</h2>
<pre style="word-wrap:break-word;word-break:break-all"><?php
if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
    $log_hd = array_map('htmlspecialchars', $log);
    echo <<<EOP
<strong>DATE:</strong> {$log_hd['date']}
<strong>USER:</strong> {$log_hd['user']}
<strong>  IP:</strong> {$log_hd['ip']}
<strong>HOST:</strong> {$log_hd['host']}
<strong>  UA:</strong> {$log_hd['ua']}
<strong>REFERER:</strong> {$log_hd['referer']}
EOP;
}
?></pre>
<?php } ?>
</div>
<!-- }}} -->

<!-- {{{ �����_�C�A���O -->
<form id="boardSearch" class="dialog"
  method="post" action="menu_i.php"
  accept-charset="<?php echo $_conf['accept_charset']; ?>">
<fieldset>
    <h1>����</h1>
    <a class="button leftButton" type="cancel">Cancel</a>
    <a class="button blueButton" type="submit">Search</a>
    <label>word:</label>
    <input type="text" name="word" />
</fieldset>
</form>
<!-- }}} -->

<!-- {{{ �X���b�h�^�C�g�������_�C�A���O -->
<form id="threadSearch" class="dialog"
  method="post" action="tgrepc.php"
  accept-charset="<?php echo $_conf['accept_charset']; ?>">
<fieldset>
    <h1>�X���b�h����</h1>
    <a class="button leftButton" type="cancel">Cancel</a>
    <a class="button blueButton" type="submit">Search</a>
    <label>word:</label>
    <input type="text" name="iq" />
</fieldset>
</form>
<!-- }}} -->

</body>
</html>
<?php
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
