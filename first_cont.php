<?php
/**
 * rep2 - �X���b�h�\�������̏����\��
 * �t���[��3������ʁA�E������
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// {{{ �X���w��t�H�[��

$explanation = '�������X���b�h��URL����͂��ĉ������B��Fhttp://pc.2ch.net/test/read.cgi/mac/1034199997/';

// $defurl = getLastReadTreadUrl();
$defurl = '';
$ini_url_text = '';

$onclick_ht = <<<EOP
var url_v=document.forms["urlform"].elements["url_text"].value;
if(url_v=="" || url_v=="{$ini_url_text}"){
    alert("{$explanation}");
    return false;
}
EOP;
$onclick_ht = htmlspecialchars($onclick_ht, ENT_QUOTES);
$htm['urlform'] = <<<EOP
    <form id="urlform" method="GET" action="{$_conf['read_php']}" target="read">
        �X��URL�𒼐ڎw��
        <input id="url_text" type="text" value="{$defurl}" name="url" size="62">
        <input type="submit" name="btnG" value="�\��" onclick="{$onclick_ht}">
    </form>\n
EOP;

// }}}
// {{{ HTML�v�����g

echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>rep2</title>
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
<br>
<div class="container">
    {$htm['urlform']}
    <hr>
    <h1><img src="img/rep2.gif" alt="rep2" width="131" height="63"></h1>
</div>
</body>
</html>
EOP;

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
