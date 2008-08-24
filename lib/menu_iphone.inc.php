<?php
/**
 * rep2 - iPhone/iPod Touch��p���j���[���C�u����
 */

// {{{ unicode_urldecode

/**
 * �t�H�[������ %uHHHH �`���ő����Ă�����������f�R�[�h����
 * %UHHHHHHHH �Ƃ���BMP�O�̕���������\��������?
 *
 * %HH �͊��Ƀf�R�[�h����Ă�����̂Ƃ��Ĉ������߁A�\�����Ȃ����ʂɂȂ邱�Ƃ��B
 * �����ɂ� $_SERVER['QUERY_STRING'] (GET) �Ȃ� php://input (POST) �Ȃ��ǂ��
 * ���̃f�[�^����͂���K�v������B
 *
 * @param string $str
 */
function unicode_urldecode($str)
{
    return preg_replace_callback('/%u([0-9A-F]{4})/', '_unicode_urldecode', $str);
}

/**
 * unicode_urldecode() ����Ă΂��R�[���o�b�N�֐�
 *
 * @param array $m
 * @return string
 */
function _unicode_urldecode($m)
{
    $code = hexdec($m[1]);

    if (/* Out of Unicode */
        //$code > 0x10FFFF ||
        /* Out of BMP */
        $code > 0xFFFF ||
        /* Surrogate */
        ($code > 0xD7FF && $code < 0xE000) ||
        /* Noncharacter */
        ($code > 0xFDCF && $code < 0xFDF0) || ($code & 0xFFFE) == 0xFFFE ||
        /* Overflow */
        $code < 0 ||
        /* Null byte */
        $code == 0)
    {
        return '';
    }

    return mb_convert_encoding(pack('n', $code), 'CP932', 'UCS-2');
}

// }}}
// {{{ xWrap()

/**
 * XMLHttpRequest�p�̃��b�p�[
 *
 * @param callback $func
 * @param ...
 * @return mixed
 */
function xWrap($func)
{
    if (!headers_sent()) {
        //header('Content-Type: application/xhtml+xml; charset=UTF-8');
        header('Content-Type: application/xml; charset=UTF-8');
        //header('Content-Type: text/plain; charset=UTF-8');
    }
    ob_start();

    $args = func_get_args();
    if (count($args) > 1) {
        array_shift($args);
        $ret = call_user_func_array($func, $args);
    } else {
        $ret = $func();
    }

    echo mb_convert_encoding(ob_get_clean(), 'UTF-8', 'CP932');

    return $ret;
}

// }}}
// {{{ iShowBrdMenu()

/**
 * ���X�g���J�e�S�����Ƃɕ\������
 *
 * @param int $cateid
 * @return void
 */
function iShowBrdMenu($cateid = 0)
{
    global $_conf;

    require_once P2_LIB_DIR . '/brdctl.class.php';

    $brd_menus = BrdCtl::read_brds();

    if (!$brd_menus) {
        echo "<div id=\"cate{$cateid}\" class=\"panel\">���X�g�͋�ł��B</div>\n";
        return;
    }

    // {{{ �J�e�S���ꗗ

    if (!$cateid) {
        echo '<ul id="cate0" title="���X�g">';
        $i = 0;
        $j = 0;
        foreach ($brd_menus as $a_brd_menu) {
            foreach ($a_brd_menu->categories as $category) {
                $i++;
                echo "<li><a href=\"menu_i.php?cateid={$i}\">{$category->name}</a></li>";
            }
            if ($j++ > 0) {
                echo '<li class="group">&nbsp;</li>';
            }
        }
        echo "</ul>\n";
        return;
    }

    // }}}
    // {{{ �J�e�S��

    $i = 0;
    foreach ($brd_menus as $a_brd_menu) {
        foreach ($a_brd_menu->categories as $category) {
            if (++$i == $cateid) {
                echo "<ul id=\"cate{$cateid}\" title=\"{$category->name}\">";
                foreach ($category->menuitas as $mita) {
                    echo "<li><a href=\"{$_conf['subject_php']}?host={$mita->host}&amp;bbs={$mita->bbs}",
                            "&amp;itaj_en={$mita->itaj_en}\" target=\"_self\">{$mita->itaj_ht}</a></li>";
                }
                echo "</ul>\n";
                return;
            }
        }
    }

    // }}}

    echo "<div id=\"cate{$cateid}\" class=\"panel\">�J�e�S����������܂���ł����B</div>\n";
}

// }}}
// {{{ iShowBrdMatched()

/**
 * �L�[���[�h�Ƀ}�b�`�������X�g��\������
 *
 * @param string $word
 * @return void
 */
