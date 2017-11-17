<?php

// {{{ P2HostType

/**
 * rep2 - p2�p�̃��[�e�B���e�B�N���X
 * �C���X�^���X����炸�ɃN���X���\�b�h�ŗ��p����
 *
 * @create  2017/10/19
 * @static
 */
class P2HostType
{
    // {{{ properties

    /**
     * isHost2ch() �̃L���b�V��
     */
    static private $_hostIs2ch = array();

    /**
     * isHost5ch() �̃L���b�V��
     */
    static private $_hostIs5ch = array();

    /**
     * isHostBe2chNet() �̃L���b�V��
     */
    //static private $_hostIsBe2chNet = array();

    /**
     * isHostBbsPink() �̃L���b�V��
     */
    static private $_hostIsBbsPink = array();

    /**
     * isHostMachiBbs() �̃L���b�V��
     */
    static private $_hostIsMachiBbs = array();

    /**
     * isHostMachiBbsNet() �̃L���b�V��
     */
    static private $_hostIsMachiBbsNet = array();

    /**
     * isHostJbbsShitaraba() �̃L���b�V��
     */
    static private $_hostIsJbbsShitaraba = array();

    /**
     * isHostVip2ch()�̃L���b�V��
     */
    static private $_hostIsVip2ch = array();

    /**
     * isHost2chSc()�̃L���b�V��
     */
    static private $_hostIs2chSc = array();

    /**
     * isHostOpen2ch()�̃L���b�V��
     */
    static private $_hostIsOpen2ch = array();

    // }}}
    // {{{ getHostGroupName()

    /**
     * �z�X�g�ɑΉ����邨�C�ɔE���C�ɃX���O���[�v�����擾����
     *
     * @param string $host
     * @return void
     */
    static public function getHostGroupName($host)
    {
        if (self::isHost2chs($host)) {
            return '2channel';
        } elseif (self::isHostMachiBbs($host)) {
            return 'machibbs';
        } elseif (self::isHostJbbsShitaraba($host)) {
            return 'shitaraba';
        } elseif (self::isHostVip2ch($host)) {
            return 'vip2ch';
        } else {
            return $host;
        }
    }

    // }}}
    // {{{ isHostExample

    /**
     * host ���Ꭶ�p�h���C���Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostExample($host)
    {
        return (bool)preg_match('/(?:^|\\.)example\\.(?:com|net|org|jp)$/i', $host);
    }

    // }}}
    // {{{ isHost2chs()

    /**
     * host �� 2ch or 5ch or bbspink �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHost2chs($host)
    {
        return self::isHost2ch($host) || self::isHost5ch($host) || self::isHostBbsPink($host);
    }

    // }}}
    // {{{ isHostBe2chs()

    /**
     * host �� be.2ch.net or be.5ch.net �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostBe2chs($host)
    {
        return self::isHostBe2chNet($host) || self::isHostBe5chNet($host);
    }

    // }}}
    // {{{ isNotUse2chsAPI()

    /**
     * host �� API ��p���Ȃ��Ă��擾�ł���ꍇ�Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isNotUse2chsAPI($host)
    {
        return self::isNotUse2chAPI($host) || self::isNotUse5chAPI($host);
    }

    // }}}
    // {{{ isHost2ch()

    /**
     * host �� 2ch �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHost2ch($host)
    {
        if (!array_key_exists($host, self::$_hostIs2ch)) {
            self::$_hostIs2ch[$host] = (bool)preg_match('<^\\w+\\.(?:2ch\\.net)$>', $host);
        }
        return self::$_hostIs2ch[$host];
    }

    // }}}
    // {{{ isHostBe2chNet()

    /**
     * host �� be.2ch.net �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostBe2chNet($host)
    {
        return $host == 'be.2ch.net';
    }

    // }}}
    // {{{ isNotUse2chAPI()

    /**
     * host �� API ��p���Ȃ��Ă��擾�ł���ꍇ�Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isNotUse2chAPI($host)
    {
        return ($host == 'qb5.2ch.net' || $host == 'carpenter.2ch.net');
    }

    // }}}
    // {{{ isHost5ch()

    /**
     * host �� 5ch �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHost5ch($host)
    {
        if (!array_key_exists($host, self::$_hostIs5ch)) {
            self::$_hostIs5ch[$host] = (bool)preg_match('<^\\w+\\.(?:5ch\\.net)$>', $host);
        }
        return self::$_hostIs5ch[$host];
    }

    // }}}
    // {{{ isHostBe5chNet()

    /**
     * host �� be.2ch.net �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostBe5chNet($host)
    {
        return $host == 'be.5ch.net';
    }

    // }}}
    // {{{ isNotUse5chAPI()

    /**
     * host �� API ��p���Ȃ��Ă��擾�ł���ꍇ�Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isNotUse5chAPI($host)
    {
        return ($host == 'qb5.5ch.net' || $host == 'carpenter.5ch.net');
    }

    // }}}
    // {{{ isHostBbsPink()

    /**
     * host �� bbspink �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostBbsPink($host)
    {
        if (!array_key_exists($host, self::$_hostIsBbsPink)) {
            self::$_hostIsBbsPink[$host] = (bool)preg_match('<^\\w+\\.bbspink\\.com$>', $host);
        }
        return self::$_hostIsBbsPink[$host];
    }

    // }}}
    // {{{ isHost2chSc()

    /**
     * host �� 2ch.sc �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return  boolean
     */
    static public function isHost2chSc($host)
    {
        if (!array_key_exists($host, self::$_hostIs2chSc)) {
            self::$_hostIs2chSc[$host] = (bool)preg_match('/\\.(2ch\\.sc)$/', $host);
        }
        return self::$_hostIs2chSc[$host];
    }

