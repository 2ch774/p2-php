<?php
/**
 * rep2 �X���b�h�T�u�W�F�N�g�\���֐�
 * for subject.php
 */

// {{{ sb_print()

/**
 * sb_print - �X���b�h�ꗗ��\������ (<tr>�`</tr>)
 */
function sb_print($aThreadList)
{
    global $_conf, $sb_view, $p2_setting, $STYLE;

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('sb_print()');

    if (!$aThreadList->threads) {
        echo '<tr><td>�@�Y���T�u�W�F�N�g�͂Ȃ�������</td></tr>';
        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sb_print()');
        return;
    }

    // �ϐ� ================================================

    // >>1 �\�� (spmode�͏���)
    $only_one_bool = false;
    if (!$aThreadList->spmode && ($_conf['sb_show_one'] == 1 || ($_conf['sb_show_one'] == 2 &&
        (strpos($aThreadList->bbs, 'news') !== false || $aThreadList->bbs == 'bizplus')
    ))) {
        $only_one_bool = true;
    }

    // �`�F�b�N�{�b�N�X
    if ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
        $checkbox_bool = true;
    } else {
        $checkbox_bool = false;
    }

    // ��
    if ($aThreadList->spmode && $aThreadList->spmode != 'taborn' && $aThreadList->spmode != 'soko') {
        $ita_name_bool = true;
    } else {
        $ita_name_bool = false;
    }

    $htm = array('ita_td' => '');

    $norefresh_q = '&amp;norefresh=true';

    // �\�[�g ==================================================

    // ���݂̃\�[�g�`����class�w���CSS�J���[�����O
    $class_sort_midoku  = '';   // �V��
    $class_sort_res     = '';   // ���X
    $class_sort_no      = '';   // No.
    $class_sort_title   = '';   // �^�C�g��
    $class_sort_ita     = '';   // ��
    $class_sort_spd     = '';   // ���΂₳
    $class_sort_ikioi   = '';   // ����
    $class_sort_bd      = '';   // Birthday
    $class_sort_fav     = '';   // ���C�ɓ���
    ${'class_sort_' . $GLOBALS['now_sort']} = ' class="now_sort"';

    $sortq_spmode = '';
    $sortq_host = '';
    $sortq_ita = '';
    // spmode��
    if ($aThreadList->spmode) {
        $sortq_spmode = "&amp;spmode={$aThreadList->spmode}";
    }
    // spmode�łȂ��A�܂��́Aspmode�����ځ[�� or dat�q�ɂȂ�
    if (!$aThreadList->spmode || $aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
        $sortq_host = "&amp;host={$aThreadList->host}";
        $sortq_ita = "&amp;bbs={$aThreadList->bbs}";
    }

    //=====================================================
    // �e�[�u���w�b�_
    //=====================================================
    echo '<tr class="tableheader">';

    // ����
    if ($sb_view == 'edit') {
        echo '<td class="te">&nbsp;</td>';
    }
    // �����̉���
    if ($aThreadList->spmode == 'recent') {
        echo '<td class="t">&nbsp;</td>';
    }
    // �V��
    if ($sb_view != 'edit') {
        echo <<<EOP
<td id="sb_th_midoku" class="tu" nowrap><a{$class_sort_midoku} href="{$_conf['subject_php']}?sort=midoku{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}" target="_self">�V��</a></td>
EOP;
    }
    // ���X��
    if ($sb_view != 'edit') {
        echo <<<EOP
<td id="sb_th_res" class="tn" nowrap><a{$class_sort_res} href="{$_conf['subject_php']}?sort=res{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}" target="_self">���X</a></td>
EOP;
    }
    // >>1
    if ($only_one_bool) {
        echo '<td class="t">&nbsp;</td>';
    }
    // �`�F�b�N�{�b�N�X
    if ($checkbox_bool) {
        echo <<<EOP
<td class="tc"><input id="allbox" name="allbox" type="checkbox" onclick="checkAll();" title="���ׂĂ̍��ڂ�I���A�܂��͑I������"></td>
EOP;
    }
    // No.
    $title = empty($aThreadList->spmode) ? ' title="2ch�W���̕��я��ԍ�"' : '';
    echo <<<EOP
<td id="sb_th_no" class="to"><a{$class_sort_no} href="{$_conf['subject_php']}?sort=no{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}" target="_self"{$title}>No.</a></td>
EOP;
    // �^�C�g��
    echo <<<EOP