function iShowBrdMatched($word)
{
    global $_conf;

    require_once P2_LIB_DIR . '/brdctl.class.php';

    $brd_menus = BrdCtl::read_brds();

    $word_ht = htmlspecialchars($word, ENT_QUOTES);
    $title = $word_ht . ' (��)';

    if (!$brd_menus) {
        echo "<div class=\"panel\" title=\"{$title}\">���X�g�͋�ł��B</div>\n";
        return;
    }

    if ($GLOBALS['ita_mikke']['num'] == 0) {
        echo "<div class=\"panel\" title=\"{$title}\">",
                "&quot;{$word_ht}&quot; �Ƀ}�b�`����͂���܂���ł����B</div>\n";
        return;
    }

    echo "<ul title=\"{$title}\">";

    foreach ($brd_menus as $a_brd_menu) {
        foreach ($a_brd_menu->categories as $category) {
            $t = false;
            foreach ($category->menuitas as $mita) {
                if (!$t) {
                    echo "<li class=\"group\">{$category->name}</li>";
                    $t = true;
                }
                echo "<li><a href=\"{$_conf['subject_php']}?host={$mita->host}&amp;bbs={$mita->bbs}",
                        "&amp;itaj_en={$mita->itaj_en}\" target=\"_self\">{$mita->itaj_ht}</a></li>";
                $i++;
            }
        }
    }

    echo "</ul>\n";
}

// }}}
// {{{ iShowFavIta()

/**
 * ���C�ɔ��X�g��\������
 *
 * @param string $title
 * @param int    $no
 * @return void
 */
function iShowFavIta($title, $no = null)
{
    global $_conf;

    echo "<ul id=\"favita{$no}\" title=\"{$title}\">";

    if ($lines = @file($_conf['favita_path'])) {
        foreach ($lines as $l) {
            if (preg_match("/^\t?(.+)\t(.+)\t(.+)$/", rtrim($l), $matches)) {
                $itaj = rtrim($matches[3]);
                $itaj_view = htmlspecialchars($itaj, ENT_QUOTES);
                $itaj_en = rawurlencode(base64_encode($itaj));
                echo "<li><a href=\"{$_conf['subject_php']}?host={$matches[1]}&amp;bbs={$matches[2]}",
                        "&amp;itaj_en={$itaj_en}\" target=\"_self\">{$itaj_view}</a></li>";
            }
        }
    }

    //echo '<li class="group">&nbsp;</li>';
    echo '<li><a href="editfavita.php';
    if ($_conf['expack.misc.multi_favs']) {
        echo '?m_favita_set=' . $no;
    }
    echo '" class="align-r" target="_self">�ҏW</a></li>';

    echo "</ul>\n";
}

// }}}
// {{{ iShowRSS()

/**
 * RSS���X�g��\������
 *
 * @param string $title
 * @param int    $no
 * @return void
 */
function iShowRSS($title, $no = null)
{
    global $_conf;

    require_once P2EX_LIB_DIR . '/rss/common.inc.php';

    echo "<ul id=\"rss{$no}\" title=\"{$title}\">";

    $errors = array();

    if ($rss_list = @file($_conf['expack.rss.setting_path'])) {
        foreach ($rss_list as $rss_info) {
            $rss_info = rtrim($rss_info);
            $p = explode("\t", $rss_info);
            if (count($p) > 1) {
                $site = $p[0];
                $xml  = $p[1];
                if (!empty($p[2])) {
                    $atom = 1;
                    $atom_q = '&atom=1';
                } else {
                    $atom = 0;
                    $atom_q = '';
                }
                $localpath = rss_get_save_path($xml);
                if (PEAR::isError($localpath)) {
                    $errors[] = array($site, $localpath->getMessage());
                } else {
                    $mtime   = file_exists($localpath) ? filemtime($localpath) : 0;
                    $site_en = rawurlencode(base64_encode($site));
                    $xml_en = rawurlencode($xml);
                    $rss_q = sprintf('?xml=%s&site_en=%s%s&mt=%d', $xml_en, $site_en, $atom_q, $mtime);
                    $rss_q_ht = htmlspecialchars($rss_q, ENT_QUOTES);
                    echo "<li><a href=\"subject_rss.php{$rss_q_ht}\" target=\"_self\">{$site}</a></li>";
                }
            }
        }
    }

    if (count($errors)) {
        echo '<li class="group">�G���[</li>';
        foreach ($errors as $error) {
            echo "<li>{$error[0]} - {$error[1]}</li>";
        }
    }

    //echo '<li class="group">&nbsp;</li>';
    echo '<li><a href="editrss.php';
    if ($_conf['expack.misc.multi_favs']) {
        echo '?m_rss_set=' . $no;
    }
    echo '" class="align-r" target="_self">�ҏW</a></li>';

    echo "</ul>\n";
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
