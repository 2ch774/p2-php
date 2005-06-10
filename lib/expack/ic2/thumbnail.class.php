<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once (P2EX_LIBRARY_DIR . '/ic2/findexec.inc.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/database.class.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/db_images.class.php');

class ThumbNailer
{
    // {{{ properties

    var $db;         // @var object,  PEAR DB_{phptype}�̃C���X�^���X
    var $ini;        // @var array,   ImageCache2�̐ݒ�
    var $mode;       // @var integer, �T���l�C���̎��
    var $cachedir;   // @var string,  ImageCache2�̃L���b�V���ۑ��f�B���N�g��
    var $sourcedir;  // @var string,  �I���W�i���ۑ��f�B���N�g��
    var $thumbdir;   // @var string,  �T���l�C���ۑ��f�B���N�g��
    var $magick;     // @var string,  ImageMagick�̃p�X
    var $max_width;  // @var integer, �T���l�C���̍ő啝
    var $max_height; // @var integer, �T���l�C���̍ő卂��
    var $type;       // @var string,  �T���l�C���̉摜�`���iJPEG��PNG�j
    var $quality;    // @var integer, �T���l�C���̕i��
    var $bgcolor;    // @var mixed,   �T���l�C���̔w�i�F
    var $resize;     // @var bolean,  �摜�����T�C�Y���邩�ۂ�
    var $rotate;     // @var integer, �摜����]����p�x�i��]���Ȃ��Ƃ�0�j
    var $trim;       // @var bolean , �摜���g���~���O���邩�ۂ�
    var $coord;      // @var array ,  �摜���g���~���O����͈́i�g���~���O���Ȃ��Ƃ�FALSE�j
    var $found;      // @var array,   IC2DB_Images�ŃN�G���𑗐M��������
    var $dynamic;    // @var boolean, ���I�������邩�ۂ��iTRUE�̂Ƃ����ʂ��t�@�C���ɕۑ����Ȃ��j
    var $cushion;    // @var string , ���I�����ɗ��p���钆�ԃC���[�W�̃p�X�i�\�[�X���璼�ڐ�������Ƃ�FALSE�j
    var $buf;        // @var string,  ���I���������摜�f�[�^
    // @var array $default_options,    ���I�������̃I�v�V����
    var $default_options = array(
        'quality' => NULL,
        'rotate'  => 0,
        'trim'    => FALSE,
        'cushion' => FALSE,
    );
    // @var array $mimemap, MIME�^�C�v�Ɗg���q�̑Ή��\
    var $mimemap = array('image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif');

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     * 
     * @access public
     */
    function ThumbNailer($mode = 1, $options = NULL)
    {
        if (is_array($options) && count($options) > 0) {
            $options = array_merge($this->default_options, $options);
            $this->dynamic = TRUE;
            $this->cushion = $options['cushion'];
        } else {
            $options = $this->default_options;
            $this->dynamic = FALSE;
            $this->cushion = FALSE;
        }

        // �ݒ�
        $this->ini = ic2_loadconfig();

        // �f�[�^�x�[�X�ɐڑ�
        $icdb = &new IC2DB_Images;
        $this->db = &$icdb->getDatabaseConnection();
        if (DB::isError($this->db)) {
            $this->error($this->db->getMessage());
        }

        // �T���l�C�����[�h����
        switch ($mode) {
            case 1:  $this->mode = 1; $setting = $this->ini['Thumb1']; break;
            case 2:  $this->mode = 2; $setting = $this->ini['Thumb2']; break;
            case 3:  $this->mode = 3; $setting = $this->ini['Thumb3']; break;
            default: $this->mode = 1; $setting = $this->ini['Thumb1']; 
        }

        // �C���[�W�h���C�o����
        $this->driver = $this->dynamic ? 'gd' : strtolower($this->ini['General']['driver']);
        switch ($this->driver) {
            case 'imagemagick': // �V�X�e����ImageMagick
            case 'imagemagick6': // �V�X�e����ImageMagick6
                $searchpath = $this->ini['General']['magick'];
                if (!findexec('convert', $searchpath)) {
                    $this->error('ImageMagick���g���܂���B');
                }
                if ($searchpath) {
                    $this->magick = $searchpath . DIRECTORY_SEPARATOR . 'convert';
                } else {
                    $this->magick = 'convert';
                }
                break;
            case 'gd': // PHP��GD�g���@�\
                if (!extension_loaded('gd')) { $this->error('GD���g���܂���B'); }
                if (!function_exists('imagerotate') && $options['rotate'] != 0) { $this->error('imagerotate�֐����g���܂���B'); };
                break;
            /*case 'imagick': // PHP��ImageMagick�g���@�\ (PECL)
                // ����ŁiImageMagick6�Ɋ��S�Ή��H�j�������[�X���ꂽ�玎���Ă݂悤�B
                if (!extension_loaded('imagick')) { $this->error('imagick���g���܂���B'); }
                break;*/
            default:
                $this->error('�����ȃC���[�W�h���C�o�ł��B');
        }

        // �f�B���N�g���ݒ�
        $this->cachedir   = $this->ini['General']['cachedir'];
        $this->sourcedir  = $this->cachedir . '/' . $this->ini['Source']['name'];
        $this->thumbdir   = $this->cachedir . '/' . $setting['name'];

        // �T���l�C���̉摜�`���E���E�����E��]�p�x�E�i���ݒ�
        $rotate = (int) $options['rotate'];
        if (abs($rotate) < 4) {
            $rotate = $rotate * 90;
        }
        $rotate = ($rotate < 0) ? ($rotate % 360) + 360 : $rotate % 360;
        $this->rotate = ($rotate % 90 == 0) ? $rotate : 0;
        if ($this->rotate % 180 == 90) {
            $this->max_width  = (int) $setting['height'];
            $this->max_height = (int) $setting['width'];
        } else {
            $this->max_width  = (int) $setting['width'];
            $this->max_height = (int) $setting['height'];
        }
        if (is_null($options['quality'])) {
            $this->quality = (int) $setting['quality'];
        } else {
            $this->quality = (int) $options['quality'];
        }
        if (0 < $this->quality && $this->quality <= 100) {
            $this->type = '.jpg';
        } else {
            $this->type = '.png';
            $this->quality = 0;
        }
        $this->trim = (bool) $options['trim'];

        // �T���l�C���̔w�i�F�ݒ�
        if (preg_match('/^#?([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i', // RGB�e�F2����16�i��
                       $this->ini['General']['bgcolor'], $c)) {
            $r = hexdec($c[1]);
            $g = hexdec($c[2]);
            $b = hexdec($c[3]);
        } elseif (preg_match('/^#?([0-9A-F])([0-9A-F])([0-9A-F])$/i', // RGB�e�F1����16�i��
                  $this->ini['General']['bgcolor'], $c)) {
            $r = hexdec($c[1] . $c[1]);
            $g = hexdec($c[2] . $c[2]);
            $b = hexdec($c[3] . $c[3]);
        } elseif (preg_match('/^(\d{1,3}),(\d{1,3}),(\d{1,3})$/', // RGB�e�F1�`3����10�i��
                  $this->ini['General']['bgcolor'], $c)) {
            $r = min(intval($c[1]), 255);
            $g = min(intval($c[2]), 255);
            $b = min(intval($c[3]), 255);
        } else {
            $r = NULL;
            $g = NULL;
            $b = NULL;
        }
        $this->_bgcolor($r, $g, $b);
    }

