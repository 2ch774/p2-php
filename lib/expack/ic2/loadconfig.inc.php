<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/* ImageCache2 - ���[�U�ݒ�ǂݍ��݊֐� */

function ic2_loadconfig($path = 'conf/imgcache.ini.php')
{
    static $ini  = NULL;
    static $file = NULL;
    if (is_null($ini) || $file != $path) {
        $file = $path;
        $ini  = @parse_ini_file($file, true);
        if (!$ini) {
            die("�ݒ�t�@�C�����ǂݍ��߂܂���ł����B ({$file})");
        }
    }
    return $ini;
}

?>
