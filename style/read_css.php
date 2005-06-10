<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 �[�X�^�C���ݒ�
// for read.php

if($STYLE['fontfamily_bold']){
    $stylesheet .= <<<EOP
    h3{font-weight: normal; font-family: "{$STYLE['fontfamily_bold']}";} /* �X���b�h�^�C�g��*/\n
EOP;
}
$spm_before = '';
if ($_exconf['spm']['before'] !== '') {
    $spm_before = "\n\t.spm a:hover:before{ content: \"{$_exconf['spm']['before']}\"; }";
}
$spm_after = '';
if ($_exconf['spm']['after'] !== '') {
    $spm_after = "\n\t.spm a:hover:after{ content: \"{$_exconf['spm']['after']}\"; }";
}
if (!isset($SYTLE['live_b_width'])) { $SYTLE['live_b_width'] = $STYLE['respop_b_width']; }
if (!isset($SYTLE['live_b_color'])) { $SYTLE['live_b_color'] = $STYLE['respop_b_color']; }
if (!isset($SYTLE['live_b_style'])) { $SYTLE['live_b_style'] = $STYLE['respop_b_style']; }

$stylesheet .= <<<EOP
body{
    background: {$STYLE['read_bgcolor']} {$STYLE['read_background']};
    line-height: 130%;
    color: {$STYLE['read_color']};
}
body, td{
    font-size: {$STYLE['read_fontsize']};
}

a:link{color: {$STYLE['read_acolor']};}
a:visited{color: {$STYLE['read_acolor_v']};}
a:hover{color: {$STYLE['read_acolor_h']};}

i{font-style: normal;} /* ���p���X*/
dd.respopup{margin: 8px;} /* ���X�|�b�v�A�b�v*/

.thread_title{margin: 6px 0; line-height: 120%; font-size: 14pt; color: {$STYLE['read_thread_title_color']};}
.thre_title{color: {$STYLE['read_thread_title_color']};}
.name{color: {$STYLE['read_name_color']};} /* ���e�҂̖��O */
.mail{color: {$STYLE['read_mail_color']};} /* ���e�҂�mail */
.sage{color: {$STYLE['read_mail_sage_color']};} /* ���e�҂�mail(sage) */
img.thumbnail{border: solid 1px;} /* �摜URL�̐�ǂ݃T���l�C��*/

/* �V�����X�ԍ��i�����ł̓J���[���V���m�F�̋@�\�������Ă���̂œ��ʂ�font��
lib/�J���[�w������Ă���Bthread.class.php - transRes ���Q��)    */
/* .newres{color: {$STYLE['read_newres_color']};} �� ����Č��݂͖����̐ݒ� */

.onthefly{ /* on the fly */
    color: #0a0;
    border: 1px #0a0 solid;
    padding: 2px;
    font-size: 11px;
}
.ontheflyresorder{
    color: #0a0;
}

.ngword{color: {$STYLE['read_ngword']};}
.aborned{ font-size: 1px; }
.aborned span{ display: none; }

.respopup{     /* ���p���X�|�b�v�A�b�v */
    position: absolute;
    visibility: hidden; /* ���i�͉B���Ă���*/
    color: {$STYLE['respop_color']};
    font-size: {$STYLE['respop_fontsize']};
    line-height: 120%;
    padding: 8px;
    background: {$STYLE['respop_bgcolor']} {$STYLE['respop_background']};
    border: {$STYLE['respop_b_width']} {$STYLE['respop_b_color']} {$STYLE['respop_b_style']};
}

span.spd {    /* ���X�̂��΂₳ */
    font-size: 8pt;
    color: #777;
}

#iframespace{ /* HTML�|�b�v�A�b�v�X�y�[�X */
    position: absolute;
    z-index: 100;
    /*border: solid 1px;*/
}

#closebox{
    width: 14px;
    height: 14px;
    position: absolute;
    z-index: 101;
    border: solid 2px;
    padding: 1px;
    line-height: 100%;
    background-color: #ceddf7;
}

div#kakiko{
    display: none;
}

