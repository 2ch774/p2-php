<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/**
 *  p2 �������݃t�H�[��
 */

// handle�L���x��
if ($_exconf['handle']['*']) {
    @include(P2EX_LIBRARY_DIR . '/handle.inc.php');
}

// �����R�[�h����p�������擪�Ɏd���ނ��Ƃ�mb_convert_variables()�̎��������������
$htm['post_form'] = <<<EOP
{$htm['resform_ttitle']}
{$htm['orig_msg']}
<form id="resform" method="POST" action="./post.php" accept-charset="{$_conf['accept_charset']}" {$js['onsubmit']}>
    <input type="hidden" name="detect_hint" value="����">
    {$htm['handle_ht']}
    {$htm['subject']}
    {$htm['maru_post']} ���O�F<input id="FROM" name="FROM" type="text" value="{$hd['FROM']}"{$name_size_at}{$dp_name_at}>
    E-mail : <input id="mail" name="mail" type="text" value="{$hd['mail']}"{$mail_size_at}{$on_check_sage}{$dp_mail_at}>
    {$htm['sage_cb']}
    {$htm['options']}
    <br>
    <textarea id="MESSAGE" name="MESSAGE" rows="{$STYLE['post_msg_rows']}" {$msg_cols_at} wrap="off"{$dp_msg_at}>{$hd['MESSAGE']}</textarea>
    <br>
    {$htm['src_fix']}
    {$htm['dpreview_onoff']}
    <input type="submit" name="submit" value="{$submit_value}">
    {$htm['be2ch']}
    {$htm['readnew_hidden']}
    {$htm['newthread_hidden']}
    <input type="hidden" name="host" value="{$host}">
    <input type="hidden" name="bbs" value="{$bbs}">
    <input type="hidden" name="key" value="{$key}">
    <input type="hidden" name="time" value="{$time}">
    <input type="hidden" name="popup" value="{$popup}">
    <input type="hidden" name="rescount" value="{$rescount}">
    <input type="hidden" name="ttitle_en" value="{$ttitle_en}">
    <input type="hidden" name="csrfid" value="{$csrfid}">
</form>
{$htm['options_k']}
EOP;

?>
