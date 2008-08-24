<?php
// {{{ constants
/*
if (!defined('FILE_USE_INCLUDE_PATH')) {
    define('FILE_USE_INCLUDE_PATH', 1);
}

if (!defined('FILE_APPEND')) {
    define('FILE_APPEND', 8);
}
*/
// }}}
// {{{ FileCtl

/**
 * �t�@�C���𑀍삷��N���X
 * �C���X�^���X����炸�ɃN���X���\�b�h�ŗ��p����
 *
 * @static
 */
class FileCtl
{
    // {{{ make_datafile()

    /**
     * �������ݗp�̃t�@�C�����Ȃ���ΐ������ăp�[�~�b�V�����𒲐�����
     */
    static public function make_datafile($file, $perm = 0606)
    {
        // �O�̂��߂Ƀf�t�H���g�␳���Ă���
        if (empty($perm)) {
            $perm = 0606;
        }

        if (!file_exists($file)) {
            // �e�f�B���N�g����������΍��
            FileCtl::mkdir_for($file) or die("Error: cannot make parent dirs. ( $file )");
            touch($file) or die("Error: cannot touch. ( $file )");
            chmod($file, $perm);
        } else {
            if (!is_writable($file)) {
                $cont = @file_get_contents($file);
                unlink($file);
                if (FileCtl::file_write_contents($file, $cont) === false) {
                    die('Error: cannot write file.');
                }
                chmod($file, $perm);
            }
        }
        return true;
    }

    // }}}
    // {{{ mkdir_for()

    /**
     * �e�f�B���N�g�����Ȃ���ΐ������ăp�[�~�b�V�����𒲐�����
     */
    static public function mkdir_for($apath)
    {
        global $_conf;

        $dir_limit = 50; // �e�K�w����鐧����

        $perm = (!empty($_conf['data_dir_perm'])) ? $_conf['data_dir_perm'] : 0707;

        if (!$parentdir = dirname($apath)) {
            die("Error: cannot mkdir. ( {$parentdir} )<br>�e�f�B���N�g�����󔒂ł��B");
        }
        $i = 1;
        if (!is_dir($parentdir)) {
            if ($i > $dir_limit) {
                die("Error: cannot mkdir. ( {$parentdir} )<br>�K�w���オ��߂����̂ŁA�X�g�b�v���܂����B");
            }
            FileCtl::mkdir_for($parentdir);
            mkdir($parentdir, $perm) or die("Error: cannot mkdir. ( {$parentdir} )");
            chmod($parentdir, $perm);
            $i++;
        }
        return true;
    }

    // }}}
    // {{{ get_gzfile_contents()

    /**
     * gz�t�@�C���̒��g���擾����
     */
    static public function get_gzfile_contents($filepath)
    {
        if (is_readable($filepath)) {
            ob_start();
            readgzfile($filepath);
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ file_write_contents()

    /**
     * ��������t�@�C���ɏ�������
     * �ifile_put_contents()+����LOCK_EX�j
     *
     * @param string $filename
     * @param mixed $data
     * @param int $flags
     * @param resource $context
     */
    static public function file_write_contents($filename,
                                               $data,
                                               $flags = 0,
                                               $context = null
                                               )
    {
        return file_put_contents($filename, $data, $flags | LOCK_EX, $context);
    }

    // }}}
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
