<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// {{{ �w�b�_

echo <<<EOH
<html lang="ja">
<head>
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<title>{$title}</title>
</head>
<body{$k_color_settings}>
{$_info_msg_ht}
<p><b>{$title}</b></p>
<hr>\n
EOH;

// RSS���p�[�X�ł��Ȃ������Ƃ�
if (!$rss_parse_success) {
    echo '</body></html>';
    exit;
}

// }}}
// {{{ �\���p�ϐ�

if ($atom) {
    $atom_q = '&amp;atom=1';
    $atom_ht = '<input type="hidden" name="atom" value="1">';
    $atom_chk = ' chedked';
} else {
    $atom_q = '';
    $atom_ht = '';
    $atom_chk = '';
}
if ($mtime) {
    $mtime_q = '&amp;mt=' . $mtime;
} else {
    $mtime_q = '';
}

// }}}
// {{{ ���o��

reset($items);
$i = 0;
echo "<ol>\n";
foreach ($items as $item) {
    $item = array_map('trim', $item);
    $item_title = P2Util::re_htmlspecialchars($item['title']);
    if ((isset($item['content:encoded']) && $item['content:encoded'] !== '') ||
        (isset($item['description']) && $item['description'] !== '')
    ) {
        echo "<li><a href=\"read_rss.php?xml={$xml_en}&amp;title_en={$title_en}&amp;num={$i}{$atom_q}{$mtime_q}\">{$item_title}</a></li>\n";
    } else {
        echo "<li>{$item_title}</li>\n";
    }
    $i++;
}
echo "</ol>\n";

// }}}
// {{{ �t�b�^

echo <<<EOF
<hr>
<p>
<a {$_conf['accesskey']}="9" href="menu_k.php?view=rss">9.RSS</a>
{$_conf['k_to_index_ht']}
</p>
<hr>
<form id="urlform" method="post" action="{$_SERVER['PHP_SELF']}" target="_self">
RSS/Atom�𒼐ڎw��<br>
<input type="hidden" name ="k" value="1">
<input type="text" name="xml" value="{$xml_ht}"><br>
<input type="submit" name="btnG" value="�\��">
(<input type="checkbox" name="atom" value="1"{$atom_chk}>Atom)
</form>
</body>
</html>
EOF;

// }}}

?>
