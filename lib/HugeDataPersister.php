<?php
require_once dirname(__FILE__) . '/KeyValuePersister.php';

// {{{ HugeDataPersister

/**
 * �T�C�Y�̑傫������������k���ĉi��������
 */
class HugeDataPersister extends KeyValuePersister
{
    // {{{ _encodeValue()

    /**
     * �l��gzip+Base64�G���R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _encodeValue($value)
    {
        return base64_encode(gzdeflate($value, 6));
    }

    // }}}
    // {{{ _decodeValue()

    /**
     * �l��gzip+Base64�f�R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _decodeValue($value)
    {
        return gzinflate(base64_decode($value));
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
