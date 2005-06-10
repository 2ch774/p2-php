<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 �[�X�^�C���ݒ�
// for �A�N�e�B�u���i�[

$am_aafont = "'" . str_replace(",", "','", $_exconf['aMona']['aafont']) . "'";

$stylesheet .= <<<EOP

/* �X�C�b�` */
span.aMonaSW {
    cursor: pointer;
}

/* �A�N�e�B�u���i�[:����AA�X�^�C���K�p */
.AutoMona {
    font-family: {$am_aafont};
    font-size: {$_exconf['aMona']['auto_fontsize']};
    line-height: 100%;
    white-space: pre;
}

/* �A�N�e�B�u���i�[:AA�X�^�C���K�p */
.ActiveMona {
    font-family: {$am_aafont};
    line-height: 100%;
    white-space: pre;
}

/* �A�N�e�B�u���i�[:���� */
.NoMona {
    font-family: "{$STYLE['fontfamily']}";
    font-size: {$STYLE['read_fontsize']};
    line-height: 130%;
    white-space: normal;
}

/* �A�N�e�B�u���i�[:����(���X�|�b�v�A�b�v) */
.NoMonaQ {
    font-family: "{$STYLE['fontfamily']}";
    font-size: {$STYLE['respop_fontsize']};
    line-height: 120%;
    white-space: normal;
}

EOP;

// �X�^�C���̏㏑��
if (isset($MYSTYLE) && is_array($MYSTYLE)) {
    include_once (P2_STYLE_DIR . '/mystyle_css.php');
    $stylename = str_replace('_css.php', '', basename(__FILE__));
    if (isset($MYSTYLE[$stylename])) {
        $stylesheet .= get_mystyle($stylename);
    }
}

?>