    // }}}
    // {{{ convert()

    /**
     * �T���l�C�����쐬
     * 
     * @access public
     */
    function &convert($size, $md5, $mime, $width, $height, $force = FALSE)
    {
        // �摜
        if (!empty($this->cushion) && file_exists($this->cushion)) {
            $src    = realpath($this->cushion);
            $csize  = getimagesize($this->cushion);
            $width  = $csize[0];
            $height = $csize[1];
        } else {
            $src = $this->srcPath($size, $md5, $mime, TRUE);
        }
        $thumbURL = $this->thumbPath($size, $md5, $mime);
        $thumb = $this->thumbPath($size, $md5, $mime, TRUE);
        if ($src == FALSE) {
            return PEAR::raiseError("������MIME�^�C�v�B({$mime})");
        } elseif (!file_exists($src)) {
            return PEAR::raiseError("�I���W�i���摜���L���b�V������Ă��܂���B({$src})");
        }
        if (!$force && !$this->dynamic && file_exists($thumb)) {
            return $thumbURL;
        }
        $thumbdir = dirname($thumb);
        if (!is_dir($thumbdir) && !@mkdir($thumbdir)) {
            return PEAR::raiseError("�f�B���N�g�����쐬�ł��܂���ł����B({$thumbdir})");
        }
        
        // �T�C�Y������l�ȉ��ŉ�]�Ȃ��A�摜�`���������Ȃ�΂��̂܂܃R�s�[
        $_size = $this->calc($width, $height);
        if ($this->resize == FALSE && $this->rotate == 0 && $this->type == $this->mimemap[$mime]) {
            if (@copy($src, $thumb)) {
                return $thumbURL;
            } else {
                return PEAR::raiseError("�摜���R�s�[�ł��܂���ł����B({$src} -&gt; {$thumb})");
            }
        }
        
        // �C���[�W�h���C�o�ɃT���l�C���쐬������������
        switch ($this->driver) {
            case 'imagemagick':
                $result = &$this->_magick($src, $thumb, $_size);
                break;
            case 'imagemagick6':
                $result = &$this->_magick6($src, $thumb, $_size);
                break;
            case 'gd':
                $size = array();
                list($size['tw'], $size['th']) = explode('x', $_size);
                if (is_array($this->coord)) {
                    $size['sx'] = $this->coord['x'][0];
                    $size['sy'] = $this->coord['y'][0];
                    $size['sw'] = $this->coord['x'][1];
                    $size['sh'] = $this->coord['y'][1];
                } else {
                    $size['sx'] = 0;
                    $size['sy'] = 0;
                    $size['sw'] = $width;
                    $size['sh'] = $height;
                }
                if ($this->dynamic) {
                    $result = &$this->_gdBuffer($src, $thumb, $size);
                    //$result = &$this->_gdDirect($src, $thumb, $size);
                } else {
                    $result = &$this->_gd($src, $thumb, $size);
                }
                break;
            default:
                $this->error('�����ȃC���[�W�h���C�o�ł��B');
        }
        
        if (PEAR::isError($result)) {
            return $result;
        }
        return $thumbURL;
    }