a.resnum:link, a.resnum:visited, a.resnum:hover, a.resnum:active{ /* ���X�ԍ� */
    color: {$STYLE['read_color']};
    text-decoration: none;
}

a.newres:link, a.newres:visited, a.newres:hover, a.newres:active{ /* �V�����X */
    color: {$STYLE['read_newres_color']};
    text-decoration: none;
}

table#readhere{
    margin: 2em auto 0px auto;
    background: {$STYLE['respop_bgcolor']} {$STYLE['respop_background']};
    border: {$STYLE['respop_b_width']} {$STYLE['respop_b_color']} {$STYLE['respop_b_style']};
}
table#readhere td{
    padding: 0.5em;
    text-align: center;
}

/* {{{ �X�}�[�g�|�b�v�A�b�v���j���[ */

.spm {
    position: absolute;
    visibility: hidden; /* ���i�͉B���Ă���*/
    color: {$STYLE['respop_color']};
    font-size: {$STYLE['respop_fontsize']};
    line-height: 150%;
    width: 8.5em;
    margin: 0px;
    padding: 2px 4px;
    background: {$STYLE['respop_bgcolor']} {$STYLE['respop_background']};
    border: {$STYLE['respop_b_width']} {$STYLE['respop_b_color']} {$STYLE['respop_b_style']};
}

.spm p {    /* �X�}�[�g�|�b�v�A�b�v���j���[�F�w�b�_ */
    white-space: nowrap;
    margin: 2px;
    padding: 0px;
    border-bottom: {$STYLE['respop_b_width']} {$STYLE['respop_b_color']} {$STYLE['respop_b_style']};
    vertical-align: middle;
}

.spm a {    /* �X�}�[�g�|�b�v�A�b�v���j���[�F�����N */
    display: block;
    white-space: nowrap;
    margin: 2px -4px;
    padding: 0px 4px;
    vertical-align: middle;
    text-decoration: none;
}
.spm a:hover {
    background: {$STYLE['read_bgcolor']} {$STYLE['read_background']};
}
{$spm_before}
{$spm_after}
.spm a.closemenu {
    text-align: right;
}

.spm a.closebox {    /* �X�}�[�g�|�b�v�A�b�v���j���[�F�N���[�Y�{�b�N�X */
    position: absolute;
    top: 0;
    right: 0;
    width: 14px;
    height: 14px;
    margin: {$STYLE['respop_b_width']};
    padding: 1px;
    border: 1px {$STYLE['respop_b_color']} {$STYLE['respop_b_style']};
}

.spm div.spmMona {
    white-space: nowrap;
    margin: 2px;
    padding: 0px;
    vertical-align: middle;
}

.spm div.spmMona a {
    display: inline;
    color: {$STYLE['respop_color']};
    text-decoration: none;
}
.spm div.spmMona a:hover{ background: transparent none; }

.spmMonoSpace { white-space: pre; font-family: monospace; }

/* }}} */
/* {{{ �������[�h */

dd.jikkyo {
    margin: 2px;
    padding: 0px;
}

table.jikkyo_res {
    margin: 0px;
    padding: 0px;
    width: 100%;
    border-top-width: {$SYTLE['live_b_width']};
    border-top-color: {$SYTLE['live_b_color']};
    border-top-style: {$SYTLE['live_b_style']};
}

td.jikkyo_info {
    width: 5em;
    white-space: nowrap;
    text-align: left;
    vertical-align: top;
}

span.jikkyo_dateid {
    font-size: {$STYLE['respop_fontsize']};
}

td.jikkyo_all {
    width: 1em;
    text-align: center;
    vertical-align: top;
}

td.jikkyo_all a {
    text-decoration: none;
}

td.jikkyo_msg {
    text-align: left;
    vertical-align: top;
}

div.jikkyo_ryaku {
    text-align: right;
    font-size: {$STYLE['respop_fontsize']};
}

/* }}} */
/* {{{ �c���[ */

span.node_marker { /* �� �� */
    color: {$STYLE['read_color']};
    cursor: pointer;
}


span.node_opener { /* + - */
    color: {$STYLE['read_newres_color']};
    cursor: pointer;
    font-family: monospace;
}

/* }}} */

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
