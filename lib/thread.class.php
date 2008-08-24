<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once P2_LIB_DIR . '/filectl.class.php';

/**
 * p2 - �X���b�h�N���X
 */
class Thread{

    var $ttitle;    // �X���^�C�g�� // idxline[0] // < �� &lt; �������肷��
    var $key;       // �X���b�hID // idxline[1]
    var $length;    // local Dat Bytes(int) // idxline[2]
    var $gotnum;    //�i�l�ɂƂ��Ắj�������X�� // idxline[3]
    var $rescount;  // �X���b�h�̑����X���i���擾�����܂ށj
    var $modified;  // dat��Last-Modified // idxline[4]
    var $readnum;   // ���ǃ��X�� // idxline[5] // MacMoe�ł̓��X�\���ʒu�������Ǝv���ilast res�j
    var $fav;       //���C�ɓ���(bool�I��) // idxline[6] favlist.idx���Q��
    var $favs;      //���C�ɓ���Z�b�g�o�^���(bool�̔z��)
    // name         // �����ł͗��p���� idxline[7]�i�����ŗ��p�j
    // mail         // �����ł͗��p���� idxline[8]�i�����ŗ��p�j
    // var $newline; // ���̐V�K�擾���X�ԍ� // idxline[9] �p�~�\��B���݊��̂��ߎc���Ă͂���B

    // ��host�Ƃ͂������̂́A2ch�O�̏ꍇ�́Ahost�ȉ��̃f�B���N�g���܂Ŋ܂܂�Ă����肷��B
    var $host;      // ex)pc.2ch.net // idxline[10]
    var $bbs;       // ex)mac // idxline[11]
    var $itaj;      // �� ex)�V�Emac

    var $datochiok; // DAT�����擾�����������TRUE(1) // idxline[12]

    var $torder;    // �X���b�h�V�������ԍ�
    var $unum;      // ���ǁi�V�����X�j��

    var $keyidx;    // idx�t�@�C���p�X
    var $keydat;    // ���[�J��dat�t�@�C���p�X

    var $isonline;  // �T�[�o�ɂ����true�Bsubject.txt��dat�擾���Ɋm�F���ăZ�b�g�����B
    var $new;       // �V�K�X���Ȃ�true

    var $ttitle_hc; // < �� &lt; �ł������肷��̂ŁA�f�R�[�h�����X���^�C�g��
    var $ttitle_hd; // HTML�\���p�ɁA�G���R�[�h���ꂽ�X���^�C�g��
    var $ttitle_ht; // �X���^�C�g���\���pHTML�R�[�h�B�t�B���^�����O��������Ă�������B

    var $dayres;    // ���������̃��X���B�����B

    var $dat_type;  // dat�̌`���i2ch�̋��`��dat�i,��؂�j�Ȃ�"2ch_old"�j

    var $ls = '';   // �\�����X�ԍ��̎w��

    var $similarity; // �^�C�g���̗ގ���

    /**
     * �R���X�g���N�^
     */
    function Thread()
    {
    }

