<?php
/*
�g�p��:
$hissi = new Hissi();
$hissi->host = $host; // �w�肵�Ȃ��ꍇ��2ch�Ƃ݂Ȃ�
$hissi->bbs  = $bbs;
if ($hissi->isEnabled()) {
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
    public $boards; // array
    public $host;   // �̃z�X�g
    public $bbs;    // �̃f�B���N�g����
    public $id;     // ID
    public $date;   // ���t��yyyymmdd�Ŏw��
    protected $enabled;

    /**
     * �K���`�F�b�J�[�Ή���ǂݍ���
     * �����œǂݍ��܂��̂Œʏ�͎��s����K�v�͂Ȃ�
     */
    public function load()
    {
        global $_conf;

        $url  = 'http://hissi.org/menu.html';
        $path = P2Util::cacheFileForDL($url);
        // ���j���[�̃L���b�V�����Ԃ�10�{�L���b�V��
        P2UtilWiki::cacheDownload($url, $path, $_conf['menu_dl_interval'] * 36000);

        $this->boards = array();
        $file = @file_get_contents($path);
        if ($file) {
            if (preg_match_all('{<a href=http://hissi\.org/read\.php/(\w+?)/>.+?</a><br>}', $file, $boards)) {
                $this->boards = $boards[1];
            }
        }
    }

    /**
     * �K���`�F�b�J�[�ɑΉ����Ă��邩���ׂ�
     * $board���Ȃ����load�����s�����
     */
    public function isEnabled()
    {
        if ($this->host) {
            if (!P2Util::isHost2chs($this->host)) {
                return false;
            }
        }

        if (!is_array($this->boards)) {
            $this->load();
        }
        $this->enabled = in_array($this->bbs, $this->boards) ? true : false;

        return $this->enabled;
    }

    /**
     * ID��URL���擾����
     * $all = true�őS�ẴX���b�h��\��
     * isEnabled() == false�ł��擾�ł���̂Œ���
     */
    public function getIDURL($all = false, $page = 0)
    {
        $id_en = rtrim(base64_encode($this->id), '=');
        $query = $all ? '?thread=all' : '';
        if ($page) {
            $query = $query ? "{$query}&p={$page}" : "?p={page}";
        }
        return "http://hissi.org/read.php/{$this->bbs}/{$this->date}/{$id_en}.html{$query}";
    }

    /**
     * ��URL��ݒ肷��
     * isEnabled() == false�ł��擾�ł���̂Œ���
     */
    public function getBoardURL()
    {
        return "http://hissi.org/read.php/{$this->bbs}/";
    }

    /**
     * �̂��̓��t��URL��ݒ肷��
     * isEnabled() == false�ł��擾�ł���̂Œ���
     */
    public function getBoardDateURL()
    {
        return "http://hissi.org/read.php/{$this->bbs}/{$this->date}/";
    }
}
