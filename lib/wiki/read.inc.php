<?php
/*
    p2 - �X���b�h�\����include�����t�@�C��
*/

if (!$_conf['ktai']) {
    // �����N�v���O�C��
    require_once P2_LIB_DIR . '/wiki/linkpluginctl.class.php';
    $GLOBALS['linkplugin'] = new LinkPluginCtl;
    // �u���摜URL(PC��ImageCache2���L���̏ꍇ)
    if ($_conf['expack.ic2.enabled'] % 2 == 1) {
        require_once P2_LIB_DIR . '/wiki/replaceimageurlctl.class.php';
        $GLOBALS['replaceimageurl'] = new ReplaceImageURLCtl;
    }
} else {
    // �u���摜URL(�g�т�ImageCache2���L���̏ꍇ)
    if ($_conf['expack.ic2.enabled'] >= 2) {
        require_once P2_LIB_DIR . '/wiki/replaceimageurlctl.class.php';
        $GLOBALS['replaceimageurl'] = new ReplaceImageURLCtl;
    }
}
// �u�����[�h
require_once P2_LIB_DIR . '/wiki/replacewordctl.class.php';
$GLOBALS['replaceword'] = new ReplaceWordCtl;
