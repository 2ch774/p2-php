<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 -  �T�u�W�F�N�g - �g�уt�b�^�\��
    for subject.php
*/

//=================================================
//�t�b�^�v�����g
//=================================================
$mae_ht = '';

$host_bbs_q = 'host=' . $aThreadList->host . '&amp;bbs=' . $aThreadList->bbs;

if ($word) {
    $word_at = '&amp;word='.$word;
} else {
    $word_at = '';
}

if ($aThreadList->spmode == 'fav' && $sb_view == 'shinchaku') {
    $allfav_ht = "<p><a href=\"{$_conf['subject_php']}?spmode=fav{$norefresh_q}\">�S�Ă̂��C�ɽڂ�\��</a></p>";
}

// �y�[�W�^�C�g������HTML�ݒ� ====================================
if ($aThreadList->spmode == 'taborn') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\" {$_conf['accesskey']}=\"{$_conf['k_accesskey']['above']}\">{$_conf['k_accesskey']['above']}.<b>{$aThreadList->itaj_hd}</b></a>�i���ݒ��j";
} elseif ($aThreadList->spmode == 'soko') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\" {$_conf['accesskey']}=\"{$_conf['k_accesskey']['above']}\">{$_conf['k_accesskey']['above']}.<b>{$aThreadList->itaj_hd}</b></a>�idat�q�Ɂj";
} elseif ($ptitle_url) {
    $ptitle_ht = "<a href=\"{$ptitle_url}\"><b>{$ptitle_hd}</b></a>";
} else {
    $ptitle_ht = "<b>{$ptitle_hd}</b>";
}

// �i�r ===============================
if ($disp_navi['from'] > 1) {
    $mae_ht = "<a href=\"{$_conf['subject_php']}?{$host_bbs_q}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;from={$disp_navi['mae_from']}{$word_at}\" {$_conf['accesskey']}=\"{$_conf['k_accesskey']['prev']}\">{$_conf['k_accesskey']['prev']}.�O</a>";
}

if ($disp_navi['tugi_from'] < $sb_disp_all_num) {
    $tugi_ht = "<a href=\"{$_conf['subject_php']}?{$host_bbs_q}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;from={$disp_navi['tugi_from']}{$word_at}\" {$_conf['accesskey']}=\"{$_conf['k_accesskey']['next']}\">{$_conf['k_accesskey']['next']}.��</a>";
}

if ($disp_navi['from'] == $disp_navi['end']) {
    $sb_range_on = $disp_navi['from'];
} else {
    $sb_range_on = $disp_navi['from'].'-'.$disp_navi['end'];
}
$sb_range_st = $sb_range_on.'/'.$sb_disp_all_num;

if (!$disp_navi['all_once']) {
    $k_sb_navi_ht = "<p>{$sb_range_st} {$mae_ht} {$tugi_ht}</p>";
}

// {{{ dat�q��
// �X�y�V�������[�h�łȂ���΁A�܂��͂��ځ[�񃊃X�g�Ȃ�
if (!$aThreadList->spmode or $aThreadList->spmode == 'taborn') {
    $dat_soko_ht = "<a href=\"{$_conf['subject_php']}?{$host_bbs_q}{$norefresh_q}&amp;spmode=soko\">dat�q��</a>\n";
}
// }}}

// {{{ ���ځ[�񒆂̃X���b�h
if ($ta_num) {
    $taborn_link_ht = "<a href=\"{$_conf['subject_php']}?{$host_bbs_q}{$norefresh_q}&amp;spmode=taborn\">���ݒ�({$ta_num})</a>\n";
}
// }}}

// {{{ �V�K�X���b�h�쐬
if (!$aThreadList->spmode) {
    $buildnewthread_ht = "<a href=\"post_form.php?{$host_bbs_q}&amp;newthread=1\">�ڗ���</a>\n";
}
// }}}

// {{{ ���C�ɃX���Z�b�g�ؑ�
if ($aThreadList->spmode == 'fav' && $_exconf['etc']['multi_favs']) {
    $switchfavlist_ht = '<div>' . FavSetManager::makeFavSetSwitchForm('m_favlist_set', '���C�ɃX��', NULL, NULL, FALSE, array('spmode' => 'fav')) . '</div>';
}

// }}}
// {{{ �\�[�g�ύX �i�V�� ���X No. �^�C�g�� �� ���΂₳ ���� Birthday ���j

$sorts = array('midoku' => '�V��', 'res' => 'ڽ', 'no' => 'No.', 'title' => '����');
if ($aThreadList->spmode and $aThreadList->spmode != 'taborn' and $aThreadList->spmode != 'soko') { $sorts['ita'] = '��'; }
if ($_conf['sb_show_spd']) { $sorts['spd'] = '���΂₳'; }
if ($_conf['sb_show_ikioi']) { $sorts['ikioi'] = '����'; }
$sorts['bd'] = 'Birthday';
if ($_conf['sb_show_fav'] and $aThreadList->spmode != 'taborn') { $sorts['fav'] = '��'; }

$htm['change_sort'] = "<form method=\"get\" action=\"{$_conf['subject_php']}\">";
$htm['change_sort'] .= '<input type="hidden" name="norefresh" value="1">';
// spmode��
if ($aThreadList->spmode) {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"spmode\" value=\"{$aThreadList->spmode}\">";
}
// spmode�łȂ��A�܂��́Aspmode�����ځ[�� or dat�q�ɂȂ�
if (!$aThreadList->spmode || $aThreadList->spmode == "taborn" || $aThreadList->spmode == "soko") {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"host\" value=\"{$aThreadList->host}\">";
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"bbs\" value=\"{$aThreadList->bbs}\">";
}
$htm['change_sort'] .= '���:<select name="sort">';
foreach ($sorts as $k => $v) {
    if ($now_sort == $k) {
        $htm['change_sort'] .= "<option value=\"{$k}\" selected>{$v}</option>";
    } else {
        $htm['change_sort'] .= "<option value=\"{$k}\">{$v}</option>";
    }
}
$htm['change_sort'] .= '</select>';
$htm['change_sort'] .= '<input type="submit" value="�ύX"></form>';

// }}}

// HTML�v�����g ==============================================
echo '<hr>';
echo $k_sb_navi_ht;
include (P2_LIBRARY_DIR . '/sb_toolbar_k.inc.php');
echo $allfav_ht;
echo $switchfavlist_ht;
echo '<p>';
echo $dat_soko_ht;
echo $taborn_link_ht;
echo $buildnewthread_ht;
echo '</p>';
echo $htm['change_sort'];
echo '<hr>';
echo '<p>';
echo $_conf['k_to_index_ht'];
echo '</p>';
echo '</body></html>';

?>
