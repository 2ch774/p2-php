<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

// �������݃t�H�[�������i�[�t�H���g�ɂ���X�C�b�`��ǉ�
if ($_conf['expack.editor.with_aMona']) {
    $htm['options'] .=  <<<EOM
    <select id="MONAFONT" name="MONAFONT" onchange="activeMonaForm(this.options[this.selectedIndex].value);">
        <option value="">(�L�́M)</option>
        <option value="normal">Normal</option>
        <option value="16px">Mona-16</option>
        <option value="14px">Mona-14</option>
        <option value="12px">Mona-12</option>
    </select>\n
EOM;
}

// ��^�����j���[��ǉ�
if ($_conf['expack.editor.constant']) {
    // ��^���̏������Ɠǂݍ���
    $CONSTANT = array();
    @include 'conf/conf_constant.php';
    // �t�H�[���̐���
    $js['dp_cnstmsg'] = '';
    if ($_conf['expack.editor.dpreview']) {
        $js['dp_cnstmsg'] = "DPSetMsg(document.getElementById('MESSAGE').value);";
    }
    $htm['options'] .=<<<EOS
    <select id="CONSTANT" name="CONSTANT" onchange="inputConstant(this);{$js['dp_cnstmsg']}">
        <option value="">��</option>\n
EOS;
    foreach ($CONSTANT as $constant_key => $constant_value) {
        $htm['options'] .= "\t\t<option value=\"{$constant_value}\">{$constant_key}</option>\n";
    }
    $htm['options'] .= "\t</select>\n";
}
