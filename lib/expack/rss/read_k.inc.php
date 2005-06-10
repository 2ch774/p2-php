<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// {{{ �w�b�_

$ch_title = P2Util::re_htmlspecialchars($channel['title']);

echo <<<EOH
<html lang="ja">
<head>
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<title>{$title}</title>
</head>
<body{$k_color_settings}>
{$_info_msg_ht}
<h1>{$ch_title}</h1>
<hr>
EOH;

// RSS���p�[�X�ł��Ȃ������Ƃ�
if (!$rss_parse_success) {
    echo '</body></html>';
    exit;
}

// }}}
// {{{ �T�v

if (isset($num) && is_numeric($num)) {
    rss_print_content_k($items[$num], $num, count($items));
}

// }}}
// {{{ �t�b�^

echo '</body></html>';

// }}}
// {{{ �\���p�֐�

function rss_print_content_k($item, $num, $count)
{
    global $_conf, $channel, $xml_en;
    $item = array_map('trim', $item);
    // �ϐ��̏�����
    $date_ht = '';
    $subject_ht = '';
    $creator_ht = '';
    $description_ht = '';
    $prev_item_ht = '';
    $next_item_ht = '';
    // �g�s�b�N
    if (isset($item['dc:subject'])) {
        $subject_ht = $item['dc:subject'];
    }
    // ����
    if (isset($item['dc:creator']) && $item['dc:creator'] !== '') {
        $creator_ht = $item['dc:creator'];
    }
    // ����
    if (!empty($item['dc:date'])) {
        $date_ht = rss_format_date($item['dc:date']);
    } elseif (!empty($item['dc:pubdate'])) {
        $date_ht = rss_format_date($item['dc:pubdate']);
    }
    // �T�v
    if (isset($item['content:encoded']) && $item['content:encoded'] !== '') {
        $description_ht = rss_desc_converter($item['content:encoded']);
    } elseif (isset($item['description']) && $item['description'] !== '') {
        $description_ht = rss_desc_converter($item['description']);
    }
    $prev_item_num = $num - 1;
    $next_item_num = $num + 1;
    if ($prev_item_num >= 0) {
        $prev_item_ht = "<a {$_conf['accesskey']}=\"4\" href=\"read_rss.php?xml={$xml_en}&amp;num={$prev_item_num}\">4.�O</a>";
    }
    if ($next_item_num <= $count) {
        $next_item_ht = "<a {$_conf['accesskey']}=\"6\" href=\"read_rss.php?xml={$xml_en}&amp;num={$next_item_num}\">6.��</a>";
    }
    // �\��
    $item_title = P2Util::re_htmlspecialchars($item['title']);
    echo <<<EOP
<h3>{$item_title}</h3>
<p>{$creator_ht}{$date_ht}</p>
<div>
{$description_ht}
</div>
<hr>
<p>{$prev_item_ht} {$next_item_ht}<br>
<a {$_conf['accesskey']}="5" href="subject_rss.php?xml={$xml_en}">5.{$ch_title}</a><br>
<a {$_conf['accesskey']}="9" href="menu_k.php?view=rss">9.RSS</a>
{$_conf['k_to_index_ht']}
</p>\n
EOP;

}

// }}}

?>
