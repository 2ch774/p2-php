<?php
/*
�g�p��:
$mimizun = new mimizun();
$mimizun->host = $host; // �w�肪�Ȃ��ꍇ��2ch�Ƃ݂Ȃ�
$mimizun->bbs  = $bbs;
$mimizun->from  = 0;    // 1:���C�u, 2:�ߋ�, ����ȊO:�S��
if ($mimizun->isEnable()) {
    $mimizun->id = $id;
    echo $mimizun->getIDURL();
}

loadBoard�֌W�͈�x�����s����Ȃ����isEnable�ł��Ăяo�����̂ŁA���Ɏ��s����K�v�͂Ȃ��B
�Ď擾�������ꍇ�Ɏg�����ƁB
loadAll�c�S�Ă̔��X�g��ǂݍ���
loadLive�c���C�u�X���b�h�̔��X�g��ǂݍ���
loadKako�c�ߋ����O�̔��X�g��ǂݍ���
isEnable�c����host, bbs��from�őΉ����Ă��邩�`�F�b�N
getIDURL�c����ID�݂݂̂���ID������URL��Ԃ�
*/
class mimizun
{
    var $liveBoards; //���C�u�X���b�h�̑Ή���
    var $kakoBoards; //�ߋ����O�̑Ή���
    var $host;       // �z�X�g(�Ȃ�ׂ��w�肷�邱��)
    var $bbs;        // �̃f�B���N�g���� (�K���w�肷�邱��)
    var $from = 0;   // 0:�S��, 1:���C�u, 2:�ߋ�
    var $id;         // ID (ID�����ŕK�v)
    var $enabled;

    /**
     * �݂݂���Ή���ǂݍ���
     */
    function load($type)
    {
        global $_conf;

        // �Ή��̎擾
        switch($type) {
            case 0:
                $url = 'http://mimizun.com/search/2chlive.html';
                $path = $_conf['cache_dir'] . '/search.mimizun.com/2chlive.html';
                $match = '{<input type="checkbox" name="idxname" value="_(.+?)">}';
                break;
            case 1:
                $url = 'http://mimizun.com/search/2ch.html';
                $path = $_conf['cache_dir'] . '/search.mimizun.com/2ch.html';
                $match = '{<input type="checkbox" name="idxname" value="(.+?)">}';
                break;
        }
        // ���j���[�̃L���b�V�����Ԃ����L���b�V��
        P2UtilWiki::cacheDownload($url, $path, $_conf['menu_dl_interval'] * 3600);
        $file = @file_get_contents($path);
        preg_match_all($match, $file, $boards);
        return $boards[1];
    }

    /**
     * �݂݂���Ή���(���C�u)��ǂݍ���
     */
    function loadLive()
    {
        $this->liveBoards = $this->load(0);
    }

    /**
     * �݂݂���Ή���(�ߋ����O)��ǂݍ���
     */
    function loadKako()
    {
        $this->kakoBoards = $this->load(1);
    }

    /**
     * �݂݂���Ή���ǂݍ���
     */
    function loadAll()
    {
        $this->loadLive();
        $this->loadKako();
    }

    /**
     * �݂݂��񌟍��ɑΉ����Ă��邩���ׂ�
     */
    function isEnable()
    {
        // host���Z�b�g����ĂȂ���������Ȃ��̂�
        // (�Z�b�g����Ă��Ȃ����2ch�Ƃ݂Ȃ�)
        if ($this->host) {
            // �݂݂��񌟍��Ȃ�true
            if (P2Util::isHostMachiBbs($this->host)) return true;
            
            // 2ch�łȂ����false
            if (!P2Util::isHost2chs($this->host)) return false;
        }

        switch ($this->from) {
            case 1:
                if (!isset($this->liveBoards)) $this->loadLive();
                $this->enabled = in_array($this->bbs, $this->liveBoards);
                break;
            case 2:
                if (!isset($this->kakoBoards)) $this->loadKako();
                $this->enabled = in_array($this->bbs, $this->kakoBoards);
                break;
            default:
                if (!isset($this->liveBoards)) $this->loadLive();
                if (!isset($this->kakoBoards)) $this->loadKako();
                $this->enabled =  in_array($this->bbs, array_merge($this->liveBoards, $this->kakoBoards));
                break;
        }
        return $this->enabled;
    }

    /**
     * �݂݂���ID������URL��Ԃ�
     */
    function getIDURL()
    {
        return "http://mimizun.com/search/perl/idsearch.pl?board={$this->bbs}&id={$this->id}";
    }

}
