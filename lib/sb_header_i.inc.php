<?php
/**
 * rep2 - �T�u�W�F�N�g - iPhone�w�b�_�\��
 * for subject.php
 */

//===============================================================
// HTML�\���p�ϐ�
//===============================================================
$newtime = date('gis');
$norefresh_q = '&amp;norefresh=1';

// {{{ �y�[�W�^�C�g������URL�ݒ�

$p2_subject_url = "{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$_conf['k_at_a']}";

// �ʏ� ��
if (!$aThreadList->spmode) {
    // �����ꂠ��
    if ((isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) || !empty($GLOBALS['wakati_words'])) {
        $ptitle_url = $p2_subject_url;

    // ���̑�
    } else {
        $ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/";
        // ���ʂȃp�^�[�� index2.html
        // match�o�^���head�Ȃ��ĕ������ق����悳���������A�������X�|���X������̂�����
        if (!strcasecmp($aThreadList->host, 'livesoccer.net')) {
            $ptitle_url .= 'index2.html';
        }
    }

// ���ځ[�� or �q��
} elseif ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
    $ptitle_url = $p2_subject_url;

// �������ݗ���
} elseif ($aThreadList->spmode == 'res_hist') {
    $ptitle_url = "./read_res_hist.php{$_conf['k_at_q']}#footer";
}

// }}}
// {{{ �y�[�W�^�C�g������HTML�ݒ�

if ($aThreadList->spmode == 'fav' && $_conf['expack.misc.multi_favs']) {
    $ptitle_hd = FavSetManager::getFavSetPageTitleHt('m_favlist_set', $aThreadList->ptitle);
} else {
    $ptitle_hd = htmlspecialchars($aThreadList->ptitle, ENT_QUOTES);
}

if ($aThreadList->spmode == 'taborn') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\"><b>{$aThreadList->itaj_hd}</b></a> (���ځ[��)";
} elseif ($aThreadList->spmode == 'soko') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\"><b>{$aThreadList->itaj_hd}</b></a> (dat�q��)";
} elseif (!empty($ptitle_url)) {
    $ptitle_ht = "<a href=\"{$ptitle_url}\"><b>{$ptitle_hd}</b></a>";
} else {
    $ptitle_ht = "<b>{$ptitle_hd}</b>";
}

// }}}
// �t�H�[�� ==================================================
$sb_form_hidden_ht = <<<EOP
<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
<input type="hidden" name="host" value="{$aThreadList->host}">
<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}{$_conf['m_favita_set_input_ht']}
EOP;

// �t�B���^���� ==================================================

$hd['word'] = htmlspecialchars($word, ENT_QUOTES);

// iPhone�p�w�b�_�v�f
$_conf['extra_headers_ht'] .= <<<EOS
<link rel="stylesheet" type="text/css" href="iui/toggle-only.css?{$_conf['p2_version_id']}">
<script type="text/javascript" src="js/json2.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/sb_iphone.js?{$_conf['p2_version_id']}"></script>
EOS;
// �X���̐������������߂̃X�^�C���V�[�g
if ($_conf['iphone.subject.indicate-speed']) {
    $_conf['extra_headers_ht'] .= <<<EOS
<style type="text/css">
/* <![CDATA[ */
ul.subject > li > a { border-left: transparent solid {$_conf['iphone.subject.speed.width']}px; }
ul.subject > li > a.dayres-0 { border-left-color: {$_conf['iphone.subject.speed.0rpd']}; }
ul.subject > li > a.dayres-1 { border-left-color: {$_conf['iphone.subject.speed.1rpd']}; }
ul.subject > li > a.dayres-10 { border-left-color: {$_conf['iphone.subject.speed.10rpd']}; }
ul.subject > li > a.dayres-100 { border-left-color: {$_conf['iphone.subject.speed.100rpd']}; }
ul.subject > li > a.dayres-1000 { border-left-color: {$_conf['iphone.subject.speed.1000rpd']}; }
ul.subject > li > a.dayres-10000 { border-left-color: {$_conf['iphone.subject.speed.10000rpd']}; }
/* ]]> */
</style>
EOS;
}

// �X�����
if (!$spmode) {
    if (!function_exists('get_board_info')) {
        include P2_LIB_DIR . '/get_info.inc.php';
    }
    $board_info = get_board_info($aThreadList->host, $aThreadList->bbs);
} else {
    $board_info = null;
}

//=================================================
//�w�b�_�v�����g
//=================================================
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
{$_conf['extra_headers_ht']}
<title>{$ptitle_hd}</title>
</head>
<body class="nopad">
<div class="ntoolbar" id="header">
<h1 class="ptitle">{$ptitle_ht}</h1>
EOP;

// {{{ �e��{�^����

echo '<table><tbody><tr>';

