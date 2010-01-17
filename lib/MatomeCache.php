<?php

// {{{ MatomeCache

/**
 * �܂Ƃߓǂ݃L���b�V���f�[�^�N���X
 */
class MatomeCache
{
    // {{{ properties

    /**
     * �܂Ƃߓǂ݂̓��e (HTML)
     *
     * @var string
     */
    private $_content;

    /**
     * �܂Ƃߓǂ݂̃��^�f�[�^
     *
     * @var array
     */
    private $_metaData;

    /**
     * �܂Ƃߓǂ݃L���b�V�����c����
     *
     * @var int
     */
    private $_maxNumEntries;

    /**
     * �܂Ƃߓǂ݃L���b�V�����L�����ǂ���
     *
     * @var bool
     */
    private $_enabled;

    // }}}
    // {{{ __construct()

    /**
     * �R���X�g���N�^
     *
     * ���e�����������A�L�[���擾����B
     *
     * @param string $title
     * @param int $maxNumEntries
     */
    public function __construct($title, $maxNumEntries = -1)
    {
        $this->_content = '';
        $this->_metaData = array(
            'time' => time(),
            'title' => $title,
            'threads' => array(),
            'size' => null,
        );
        $this->_maxNumEntries = $maxNumEntries;
        if ($maxNumEntries == 0) {
            $this->_enabled = false;
        } else {
            $this->_enabled = true;
        }
    }

    // }}}
    // {{{ __destruct()

    /**
     * �f�X�g���N�^
     *
     * ���e��ۑ����A�Â��L���b�V�����폜����B
     * �X���b�h��񂪋�̏ꍇ�͐V�����X�Ȃ��Ƃ݂Ȃ��A�ۑ����Ȃ��B
     *
     * @param void
     */
    public function __destruct()
    {
        if ($this->_enabled && count($this->_metaData['threads'])) {
            $this->_metaData['size'] = strlen($this->_content);
            MatomeCacheList::add($this->_content, $this->_metaData);
            if ($this->_maxNumEntries > 0) {
                MatomeCacheList::trim($this->_maxNumEntries);
            }
        }
    }

    // }}}
    // {{{ concat()

    /**
     * ���e��ǉ�����
     *
     * @param string $content
     * @return void
     */
    public function concat($content)
    {
        if ($this->_enabled) {
            $this->_content .= $content;
        }
    }

    // }}}
    // {{{ addReadThread()

    /**
     * �܂Ƃߓǂ݂Ɋ܂܂��X���b�h����ǉ�����
     *
     * @param ThreadRead $aThread
     * @return void
     */
    public function addReadThread(ThreadRead $aThread)
    {
        if ($this->_enabled) {
            $this->_metaData['threads'][] = array(
                'title' => $aThread->ttitle_hd,
                'host'=> $aThread->host,
                'bbs'=> $aThread->bbs,
                'key'=> $aThread->key,
                'ls' => sprintf('%d-%dn',
                                $aThread->resrange['start'],
                                $aThread->resrange['to']),
            );
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
