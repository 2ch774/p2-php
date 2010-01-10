<?php
require_once dirname(__FILE__) . '/../P2KeyValueStore.php';

// {{{ P2KeyValueStore_ShiftJIS

/**
 * Shift_JIS�̕������UTF-8�ɕϊ����ĉi��������
 */
class P2KeyValueStore_ShiftJIS extends P2KeyValueStore
{
    // {{{ _encode()

    /**
     * Shift_JIS (SJIS-win=CP932) �̕������UTF-8�ɕϊ�����
     *
     * @param string $str
     * @return string
     */
    private function _encode($str)
    {
        return mb_convert_encoding($str, 'UTF-8', 'SJIS-win');
    }

    // }}}
    // {{{ _decode()

    /**
     * UTF-8�̕������Shift_JIS (CP932) �ɕϊ�����
     *
     * @param string $str
     * @return string
     */
    private function _decode($str)
    {
        return mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
    }

    // }}}
    // {{{ encodeKey()

    /**
     * �L�[���G���R�[�h����
     *
     * @param string $key
     * @return string
     */
    public function encodeKey($key)
    {
        return $this->_encode($key);
    }

    // }}}
    // {{{ decodeKey()

    /**
     * �L�[���f�R�[�h����
     *
     * @param string $key
     * @return string
     */
    public function decodeKey($key)
    {
        return $this->_decode($key);
    }

    // }}}
    // {{{ encodeValue()

    /**
     * �l���G���R�[�h����
     *
     * @param string $value
     * @return string
     */
    public function encodeValue($value)
    {
        return $this->_encode($value);
    }

    // }}}
    // {{{ decodeValue()

    /**
     * �l���f�R�[�h����
     *
     * @param string $value
     * @return string
     */
    public function decodeValue($value)
    {
        return $this->_decode($value);
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
