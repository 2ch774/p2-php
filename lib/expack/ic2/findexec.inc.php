<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// $search_path������s�t�@�C��$command����������
// ������΃p�X���G�X�P�[�v���ĕԂ��i$escape���U�Ȃ炻�̂܂ܕԂ��j
// ������Ȃ����FALSE��Ԃ�
function findexec($command, $search_path = '', $escape=TRUE)
{
    // Windows���A���̑���OS��
    if (strstr(PHP_OS, 'WIN')) {
        $ext = '.exe';
        $chk = 'file_exists'; // PHP5������Windows���is_executable()���g���Ȃ�
    } else {
        $ext = '';
        $chk = 'is_executable';
    }
    // $search_path����̂Ƃ��͊��ϐ�PATH���猟������
    if (!$search_path) {
        $search_path = explode(PATH_SEPARATOR, getenv('PATH'));
    }
    // ����
    if (is_string($search_path) && is_dir($search_path)) {
        if ($ext !== '' && !preg_match('/'.preg_quote($ext).'$/i', $command)) {
            $cmd = $search_path . DIRECTORY_SEPARATOR . $command . $ext;
        } else {
            $cmd = $search_path . DIRECTORY_SEPARATOR . $command;
        }
        if (call_user_func($chk, $cmd)) {
            return ($escape ? escapeshellarg($cmd) : $cmd);
        }
    } elseif (is_array($search_path)) {
        foreach ($search_path as $path) {
            $path = realpath($path);
            if ($path === FALSE || !is_dir($path)) {
                continue;
            }
            if (($found = findexec($command, $path, $escape)) !== FALSE) {
                return $found;
            }
        }
    }
    // ������Ȃ�����
    return FALSE;
}

?>
