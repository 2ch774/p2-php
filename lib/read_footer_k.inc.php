<?php
/*
    p2 -  �X���b�h�\�� -  �t�b�^���� -  �g�їp for read.php
*/

//=====================================================================
// �t�b�^
//=====================================================================
// �\���͈�
if ($_conf['filtering'] && $aThread->rescount) {
    $filter_range['end'] = min($filter_range['to'], $filter_hits);
    $read_range_on = "{$filter_range['start']}-{$filter_range['end']}/{$filter_hits}hit";
} elseif ($aThread->resrange['start'] == $aThread->resrange['to']) {
    $read_range_on = $aThread->resrange['start'];
} else {
    $read_range_on = "{$aThread->resrange['start']}-{$aThread->resrange['to']}";
}
$hd['read_range'] = $read_range_on . '/' . $aThread->rescount;

// ���X�Ԏw��ړ� etc.
$htm['goto'] = kspform($aThread, ($_conf['filtering'] ? $last_hit_resnum : $aThread->resrange['to']));

//=====================================================================
// �v�����g
//=====================================================================
if (($aThread->rescount or $_GET['one'] && !$aThread->diedat)) { // and (!$_GET['renzokupop'])

    if (!$aThread->diedat) {
        if (!empty($_conf['disable_res'])) {
            $dores_ht = <<<EOP
      | <a href="{$motothre_url}" target="_blank" {$_conf['accesskey']}="{$_conf['k_accesskey']['res']}">{$_conf['k_accesskey']['res']}.{$dores_st}</a>
EOP;
        } else {
            $dores_ht = <<<EOP
<a href="post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rescount={$aThread->rescount}{$ttitle_en_q}{$_conf['k_at_a']}" {$_conf['accesskey']}="{$_conf['k_accesskey']['res']}">{$_conf['k_accesskey']['res']}.{$dores_st}</a>
EOP;
        }
    }
    if ($res1['body']) {
        $q_ichi = $res1['body']." | ";
    }
    echo <<<EOP
<p>
<a id="footer" name="footer">{$hd['read_range']}</a><br>
{$read_navi_previous_btm}
{$read_navi_next_btm}
{$read_navi_latest_btm}
{$read_footer_navi_new_btm}
{$dores_ht}
{$read_navi_filter_btm}
</p>
<p>
    {$toolbar_right_ht} <a {$_conf['accesskey']}="{$_conf['k_accesskey']['above']}" href="#header">{$_conf['k_accesskey']['above']}.��</a>
</p>
{$htm['goto']}\n
EOP;
    if ($diedat_msg) {
        echo '<hr>';
        echo $diedat_msg;
        echo '<p>';
        echo  $motothre_ht;
        echo '</p>' . "\n";
    }
}
echo '<hr>'.$_conf['k_to_index_ht'] . "\n";

echo '</body></html>';


//=====================================================================
// �֐�
//=====================================================================

/**
 * ���X�ԍ����w�肵�� �ړ��E�R�s�[(+���p)�EAAS ����t�H�[���𐶐�
 */
function kspform(&$aThread, $default = '')
{
    global $_conf;

    //$numonly_at = 'maxlength="4" istyle="4" format="*N" mode="numeric"';
    $numonly_at = 'maxlength="4" istyle="4" format="4N" mode="numeric"';

    $form = "<form method=\"get\" action=\"{$_conf['read_php']}\">";
    $form .= $_conf['k_input_ht'];

    $hidden = '<input type="hidden" name="%s" value="%s">';
    $form .= sprintf($hidden, 'host', htmlspecialchars($aThread->host, ENT_QUOTES));
    $form .= sprintf($hidden, 'bbs', htmlspecialchars($aThread->bbs, ENT_QUOTES));
    $form .= sprintf($hidden, 'key', htmlspecialchars($aThread->key, ENT_QUOTES));
    $form .= sprintf($hidden, 'offline', '1');

    $form .= '<select name="ktool_name">';
    $form .= '<option value="goto">GO</option>';
    $form .= '<option value="copy">��߰</option>';
    $form .= '<option value="copy_quote">&gt;��߰</option>';
    $form .= '<option value="res_quote">&gt;ڽ</option>';
    if ($_conf['expack.aas.enabled']) {
        $form .= '<option value="aas">AAS</option>';
        $form .= '<option value="aas_rotate">AAS*</option>';
    }
    $form .= '<option value="aborn_res">����:ڽ</option>';
    $form .= '<option value="aborn_name">����:���O</option>';
    $form .= '<option value="aborn_mail">����:Ұ�</option>';
    $form .= '<option value="aborn_id">����:ID</option>';
    $form .= '<option value="aborn_msg">����:ү����</option>';
    $form .= '<option value="ng_name">NG:���O</option>';
    $form .= '<option value="ng_mail">NG:Ұ�</option>';
    $form .= '<option value="ng_id">NG:ID</option>';
    $form .= '<option value="ng_msg">NG:ү����</option>';
    $form .= '</select>';

    $form .= "<input type=\"text\" size=\"3\" name=\"ktool_value\" value=\"{$default}\" {$numonly_at}>";
    $form .= '<input type="submit" value="OK" title="OK">';

    $form .= '</form>';

    return $form;
}

?>