    /**
     * ttitle���Z�b�g����i���ł�ttitle_hc, ttitle_hd, ttitle_ht���j
     */
    function setTtitle($ttitle)
    {
        global $_conf;

        $this->ttitle = $ttitle;
        // < �� &lt; �ł������肷��̂ŁA�܂��f�R�[�h�������̂�
        //$this->ttitle_hc = html_entity_decode($this->ttitle, ENT_COMPAT, 'Shift_JIS');

        // html_entity_decode() �͌��\�d���̂ő�ցA�A���������Ɣ������炢�̏�������
        $a_ttitle = str_replace('&lt;', '<', $this->ttitle);
        $this->ttitle_hc = str_replace('&gt;', '>', $a_ttitle);

        // HTML�\���p�� htmlspecialchars() ��������
        $this->ttitle_hd = htmlspecialchars($this->ttitle_hc, ENT_QUOTES);

        // �ꗗ�\���p�ɒ�����؂�l�߂Ă��� htmlspecialchars() ��������
        if ($_conf['ktai']) {
            $tt_max_len = $_conf['sb_ttitle_max_len_k'];
            $tt_trim_len = $_conf['sb_ttitle_trim_len_k'];
            $tt_trip_pos = $_conf['sb_ttitle_trim_pos_k'];
        } else {
            $tt_max_len = $_conf['sb_ttitle_max_len'];
            $tt_trim_len = $_conf['sb_ttitle_trim_len'];
            $tt_trip_pos = $_conf['sb_ttitle_trim_pos'];
        }
        $ttitle_len = strlen($this->ttitle_hc);
        if ($tt_max_len > 0 && $ttitle_len > $tt_max_len && $ttitle_len > $tt_trim_len) {
            switch ($tt_trip_pos) {
            case -1:
                $a_ttitle = '... ';
                $a_ttitle .= mb_strcut($this->ttitle_hc, $ttitle_len - $tt_trim_len);
                break;
            case 0:
                $trim_len = floor($tt_trim_len / 2);
                $a_ttitle = mb_strcut($this->ttitle_hc, 0, $trim_len);
                $a_ttitle .= ' ... ';
                $a_ttitle .= mb_strcut($this->ttitle_hc, $ttitle_len - $trim_len);
                break;
            case 1:
            default:
                $a_ttitle = mb_strcut($this->ttitle_hc, 0, $tt_trim_len);
                $a_ttitle .= ' ...';
            }
            $this->ttitle_ht = htmlspecialchars($a_ttitle, ENT_QUOTES);
        } else {
            $this->ttitle_ht = $this->ttitle_hd;
        }
    }

    /**
     * fav, recent�p�̊g��idx���X�g���烉�C���f�[�^���擾����
     */
    function getThreadInfoFromExtIdxLine($l)
    {
        $la = explode('<>', rtrim($l));
        $this->host = $la[10];
        $this->bbs = $la[11];
        $this->key = $la[1];

        if (!$this->ttitle) {
            if ($la[0]) {
                $this->setTtitle(rtrim($la[0]));
            }
        }

        /*
        if ($la[6]) {
            $this->fav = $la[6];
        }
        */

        $this->getFavStatus();
    }

    /**
     * Set Path info
     */
    function setThreadPathInfo($host, $bbs, $key)
    {
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('setThreadPathInfo()');

        $this->host =   $host;
        $this->bbs =    $bbs;
        $this->key =    $key;

        $dat_host_dir = P2Util::datDirOfHost($this->host);
        $idx_host_dir = P2Util::idxDirOfHost($this->host);

        $this->keydat = $dat_host_dir . '/' . $this->bbs . '/' . $this->key . '.dat';
        $this->keyidx = $idx_host_dir . '/' . $this->bbs . '/' . $this->key . '.idx';

        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('setThreadPathInfo()');

        $this->getFavStatus();

        return true;
    }

    /**
     * �X���b�h�������ς݂Ȃ�true��Ԃ�
     */
    function isKitoku()
    {
        // if (file_exists($this->keyidx)) {
        if ($this->gotnum || $this->readnum || $this->newline > 1) {
            return true;
        }
        return false;
    }

