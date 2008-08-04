<?php
// vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
/**
 * rep2 - iPhone��p���j���[ (�viui)
 *
 * @link http://code.google.com/p/iui/
 */

require_once P2_LIB_DIR . '/brdctl.class.php';

// TODO: �����_�����O�ς̔��X�g���L���b�V������
$brd_menus = BrdCtl::read_brds()

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS" />
    <meta name="viewport" content="width=320" content="initial-scale=1.0" />
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
    <title>rep2</title>
    <script type="application/x-javascript" src="iui/iui.js"></script>
    <link rel="stylesheet" type="text/css" href="iui/iui.css" />
</head>
<body>

<div class="toolbar">
    <h1 id="pageTitle"></h1>
    <a id="backButton" class="button" href="#"></a>
    <a class="button" href="#search">����</a>
</div>

<!-- {{{ �g�b�v���j���[ -->
<ul id="top" title="Menu" selected="true">
<?php if ($_info_msg_ht) { ?>
    <li><a href="#info_msg" style="color:red">�G���[</a></li>
<?php } ?>

    <li class="group">���X�g</li>
<?php if ($_conf['expack.misc.multi_favs']) { ?>
    <li><a href="#fav">���C�ɃX��</a></li>
    <li><a href="#favita">���C�ɔ�</a></li>
<?php } else { ?>
    <li><a href="subject.php?spmode=fav&amp;sb_view=shinchaku" target="_self">���C�ɃX���̐V��</a></li>
    <li><a href="subject.php?spmode=fav" target="_self">���C�ɃX��</a></li>
    <li><a href="menu_k.php?view=favita" target="_self">���C�ɔ�</a></li>
<?php } ?>
    <li><a href="#cate">���X�g</a></li>
    <li><a href="subject.php?spmode=palace&amp;norefresh=1" target="_self">�X���̓a��</a></li>

    <li class="group">����</li>
    <li><a href="subject.php?spmode=recent&amp;sb_view=shinchaku" target="_self">�ŋߓǂ񂾃X���̐V��</a></li>
    <li><a href="subject.php?spmode=recent" target="_self">�ŋߓǂ񂾃X��</a></li>
    <li><a href="subject.php?spmode=res_hist" target="_self">�������ݗ���</a></li>
    <li><a href="read_res_hist.php" target="_self">�������ݗ����̓��e</a></li>

    <li class="group">expack</li>
<?php if ($_conf['expack.rss.enabled']) { if ($_conf['expack.misc.multi_favs']) { ?>
    <li><a href="#rss">RSS</a></li>
<?php } else { ?>
    <li><a href="menu_k.php?view=rss" target="_self">RSS</a></li>
<?php } } ?>
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

// {{{ ���C�ɃZ�b�g
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
    echo '</ul>';

    // }}}
    // {{{ ���C�ɔ�

    $favita = FavSetManager::getFavSetTitles('m_favita_set');

    echo '<ul id="favita" title="���C�ɔ�">';

    foreach ($favita as $no => $name) {
        echo "<li><a href=\"menu_k.php?view=favita&amp;m_favita_set={$no}\" target=\"_self\">{$name}</a></li>";
    }

    echo '</ul>';

    // }}}
    // {{{ RSS

    if ($_conf['expack.rss.enabled']) { 
        $rss = FavSetManager::getFavSetTitles('m_rss_set');

        echo '<ul id="rss" title="RSS">';

        foreach ($favita as $no => $name) {
            echo "<li><a href=\"menu_k.php?view=rss&amp;m_rss_set={$no}\" target=\"_self\">{$name}</a></li>";
        }

        echo '</ul>';
    }

    // }}}
}
// }}}
?>

<!-- {{{ ���X�g (�J�e�S���ꗗ) -->
<ul id="cate" title="���X�g"><?php
if ($brd_menus) {
    $cate_id = 0;
    foreach ($brd_menus as $a_brd_menu) {
        foreach ($a_brd_menu->categories as $category) {
            $cate_id++;
            echo "<li><a href=\"#cate{$cate_id}\">{$category->name}</a></li>";
        }
    }
}
?></ul>
<!-- }}} -->

<!-- {{{ ���X�g (�J�e�S����) -->
<?php
if ($brd_menus) {
    $cate_id = 0;
    foreach ($brd_menus as $a_brd_menu) {
        foreach ($a_brd_menu->categories as $category) {
            $cate_id++;

            echo "<ul id=\"cate{$cate_id}\" title=\"{$category->name}\">";

            foreach ($category->menuitas as $mita) {
                echo "<li><a href=\"{$_conf['subject_php']}?host={$mita->host}&amp;bbs={$mita->bbs}",
                        "&amp;itaj_en={$mita->itaj_en}\" target=\"_self\">{$mita->itaj_ht}</a></li>";
            }

            echo '</ul>';
        }
    }
}
?>
<!-- }}} -->

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

<!-- {{{ �����pJavaScript -->
<script type="application/x-javascript">
function setSearchTarget(is_board)
{
    var f = document.getElementById('search');
    var k = document.getElementById('keyword');
    if (is_board == 'true') {
        f.setAttribute('action', 'menu_k.php');
        k.setAttribute('name', 'word');
    } else {
        f.setAttribute('action', 'tgrepc.php');
        k.setAttribute('name', 'Q');
    }
}
</script>
<!-- }}} -->

<!-- {{{ �����p�l�� -->
<form id="search" class="panel" title="����"
  method="get" action="tgrepc.php" target="_self"
  accept-charset="<?php echo $_conf['accept_charset']; ?>">
<fieldset>
    <div class="row">
        <label>���[�h</label>
        <div class="toggle" onclick="setSearchTarget(this.getAttribute('toggled'))">
            <span class="thumb"></span>
            <span class="toggleOn">��</span>
            <span class="toggleOff">�X��</span>
        </div>
    </div>
    <div class="row">
        <label>�L�[���[�h</label>
        <input type="text" id="keyword" name="Q" value="" />
    </div>
    <div class="row">
        <input type="submit" value="OK" />
    </div>
</fieldset>
</form>
<!-- }}} -->

</body>
</html>