    // }}}
    // {{{ isHostOpen2ch()

    /**
     * host �� ���[�Ղ�2ch �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return  boolean
     */
    static public function isHostOpen2ch($host)
    {
        if (!array_key_exists($host, self::$_hostIsOpen2ch)) {
            self::$_hostIsOpen2ch[$host] = (bool)preg_match('/\\.(open2ch\\.net)$/', $host);
        }
        return self::$_hostIsOpen2ch[$host];
    }

    // }}}
    // {{{ isHostMachiBbs()

    /**
     * host �� machibbs �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostMachiBbs($host)
    {
        if ($host === "machi.to") {
            return true;
        }

        if (!array_key_exists($host, self::$_hostIsMachiBbs)) {
            self::$_hostIsMachiBbs[$host] = (bool)preg_match('<^\\w+\\.machi(?:bbs\\.com|\\.to)$>', $host);
        }
        return self::$_hostIsMachiBbs[$host];
    }

    // }}}
    // {{{ isHostMachiBbsNet()

    /**
     * host �� machibbs.net �܂��r�˂��� �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostMachiBbsNet($host)
    {
        if (!array_key_exists($host, self::$_hostIsMachiBbsNet)) {
            self::$_hostIsMachiBbsNet[$host] = (bool)preg_match('<^\\w+\\.machibbs\\.net$>', $host);
        }
        return self::$_hostIsMachiBbsNet[$host];
    }

    // }}}
    // {{{ isHostJbbsShitaraba()

    /**
     * host �� livedoor �����^���f���� : ������� �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostJbbsShitaraba($in_host)
    {
        if (!array_key_exists($in_host, self::$_hostIsJbbsShitaraba)) {
            if ($in_host == 'rentalbbs.livedoor.com') {
                self::$_hostIsJbbsShitaraba[$in_host] = true;
            } elseif (preg_match('<^jbbs\\.(?:shitaraba\\.(?:net|com)|livedoor\\.(?:com|jp))(?:/|$)>', $in_host)) {
                self::$_hostIsJbbsShitaraba[$in_host] = true;
            } else {
                self::$_hostIsJbbsShitaraba[$in_host] = false;
            }
        }
        return self::$_hostIsJbbsShitaraba[$in_host];
    }

    // }}}
    // {{{ adjustHostJbbs()

    /**
     * livedoor �����^���f���� : ������΂̃z�X�g���ύX�ɑΉ����ĕύX����
     *
     * @param   string $in_str �z�X�g���ł�URL�ł��Ȃ�ł��ǂ�
     * @return  string
     */
    static public function adjustHostJbbs($in_str)
    {
        return preg_replace('<(^|/)jbbs\\.(?:shitaraba|livedoor)\\.(?:net|com)(/|$)>', '\\1jbbs.shitaraba.net\\2', $in_str, 1);
    }

    // }}}
    // {{{ isHostTor()

    /**
     * host �� tor �n�� �Ȃ� true ��Ԃ�
     *
     * @access public
     * @param string $host
     * @return boolean
     */
    static function isHostTor($host, $isGatewayMode = 99)
    {
        switch ($isGatewayMode) {
            case 0:
                $ret = (bool)preg_match('/\\.onion$/', $host);
                break;

            case 1:
                $ret = (bool)preg_match('/\\.(onion\\.cab|onion\\.city|onion\\.direct|onion\\.link|onion\\.nu|onion\\.to|onion\\.rip)$/', $host);
                break;

            default:
                $ret = (bool)preg_match('/\\.(onion\\.cab|onion\\.city|onion\\.direct|onion\\.link|onion\\.nu|onion\\.to|onion\\.rip|onion)$/', $host);
                break;
        }

        return $ret;
    }

    // }}}
    // {{{ isHostVip2ch()

    /**
     * host �� vip2ch �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostVip2ch($host)
    {
        if (!array_key_exists($host, self::$_hostIsVip2ch)) {
            self::$_hostIsVip2ch[$host] = (bool)preg_match('<^\\w+\\.(?:vip2ch\\.com)$>', $host);
        }
        return self::$_hostIsVip2ch[$host];
    }

    // }}}
    // {{{ isUrlWikipediaJa()

    /**
     * URL���E�B�L�y�f�B�A���{��ł̋L���Ȃ�true��Ԃ�
     */
    static public function isUrlWikipediaJa($url)
    {
        return (strncmp($url, 'http://ja.wikipedia.org/wiki/', 29) == 0);
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
