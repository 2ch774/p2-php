<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

/* ImageCache2 - �摜��ID or URL��������擾���� */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once 'conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    exit('<html><body><p>ImageCache2�͖����ł��B<br>conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B</p></body></html>');
}

// }}}
// {{{ HTTP�w�b�_��XML�錾

P2Util::header_nocache();
header('Content-Type: text/html; charset=UTF-8');

// }}}
// {{{ ������

// �p�����[�^������
if (!isset($_GET['id']) && !isset($_GET['url']) && !isset($_GET['md5'])) {
    echo '-1';
    exit;
}

// ���C�u�����ǂݍ���
require_once 'PEAR.php';
require_once 'DB.php';
require_once 'DB/DataObject.php';
require_once P2EX_LIB_DIR . '/ic2/loadconfig.inc.php';
require_once P2EX_LIB_DIR . '/ic2/database.class.php';
require_once P2EX_LIB_DIR . '/ic2/db_images.class.php';

// }}}
// {{{ execute

$finder = new IC2DB_Images;
if (isset($_GET['id'])) {
    $finder->whereAdd(sprintf('id=%d', (int)$_GET['id']));
} elseif (isset($_GET['url'])) {
    $finder->whereAddQuoted('uri', '=', (string)$_GET['url']);
} else {
    $finder->whereAddQuoted('md5', '=', (string)$_GET['md5']);
}
if ($finder->find(1)) {
    printf('%d,%d,%d,%d,%d,%s',
           $finder->id, $finder->width, $finder->height,
           $finder->size, $finder->rank, $finder->memo
           );
} else {
    echo '-1';
}
exit;

// }}}
