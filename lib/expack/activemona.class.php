<?php
// {{{ ActiveMona

/**
 *  rep2expack - �A�N�e�B�u���i�[
 */
class ActiveMona
{
    // {{{ constants

    /**
     * AA �ɂ悭�g����p�f�B���O
     */
    const REGEX_A = '�@{4}|(?: �@){2}';

    /**
     * �r��
     * [\\u2500-\\u257F] [\\x{849F}-\\x{84BE}]
     */
    const REGEX_B = '[��-��]{5}';

    /**
     * Latin-1,�S�p�X�y�[�X�Ƌ�Ǔ_,�Ђ炪��,�J�^�J�i,
     * ���p�E�S�p�` �ȊO�̓���������3�A������p�^�[��
     *
     * Unicode ��
     * [^\x00-\x7F\x{2010}-\x{203B}\x{3000}-\x{3002}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{FF00}-\x{FFEF}]
     * ���x�[�X�� SJIS �ɍ�蒼���Ă��邪�A�኱�̈Ⴂ������B
     *
     * "\1" ��REGEX_A��REGEX_B�ɕߊl���W�����g���Ă��Ȃ����Ƃ��O��Ȃ̂Œ���
     */
    const REGEX_C = '([^\\x00-\\x7F\\xA1-\\xDF�@�A�B�C�D�F�G�O-���[�`�E�c���I�H�����������{�^��])\\1\\1';

    // }}}
    // {{{ properties

    /**
     * �C���X�^���X
     *
     * @var ActiveMona
     */
    static private $_am = null;

    /**
     * ���i�[�t�H���g�\���X�C�b�`
     *
     * @var string
     */
    private $_mona;

    /**
     * �s������Ɏg�����s����
     *
     * @var string
     */
    private $_lb;

    /**
     * ���K�\���Ŕ��肷��s���̉���-1
     *
     * @var int
     */
    private $_ln;

    /**
     * AA���肷�鐳�K�\��
     *
     * @var string
     */
    private $_re;

    // }}}
    // {{{ singleton()

    /**
     * �V���O���g��
     */
    static public function singleton()
    {
        if (self::$_am === null) {
            self::$_am = new ActiveMona();
        }
        return self::$_am;
    }

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct($linebreaks = '<br>')
    {
        global $_conf;

        $this->_mona = '<img src="img/aa.png" width="19" height="12" alt="" class="aMonaSW" onclick="activeMona(\'%s\')">';
        //$this->_mona = '<img src="img/mona.png" width="39" height="12" alt="�i�L�́M�j class="aMonaSW" onclick="activeMona(\'%s\')"">';
        $this->_lb = $linebreaks;
        $this->_ln = $_conf['expack.am.lines_limit'] - 1;
        $this->_re = '(?:' . self::REGEX_A . '|' . self::REGEX_B . '|' . self::REGEX_C . ')';
    }

    // }}}
    // {{{ getMona()

    /**
     * ���i�[�t�H���g�\���X�C�b�`�𐶐�
     */
    function getMona($id)
    {
        return sprintf($this->_mona, $id);
    }

    // }}}
    // {{{ detectAA()

    /**
     * AA����
     */
    function detectAA($msg)
    {
        if (substr_count($msg, $this->_lb) < $this->_ln) {
            return false;
        } elseif (mb_ereg($this->_re, $msg)) {
            return true;
        } else {
            return false;
        }
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
