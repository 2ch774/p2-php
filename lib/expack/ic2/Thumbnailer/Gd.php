<?php
/**
 * Thumbnailer_Gd
 * PHP Versions 4 and 5
 */

require_once dirname(__FILE__) . '/Common.php';

// {{{ Thumbnailer_Gd

/**
 * Image manipulation class which uses gd php extension.
 */
class Thumbnailer_Gd extends Thumbnailer_Common
{
    // {{{ save()

    /**
     * Convert and save.
     *
     * @access public
     * @param string $source
     * @param string $thumbnail
     * @param array $size
     * @return boolean
     * @throws PEAR_Error
     */
    function save($source, $thumbnail, $size)
    {
        $dst = $this->_convert($source, $size);
        // �T���l�C����ۑ�
        if ($this->isPng()) {
            $result = imagepng($dst, $thumbnail);
        } else {
            $result = imagejpeg($dst, $thumbnail, $this->getQuality());
        }
        imagedestroy($dst);
        if (!$result) {
            $retval = &PEAR::raiseError("Failed to create a thumbnail. ({$thumbnail})");
        } else {
            $retval = true;
        }
        return $retval;
    }

    /**
     * Convert and capture.
     *
     * @access public
     * @param string $source
     * @param array $size
     * @return string
     * @throws PEAR_Error
     */
    function capture($source, $size)
    {
        $dst = $this->_convert($source, $size);
        // �T���l�C�����쐬
        ob_start();
        if ($this->isPng()) {
            $result = imagepng($dst);
        } else {
            $result = imagejpeg($dst, '', $this->getQuality());
        }
        $retval = ob_get_clean();
        imagedestroy($dst);
        if (!$result) {
            unset($retval);
            $retval = &PEAR::raiseError("Failed to create a thumbnail. ({$thumbnail})");
        }
        return $retval;
    }

    /**
     * Convert and output.
     *
     * @access public
     * @param string $source
     * @param string $name
     * @param array $size
     * @return boolean
     * @throws PEAR_Error
     */
    function output($source, $name, $size)
    {
        $dst = $this->_convert($source, $size);
        // �T���l�C�����o��
        $this->_httpHeader($name);
        if ($this->isPng()) {
            $result = imagepng($dst);
        } else {
            $result = imagejpeg($dst, '', $this->getQuality());
        }
        imagedestroy($dst);
        if (!$result) {
            $retval = &PEAR::raiseError("Failed to create a thumbnail. ({$name})");
        } else {
            $retval = true;
        }
        return $retval;
    }

    // }}}
    // {{{ _convert()

    /**
     * Image conversion abstraction.
     *
     * @access protected
     * @param string $source
     * @param array $size
     * @return resource gd
     */
    function _convert($source, $size)
    {
        extract($size);
        // �\�[�X�̃C���[�W�X�g���[�����擾
        $ext = strrchr($source, '.');
        switch ($ext) {
            case '.jpg': $src = imagecreatefromjpeg($source); break;
            case '.png': $src = imagecreatefrompng($source); break;
            case '.gif': $src = imagecreatefromgif($source); break;
        }
        if (!is_resource($src)) {
            $error = &PEAR::raiseError("Failed to load the image. ({$source})");
            return $error;
        }
        // �T���l�C���̃C���[�W�X�g���[�����쐬
        $dst = imagecreatetruecolor($tw, $th);
        $bgcolor = $this->getBgColor();
        $bg = imagecolorallocate($dst, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
        imagefill($dst, 0, 0, $bg);
        // �\�[�X���T���l�C���ɃR�s�[
        if ($this->doesResampling()) {
            imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $tw, $th, $sw, $sh);
        } else {
            imagecopy($dst, $src, 0, 0, $sx, $sy, $sw, $sh);
        }
        imagedestroy($src);
        // ��]
        if ($degrees = $this->getRotation()) {
            $degrees = ($degrees == 90) ? -90 : (($degrees == 270) ? 90: $degrees);
            $tmp = imagerotate($dst, $degrees, $bg);
            imagedestroy($dst);
            return $tmp;
        }
        return $dst;
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
