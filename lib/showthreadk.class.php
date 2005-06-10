<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 - �X���b�h��\������ �N���X �g�їp
*/

require_once (P2_LIBRARY_DIR . '/showthread.class.php');
require_once (P2EX_LIBRARY_DIR . '/expack_loader.class.php');

ExpackLoader::loadActiveMona();
ExpackLoader::loadImageCache();

class ShowThreadK extends ShowThread {

    var $activemona; // �A�N�e�B�u���i�[�N���X�̃C���X�^���X
    var $am_aaryaku = FALSE;

    var $thumbnailer; // �T���l�C���쐬�N���X�̃C���X�^���X
    var $img_memo; // DB�̉摜���ɕt�����郁���iUTF-8�G���R�[�h�����X���^�C�j
    var $img_memo_query;

    /**
     * �R���X�g���N�^ (PHP4 style)
     */
    function ShowThreadK(&$aThread)
    {
        $this->__construct($aThread);
    }

    /**
     * �R���X�g���N�^ (PHP5 style)
     */
    function __construct(&$aThread)
    {
        parent::__construct($aThread);

        global $_conf, $_exconf;

        // URL���������n���h����o�^
        $this->url_handlers = array(
            array('this' => 'plugin_link2ch'),
            array('this' => 'plugin_linkMachi'),
            array('this' => 'plugin_linkJBBS'),
            array('this' => 'plugin_link2chKako'),
            array('this' => 'plugin_link2chSubject'),
        );
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            $this->url_handlers[] = array('this' => 'plugin_imageCache2');
        } elseif ($_conf['k_use_picto']) {
            $this->url_handlers[] = array('this' => 'plugin_viewImage');
        }
        $this->url_handlers[] = array('this' => 'plugin_linkURL');

        // �T���l�C���\����������ݒ�
        if (!isset($GLOBALS['pre_thumb_unlimited']) || !isset($GLOBALS['pre_thumb_limit_k'])) {
            if (isset($_conf['pre_thumb_limit_k']) && $_conf['pre_thumb_limit_k'] >= 0) {
                $GLOBALS['pre_thumb_limit_k'] = $_conf['pre_thumb_limit_k'];
                $GLOBALS['pre_thumb_unlimited'] = FALSE;
            } else {
                $GLOBALS['pre_thumb_limit_k'] = NULL;   // �k���l����isset()��FALSE��Ԃ�
                $GLOBALS['pre_thumb_unlimited'] = TRUE;
            }
        }
        $GLOBALS['pre_thumb_ignore_limit'] = FALSE;

        // �A�N�e�B�u���i�[������
        if (P2_ACTIVEMONA_AVAILABLE) {
            ExpackLoader::initActiveMona($this);
        }

