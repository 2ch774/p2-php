<?php
/*
    p2 - �X���b�h�\����include�����t�@�C��
    read�n���ʂŎg�p���Ă��邽�ߍ폜�s��
*/

// ImageCache2���L���̏ꍇ
if (in_array($_conf['expack.ic2.enabled'], array($_conf['ktai'] ? 1 : 2, 3))) {
// �u���摜URL
    if ($_conf['expack.ic2.enabled'] >= 2) {
        require_once P2_LIB_DIR . '/wiki/ReplaceImageUrlCtl.php';
        $GLOBALS['replaceImageUrlCtl'] = new ReplaceImageUrlCtl();
    }
}
// �g�уr���[�ȊO�̏ꍇ
if (!$_conf['ktai'] || $_conf['iphone']) {
// �����N�v���O�C��
    require_once P2_LIB_DIR . '/wiki/LinkPluginCtl.php';
    $GLOBALS['linkPluginCtl'] = new LinkPluginCtl();
}
// �u�����[�h
require_once P2_LIB_DIR . '/wiki/ReplaceWordCtl.php';
$GLOBALS['replaceWordCtl'] = new ReplaceWordCtl();
