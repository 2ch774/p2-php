<?php
/*
�g�p��:
$hissi = new hissi();
$hissi->host = $host; // �w�肵�Ȃ��ꍇ��2ch�Ƃ݂Ȃ�
$hissi->bbs  = $bbs;
if ($hissi->isEnable()) {
    // bbs�̎w�肪�K�v
    echo $hissi->getBoardURL();
    $hissi->date = $date;
    // bbs, date�̎w�肪�K�v
    echo $hissi->getBoardDateURL();
    $hissi->id   = $id;
    // bbs, date, id�̎w�肪�K�v
    echo $hissi->getIDURL();
}
*/
class Hissi
{
    var $boards;    // array
    var $host;      // �̃z�X�g
    var $bbs;       // �̃f�B���N�g����
    var $id;        // ID
    var $date;      // ���t��yyyymmdd�Ŏw��
    var $enabled;   // isEnable

    /**
     * �K���`�F�b�J�[�Ή���ǂݍ���
     * �����œǂݍ��܂��̂Œʏ�͎��s����K�v�͂Ȃ�
     */
    function load()
    {
        global $_conf;
        // include_once P2_LIBRARY_DIR . '/p2util.class.php';
        $url  = 'http://hissi.dyndns.ws/menu.html';
        $path = P2Util::cacheFileForDL($url);
        P2UtilWiki::cacheDownload($url, $path, $_conf['menu_dl_interval'] * 3600);
        $file = @file_get_contents($path);
        preg_match_all('{<a href=http://hissi\.dyndns\.ws/read\.php/(\w+?)/>.+?</a><br>}',$file, $boards);
        $this->boards = $boards[1];
    }

    /**
     * �K���`�F�b�J�[�ɑΉ����Ă��邩���ׂ�
     * $board���Ȃ����load�����s�����
     */
    function isEnable()
    {
        if ($this->host) {
            require_once P2_LIBRARY_DIR . '/p2util.class.php';
            if (!P2Util::isHost2chs($this->host)) return false;
        }
        
        if (!isset($this->boards)) $this->load();
        $this->enabled = in_array($this->bbs, $this->boards) ? true : false;
        return $this->enabled;
    }

    /**
     * ID��URL���擾����
     * $all = true�őS�ẴX���b�h��\��
     * isEnable() == false�ł��擾�ł���̂Œ���
     */
    function getIDURL($all = false, $page = 0)
    {
        $id_en = base64_encode($this->id);
        $query = $all ? '?thread=all' : '';
        if($page)  $query = $query ? "{query}&p={$page}" : "?p={page}";
        return "http://hissi.dyndns.ws/read.php/{$this->bbs}/{$this->date}/{$id_en}.html{$query}";
    }

    /**
     * ��URL��ݒ肷��
     * isEnable() == false�ł��擾�ł���̂Œ���
     */
    function getBoardURL()
    {
        return "http://hissi.dyndns.ws/read.php/{$this->bbs}/";
    }

    /**
     * �̂��̓��t��URL��ݒ肷��
     * isEnable() == false�ł��擾�ł���̂Œ���
     */
    function getBoardDateURL()
    {
        return "http://hissi.dyndns.ws/read.php/{$this->bbs}/{$this->date}/";
    }
}