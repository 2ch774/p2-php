<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 -  �X���b�h�\�� -  �t�b�^���� -  for read.php
*/

require_once (P2_LIBRARY_DIR . '/p2util.class.php'); // p2�p�̃��[�e�B���e�B�N���X

//=====================================================================
// ���t�b�^
//=====================================================================

if ($_conf['bottom_res_form']) {

    $bbs = $aThread->bbs;
    $key = $aThread->key;
    $host = $aThread->host;
    $rescount = $aThread->rescount;

    $submit_value = '��������';

    $key_idx = $aThread->keyidx;

    // �t�H�[���̃I�v�V�����ǂݍ���
    include (P2_LIBRARY_DIR . '/post_options_loader.inc.php');

    $htm['resform_ttitle'] = "<p><b class=\"thre_title\">{$aThread->ttitle_hd}</b></p>";

    include (P2_LIBRARY_DIR . '/post_form.inc.php');

    // �t�H�[��
    $res_form_ht = <<<EOP
{$htm['dpreview']}
<div id="kakiko">
{$htm['post_form']}
</div>
{$htm['dpreview2']}
EOP;

    $onmouse_showform_ht = " onmouseover=\"document.getElementById('kakiko').style.display = 'block';{$js['dp_startup']}\"";
}

// ============================================================

if (($aThread->rescount or $_GET['one'] && !$aThread->diedat)) { // and (!$_GET['renzokupop'])

    if (!$aThread->diedat) {
        $htm['dores'] = "| <a href=\"post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}\" target='_self' onclick=\"return OpenSubWin('post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}&amp;popup=1',{$STYLE['post_pop_size']},'auto',1)\"{$onmouse_showform_ht}>{$dores_st}</a>";
        $res_form_ht_pb = $res_form_ht;
    } else {
        $htm['dores'] = '';
        $res_form_ht_pb = '';
    }
    if ($res1['body']) {
        $q_ichi = $res1['body']." | ";
    }

    // ���X�̂��΂₳
    $htm['spd'] = '';
    if ($spd_st = $aThread->getTimePerRes() and $spd_st != "-") {
        $htm['spd'] = '<span class="spd" title="���΂₳������/���X">'."" . $spd_st."".'</span>';
    }

    // ���X�Ԏw��ړ�
    $htm['goto'] = <<<GOTO
            <form method="get" action="{$_conf['read_php']}" class="inline-form">
                <input type="hidden" name="host" value="{$aThread->host}">
                <input type="hidden" name="bbs" value="{$aThread->bbs}">
                <input type="hidden" name="key" value="{$aThread->key}">
                <input type="text" size="5" name="ls" value="{$aThread->ls}">
                <input type="submit" value="go">
            </form>
GOTO;

    // {{{ �t�B���^�q�b�g���������ꍇ�A��X�Ƒ�����ǂނ��X�V
    /*
    //if (!$read_navi_next_isInvisible) {
    $read_navi_next = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$after_rnum}{$offline_range_q}&amp;nt={$newtime}{$read_navi_next_anchor}\">{$next_st}{$rnum_range}</a>";
    //}

    $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$offline_q}\" accesskey=\"r\">{$tuduki_st}</a>";
    */

    if (!empty($GLOBALS['last_hit_resnum'])) {
        $read_navi_next_anchor = "";
        if ($GLOBALS['last_hit_resnum'] == $aThread->rescount) {
            $read_navi_next_anchor = "#r{$aThread->rescount}";
        }
        $after_rnum = $GLOBALS['last_hit_resnum'] + $rnum_range;
        $read_navi_next = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$GLOBALS['last_hit_resnum']}-{$after_rnum}{$offline_range_q}&amp;nt={$newtime}{$read_navi_next_anchor}\">{$next_st}{$rnum_range}</a>";

        // �u������ǂށv
        $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$GLOBALS['last_hit_resnum']}-{$offline_q}\" accesskey=\"r\">{$tuduki_st}</a>";
    }
    // }}}
    
    if($_exconf['status']['processtime'] || $_exconf['status']['datsize']){
	$status_ht="<div align=\"right\">\n\t";
	if($_exconf['status']['datsize']){
	    // ���ݓǂ�ł���X����.dat�e��
	    require_once(P2EX_LIBRARY_DIR . '/status/datsize.inc.php');
	    $status_ht .= "dat: ".getthread_dir($host, $bbs, $key)."KB";
	    if($_exconf['status']['datdirsize']){
		// data�f�B���N�g���̑��e��
		require_once(P2EX_LIBRARY_DIR . '/status/datdirsize.inc.php');
		$status_ht .= " / ".getdirfile($datdir)."MB";
	    }
	}
	if($_exconf['status']['processtime']){
	    // �v���Z�X�^�C��(�����܂łɗv��������)
	    if($_exconf['status']['datsize']){
		$status_ht .=" | ";
	    }
	    require_once(P2EX_LIBRARY_DIR . '/status/process_time.inc.php');
	    $status_ht .= "CPU : " . getprocess_time( $CPU_start ) . " sec";
	}
	$status_ht.="\n</div>\n";
    }

    // ���v�����g
    echo <<<EOP
<hr>
<table id="footer" width="100%" style="padding:0px 10px 0px 0px;">
    <tr>
        <td align="left">
            {$q_ichi}
            <a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=all">{$all_st}</a>
            {$read_navi_previous}
            {$read_navi_next}
            <a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}">{$latest_st}{$latest_show_res_num}</a>
            <a href="{$tree_view_url}">{$tree_st}</a>
            | {$read_footer_navi_new}
            | {$htm['goto']}
            {$htm['dores']}
            {$htm['spd']}
        </td>
        <td align="right">
            {$htm['p2frame']}
            {$toolbar_right_ht}
        </td>
        <td align="right">
            <a href="#header">��</a>
        </td>
    </tr>
</table>
{$status_ht}{$res_form_ht_pb}
EOP;
    if ($diedat_msg) {
        echo '<hr>';
        echo $diedat_msg;
        echo '<p>';
        echo  $motothre_ht;
        echo '</p>';
    }
}

echo '</body></html>';

?>
