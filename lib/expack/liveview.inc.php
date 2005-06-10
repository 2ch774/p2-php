<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// {{{ init


// ���O�Ɠ��t�EID
$jikkyo_name = trim($name);

//�S���|�b�v�A�b�v�\���̃C�x���g�n���h��
$onPopUp_all  = " onmouseover=\"showResPopUp('q{$i}of{$this->thread->key}',event)\"";
$onPopUp_all .= " onmouseout=\"hideResPopUp('q{$i}of{$this->thread->key}')\"";

// ���̑��ϐ���������
$jikkyo_msg = '';
$jikkyo_all = '';
$jikkyo_ryaku = '';
$jikkyo_all_mark = '��';


// }}}
// {{{ convert


// ���t�EID�����s
$jikkyo_dateid = preg_replace('/ ((\\()?<a .+?>(?(2)p|<img .+?>)<\\/a>(?(2)\\))<a [^>]+?>)?(ID|BE):/', '<br>$0', $date_id);
// �{������s���Ƃɕ������ċ�s����
$jikkyo_lines = preg_split('/<br[^>]*?>/i', $live_match['msgbody']);
$jikkyo_lines = $this->arraycleaner->blankFilter($jikkyo_lines);

// ���������AA�Ƃ݂Ȃ��ꂽ�Ƃ��A���ׂĕ\��
if ($mona && $_exconf['aMona']['*'] == 2) {
    $jikkyo_msg = $live_match['fullbody'];
    // 1�sAA�̓��i�[�t�H���g�\�����Ȃ�
    if (count($jikkyo_lines) == 1) {
        $jikkyo_msg = str_replace(' class="AutoMona"', '', $jikkyo_msg);
    // AA��������on���������i�[�t�H���goff�̂Ƃ����O�̉���(�L�́M)
    } elseif (!$_exconf['aMona']['auto_monafont']) {
        $jikkyo_name .= $mona;
    }

// ���ʂ̕\���i�ŏ���$_exconf['liveView']['rowlimit']�s�̂ݕ\���j
} else {
    $jikkyo_line_count = count($jikkyo_lines);
    $jikkyo_row_limit = $_exconf['liveView']['rowlimit'];
    
    // NG���[�h����уu���b�N�J�n�^�O
    $jikkyo_msg .= $live_match['ngword'];
    $jikkyo_msg .= $live_match['ngbegin'];
    $jikkyo_msg .= $live_match['msgbegin'];
    
    // �{��
    if (0 < $jikkyo_row_limit && $jikkyo_row_limit < $jikkyo_line_count) {
        $jikkyo_msg .= implode('<br>', array_slice($jikkyo_lines, 0, $jikkyo_row_limit));
        $jikkyo_show_all = true;
    } else {
        $jikkyo_msg .= implode('<br>', $jikkyo_lines);
        $jikkyo_show_all = false;
    }
    
    // �u���b�N�I���^�O
    $jikkyo_msg .= $live_match['msgend'];
    $jikkyo_msg .= $live_match['ngend'];

    // �\��������Ȃ������Ƃ���<del>�S�s����\����</del>�A�{�����̃}�[�N����S�����|�b�v�A�b�v�\������
    if ($jikkyo_show_all) {
        $jikkyo_all = "<a href=\"javascript:void(0);\"{$onPopUp_all}>{$jikkyo_all_mark}</a>";
        if (!$this->quote_res_nums_done[$i]) {
            $ds = $this->qRes($i);
            $rpop .=  "<dd id=\"q{$i}of{$this->thread->key}\" class=\"respopup\"{$onPopUp_all}><i>" . rtrim($ds) . "</i></dd>\n";
            $this->quote_res_nums_done[$i] = true;
        }
        //$jikkyo_ryaku = '<div class="jikkyo_ryaku">(' . ($jikkyo_line_count - $jld) . '�s�ȗ�)</div>';
    }
}

// �e�[�u���ݒ�
if ($this->thread->onthefly) {
    $spmEventHandler = '';
    $GLOBALS['newres_to_show_flag'] = true;
    $num_class = 'ontheflyresorder';
} elseif ($i > $this->thread->readnum) {
    $GLOBALS['newres_to_show_flag'] = true;
    $num_class = 'newres';
} else {
    $num_class = 'resnum';
}


// }}}
// {{{ render


// �e�[�u���\��
$jikkyo_tores = <<<EOLV
<dd{$resAnchor} class="jikkyo">
<table cellspacing="3" cellpadding="0" class="jikkyo_res">
<tr>
    <td id="{$resHeadID}" class="jikkyo_info">
        <a href="javascript:void(0);" class="{$num_class}"{$spmEventHandler}>{$i}</a>:
        <span class="name" title="{$mail}"><b>{$jikkyo_name}</b></span><br>
        <span class="jikkyo_dateid">{$jikkyo_dateid}</span>
    </td>
    <td class="jikkyo_all">
        {$jikkyo_all}
    </td>
    <td class="jikkyo_msg">
        {$jikkyo_msg}
        {$jikkyo_ryaku}
    </td>
    <td align="left" valign="top" class="jikkyo_date">{$jikkyo_date}</td>
</tr>
</table>
</dd>
{$rpop}
EOLV;


// }}}

?>
