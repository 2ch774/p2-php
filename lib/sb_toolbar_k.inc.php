<?php
// p2 -  �T�u�W�F�N�g -  �c�[���o�[�\���i�g�сj
// for subject.php

$matome_accesskey_at = "";
$matome_accesskey_navi = "";

// �V���܂Ƃߓǂ� =========================================
if ($upper_toolbar_done && !$_conf['iphone']) {
    $matome_accesskey_at = " {$_conf['accesskey']}=\"{$_conf['k_accesskey']['matome']}\"";
    $matome_accesskey_navi = "{$_conf['k_accesskey']['matome']}.";
}

// �q�ɂłȂ����
if ($aThreadList->spmode != "soko") {
    if ($shinchaku_attayo) {
        $shinchaku_matome_ht = <<<EOP
<a href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;nt={$newtime}{$_conf['k_at_a']}"{$matome_accesskey_at}>{$matome_accesskey_navi}�V�܂Ƃ�({$shinchaku_num})</a>
EOP;
        $shinchaku_norefresh_ht = '<input type="hidden" name="norefresh" value="1">';
    } else {
        $shinchaku_matome_ht = <<<EOP
<a href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}&amp;nt={$newtime}{$_conf['k_at_a']}"{$matome_accesskey_at}>{$matome_accesskey_navi}�V�܂Ƃ�</a>
EOP;
        $shinchaku_norefresh_ht = '';
    }
} else {
    $shinchaku_matome_ht = '';
}

if ($_conf['iphone'] && empty($upper_toolbar_done)) {
    // iPhone (2.0.1) ��Safari�ł�label�v�f�������Ȃ� (�^�O�ň͂ށAfor�����Ƃ���) �̂�onclick�ő�p����
    $shinchaku_matome_ht .= <<<EOP
<input type="checkbox" onclick="
 change_link_target('.//a[@href and starts-with(@href, &quot;{$_conf['read_new_k_php']}?&quot;)]', this.checked);
 change_link_target('.//ul[@class=&quot;subject&quot;]/li/a[@href]', this.checked);
"><span onclick="check_prev(this); this.previousSibling.onclick();">TAB</span>
EOP;
}

// �v�����g==============================================
echo "<div>{$ptitle_ht} {$shinchaku_matome_ht}</div>\n";

// ��ϐ�==============================================
$upper_toolbar_done = true;
