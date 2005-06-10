<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/**
 * fsockopen�̃��b�p�[�N���X
 */
class P2Socket
{

    var $rsrc;      // �t�@�C���|�C���^
    var $errno;     // �G���[�R�[�h
    var $errstr;    // �G���[���b�Z�[�W
    var $warning;   // PHP���o�͂���x��

    /**
     * �R���X�g���N�^ (PHP4 style)
     */
    function P2Socket($target, $port, $timeout = 0)
    {
        $this->__construct($target, $port, $timeout);
    }

    /**
     * �R���X�g���N�^ (PHP5 style)
     *
     * @param   string  $tareget    �z�X�g���ȂǁA�\�P�b�g�ڑ����J���Ώۂ̃��\�[�X
     * @param   integer $port       �|�[�g�ԍ�
     * @param   float   $timeout    �\�P�b�g�ɐڑ��ł���܂ł̃^�C���A�E�g�i�b�j
     */
    function __construct($target, $port, $timeout = 0)
    {
        ob_start();
        if ($timeout) {
            $this->rsrc = fsockopen($target, $port, $this->errno, $this->errstr, $timeout);
        } else {
            $this->rsrc = fsockopen($target, $port, $this->errno, $this->errstr);
        }
        $warning = ob_get_contents();
        ob_end_clean();
        if ($warning) {
            $this->warning = $warning;
        }
    }

    /**
     * �t�@�N�g��
     *
     * �ڑ��Ɏ��s�����Ƃ��̓p�����[�^���L�[�Ƃ���X�^�e�B�b�N�ϐ��̔z��ɃC���X�^���X���i�[��
     * �Ȍ㓯���p�����[�^�ŌĂ΂ꂽ�Ƃ��͐ڑ������݂Ȃ��悤�ɂ���B
     *
     * �����̓R���X�g���N�^�ɏ�����
     *
     * @return  object  P2Socket�̃C���X�^���X
     */
    function &open($target, $port, $timeout = 0)
    {
        static $errors = array();

        $id = $target . ':' . $port . '(' . $timeout . ')';
        if (isset($errors[$id])) {
            return $errors[$id];
        }

        $sock = &new P2Socket($target, $port, $timeout);

        if ($sock->isError()) {
            $errors[$id] = $sock;
        }

        return $sock;
    }

    /**
     * �\�P�b�g�ڑ����I�[�v���ł��Ă����TRUE�A�ł��Ă��Ȃ����FALSE��Ԃ�
     */
    function isError()
    {
        return !is_resource($this->rsrc);
    }

    /**
     * �\�P�b�g�̃t�@�C���|�C���^��Ԃ�
     */
    function &getResource()
    {
        return $this->rsrc;
    }

    /**
     * �G���[�R�[�h�ƃG���[���b�Z�[�W��z��ŕԂ�
     */
    function getError()
    {
        return array($this->errno, $this->errstr);
    }

    /**
     * PHP���o�͂����x����Ԃ�
     */
    function getWarning()
    {
        return $this->warning;
    }

}

?>