// �V���܂Ƃߓǂ�
$shinchaku_norefresh_ht = '';
echo '<td>';
if ($aThreadList->spmode != 'soko') {
    $shinchaku_matome_url = "{$_conf['read_new_k_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}&amp;nt={$newtime}{$_conf['k_at_a']}";

    if ($aThreadList->spmode == 'merge_favita') {
        $shinchaku_matome_url .= $_conf['m_favita_set_at_a'];
    }

    if ($shinchaku_attayo) {
        $shinchaku_norefresh_ht = '<input type="hidden" name="norefresh" value="1">';
        echo toolbar_i_badged_button('img/glyphish/icons2/104-index-cards.png', '�V�܂Ƃ�',
                                      $shinchaku_matome_url . $norefresh_q, $shinchaku_num);
    } else {
        echo toolbar_i_standard_button('img/glyphish/icons2/104-index-cards.png', '�V�܂Ƃ�', $shinchaku_matome_url);
    }
} else {
    echo toolbar_i_disabled_button('img/glyphish/icons2/104-index-cards.png', '�V�܂Ƃ�');
}
echo '</td>';

// �X������
echo '<td>';
if (!$spmode_without_palace_or_favita) {
    echo toolbar_i_showhide_button('img/glyphish/icons2/06-magnifying-glass.png', '�X������', 'sb_toolbar_filter');
} else {
    echo toolbar_i_disabled_button('img/glyphish/icons2/06-magnifying-glass.png', '�X������');
}
echo '</td>';

// ���C�ɔ�
echo '<td>';
if ($board_info) {
    echo toolbar_i_favita_button('img/glyphish/icons2/28-star.png', '���C�ɔ�', $board_info);
} else {
    echo toolbar_i_disabled_button('img/glyphish/icons2/28-star.png', '���C�ɔ�');
}
echo '</td>';

// ���̑�
echo '<td>';
echo toolbar_i_showhide_button('img/gp0-more.png', '���̑�', 'sb_toolbar_extra');
echo '</td>';

// ����
echo '<td>', toolbar_i_standard_button('img/gp2-down.png', '��', '#footer'), '</td>';

echo '</tr></tbody></table>';

// }}}
// {{{ �X�������t�H�[��

if (!$spmode_without_palace_or_favita) {
    if (array_key_exists('method', $sb_filter) && $sb_filter['method'] == 'or') {
        $hd['method_checked_at'] = ' checked';
    } else {
        $hd['method_checked_at'] = '';
    }

    echo <<<EOP
<div id="sb_toolbar_filter" class="extra">
<form id="sb_filter" method="get" action="{$_conf['subject_php']}" accept-charset="{$_conf['accept_charset']}">
{$sb_form_hidden_ht}<input type="text" id="sb_filter_word" name="word" value="{$hd['word']}" size="15" autocorrect="off" autocapitalize="off">
<input type="checkbox" id="sb_filter_method" name="method" value="or"{$hd['method_checked_at']}><label for="sb_filter_method">OR</label>
<input type="submit" name="submit_kensaku" value="����">
</form>
</div>
EOP;
}


// }}}
// {{{ ���̑��̃c�[��

echo '<div id="sb_toolbar_extra" class="extra">';

if ($board_info && $_conf['expack.misc.multi_favs']) {
    echo '<table><tbody><tr>';
    for ($i = 1; $i <= $_conf['expack.misc.favset_num']; $i++) {
        echo '<td>';
        echo toolbar_i_favita_button('img/glyphish/icons2/28-star.png', '-', $board_info, $i);
        echo '</td>';
        if ($i % 5 === 0 && $i != $_conf['expack.misc.favset_num']) {
            echo '</tr><tr>';
        }
    }
    $mod_cells = $_conf['expack.misc.favset_num'] % 5;
    if ($mod_cells) {
        $mod_cells = 5 - $mod_cells;
        for ($i = 0; $i < $mod_cells; $i++) {
            echo '<td>&nbsp;</td>';
        }
    }
    echo '</tr></tbody></table>';
}

echo <<<EOP
<form method="get" action="{$_conf['read_new_k_php']}">
{$sb_form_hidden_ht}<input type="hidden" name="nt" value="1">{$shinchaku_norefresh_ht}
���ǐ���<input type="text" name="unum_limit" value="100" size="4" autocorrect="off" autocapitalize="off" placeholder="#">������
<input type="submit" value="�V�܂Ƃ�">
</form>
EOP;

echo '</div>';

// }}}
// {{{ �e��ʒm

$info_ht = P2Util::getInfoHtml();
if (strlen($info_ht)) {
    echo "<div class=\"info\">{$info_ht}</div>";
}

if ($GLOBALS['sb_mikke_num']) {
    echo "<div class=\"hits\">&quot;{$hd['word']}&quot; {$GLOBALS['sb_mikke_num']}hit!</div>";
}

// }}}

echo '</div>';

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
