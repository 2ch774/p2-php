<?php
/*
�g�p��:
$stalker = new stalker();
$stalker->host = $host; // �w�肵�Ȃ��ꍇ��2ch�Ƃ݂Ȃ�
$stalker->bbs  = $bbs;
if ($stalker->isEnable()) {
    // bbs, date, id�̎w�肪�K�v
    echo $stalker->getIDURL();
}
*/

class stalker
{
    var $host;      // �̃z�X�g
    var $bbs;       // �̃f�B���N�g����
    var $id;        // ID
    var $enabled;   // isEnable

    /**
     * ID�X�g�[�J�[�ɑΉ����Ă��邩���ׂ�
     * $board���Ȃ����load�����s�����
     */
    function isEnable()
    {
        if ($this->host) {
            if (!P2Util::isHost2chs($this->host)) return false;
        }
        return preg_match('/plus$/', $this->bbs);
    }

    /**
     * ID��URL���擾����
     */
    function getIDURL()
    {
        return "http://stick.newsplus.jp/id.cgi?bbs={$this->bbs}&word=" . URLencode($this->id);
    }
}