<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

/* �g���b�v�E���[�J�[ */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

echo P2Util::mkTrip($_GET['tk']);
