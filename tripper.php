<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/* �g���b�v�E���[�J�[ */

require_once 'conf/conf.php';

authorize();

echo P2Util::mkTrip($_GET['tk']);

?>
