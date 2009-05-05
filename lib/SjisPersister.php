<?php
require_once dirname(__FILE__) . '/KeyValuePersister.php';

// {{{ SjisPersister

/**
 * Shift_JIS�̕������UTF-8�ɕϊ����ĉi��������
 */
class SjisPersister extends KeyValuePersister
{
    // {{{ _encodeValue()

    /**
     * �l��UTF-8�ɕϊ�����
     *
     * @param string $value
     * @return string
     */
    protected function _encodeValue($value)
    {
        return mb_convert_encoding($value, 'UTF-8', 'CP932');
    }

    // }}}
    // {{{ _decodeValue()

    /**
     * �l��Shift_JIS (CP932) �ɕϊ�����
     *
     * @param string $value
     * @return string
     */
    protected function _decodeValue($value)
    {
        return mb_convert_encoding($value, 'CP932', 'UTF-8');
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