    /**
     * �����X���b�h�f�[�^��key.idx����擾����
     */
    function getThreadInfoFromIdx()
    {
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('getThreadInfoFromIdx');

        if (!$lines = @file($this->keyidx)) {
            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromIdx');
            return false;
        }

        $key_line = rtrim($lines[0]);
        $lar = explode('<>', $key_line);
        if (!$this->ttitle) {
            if ($lar[0]) {
                $this->setTtitle(rtrim($lar[0]));
            }
        }

        if ($lar[5]) {
            $this->readnum = intval($lar[5]);

        // ���݊��[�u�i$lar[9] newline�̔p�~�j
        } elseif ($lar[9]) {
            $this->readnum = $lar[9] -1;
        }

        if ($lar[3]) {
            $this->gotnum = intval($lar[3]);

            if ($this->rescount) {
                $this->unum = $this->rescount - $this->readnum;
                // machi bbs ��subject�̍X�V�Ƀf�B���C������悤�Ȃ̂Œ������Ă���
                if ($this->unum < 0) {
                    $this->unum = 0;
                }
            }
        } else {
            $this->gotnum = 0;
        }

        if ($lar[6]) {
            $this->fav = $lar[6];
        }

        if ($lar[12]) {
            $this->datochiok = $lar[12];
        }

        /*
        // ����key.idx�̂��̃J�����͎g�p���Ă��Ȃ��Bdat�T�C�Y�͒��ڃt�@�C���̑傫����ǂݎ���Ē��ׂ�
        if ($lar[2]) {
            $this->length = $lar[2];
        }
        */
        if ($lar[4]) { $this->modified = $lar[4]; }

        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromIdx');

        return $key_line;
    }

    /**
     * ���[�J��DAT�̃t�@�C���T�C�Y���擾����
     */
    function getDatBytesFromLocalDat()
    {
        clearstatcache();
        return $this->length = intval(@filesize($this->keydat));
    }