        // ImageCache2������
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            ExpackLoader::initImageCache($this);
        }
    }

    /**
     * Dat��HTML�ɕϊ��\������
     */
    function datToHtml()
    {

        if (!$this->thread->resrange) {
            echo '<p><b>p2 error: {$this->resrange} is FALSE at datToHtml()</b></p>';
            return false;
        }

        $start = $this->thread->resrange['start'];
        $to = $this->thread->resrange['to'];
        $nofirst = $this->thread->resrange['nofirst'];

        // 1��\��
        if (!$nofirst) {
            echo $this->transRes(1);
        }

        for ($i = $start; $i <= $to; $i++) {
            if (!$nofirst && $i == 1) {
                continue;
            }
            if (!isset($this->pDatLines[$i])) {
                $this->thread->readnum = $i-1;
                break;
            }
            echo $this->transRes($i);
            flush();
        }

        //$s2e = array($start, $i-1);
        //return $s2e;
        return true;
    }


    /**
     * Dat���X��HTML���X�ɕϊ�����
     *
     * ���� - ���X�ԍ�
     */
    function transRes($i)
    {
        global $_conf, $_exconf;
        global $STYLE, $mae_msg, $res_filter, $filter_range;
        global $ngaborns_hits;

        $tores = '';
        $rpop = '';

        $name = $this->pDatLines[$i]['name'];
        $mail = $this->pDatLines[$i]['mail'];
        $date_id = $this->transDateId($i);
        $msg = $this->pDatLines[$i]['msg'];

        // {{{ transRes - �t�B���^�����O�E���ځ[��ENG�EAA�`�F�b�N

        // �t�B���^�����O
        if (!empty($filter_range['to']) && !$this->filterMatch($i)) {
            return '';
        }

        // ���ځ[��`�F�b�N
        $aborned_res = "<div id=\"r{$i}\" name=\"r{$i}\">&nbsp;</div>\n"; //���O
        $aborned_res .= ""; //���e
        if ($this->checker->abornCheck($i)) {
            $ngaborns_hits["aborn_{$aborn_hit_field}"]++;
            return $aborned_res;
        }

        // NG�`�F�b�N
        if (!$_GET['nong']) {
            $ng_fields = $this->checker->ngCheck($i);
            foreach ($ng_fields as $ng_hit_field => $ng_hit_value) {
                $ngaborns_hits["ng_{$ng_hit_field}"]++;
            }
        }

        // AA�`�F�b�N
        if ($this->am_aaryaku && $this->activemona->detectAA($msg)) {
            if ($this->am_aaryaku == 2) {
                return $aborned_res;
            } elseif (!$_GET['nong']) {
                $ngaborns_hits['ng_aa']++;
                $ng_fields['aa'] = TRUE;
            }
        }

        // }}}

        //=============================================================
        // �܂Ƃ߂ďo��
        //=============================================================

        $name = $this->transName($name); // ���OHTML�ϊ�
        $msg = $this->transMsg($msg, $i); //���b�Z�[�WHTML�ϊ�

        // {{{ transRes - NG���[�h�ϊ�

        if (!$_GET['nong']) {

            $_ng_color = ($_exconf['ubiq']['c_ngword']) ? $_exconf['ubiq']['c_ngword'] : $STYLE['read_ngword'];

            // NG�̗��R��ݒ�
            $a_ng_msg = '';
            // AA��
            if (isset($ng_fields['aa'])) {
                $a_ng_msg = $this->am_aaryaku_msg;

            // �s����������
            } elseif (isset($ng_fields['lines'])) {
                $a_ng_msg = $ng_fields['lines'] . 'lines';

            // ���b�Z�[�W��NG���[�h���܂�
            } elseif (isset($ng_fields['msg'])) {
                $a_ng_msg = 'NGܰ��:' . $ng_fields['msg'];

            // ���ځ[�񃌃X���Q��
            } elseif (isset($ng_fields['aborn'])) {
                $a_ng_msg = '�A��NG(���-�):&gt;&gt;' . $ng_fields['aborn'];

            // NG���X���Q��
            } elseif (isset($ng_fields['aborn'])) {
                $a_ng_msg = '�A��NG:&gt;&gt;' . $ng_fields['chain'];
            }

            // NG���b�Z�[�W�ϊ�
            if ($a_ng_msg) {
                $a_ng_msg = "<s><font color=\"{$_ng_color}\">{$a_ng_msg}</font></s> ";
            }
            if ($ng_fields) {
                $msg = "{$a_ng_msg}<a href=\"{$this->read_url_base}&amp;ls={$i}&amp;k_continue=1&amp;nong=1\">�m</a>";
            }

            // NG�l�[���ϊ�
            if (isset($ng_fields['name'])) {
                $name = "<s><font color=\"{$_ng_color}\">{$name}</font></s>";
            }

            // NG���[���ϊ�
            if (isset($ng_fields['mail'])) {
                $mail = "<s><font color=\"{$_ng_color}\">{$mail}</font></s>";
            }

            // NGID�ϊ�
            if (isset($ng_fields['id'])) {
                $date_id = "<s><font color=\"{$_ng_color}\">{$date_id}</font></s>";
            }

        }

        // }}}

        /*
        // �u��������V���v�摜��}��========================
        if ($i == $this->thread->readnum + 1) {
            $tores .=<<<EOP
                <div><img src="img/image.png" alt="�V�����X" border="0" vspace="4"></div>
EOP;
        }
        */
        if ($this->thread->onthefly) { // ontheflyresorder
            $GLOBALS['newres_to_show_flag'] = true;
            $_ontefly_color = ($_exconf['ubiq']['c_onthefly']) ? $_exconf['ubiq']['c_onthefly'] : '#00aa00';
            $tores .= "<div id=\"r{$i}\" name=\"r{$i}\">[<font color=\"{$_ontefly_color}'\">{$i}</font>]"; //�ԍ��i�I���U�t���C���j
        } elseif ($i > $this->thread->readnum) {
            $GLOBALS['newres_to_show_flag'] = true;
            $_newres_color = ($_exconf['ubiq']['c_newres']) ? $_exconf['ubiq']['c_newres'] : $STYLE['read_newres_color'];
            $tores .= "<div id=\"r{$i}\" name=\"r{$i}\">[<font color=\"{$_newres_color}\">{$i}</font>]"; //�ԍ��i�V�����X���j
        } else {
            $tores .= "<div id=\"r{$i}\" name=\"r{$i}\">[{$i}]"; //�ԍ�
        }
        $tores .= $name.': '; // ���O
        if ($mail) { $tores .= $mail.': '; } // ���[��
        $tores .= $date_id."<br>\n"; // ���t��ID
        $tores .= $rpop; // ���X�|�b�v�A�b�v�p���p
        $tores .= "{$msg}</div><hr>\n"; //���e

        // �������}��========================
        if ($_exconf['bookmark']['*'] && $i > 0 && $i == $this->thread->readhere) {
            $tores .= "<div id=\"readhere\">{$_exconf['bkmk']['marker_k']}</div><hr>\n";
        }

        return $tores;
    }

    /**
     * ���O��HTML�p�ɕϊ�����
     */
    function transName($name)
    {
        global $_conf;
        $nameID = '';

        // ID�t�Ȃ番������
        if (preg_match("/(.*)(��.*)/", $name, $matches)) {
            $name = $matches[1];
            $nameID = $matches[2];
        }

        // ���������p���X�����N��
        // </b>�`<b> �́A�z�X�g��g���b�v�Ȃ̂Ń}�b�`�����Ȃ�
        $pettern = '/^( ?(?:&gt;|��)* ?)?([1-9]\d{0,3})(?=\\D|$)/';
        $name && $name = preg_replace_callback($pettern, array($this, 'quote_res_callback'), $name, 1);

        if ($nameID) { $name = $name . $nameID; }

        $name = $name.' '; // �����������

        $name = strip_tags($name, '<a>');

        return $name;
    }


    //============================================================================
    // transMsg --  dat�̃��X���b�Z�[�W��HTML�\���p���b�Z�[�W�ɕϊ����郁�\�b�h
    // string transMsg(string str)
    //============================================================================
    function transMsg($msg, $mynum)
    {
        global $_conf, $_exconf;
        global $res_filter, $word_fm, $k_filter_marker;
        global $pre_thumb_ignore_limit;

        // 2ch���`����dat
        if ($this->thread->dat_type == "2ch_old") {
            $msg = str_replace("���M", ",", $msg);
            $msg = preg_replace("/&amp([^;])/", "&\$1", $msg);
        }

        // Safari���瓊�e���ꂽ�����N���`���_�̕��������␳�i�����ɂ͕��������Ƃ͂�����ƈႤ�j
        $msg = preg_replace('{(h?t?tp://[\\w.\\-]+/)�`([\\w.\\-%]+/?)}', '$1~$2', $msg);

        // >>1�̃����N����������O��
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;[1-9][\\d\\-]*)</[Aa]>}', '$1', $msg);

        // �傫������
        if (!$_GET['k_continue'] && strlen($msg) > $_conf['ktai_res_size']) {
            // <br>�ȊO�̃^�O���������A������؂�l�߂�
            $msg = strip_tags($msg, '<br>');
            $msg = mb_strcut($msg, 0, $_conf['ktai_ryaku_size']);
            $msg = preg_replace('/ *<[^>]*$/i', '', $msg);

            // >>1, >1, ��1, ����1�����p���X�|�b�v�A�b�v�����N��
            // URL�͓r���Ő؂��\�������Ȃ荂���̂Ń����N���Ȃ�
            $msg = preg_replace_callback('/((?:&gt;|��)+ ?)([1-9][0-9\\-,]+)/', array($this, 'quote_res_callback'), $msg);

            $msg .= " <a href=\"{$this->read_url_base}&amp;ls={$mynum}&amp;k_continue=1&amp;offline=1\">��</a>";
            return $msg;
        }

        // �V�����X�̉摜�͕\�������𖳎�����ݒ�Ȃ�
        if ($mynum > $this->thread->readnum && $_exconf['imgCache']['newres_ignore_limit']) {
            $pre_thumb_ignore_limit = TRUE;
        }

        // ���p��URL�Ȃǂ������N
        $msg = preg_replace_callback($this->str_to_link_regex, array($this, 'link_callback'), $msg);

        $pre_thumb_ignore_limit = FALSE;

        // �t�B���^�F����
        if ($k_filter_marker && $word_fm && $res_filter['match'] == 'on' && $res_filter['field'] &&
            ($res_filter['field'] == 'msg' || $res_filter['field'] == 'hole')
        ) {
            $msg = StrCtl::filterMarking($word_fm, $msg, $k_filter_marker);
        }

        return $msg;
    }

    // {{{ �R�[���o�b�N���\�b�h

    /**
     * �������N�Ώە�����̎�ނ𔻒肵�đΉ������֐�/���\�b�h�ɓn��
     */
    function link_callback($s)
    {
        global $_conf, $_exconf;

        // {{{ preg_replace_callback()�ł͖��O�t���ŃL���v�`���ł��Ȃ��̂Ń}�b�s���O

        if (!isset($s['link'])) {
            $s['link']  = $s[1];
            $s['quote'] = $s[4];
            $s['url']   = $s[7];
            $s['id']    = $s[10];
        }

        // }}
        // {{{ �}�b�`�����T�u�p�^�[���ɉ����ĕ���

        // �����N
        if ($s['link']) {
            if (preg_match('{ href=(["\'])?(.+?)(?(1)\\1)(?=[ >])}i', $s[2], $m)) {
                $url = $m[2];
                $str = $s[3];
            } else {
                return $s[3];
            }

        // ���p
        } elseif ($s['quote']) {
            if (strstr($s[6], '-')) {
                return $this->quote_res_range_callback(array($s['quote'], $s[5], $s[6]));
            }
            return preg_replace_callback('/((?:&gt;|��)+ ?)?([1-9]\\d{0,3})(?=\\D|$)/', array($this, 'quote_res_callback'), $s['quote']);

        // http or ftp ��URL
        } elseif ($s['url']) {
            if ($s[9] == 'ftp') {
                return $s[0];
            }
            $url = preg_replace('/^t?(tps?)$/', 'ht$1', $s[8]) . '://' . $s[9];
            $str = $s['url'];

        // ID
        } elseif ($s['id'] && $_exconf['flex']['idlink_k']) {
            return $this->idfilter_callback(array($s['id'], $s[11]));

        // ���̑��i�\���j
        } else {
            return strip_tags($s[0]);
        }

        // }}}
        // {{{ URL�̑O����

        // ime.nu���O��
        $url = preg_replace('|^([a-z]+://)ime\.nu/|', '$1', $url);

        // URL���p�[�X
        $purl = @parse_url($url);
        if (!$purl || !isset($purl['host']) || !strstr($purl['host'], '.') || $purl['host'] == '127.0.0.1') {
            return $str;
        }

        // }}}
        // {{{ URL��ϊ�

        foreach ($this->url_handlers as $handler) {
            //if (is_array($handler)) {
                if (isset($handler['this'])) {
                    if (FALSE !== ($link = call_user_func(array($this, $handler['this']), $url, $purl, $str))) {
                        return $link;
                    }
                } elseif (isset($handler['class']) && isset($handler['method'])) {
                    if (FALSE !== ($link = call_user_func(array($handler['class'], $handler['method']), $url, $purl, $str))) {
                        return $link;
                    }
                } elseif (isset($handler['function'])) {
                    if (FALSE !== ($link = call_user_func($handler['function'], $url, $purl, $str))) {
                        return $link;
                    }
                }
            /*} elseif (is_string($handler)) {
                $function = explode('::', $handler);
                if (isset($function[1])) {
                    if ($function[0] == 'this') {
                        if (FALSE !== ($link = call_user_func(array($this, $function[1], $url, $purl, $str))) {
                            return $link;
                        }
                    } else 
                        if (FALSE !== ($link = call_user_func(array($function[0], $function[1]), $url, $purl, $str))) {
                            return $link;
                        }
                    }
                } else {
                    if (FALSE !== ($link = call_user_func($handler, $url, $purl, $str))) {
                        return $link;
                    }
                }
            }*/
        }

        // }}}

        return $str;
    }

    /**
     * ���g�їp�O��URL�ϊ�
     */
    function ktai_exturl_callback($s)
    {
        global $_conf, $_exconf;

        $in_url = $s[1];

        // �ʋ΃u���E�U
        $tsukin_link = '';
        if ($_conf['k_use_tsukin']) {
            $tsukin_url = 'http://www.sjk.co.jp/c/w.exe?y='.urlencode($in_url);
            if ($_conf['through_ime']) {
                $tsukin_url = P2Util::throughIme($tsukin_url);
            }
            $tsukin_link = '<a href="'.$tsukin_url.'">��</a>';
        }
        /*
        // jig�u���E�UWEB http://bwXXXX.jig.jp/fweb/?_jig_=
        $jig_link = '';

        $jig_url = 'http://bw5032.jig.jp/fweb/?_jig_='.urlencode($in_url);
        if ($_conf['through_ime']) {
            $jig_url = P2Util::throughIme($jig_url);
        }

        $jig_link = '<a href="'.$jig_url.'">j</a>';
        */

        $sepa ='';
        if ($tsukin_link && $jig_link) {
            $sepa = '|';
        }

        $ext_pre = '';
        if ($tsukin_link || $jig_link) {
            $ext_pre = '('.$tsukin_link.$sepa.$jig_link.')';
        }

        if ($_conf['through_ime']) {
            $in_url = P2Util::throughIme($in_url);
        }
        $r = $ext_pre.'<a href="' . $in_url . '">' . $s[2] . '</a>';

        return $r;
    }

    /**
     * �����p�ϊ�
     */
    function quote_res_callback($s)
    {
        global $_conf, $_exconf;

        list($full, $qsign, $appointed_num) = $s;
        if ($appointed_num == '-') {
            return $s[0];
        }
        $qnum = intval($appointed_num);
        if ($qnum < 1 || $qnum > $this->thread->rescount) {
            return $s[0];
        }

        $read_url = $this->read_url_base . '&amp;offline=1&amp;ls=' . $appointed_num;
        return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$qsign}{$appointed_num}</a>";
    }

    /**
     * �����p�ϊ��i�͈́j
     */
    function quote_res_range_callback($s)
    {
        global $_conf, $_exconf;

        list($full, $qsign, $appointed_num) = $s;
        if ($appointed_num == '-') {
            return $s[0];
        }

        list($from, $to) = explode('-', $appointed_num);
        if (!$from) {
            $from = 1;
        } elseif ($from < 1 || $from > $this->thread->rescount) {
            return $s[0];
        }
        // read.php�ŕ\���͈͂𔻒肷��̂ŏ璷�ł͂���
        if (!$to) {
            $to = min($from + $_conf['k_rnum_range'] - 1, $this->thread->rescount);
        } else {
            $to = min($to, $from + $_conf['k_rnum_range'] - 1, $this->thread->rescount);
        }

        $read_url = $this->read_url_base . '&amp;offline=1&amp;ls=' . $from .'-' . $to;

        return "<a href=\"{$read_url}\">{$qsign}{$appointed_num}</a>";
    }

    /**
     * ��ID�t�B���^�����O�����N�ϊ�
     */
    function idfilter_callback($s)
    {
        global $_conf, $_exconf;

        $idstr = $s[0]; // ID:xxxxxxxxxx
        $id = $s[1];    // xxxxxxxxxx

        if (isset($this->thread->idcount[$id]) && $this->thread->idcount[$id] > 0) {
            $num_ht = '('.$this->thread->idcount[$id].')';
        } else {
            return $idstr;
        }

        $filter_url = $this->read_url_base . '&amp;ls=all&amp;offline=1&amp;idpopup=1&amp;field=id&amp;method=just&amp;match=on&amp;word=' . rawurlencode($id);

        return "<a href=\"{$filter_url}\">{$idstr}{$num_ht}</a>";
    }

    // }}}
    // {{{ ���[�e�B���e�B���\�b�h

    /**
     * ���t�EID���č\�z���ABE�v���t�@�C��������΃����N����
     */
    function transDateId($resnum)
    {
        global $_conf, $_exconf;

        if (!isset($this->pDatLines[$resnum])) {
            return '';
        }
        $p = &$this->pDatLines[$resnum]['p_dateid'];

        // ���t
        if ($_exconf['etc']['datetime_rewrite_k']) {
            if (isset($p['timestamp'])) {
                $epoch = $p['timestamp'];
            } else {
                $epoch = $p['timestamp'] = $this->datetimeToEpoch($p['date'], $p['time']);
            }
            if ($epoch != -1) {
                $date_id = date($_exconf['etc']['datetime_format_k'], $epoch);
                if (strstr($_exconf['etc']['datetime_format'], '%w%')) {
                    $date_id = preg_replace('/%([0-6])%/e', '$_exconf["etc"]["datetime_weekday_k"][$1]', $date_id);
                }
            } else {
                $date_id = $p['date'].' '.$p['time'];
            }
        } else {
            $date_id = $p['date'].' '.$p['time'];
        }

        // ID
        if (isset($p['id'])) {
            if ($_exconf['flex']['idlink_k'] == 1 && $this->thread->idcount[$p['id']] > 1) {
                $date_id .= ' '. $this->idfilter_callback(array('ID:'.$p['id'], $p['id']));
            } else {
                $date_id .= ' ID:' . $p['id'];
            }
            if (isset($p['idopt'])) {
                $date_id .= $p['idopt'];
            }
        }

        // BE
        if ($p_dateid['be']) {
            $be_prof_ref = rawurlencode("http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$GLOBALS['ls']}");
            $date_id .= " <a href=\"http://be.2ch.net/test/p.php?i={$p_dateid['beid']}&u=d:{$be_prof_ref}\">Lv.{$p_dateid['belv']}</a>";
        }

        return $date_id;
    }

    // }}}
    // {{{ link_callback()����Ăяo�����URL�����������\�b�h

    // �����̃��\�b�h�͈����������Ώۃp�^�[���ɍ��v���Ȃ���FALSE��Ԃ��A
    // link_callback()��FALSE���Ԃ��Ă����$url_handlers�ɓo�^����Ă��鎟�̊֐�/���\�b�h�ɏ��������悤�Ƃ���B

    /**
     * URL�����N
     */
    function plugin_linkURL($url, $purl, $str)
    {
        global $_conf, $_exconf;

        if (isset($purl['scheme'])) {
            // �g�їp�O��URL�ϊ�
            if ($_conf['k_use_tsukin']) {
                return $this->ktai_exturl_callback(array('', $url, $str));
            }
            // ime
            if ($_conf['through_ime']) {
                $link_url = P2Util::throughIme($url);
                $type = 'url';
                if (preg_match('/\.([0-9A-Za-z]{1,5})$/', $url, $matches)) {
                    $_type = strtolower($matches[1]);
                    if (!preg_match('/^(?:[sp]?html?|cgi|phps?|pl|py|rb|[aj]sp)$/', $_type)) {
                        $type = $_type;
                    }
                }
                $title = preg_replace('|^.+?://([^/]+)(/.*)?$|', '$1', $str);
                $link_title = "[{$type}:{$title}]";
            } else {
                $link_url = $url;
                $link_title = $str;
            }
            $link = "<a href=\"{$link_url}\">{$link_title}</a>";
            return $link;
        }
        return FALSE;
    }

    /**
     * 2ch bbspink  �����N
     */
    function plugin_link2chSubject($url, $purl, $str)
    {
        global $_conf, $_exconf;

        if (preg_match('{^http://(\\w+\\.(?:2ch\\.net|bbspink\\.com))/([^/]+)/$}', $url, $m)) {
            $subject_url = "{$_conf['subject_php']}?host={$m[1]}&amp;bbs={$m[2]}";
            return "<a href=\"{$url}\">{$str}</a> [<a href=\"{$subject_url}{$_conf['k_at_a']}\">��p2�ŊJ��</a>]";
        }
        return FALSE;
    }

    /**
     * 2ch bbspink  �X���b�h�����N
     */
    function plugin_link2ch($url, $purl, $str)
    {
        global $_conf, $_exconf;

        if (preg_match('{^http://(\\w+\\.(?:2ch\\.net|bbspink\\.com))/test/read\\.cgi/([^/]+)/([0-9]+)(?:/([^/]+)?)?$}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}&amp;bbs={$m[2]}&amp;key={$m[3]}&amp;ls={$m[4]}";
            return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$str}</a>";
        }
        return FALSE;
    }

    /**
     * 2ch�ߋ����Ohtml
     */
    function plugin_link2chKako($url, $purl, $str)
    {
         global $_conf, $_exconf;

        if (preg_match('{^http://(\\w+(?:\\.2ch\\.net|\\.bbspink\\.com))(?:/[^/]+/)?/([^/]+)/kako/\\d+(?:/\\d+)?/(\\d+)\\.html$}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}&amp;bbs={$m[2]}&amp;key={$m[3]}&amp;kakolog=" . rawurlencode($url);
            return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$str}</a>";
        }
        return FALSE;
    }

    /**
     * �܂�BBS / JBBS���������  �������N
     */
    function plugin_linkMachi($url, $purl, $str)
    {
        global $_conf, $_exconf;

        if (preg_match('{^http://((\\w+\\.machibbs\\.com|\\w+\\.machi\\.to|jbbs\\.livedoor\\.(?:jp|com)|jbbs\\.shitaraba\\.com)(/\\w+)?)/bbs/read\\.(?:pl|cgi)\\?BBS=(\\w+)(?:&amp;|&)KEY=([0-9]+)(?:(?:&amp;|&)START=([0-9]+))?(?:(?:&amp;|&)END=([0-9]+))?(?=&|$)}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}&amp;bbs={$m[4]}&amp;key={$m[5]}";
            if ($m[6] || $m[7]) {
                $read_url .= "&amp;ls={$m[6]}-{$m[7]}";
            }
            return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$str}</a>";
        }
        return FALSE;
    }

    /**
     * JBBS���������  �������N
     */
    function plugin_linkJBBS($url, $purl, $str)
    {
        global $_conf, $_exconf;

        if (preg_match('{^http://(jbbs\\.livedoor\\.(?:jp|com)|jbbs\\.shitaraba\\.com)/bbs/read\\.cgi/(\\w+)/(\\d+)/(\\d+)(?:/((\\d+)?-(\\d+)?|[^/]+)|/?)$}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}/{$m[2]}&amp;bbs={$m[3]}&amp;key={$m[4]}&amp;ls={$m[5]}";
            return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$str}</a>";
        }
        return FALSE;
    }

    /**
     * �摜URL��pic.to�ϊ�
     */
    function plugin_viewImage($url, $purl, $str)
    {
        global $_conf, $_exconf;

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url) && empty($purl['query'])) {
            $picto_url = 'http://pic.to/'.$purl['host'].$purl['path'];
            $picto_tag = '<a href="'.$picto_url.'">(��)</a> ';
            if ($_conf['through_ime']) {
                $link_url  = P2Util::throughIme($url);
                $picto_url = P2Util::throughIme($picto_url);
            } else {
                $link_url = $url;
            }
            return "{$picto_tag}<a href=\"{$link_url}\">{$str}</a>";
        }
        return FALSE;
    }

    /**
     * �摜URL��ImageCache2�ϊ�
     */
    function plugin_imageCache2($url, $purl, $str)
    {
        global $_conf, $_exconf;
        global $pre_thumb_unlimited, $pre_thumb_ignore_limit, $pre_thumb_limit_k;

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url) && empty($purl['query'])) {
            // �C�����C���v���r���[�̗L������
            if ($pre_thumb_unlimited || $pre_thumb_ignore_limit || $pre_thumb_limit_k > 0) {
                $inline_preview_flag = TRUE;
                $inline_preview_done = FALSE;
            } else {
                $inline_preview_flag = FALSE;
                $inline_preview_done = FALSE;
            }

            $url_en = rawurlencode($url);
            $img_str = '[IC2:'.$purl['host'].':'.basename($purl['path']).']';

            $icdb = &new IC2DB_Images;

            // r=0:�����N;r=1:���_�C���N�g;r=2:PHP�ŕ\��
            // t=0:�I���W�i��;t=1:PC�p�T���l�C��;t=2:�g�їp�T���l�C��;t=3:���ԃC���[�W
            $img_url = 'ic2.php?r=0&amp;t=2&amp;uri=' . $url_en;

            // DB�ɉ摜��񂪓o�^����Ă����Ƃ�
            if ($icdb->get($url)) {

                // �E�B���X�Ɋ������Ă����t�@�C���̂Ƃ�
                if ($icdb->mime == 'clamscan/infected') {
                    return '[IC2:�E�B���X�x��]';
                }
                // ���ځ[��摜�̂Ƃ�
                if ($icdb->rank < 0) {
                    return '[IC2:���ځ[��摜]';
                }

                // �C�����C���v���r���[���L���̂Ƃ�
                if ($this->thumbnailer->ini['General']['inline'] == 1) {
                    // �t���X�N���[���摜������Ă���΁A�����N���X�V
                    /*$_img_url = $this->thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                    if (file_exists($_img_url)) {
                        $img_url = $_img_url;
                    }*/
                    $_prvw_url = $this->inline_prvw->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                    // �T���l�C���\���������ȓ��̂Ƃ�
                    if ($inline_preview_flag) {
                        // �v���r���[�摜������Ă��邩�ǂ�����img�v�f�̑���������
                        if (file_exists($_prvw_url)) {
                            $prvw_size = explode('x', $this->inline_prvw->calc($icdb->width, $icdb->height));
                            $img_str = "<img src=\"{$_prvw_url}\" width=\"{$prvw_size[0]}\" height=\"{$prvw_size[1]}\">";
                        } else {
                            $img_str = "<img src=\"ic2.php?r=1&amp;t=1&amp;uri={$url_en}\">";
                        }
                        $inline_preview_done = TRUE;
                    } else {
                        $img_str = '[p2:�����摜(�ݸ:' . $icdb->rank . ')]';
                    }
                }

                // �����X���^�C�����@�\��ON�ŃX���^�C���L�^����Ă��Ȃ��Ƃ���DB���X�V
                if (!is_null($this->img_memo) && !strstr($icdb->memo, $this->img_memo)){
                    $update = &new IC2DB_Images;
                    if (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                        $update->memo = $this->img_memo . ' ' . $icdb->memo;
                    } else {
                        $update->memo = $this->img_memo;
                    }
                    $update->whereAddQuoted('uri', '=', $url);
                    $update->update();
                }

            // �摜���L���b�V������Ă��Ȃ��Ƃ�
            // �����X���^�C�����@�\��ON�Ȃ�N�G����UTF-8�G���R�[�h�����X���^�C���܂߂�
            } else {
                // �摜���u���b�N���X�gor�G���[���O�ɂ��邩�m�F
                if (FALSE !== ($errcode = $icdb->ic2_isError($url))) {
                    return "<s>[IC2:�װ({$errcode})]</s>";
                }

                // �C�����C���v���r���[���L���ŁA�T���l�C���\���������ȓ��Ȃ�
                if ($this->thumbnailer->ini['General']['inline'] == 1 && $inline_preview_flag) {
                    $img_str = '<img src="ic2.php?r=1&amp;t=1&amp;uri=' . $url_en . $this->img_memo_query . '">';
                    $inline_preview_done = TRUE;
                } else {
                    $img_url .= $this->img_memo_query;
                }
            }

            // �\�����������f�N�������g
            if ($inline_preview_flag && $inline_preview_done) {
                $pre_thumb_limit_k--;
            }

            return "<a href=\"{$img_url}\">{$img_str}</a>";
        }
        return FALSE;
    }

    // }}}

}

?>
