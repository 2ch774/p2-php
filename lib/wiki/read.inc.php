<?php
/*
    p2 - �X���b�h�\����include�����t�@�C��
*/

if (!$_conf['ktai']) {
    // �����N�v���O�C��
    require_once P2_LIB_DIR . '/wiki/LinkPluginCtl.php';
    $GLOBALS['linkPluginCtl'] = new LinkPluginCtl();
    // �u���摜URL(PC��ImageCache2���L���̏ꍇ)
    if ($_conf['expack.ic2.enabled'] % 2 == 1) {
        require_once P2_LIB_DIR . '/wiki/ReplaceImageUrlCtl.php';
        $GLOBALS['replaceImageUrlCtl'] = new ReplaceImageUrlCtl();
    }
} else {
    // �u���摜URL(�g�т�ImageCache2���L���̏ꍇ)
    if ($_conf['expack.ic2.enabled'] >= 2) {
        require_once P2_LIB_DIR . '/wiki/ReplaceImageUrlCtl.php';
        $GLOBALS['replaceImageUrlCtl'] = new ReplaceImageUrlCtl();
    }
}
// �u�����[�h
require_once P2_LIB_DIR . '/wiki/replacewordctl.class.php';
$GLOBALS['replaceWordCtl'] = new ReplaceWordCtl();