    /**
     * subject.txt �̈�s����X�������擾����
     */
    function getThreadInfoFromSubjectTxtLine($l)
    {
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('getThreadInfoFromSubjectTxtLine()');

        if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $l, $matches)) {
            $this->isonline = true;
            $this->key = $matches[1];
            $this->setTtitle(rtrim($matches[4]));

            $this->rescount = $matches[6];
            if ($this->readnum) {
                $this->unum = $this->rescount - $this->readnum;
                // machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
                if ($this->unum < 0) {
                    $this->unum = 0;
                }
            }

            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromSubjectTxtLine()');
            return TRUE;
        }

        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromSubjectTxtLine()');
        return FALSE;
    }

    /**
     * �X���^�C�g���擾���\�b�h
     */
    function setTitleFromLocal()
    {
        if (!isset($this->ttitle)) {

            if ($this->datlines) {
                $firstdatline = rtrim($this->datlines[0]);
                $d = $this->explodeDatLine($firstdatline);
                $this->setTtitle($d[4]);

            // ���[�J��dat��1�s�ڂ���擾
            } elseif (is_readable($this->keydat)) {
                $fd = fopen($this->keydat, "rb");
                $l = fgets($fd, 32800);
                fclose($fd);
                $firstdatline = rtrim($l);
                if (strstr($firstdatline, "<>")) {
                    $datline_sepa = "<>";
                } else {
                    $datline_sepa = ",";
                    $this->dat_type = "2ch_old";
                }
                $d = explode($datline_sepa, $firstdatline);
                $this->setTtitle($d[4]);

                // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
                if (P2Util::isHostBe2chNet($this->host)) {
                    $ttitle = mb_convert_encoding($this->ttitle, 'SJIS-win', 'eucJP-win');
                    $this->setTtitle($ttitle);
                }
            }

        }

        return $this->ttitle;
    }

    /**
     * ���X��URL��Ԃ�
     */
    function getMotoThread($original = false)
    {
        global $_conf;

        $mobile = (!$_conf['ktai'] || $_conf['iphone'] || $original) ? false : true;

        // 2ch�n
        if (P2Util::isHost2chs($this->host)) {
            // PC
            if (!$mobile) {
                $motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$this->ls}";
            // �g��
            } else {
                if (P2Util::isHostBbsPink($this->host)) {
                    $motothre_url = "http://{$this->host}/test/r.i/{$this->bbs}/{$this->key}/{$this->ls}";
                } else {
                    $mail = urlencode($_conf['my_mail']);
                    // c.2ch��l�w��ɔ�Ή��Ȃ̂ŁA�����n
                    $ls = (substr($this->ls, 0, 1) == 'l') ? 'n' : $this->ls;
                    $motothre_url = "http://c.2ch.net/test/--3!mail={$mail}/{$this->bbs}/{$this->key}/{$ls}";
                }
            }

        // �܂�BBS
        } elseif (P2Util::isHostMachiBbs($this->host)) {
            $motothre_url = "http://{$this->host}/bbs/read.pl?BBS={$this->bbs}&KEY={$this->key}";
            if ($mobile) { $motothre_url .= '&IMODE=TRUE'; }

        // �܂��т˂���
        } elseif (P2Util::isHostMachiBbsNet($this->host)) {
            $motothre_url = "http://{$this->host}/test/read.cgi?bbs={$this->bbs}&key={$this->key}";
            if ($mobile) { $motothre_url .= '&imode=true'; }

        // JBBS�������
        } elseif (P2Util::isHostJbbsShitaraba($this->host)) {
            list($host, $category) = explode('/', P2Util::adjustHostJbbs($this->host), 2);
            $bbs_cgi = ($mobile) ? 'i.cgi' : 'read.cgi';
            $motothre_url = "http://{$host}/bbs/{$bbs_cgi}/{$category}/{$this->bbs}/{$this->key}/{$this->ls}";

        // ���̑�
        } else {
            $motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$this->ls}";
        }

        return $motothre_url;
    }

    /**
     * �����i���X/���j���Z�b�g����
     */
    function setDayRes($nowtime = false)
    {
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('setDayRes()');

        if (!isset($this->key) || !isset($this->rescount)) {
            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('setDayRes()');
            return false;
        }

        if (!$nowtime) {
            $nowtime = time();
        }
        if ($pastsc = $nowtime - $this->key) {
            $this->dayres = $this->rescount / $pastsc * 60 * 60 * 24;
            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('setDayRes()');
            return true;
        }

        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('setDayRes()');
        return false;
    }

    /**
     * ���X�Ԋu�i����/���X�j���擾����
     */
    function getTimePerRes()
    {
        $noresult_st = "-";

        if (!isset($this->dayres)) {
            if (!$this->setDayRes(time())) {
                return $noresult_st;
            }
        }

        if ($this->dayres <= 0) {
            return $noresult_st;

        } elseif ($this->dayres < 1/365) {
            $spd = 1/365 / $this->dayres;
            $spd_suffix = "�N";
        } elseif ($this->dayres < 1/30.5) {
            $spd = 1/30.5 / $this->dayres;
            $spd_suffix = "����";
        } elseif ($this->dayres < 1) {
            $spd = 1 / $this->dayres;
            $spd_suffix = "��";
        } elseif ($this->dayres < 24) {
            $spd = 24 / $this->dayres;
            $spd_suffix = "����";
        } elseif ($this->dayres < 24*60) {
            $spd = 24*60 / $this->dayres;
            $spd_suffix = "��";
        } elseif ($this->dayres < 24*60*60) {
            $spd = 24*60*60 / $this->dayres;
            $spd_suffix = "�b";
        } else {
            $spd = 1;
            $spd_suffix = "�b�ȉ�";
        }
        if ($spd > 0) {
            $spd_st = sprintf("%01.1f", @round($spd, 2)) . $spd_suffix;
        } else {
            $spd_st = "-";
        }
        return $spd_st;
    }

    /**
     * ���C�ɓ���o�^��Ԃ��擾����
     */
    function getFavStatus()
    {
        global $_conf;
        
        if (!$_conf['expack.misc.multi_favs']) {
            return;
        }

        $this->favs = array();
        foreach ($_conf['favlists'] as $num => $favlist) {
            $this->favs[$num] = false;
            foreach ($favlist as $fav) {
                if ($this->key == $fav['key'] && $this->bbs == $fav['bbs']) {
                    $this->favs[$num] = true;
                    break;
                }
            }
        }
    }
}