<td id="sb_th_title" class="tl"><a{$class_sort_title} href="{$_conf['subject_php']}?sort=title{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}" target="_self">�^�C�g��</a></td>
EOP;
    // ��
    if ($ita_name_bool) {
        echo <<<EOP
<td id="sb_th_ita" class="t"><a{$class_sort_ita} href="{$_conf['subject_php']}?sort=ita{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}" target="_self">��</a></td>
EOP;
    }
    // ���΂₳
    if ($_conf['sb_show_spd']) {
        echo <<<EOP
<td id="sb_th_spd" class="ts"><a{$class_sort_spd} href="{$_conf['subject_php']}?sort=spd{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}" target="_self">���΂₳</a></td>
EOP;
    }
    // ����
    if ($_conf['sb_show_ikioi']) {
        echo <<<EOP
<td id="sb_th_ikioi" class="ti"><a{$class_sort_ikioi} href="{$_conf['subject_php']}?sort=ikioi{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}" target="_self">����</a></td>
EOP;
    }
    // Birthday
    echo <<<EOP
<td id="sb_th_bd" class="t"><a{$class_sort_bd} href="{$_conf['subject_php']}?sort=bd{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}" target="_self">Birthday</a></td>
EOP;
    // ���C�ɓ���
    if ($_conf['sb_show_fav'] && $aThreadList->spmode != 'taborn') {
        echo <<<EOP
<td id="sb_th_fav" class="t"><a{$class_sort_fav} href="{$_conf['subject_php']}?sort=fav{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}" target="_self" title="���C�ɃX��">��</a></td>
EOP;
    }

    echo "</tr>\n";

    //=====================================================
    //�e�[�u���{�f�B
    //=====================================================

    //spmode������΃N�G���[�ǉ�
    if ($aThreadList->spmode) {
        $spmode_q = "&amp;spmode={$aThreadList->spmode}";
    } else {
        $spmode_q = '';
    }
    $sid = defined('SID') ? strip_tags(SID) : '';
    if ($sid === '') {
        $sid_q = $sid_js = '';
    } else {
        $sid_q = "&amp;{$sid}";
        $sid_js = "+'{$sid_q}'";
    }

    // td�� css�N���X
    $class_t  = ' class="t"';   // ��{
    $class_te = ' class="te"';  // ���ёւ�
    $class_tu = ' class="tu"';  // �V�����X��
    $class_tn = ' class="tn"';  // ���X��
    $class_tc = ' class="tc"';  // �`�F�b�N�{�b�N�X
    $class_to = ' class="to"';  // �I�[�_�[�ԍ�
    $class_tl = ' class="tl"';  // �^�C�g��
    $class_ts = ' class="ts"';  // ���΂₳
    $class_ti = ' class="ti"';  // ����

    $i = 0;
    foreach ($aThreadList->threads as $aThread) {
        $i++;
        $midoku_ari = false;
        $anum_ht = ''; // #r1

        $base_q = "host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}";

        if ($aThreadList->spmode != 'taborn') {
            if (!$aThread->torder) { $aThread->torder = $i; }
        }

        // tr�� css�N���X
        if ($i % 2) {
            $class_r = ' class="r1"';   // ��s
        } else {
            $class_r = ' class="r2"';   // �����s
        }

        //�V�����X�� =============================================
        $unum_ht_c = '&nbsp;';
        // �����ς�
        if ($aThread->isKitoku()) {

            // $ttitle_en_q �͐ߌ��ȗ�
            $delelog_js = "return wrapDeleLog('{$base_q}{$sid_q}',this);";
            $title_at = ' title="�N���b�N����ƃ��O�폜"';

            $anum_ht = '#r' . min($aThread->rescount, $aThread->rescount - $aThread->unum + 1 - $_conf['respointer']);

            // subject.txt�ɂȂ���
            if (!$aThread->isonline) {
                // JavaScript�ł̊m�F�_�C�A���O����
                $unum_ht_c = <<<EOP
<a class="un_n" href="{$_conf['subject_php']}?{$base_q}{$spmode_q}&amp;dele=true" target="_self" onclick="if (!window.confirm('���O���폜���܂����H')) {return false;} {$delelog_js}"{$title_at}>-</a>
EOP;

            // �V������
            } elseif ($aThread->unum > 0) {
                $midoku_ari = true;
                $unum_ht_c = <<<EOP
<a id="un{$i}" class="un_a" href="{$_conf['subject_php']}?{$base_q}{$spmode_q}&amp;dele=true" target="_self" onclick="{$delelog_js}"{$title_at}>{$aThread->unum}</a>
EOP;

            // subject.txt�ɂ͂��邪�A�V���Ȃ�
            } else {
                $unum_ht_c = <<<EOP
<a class="un" href="{$_conf['subject_php']}?{$base_q}{$spmode_q}&amp;dele=true" target="_self" onclick="{$delelog_js}"{$title_at}>{$aThread->unum}</a>
EOP;
            }
        }

        $unum_ht = "<td{$class_tu}>{$unum_ht_c}</td>";

        // �����X�� =============================================
        $rescount_ht = "<td{$class_tn}>{$aThread->rescount}</td>";

        // �� ============================================
        if ($ita_name_bool) {
            $ita_name_ht = htmlspecialchars($aThread->itaj ? $aThread->itaj : $aThread->bbs, ENT_QUOTES);
            $htm['ita_td'] = "<td{$class_t} nowrap><a href=\"{$_conf['subject_php']}?host={$aThread->host}&amp;bbs={$aThread->bbs}\" target=\"_self\">{$ita_name_ht}</a></td>";
        }


        // ���C�ɓ��� ========================================
        $fav_ht = '';
        if ($_conf['sb_show_fav']) {
            if ($aThreadList->spmode != 'taborn') {

                $favmark = (!empty($aThread->fav)) ? '��' : '+';
                $favdo = (!empty($aThread->fav)) ? 0 : 1;
                $favtitle = $favdo ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
                $favdo_q = '&amp;setfav='.$favdo;

                // $ttitle_en_q ���t���������������A�ߖ�̂��ߏȗ�����
                $fav_ht = <<<EOP
<td{$class_t}><a class="fav" href="info.php?{$base_q}{$favdo_q}" target="info" onclick="return wrapSetFavJs('{$base_q}','{$favdo}',this);" title="{$favtitle}">{$favmark}</a></td>
EOP;
            }
        }

        // torder(info) =================================================
        // ���C�ɃX��
        if ($aThread->fav) {
            $torder_st = "<b>{$aThread->torder}</b>";
        } else {
            $torder_st = $aThread->torder;
        }
        $torder_ht = <<<EOP
<a id="to{$i}" class="info" href="info.php?{$base_q}" target="_self" onclick="return wrapOpenSubWin(this.href.toString(){$sid_js})">{$torder_st}</a>
EOP;

        // title =================================================
        $rescount_q = '&amp;rescount=' . $aThread->rescount;

        // dat�q�� or �a���Ȃ�
        if ($aThreadList->spmode == 'soko' || $aThreadList->spmode == 'palace') {
            $rescount_q = '';
            $offline_q = '&amp;offline=true';
            $anum_ht = '';
        } else {
            $offline_q = '';
        }

        // �^�C�g�����擾�Ȃ�
        $ttitle_ht = $aThread->ttitle_ht;
        if (strlen($ttitle_ht) == 0) {
            $ttitle_ht = "http://{$aThread->host}/test/read.cgi/{$aThread->bbs}/{$aThread->key}/";
        }

        if ($aThread->similarity) {
            $ttitle_ht .= sprintf(' <var>(%0.1f)</var>', $aThread->similarity * 100);
        }

        // ���X��
        $moto_thre_ht = "";
        if ($_conf['sb_show_motothre']) {
            if (!$aThread->isKitoku()) {
                $moto_thre_ht = '<a class="thre_title" href="' . $aThread->getMotoThread() . '">�E</a> ';
            }
        }

        // �V�K�X��
        if ($aThread->new) {
            $classtitle_q = ' class="thre_title_new"';
        } else {
            $classtitle_q = ' class="thre_title"';
        }

        // �X�������N
        if (!empty($_REQUEST['find_cont']) && strlen($GLOBALS['word_fm']) > 0) {
            $word_q = '&amp;word=' . rawurlencode($GLOBALS['word']) . '&amp;method=' . rawurlencode($GLOBALS['sb_filter']['method']);
            $rescount_q = '';
            $offline_q = '&amp;offline=true';
            $anum_ht = '';
        } else {
            $word_q = '';
        }
        $thre_url = "{$_conf['read_php']}?{$base_q}{$rescount_q}{$offline_q}{$word_q}{$anum_ht}";


        $chUnColor_js = ($midoku_ari) ? "chUnColor('{$i}');" : '';
        $change_color = " onclick=\"chTtColor('{$i}');{$chUnColor_js}\"";

        // �I�����[>>1
        if ($only_one_bool) {
            $one_ht = "<td{$class_t}><a href=\"{$_conf['read_php']}?{$base_q}&amp;one=true\">&gt;&gt;1</a></td>";
        } else {
            $one_ht = '';
        }

        // �`�F�b�N�{�b�N�X
        if ($checkbox_bool) {
            $checked_ht = '';
            if ($aThreadList->spmode == 'taborn') {
                if (!$aThread->isonline) { // or ($aThread->rescount >= 1000)
                    $checked_ht = ' checked';
                }
            }
            $checkbox_ht = "<td{$class_tc}><input name=\"checkedkeys[]\" type=\"checkbox\" value=\"{$aThread->key}\"{$checked_ht}></td>";
        } else {
            $checkbox_ht = '';
        }

        // ����
        $edit_ht = '';
        if ($sb_view == 'edit') {
            $unum_ht = '';
            $rescount_ht = '';
            $sb_view_q = '&amp;sb_view=edit';
            if ($aThreadList->spmode == 'fav') {
                $setkey = 'setfav';
            } elseif ($aThreadList->spmode == 'palace') {
                $setkey = 'setpal';
            }
            $narabikae_a = "{$_conf['subject_php']}?{$base_q}{$spmode_q}{$sb_view_q}";

            $edit_ht = <<<EOP
<td{$class_te}>
    <a class="te" href="{$narabikae_a}&amp;{$setkey}=top" target="_self">��</a>
    <a class="te" href="{$narabikae_a}&amp;{$setkey}=up" target="_self">��</a>
    <a class="te" href="{$narabikae_a}&amp;{$setkey}=down" target="_self">��</a>
    <a class="te" href="{$narabikae_a}&amp;{$setkey}=bottom" target="_self">��</a>
</td>
EOP;
        }

        // �ŋߓǂ񂾃X���̉���
        $offrec_ht = '';
        if ($aThreadList->spmode == 'recent') {
            $offrec_ht = <<<EOP
<td{$class_tc}><a href="info.php?{$base_q}&amp;offrec=true" target="_self" onclick="return offrec_ajax(this.href.toString(),this.parentNode.parentNode);">�~</a></td>
EOP;
        }

        // ���΂₳�i�� ����/���X �� ���X�Ԋu�j
        $spd_ht = '';
        if ($_conf['sb_show_spd']) {
            if ($spd_st = $aThread->getTimePerRes()) {
                $spd_ht = "<td{$class_ts}>{$spd_st}</td>";
            }
        }

        // ����
        $ikioi_ht = '';
        if ($_conf['sb_show_ikioi']) {
            if ($aThread->dayres > 0) {
                // 0.0 �ƂȂ�Ȃ��悤�ɏ����_��2�ʂŐ؂�グ
                $dayres = ceil($aThread->dayres * 10) / 10;
                $dayres_st = sprintf("%01.1f", $dayres);
            } else {
                $dayres_st = '-';
            }
            $ikioi_ht = "<td{$class_ti}>{$dayres_st}</td>";
        }

        // Birthday
        $birthday = date('y/m/d', $aThread->key); // (y/m/d H:i)
        $birth_ht = "<td{$class_t}>{$birthday}</td>";

        //====================================================================================
        // �X���b�h�ꗗ table �{�f�B HTML�v�����g <tr></tr>
        //====================================================================================

        // �{�f�B
        echo <<<EOR
<tr{$class_r}>
{$edit_ht}{$offrec_ht}{$unum_ht}{$rescount_ht}{$one_ht}{$checkbox_ht}<td{$class_to}>{$torder_ht}</td>
<td{$class_tl} nowrap>{$moto_thre_ht}<a id="tt{$i}" href="{$thre_url}" title="{$aThread->ttitle_hd}"{$classtitle_q}{$change_color}>{$ttitle_ht}</a></td>
{$htm['ita_td']}{$spd_ht}{$ikioi_ht}{$birth_ht}{$fav_ht}
</tr>\n
EOR;

    }

    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sb_print()');
    return true;
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
