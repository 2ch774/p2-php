<?php
/**
 * rep2 - �a������֌W�̏���
 */

require_once P2_LIB_DIR . '/filectl.class.php';

// {{{ setPal()

/**
 * �X����a������ɃZ�b�g����
 *
 * $set �́A0(����), 1(�ǉ�), top, up, down, bottom
 */
function setPal($host, $bbs, $key, $setpal)
{
    global $_conf;

     // key.idx �̃p�X�����߂�
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $idxfile = $idx_host_dir.'/'.$bbs.'/'.$key.'.idx';

    // ���� key.idx �f�[�^������Ȃ�ǂݍ���
    if ($lines = FileCtl::file_read_lines($idxfile, FILE_IGNORE_NEW_LINES)) {
        $data = explode('<>', $lines[0]);
    } else {
        $data = array_fill(0, 12, '');
    }

    //==================================================================
    // p2_palace.idx�ɏ�������
    //==================================================================
    $palace_idx = $_conf['pref_dir']. '/p2_palace.idx';

    // palace_idx �t�@�C�����Ȃ���ΐ���
    FileCtl::make_datafile($palace_idx, $_conf['palace_perm']);

    // palace_idx �ǂݍ���
    $pallines = FileCtl::file_read_lines($palace_idx, FILE_IGNORE_NEW_LINES);

    $neolines = array();
    $before_line_num = 0;

     // {{{ �ŏ��ɏd���v�f���폜���Ă���

    if (!empty($pallines)) {
        $i = -1;
        foreach ($pallines as $l) {
            $i++;
            $lar = explode('<>', $l);
            // �d�����
            if ($lar[1] == $key && $lar[11] == $bbs) {
                $before_line_num = $i;    // �ړ��O�̍s�ԍ����Z�b�g
                continue;
            // key�̂Ȃ����͕̂s���f�[�^�Ȃ̂ŃX�L�b�v
            } elseif (!$lar[1]) {
                continue;
            } else {
                $neolines[] = $l;
            }
        }
    }

    // }}}

    // �V�K�f�[�^�ݒ�
    if ($setpal) {
        $newdata = "$data[0]<>{$key}<>$data[2]<>$data[3]<>$data[4]<>$data[5]<>$data[6]<>$data[7]<>$data[8]<>$data[9]<>{$host}<>{$bbs}";
        include_once P2_LIB_DIR . '/getsetposlines.inc.php';
        $rec_lines = getSetPosLines($neolines, $newdata, $before_line_num, $setpal);
    } else {
        $rec_lines = $neolines;
    }

    $cont = '';
    if (!empty($rec_lines)) {
        foreach ($rec_lines as $l) {
            $cont .= $l . "\n";
        }
    }

    // {{{ ��������

    $temp_file = $palace_idx . '.tmp';
    $write_file = P2_OS_WINDOWS ? $palace_idx : $temp_file;
    if (FileCtl::file_write_contents($write_file, $cont) === false) {
        p2die('cannot write file.');
    }
    if (!P2_OS_WINDOWS) {
        if (!rename($write_file, $palace_idx)) {
            p2die('cannot rename file.');
        }
    }

    // }}}

    return true;
}

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
