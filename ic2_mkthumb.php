<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/* ImageCache2 - �T���l�C���̍č\�z */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once 'conf/conf.php';

authorize();

if ($_exconf['imgCache']['*'] == 0) {
	exit('<html><body><p>ImageCache2�͖����ł��B<br>conf/conf_user_ex.php�̐ݒ��ς��Ă��������B</p></body></html>');
}

// }}}

$debug = FALSE;
if ($debug) {
    require_once 'Var_Dump.php';
    Var_Dump::display($_GET);
    exit;
}

require_once 'PEAR.php';
require_once 'DB/DataObject.php';
require_once (P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/database.class.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/thumbnail.class.php');

$uri   = $_GET['u'];
$thumb = isset($_GET['t']) ? intval($_GET['t']) : 0;
$options = array();
$options['quality'] = isset($_GET['q']) ? intval($_GET['q']) : NULL;
$options['rotate']  = isset($_GET['r']) ? intval($_GET['r']) : 0;
$options['trim']    = !empty($_GET['p']);

$search = &new IC2DB_Images;

if ($search->get($uri)) {
    // GD�ŏ�������ہA�摜�T�C�Y���ɒ[�ɑ傫����memory_limit�ɂЂ�������̂�
    // ImageMagick�Œ��ԃC���[�W���쐬���Ă���GD�ŗՎ��C���[�W���쐬����
    if ($search->width > 1280 || $search->height > 1280) {
        $thumbX = &new Thumbnailer(3);
        $resultX = &$thumbX->convert($search->size, $search->md5, $search->mime, $search->width, $search->height);
        if (PEAR::isError($result)) {
            error($resultX->getMessage());
            exit;
        }
        $options['cushion'] = $resultX;
    }
    $thumb = &new ThumbNailer($thumb, $options);
    $result = &$thumb->convert($search->size, $search->md5, $search->mime, $search->width, $search->height);
    if (PEAR::isError($result)) {
        error($result->getMessage());
    } else {
        $name = 'filename="' . basename($result) . '"';
        if ($thumb->type == '.png') {
            header('Content-Type: image/png; ' . $name);
        } else {
            header('Content-Type: image/jpeg; ' . $name);
        }
        header('Content-Disposition: inline; ' . $name);
        echo $thumb->buf;
    }
} else {
    error("&quot;{$uri}&quot;�̓L���b�V������Ă��܂���B");
}

function error($msg)
{
    echo <<<EOF
<html>
<head><title>ImageCache::Error</title></head>
<body>
<p>{$msg}</p>
</body>
</html>
EOF;
}

?>