    // }}}
    // {{{ gdConvert()

    /**
     * GD�ŕϊ��A�C���[�W���\�[�X��Ԃ�
     * 
     * @access private
     */
    function &gdConvert($original, $size)
    {
        extract($size);
        // �I���W�i���̃C���[�W�X�g���[�����擾
        $ext = strrchr($original, '.');
        switch ($ext) {
            case '.jpg': $src = @imagecreatefromjpeg($original); break;
            case '.png': $src = @imagecreatefrompng($original); break;
            case '.gif': $src = @imagecreatefromgif($original); break;
        }
        if (!is_resource($src)) {
            return PEAR::raiseError("�摜�̓ǂݍ��݂Ɏ��s���܂����B({$original})");
        }
        // �T���l�C���̃C���[�W�X�g���[�����쐬
        $dst = @imagecreatetruecolor($tw, $th);
        if (!is_null($this->bgcolor)) {
            $bg = imagecolorallocate($dst, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2]);
            imagefill($dst, 0, 0, $bg);
        }
        // �I���W�i�����T���l�C���ɃR�s�[
        if ($this->resize) {
            imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $tw, $th, $sw, $sh);
        } else {
            imagecopy($dst, $src, 0, 0, $sx, $sy, $sw, $sh);
        }
        if ($this->rotate > 0) {
            $rotate = abs($this->rotate - 360);
            $dst = imagerotate($dst, $rotate, 0);
        }
        imagedestroy($src);
        return $dst;
    }

    // }}}
    // {{{ &_gd()

    /**
     * GD�ŕϊ��A�t�@�C���ɏo��
     * 
     * @access private
     */
    function &_gd($original, $thumbnail, $size)
    {
        $dst = &$this->gdConvert($original, $size);
        // �T���l�C����ۑ�
        if ($this->type == '.png') {
            $result = @imagepng($dst, $thumbnail);
        } else {
            $result = @imagejpeg($dst, $thumbnail, $this->quality);
        }
        imagedestroy($dst);
        if (!$result) {
            return PEAR::raiseError("�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail})");
        }
        return TRUE;
    }

    // }}}
    // {{{ _gdBuffer()

    /**
     * GD�ŕϊ��A�o�b�t�@�ɕۑ�
     * 
     * @access private
     */
    function &_gdBuffer($original, $thumbnail, $size)
    {
        $dst = &$this->gdConvert($original, $size);
        // �T���l�C����ۑ�
        ob_start();
        if ($this->type == '.png') {
            $result = @imagepng($dst);
        } else {
            $result = @imagejpeg($dst, '', $this->quality);
        }
        $this->buf = ob_get_clean();
        imagedestroy($dst);
        if (!$result) {
            return PEAR::raiseError("�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail})");
        }
        return TRUE;
    }

    // }}}
    // {{{ _gdDirect()

    /**
     * GD�ŕϊ��A���ڕ\��
     * 
     * @access private
     */
    function &_gdDirect($original, $thumbnail, $size)
    {
        $dst = &$this->gdConvert($original, $size);
        // �T���l�C����ۑ�
        $name = 'filename="' . basename($result) . '"';
        if ($this->type == '.png') {
            header('Content-Type: image/png; ' . $name);
            header('Content-Disposition: inline; ' . $name);
            $result = @imagepng($dst);
        } else {
            header('Content-Type: image/jpeg; ' . $name);
            header('Content-Disposition: inline; ' . $name);
            $result = @imagejpeg($dst, '', $this->quality);
        }
        imagedestroy($dst);
        if (!$result) {
            return PEAR::raiseError("�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail})");
        }
        return TRUE;
    }

    // }}}
    // {{{ _magick()

    /**
     * ImageMagick�ŕϊ��A�t�@�C���ɏo��
     * 
     * @access private
     */
    function &_magick($original, $thumbnail, $size)
    {
        $command = $this->magick;
        if ($this->resize)      { $command .= ' -resize ' . $size; }
        if ($this->rotate > 0)  { $command .= ' -rotate ' . $this->rotate; }
        if ($this->quality > 0) { $command .= ' -quality ' . $this->quality; }
        if (!is_null($this->bgcolor)) {
            /* ImageMagick�œ��ߕ����̔w�i�F��C�ӂ̐F�ɂ���̂͂߂�ǂ��������Ȃ̂ŕۗ� */
        }
        if (preg_match('/\.gif$/', $original)) {
            $command .= ' +adjoin';
            $original .= '[0]';
        }
        $original  = escapeshellarg($original);
        $thumbnail = escapeshellarg($thumbnail);
        $command .= " +profile '*' $original $thumbnail";
        @exec($command, $results, $status);
        if ($status != 0) {
            $errmsg = "convert failed. ( $command . )\n";
            while (!is_null($errstr = array_shift($results))) {
                if ($errstr === '') { break; }
                $errmsg .= $errstr . "\n";
            }
            return PEAR::raiseError($errmsg);
        }
        return TRUE;
    }

    // }}}
    // {{{ _magick6()

    /**
     * ImageMagick6�ŕϊ��A�t�@�C���ɏo��
     * 
     * @access private
     */
    function &_magick6($original, $thumbnail, $size)
    {
        $command = $this->magick;
        $command .=  ($this->resize == TRUE) ? ' -thumbnail ' . $size : ' -strip';
        if ($this->rotate > 0)  { $command .= ' -rotate ' . $this->rotate; }
        if ($this->quality > 0) { $command .= ' -quality ' . $this->quality; }
        if (!is_null($this->bgcolor)) {
            /* ImageMagick�œ��ߕ����̔w�i�F��C�ӂ̐F�ɂ���̂͂߂�ǂ��������Ȃ̂ŕۗ� */
        }
        if (preg_match('/\.gif$/', $original)) {
            $command .= ' +adjoin';
            $original .= '[0]';
        }
        $original  = escapeshellarg($original);
        $thumbnail = escapeshellarg($thumbnail);
        $command .= " $original $thumbnail";
        @exec($command, $results, $status);
        if ($status != 0) {
            $errmsg = "convert failed. ( $command . )\n";
            while (!is_null($errstr = array_shift($results))) {
                if ($errstr === '') { break; }
                $errmsg .= $errstr . "\n";
            }
            return PEAR::raiseError($errmsg);
        }
        return TRUE;
    }

    // }}}
    // {{{ _bgcolor()

    /**
     * �w�i�F��ݒ�
     * 
     * @access private
     */
    function _bgcolor($r, $g, $b)
    {
        if (is_null($r) || is_null($g) || is_null($b)) {
            $this->bgcolor = NULL;
            return;
        }
        switch ($this->driver) {
            case 'gd':
                $this->bgcolor = array($r, $g, $b);
                break;
            case 'imagemagick':
            case 'imagemagick6':
                $this->bgcolor = '"#' . dechex($r) . dechex($g) . dechex($b) . '"';
                break;
            default:
                $this->bgcolor = "$r,$g,$b";
        }
    }

    // }}}
    // {{{ calc()

    /**
     * �T���l�C���T�C�Y�v�Z
     * 
     * @access public
     */
    function calc($width, $height)
    {
        $debug = FALSE;
        // �f�t�H���g�l�E�t���O��ݒ�
        $t_width  = $width;
        $t_height = $height;
        $this->resize = FALSE;
        $this->coord   = FALSE;
        // �I���W�i�����T���l�C���̍ő�T�C�Y��菬�����Ƃ��A�I���W�i���̑傫�������̂܂ܕԂ�
        if ($width <= $this->max_width && $height <= $this->max_height) {
            // ���T�C�Y�E�g���~���O�Ƃ��ɖ���
            return ($width . 'x' . $height);
        }
        // �c���ǂ���ɍ��킹�邩�𔻒�i�ő�T�C�Y��艡�� = �����ɍ��킹��j
        if (($width / $height) >= ($this->max_width / $this->max_height)) {
            // ���ɍ��킹��
            $main = $width;
            $sub  = $height;
            $max_main = $this->max_width;
            $max_sub  = $this->max_height;
            $t_main = &$t_width;  // $t_main��$t_sub���T���l�C���T�C�Y��
            $t_sub  = &$t_height; // ���t�@�����X�ɂ��Ă���̂���
            $c_main = 'x';
            $c_sub  = 'y';
        } else {
            // �c�ɍ��킹��
            $main = $height;
            $sub  = $width;
            $max_main = $this->max_height;
            $max_sub  = $this->max_width;
            $t_main = &$t_height;
            $t_sub  = &$t_width;
            $c_main = 'y';
            $c_sub  = 'x';
        }
        // �T���l�C���T�C�Y�ƕϊ��t���O������
        $t_main = $max_main;
        if ($this->trim) {
            // �g���~���O����
            $this->coord = array($c_main => array(0, $main), $c_sub => array(0, $sub));
            $ratio = $t_sub / $max_sub;
            if ($ratio <= 1) {
                // �I���W�i�����T���l�C���̍ő�T�C�Y��菬�����Ƃ��A�k�������Ƀg���~���O
                // $t_main == $max_main, $t_sub == $sub
                // ceil($sub * ($t_main / $t_sub)) = ceil($sub * $t_main / $sub) = $t_main = $max_main
                $c_length = $max_main;
            } elseif ($ratio < 1.05) {
                // �k�������ɂ߂ď������Ƃ��A�掿�򉻂�����邽�߂ɏk�������Ƀg���~���O
                $this->coord[$c_sub][0] = floor(($t_sub - $max_sub) / 2);
                $t_sub = $max_sub;
                $c_length = $max_main;
            } else {
                // �T���l�C���T�C�Y�����ς��Ɏ��܂�悤�ɏk�����g���~���O
                $this->resize = TRUE;
                $t_sub = $max_sub;
                $c_length = ceil($sub * ($t_main / $t_sub));
            }
            $this->coord[$c_main] = array(floor(($main - $c_length) / 2), $c_length);
        } else {
            // �A�X�y�N�g����ێ������܂܏k�����A�g���~���O�͂��Ȃ�
            $this->resize = TRUE;
            $t_sub = round($max_main * ($sub / $main));
        }
        // �`�F�b�N
        if ($debug) {
            require_once 'Var_Dump.php';
            $flags = array(
                'width' => $width,
                'height' => $height,
                'max_width' => $this->max_width,
                'max_height' => $this->max_height,
                't_width' => $t_width,
                't_height' => $t_height,
                'resize' => $this->resize,
                'coord' => $this->coord,
            );
            Var_Dump::display($flags);
            if ($this->dynamic) {
                exit;
            }
        }
        // �T���l�C���T�C�Y��Ԃ�
        return ($t_width . 'x' . $t_height);
    }

    // }}}
    // {{{ srcPath()

    /**
     * �\�[�X�t�@�C���̃p�X
     * 
     * @access public
     */
    function srcPath($size, $md5, $mime, $FSFullPath = FALSE)
    {
        $directory = $this->getSubDir($this->sourcedir, $size, $md5, $mime, $FSFullPath);
        if (!$directory) {
            return FALSE;
        }
        
        $basename = $size . '_' . $md5 . $this->mimemap[$mime];
        
        return $directory . ($FSFullPath ? DIRECTORY_SEPARATOR : '/') . $basename;
    }

    // }}}
    // {{{ thumbPath()

    /**
     * �T���l�C���̃p�X
     *
     * @access public
     */
    function thumbPath($size, $md5, $mime, $FSFullPath = FALSE)
    {
        $directory = $this->getSubDir($this->thumbdir, $size, $md5, $mime, $FSFullPath);
        if (!$directory) {
            return FALSE;
        }
        
        $basename = $size . '_' . $md5;
        if ($this->rotate > 0) {
            $basename .= '_' . str_pad($this->rotate, 3, 0, STR_PAD_LEFT);
        }
        if ($this->trim) {
            $basename .= '_tr';
        }
        $basename .= $this->type;
        
        return $directory . ($FSFullPath ? DIRECTORY_SEPARATOR : '/') . $basename;
    }

    // }}}
    // {{{ getSubDir()

    /**
     * ���ۂɉ摜���ۑ������T�u�f�B���N�g���̃p�X
     * 
     * @access public
     */
    function getSubDir($basedir, $size, $md5, $mime, $FSFullPath = FALSE)
    {
        if (!is_dir($basedir)) {
            return FALSE;
        }
        
        $dirID = $this->dirID($size, $md5, $mime);
        
        if ($FSFullPath) {
            $directory = realpath($basedir) . DIRECTORY_SEPARATOR . $dirID;
        } else {
            $directory = $basedir . '/' . $dirID;
        }
        
        return $directory;
    }

    // }}
    // {{{ dirID()

    /**
     * �f�B���N�g��ID
     * 
     * @access public
     */
    function dirID($size = NULL, $md5 = NULL, $mime = NULL)
    {
        if ($size && $md5 && $mime) {
            $icdb = &new IC2DB_Images;
            $icdb->whereAddQUoted('size', '=', $size);
            $icdb->whereAddQuoted('md5',  '=', $md5);
            $icdb->whereAddQUoted('mime', '=', $mime);
            $icdb->orderByArray(array('id' => 'ASC'));
            if ($icdb->find(TRUE)) {
                $this->found = $icdb->toArray();
                return str_pad(ceil($icdb->id / 1000), 5, 0, STR_PAD_LEFT);
            }
        }
        $sql = 'SELECT MAX(' . $this->db->quoteIdentifier('id') . ') + 1 FROM '
             . $this->db->quoteIdentifier($this->ini['General']['table']) . ';';
        $nextid = &$this->db->getOne($sql);
        if (DB::isError($nextid) || !$nextid) {
            $nextid = 1;
        }
        return str_pad(ceil($nextid / 1000), 5, 0, STR_PAD_LEFT);
    }

    // }}
    // {{{ error()

    /**
     * �G���[���b�Z�[�W��\�����ďI��
     * 
     * @access public
     */
    function error($message = '')
    {
        echo <<<EOF
<html>
<head><title>ImageCache::Error</title></head>
<body>
<p>{$message}</p>
</body>
</html>
EOF;
        exit;
    }

    // }}
}

?>
