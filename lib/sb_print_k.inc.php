<?php
// p2 �X���b�h�T�u�W�F�N�g�\���֐� �g�їp
// for subject.php

/**
 * sb_print - �X���b�h�ꗗ��\������ (<tr>�`</tr>)
 */
function sb_print_k(&$aThreadList)
{
    global $_conf, $browser, $_conf, $sb_view, $p2_setting, $STYLE;
    global $sb_view;

    //=================================================

    if (!$aThreadList->threads) {
        if ($aThreadList->spmode == "fav" && $sb_view == "shinchaku") {
            echo '<p class="empty-subject">���C�ɽڂɐV���Ȃ�������</p>';
        } else {
            echo '<p class="empty-subject">�Y����޼ު�Ă͂Ȃ�������</p>';
        }
        return;
    }

    // �ϐ� ================================================

    // >>1
    if (ereg("news", $aThreadList->bbs) || $aThreadList->bbs=="bizplus" || $aThreadList->spmode=="news") {
        // �q�ɂ͏���
        if ($aThreadList->spmode != "soko") {
            $only_one_bool = true;
        }
    }

    // ��
    if ($aThreadList->spmode and $aThreadList->spmode != "taborn" and $aThreadList->spmode != "soko") {
        $ita_name_bool = true;
    }

    $norefresh_q = "&amp;norefresh=1";

    // �\�[�g ==================================================

    // �X�y�V�������[�h��
    if ($aThreadList->spmode) {
        $sortq_spmode = "&amp;spmode={$aThreadList->spmode}";
        // ���ځ[��Ȃ�
        if ($aThreadList->spmode == "taborn" or $aThreadList->spmode == "soko") {
            $sortq_host = "&amp;host={$aThreadList->host}";
            $sortq_ita = "&amp;bbs={$aThreadList->bbs}";
        }
    } else {
        $sortq_host = "&amp;host={$aThreadList->host}";
        $sortq_ita = "&amp;bbs={$aThreadList->bbs}";
    }

    $midoku_sort_ht = "<a href=\"{$_conf['subject_php']}?sort=midoku{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}{$_conf['k_at_a']}\">�V��</a>";

    //=====================================================
    // �{�f�B
    //=====================================================

    // spmode������΃N�G���[�ǉ�
    if ($aThreadList->spmode) {$spmode_q = "&amp;spmode={$aThreadList->spmode}";}

    if ($_conf['iphone']) {
        echo '<ul class="subject">';
    }

    $i = 0;
    foreach ($aThreadList->threads as $aThread) {
        $i++;
        $midoku_ari = "";
        $anum_ht = ""; //#r1
        $htm = array('ita' => '', 'rnum' => '', 'unum' => '', 'sim' => '');

        $bbs_q = "&amp;bbs=".$aThread->bbs;
        $key_q = "&amp;key=".$aThread->key;

        if ($aThreadList->spmode!="taborn") {
            if (!$aThread->torder) {$aThread->torder=$i;}
        }

        // �V�����X�� =============================================
        // �����ς�
        if ($aThread->isKitoku()) {
            $htm['unum'] = "{$aThread->unum}";

            $anum = $aThread->rescount - $aThread->unum +1 - $_conf['respointer'];
            if ($anum > $aThread->rescount) { $anum = $aThread->rescount; }
            $anum_ht = "#r{$anum}";

            // iPhone�p
            if ($_conf['iphone']) {
                $classunum = 'num unread';

                // �V������
                if ($aThread->unum >= 1) {
                    $midoku_ari = true;
                    $classunum .= ' new';
                }

                // subject.txt�ɂȂ���
                if (!$aThread->isonline) {
                    $htm['unum'] = '-';
                    $classunum .= ' offline';
                }

                $htm['unum'] = "<span class=\"{$classunum}\">{$htm['unum']}</span>";
            } else {
                // �V������
                if ($aThread->unum >= 1) {
                    $midoku_ari = true;
                    $htm['unum'] = "<font color=\"{$STYLE['mobile_subject_newres_color']}\">{$aThread->unum}</font>";
                }

                // subject.txt�ɂȂ���
                if (!$aThread->isonline) {
                    $htm['unum'] = '-';
                }

                $htm['unum'] = "[{$htm['unum']}]";
            }
        }

        // �V�K�X��
        if ($_conf['iphone']) {
            $classtitle = 'title';
            if ($aThread->new) {
                $classtitle .= ' new';
            }
        } else {
            if ($aThread->new) {
                $htm['unum'] = "<font color=\"{$STYLE['mobile_subject_newthre_color']}\">�V</font>";
            }
        }

        // �����X�� =============================================
        $rescount_ht = "{$aThread->rescount}";

        // �� ============================================
        if ($ita_name_bool) {
            $ita_name = $aThread->itaj ? $aThread->itaj : $aThread->bbs;
            $ita_name_hd = htmlspecialchars($ita_name, ENT_QUOTES);

            // �S�p�p���J�i�X�y�[�X�𔼊p��
            if (!empty($_conf['k_save_packet'])) {
                $ita_name_hd = mb_convert_kana($ita_name_hd, 'rnsk');
            }

            //$htm['ita'] = "<a href=\"{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$_conf['k_at_a']}\">{$ita_name_hd}</a>)";
            //$htm['ita'] = " ({$ita_name_hd})";
            $htm['ita'] = $ita_name_hd;
        }

        // torder(info) =================================================
        /*
        if ($aThread->fav) { //���C�ɃX��
            $torder_st = "<b>{$aThread->torder}</b>";
        } else {
            $torder_st = $aThread->torder;
        }
        $torder_ht = "<a id=\"to{$i}\" class=\"info\" href=\"info.php?host={$aThread->host}{$bbs_q}{$key_q}{$_conf['k_at_a']}\">{$torder_st}</a>";
        */
        $torder_ht = $aThread->torder;

        // title =================================================
        $rescount_q = "&amp;rescount=" . $aThread->rescount;

        // dat�q�� or �a���Ȃ�
        if ($aThreadList->spmode == "soko" || $aThreadList->spmode == "palace") {
            $rescount_q = "";
            $offline_q = "&amp;offline=true";
            $anum_ht = "";
        }

        // �^�C�g�����擾�Ȃ�
        if (!$aThread->ttitle_ht) {
            // ��������̃^�C�g���Ȃ̂Ōg�ёΉ�URL�ł���K�v�͂Ȃ�
            //if (P2Util::isHost2chs($aThread->host)) {
            //    $aThread->ttitle_ht = "http://c.2ch.net/z/-/{$aThread->bbs}/{$aThread->key}/";
            //}else{
                $aThread->ttitle_ht = "http://{$aThread->host}/test/read.cgi/{$aThread->bbs}/{$aThread->key}/";
            //}
        }

        // �S�p�p���J�i�X�y�[�X�𔼊p��
        if (!empty($_conf['k_save_packet'])) {
            $aThread->ttitle_ht = mb_convert_kana($aThread->ttitle_ht, 'rnsk');
        }

        if ($_conf['iphone']) {
            $htm['rnum'] = "<span class=\"num count\">{$rescount_ht}</span>";
            if ($aThread->similarity) {
                $htm['sim'] .= sprintf(' <span class=\"num score\">%0.1f%%</span>', $aThread->similarity * 100);
            }
        } else {
            $htm['rnum'] = "({$rescount_ht})";
            if ($aThread->similarity) {
                $htm['sim'] .= sprintf(' %0.1f%%', $aThread->similarity * 100);
            }
        }

        $thre_url = "{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$rescount_q}{$offline_q}{$_conf['k_at_a']}{$anum_ht}";

        // �I�����[>>1 =============================================
        if ($only_one_bool) {
            $one_ht = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;one=true{$_conf['k_at_a']}\">&gt;&gt;1</a>";
        }

        //�A�N�Z�X�L�[=====
        /*
        $access_ht = "";
        if ($aThread->torder >= 1 and $aThread->torder <= 9) {
            $access_ht = " {$_conf['accesskey']}=\"{$aThread->torder}\"";
        }
        */

        //====================================================================================
        // �X���b�h�ꗗ table �{�f�B HTML�v�����g <tr></tr>
        //====================================================================================

        //�{�f�B
        if ($_conf['iphone']) {
            // ����
            if ($_conf['sb_show_ikioi'] && $aThread->dayres > 0) {
                // 0.0 �ƂȂ�Ȃ��悤�ɏ����_��2�ʂŐ؂�グ
                $htm['ikioi'] = sprintf(' / <span class="num speed">%01.1f</span>', ceil($aThread->dayres * 10) / 10);
            } else {
                $htm['ikioi'] = '';
            }

            if ($htm['ita'] !== '') {
                $htm['ita'] = "<span class=\"ita\">{$htm['ita']}</span>";
            }

            $htm['birthday'] = date('y/m/d', $aThread->key);

            echo <<<EOP
<li><a href="{$thre_url}">{$htm['unum']} <span class="{$classtitle}">{$aThread->ttitle_ht}</span> {$htm['rnum']} {$htm['sim']} {$htm['ita']}</a>
<div class="bg1"><span class="num order">{$aThread->torder}</span>{$htm['ikioi']}</div>
<div class="bg2"><span class="date">{$htm['birthday']}</span></div></li>\n
EOP;
        } else {
            echo <<<EOP
<div>{$htm['unum']}{$aThread->torder}.<a href="{$thre_url}">{$aThread->ttitle_ht}</a> {$htm['rnum']} {$htm['sim']} {$htm['ita']}</div>\n
EOP;
        }
    }

    if ($_conf['iphone']) {
        echo '</ul>';
    }
}
