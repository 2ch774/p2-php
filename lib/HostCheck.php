<?php
/**
 * IP�A�h���X�֘A�̃��[�e�B���e�B�N���X
 */

// {{{ HostCheck

class HostCheck
{
    // {{{ isAddressLocal()

    /**
     * ���[�J���z�X�g?
     *
     * @param string $address
     *
     * @return bool
     */
    static public function isAddressLocal($address = null)
    {
        return self::isAddressLoopback($address);
    }

    // }}}
    // {{{ isAddressLoopback()

    /**
     * ���[�v�o�b�N�A�h���X?
     *
     * @param string $address
     *
     * @return bool
     */
    static public function isAddressLoopback($address = null)
    {
        if ($address === null) {
            $address = $_SERVER['REMOTE_ADDR'];
        }
        if ($address === '127.0.0.1' || $address === '::1') {
            return true;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ normalizeIPv6Address()

    /**
     * IPv6�`���̃A�h���X�Ȃ琳�K�����ĕԂ��A�����łȂ����false��Ԃ�
     *
     * @param string $address
     * @param bool $binary
     *
     * @return string
     */
    static public function normalizeIPv6Address($address, $binary = false)
    {
        // �g�p�\�ȕ��������ō\������Ă��邩?
        $address = strtolower($address);
        if (preg_match('/[^0-9a-f:.]/', $address)) {
            return false;
        }
        if (strpos($address, ':::') !== false) {
            return false;
        }

        // ����32bit��IPv4�`���̏ꍇ
        if (preg_match('/:(([0-9]{1,3})\\.([0-9]{1,3})\\.([0-9]{1,3})\\.([0-9]{1,3}))$/', $address, $matches)) {
            if (ip2long($matches[1]) === false) {
                return false;
            }
            $address = substr($address, 0, -strlen($matches[1])) . sprintf('%04x:%04x', ($matches[2] << 8) | $matches[3], ($matches[4] << 8) | $matches[5]);
        }

        // "::" ��W�J
        switch (substr_count($address, '::')) {
            case 1:
                $nsecs = substr_count($address, ':') - 2;
                if ($nsecs >= 6) {
                    return false;
                }
                $zeros = ':' . str_repeat('0:', 6 - $nsecs);
                $pos = strpos($address, '::');
                if ($pos === 0) {
                    $zeros = '0' . $zeros;
                }
                if ($pos === strlen($address) - 2) {
                    $zeros .= '0';
                }
                $address = str_replace('::', $zeros, $address);
            case 0:
                break;
            default:
                return false;
        }

        // �ŏI�`�F�b�N
        if (preg_match('/^([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4})$/', $address, $matches)) {
            array_shift($matches);
            if ($binary) {
                return vsprintf('%016b%016b%016b%016b%016b%016b%016b%016b', array_map('hexdec', $matches));
            }
            return vsprintf('%04s:%04s:%04s:%04s:%04s:%04s:%04s:%04s', $matches);
        }

        return false;
    }

    // }}}
    // {{{ isAddressPrivate()

    /**
     * �v���C�x�[�g�A�h���X?
     *
     * @see RFC1918
     *
     * @param string $address
     * @param string $class
     *
     * @return bool
     */
    static public function isAddressPrivate($address = '', $class = 'ABC')
    {
        if (!$address) {
            $address = $_SERVER['REMOTE_ADDR'];
        }

        $lval = ip2long($address);
        if ($lval === false) {
            return false;
        }

        $classes = array(
            'A' => array('10.0.0.0', '255.0.0.0'),
            'B' => array('172.16.0.0','255.240.0.0'),
            'C' => array('192.168.0.0', '255.255.0.0'),
        );

        foreach ($classes as $k => $v) {
            if (stripos($class, $k) !== false) {
                $rval = ip2long($v[0]);
                $mask = ip2long($v[1]);
                if (($lval & $mask) === $rval) {
                    return true;
                }
            }
        }

        return false;
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
