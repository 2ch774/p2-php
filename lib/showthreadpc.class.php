<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 - �X���b�h��\������ �N���X PC�p
*/

require_once (P2_LIBRARY_DIR . '/showthread.class.php');
require_once (P2EX_LIBRARY_DIR . '/expack_loader.class.php');

ExpackLoader::loadActiveMona();
ExpackLoader::loadImageCache();
ExpackLoader::loadLiveView();

class ShowThreadPc extends ShowThread {

    var $quote_res_nums_checked; // �|�b�v�A�b�v�\�������`�F�b�N�ς݃��X�ԍ���o�^�����z��
    var $quote_res_nums_done; // �|�b�v�A�b�v�\�������L�^�ς݃��X�ԍ���o�^�����z��
    var $quote_check_depth; // ���X�ԍ��`�F�b�N�̍ċA�̐[�� checkQuoteResNums()

    var $activemona; // �A�N�e�B�u���i�[�N���X�̃C���X�^���X
    var $am_enabled = FALSE;
    var $am_aaryaku = FALSE;

    var $thumbnailer; // �T���l�C���쐬�N���X�̃C���X�^���X
    var $img_memo; // DB�̉摜���ɕt�����郁���iUTF-8�G���R�[�h�����X���^�C�j
    var $img_memo_query;

    var $lv_enabled = FALSE; // �����\���t���O
    var $arraycleaner; // �z�񏈗��N���X�̃C���X�^���X

    var $asyncObjName;  // �񓯊��ǂݍ��ݗpJavaScript�I�u�W�F�N�g��
    var $spmObjName;    // �X�}�[�g�|�b�v�A�b�v���j���[�pJavaScript�I�u�W�F�N�g��

    /**
     * �R���X�g���N�^ (PHP4 style)
     */
    function ShowThreadPc(&$aThread)
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
        } elseif ($_conf['preview_thumbnail']) {
            $this->url_handlers[] = array('this' => 'plugin_viewImage');
        }
        $this->url_handlers[] = array('this' => 'plugin_linkURL');

        // �T���l�C���\����������ݒ�
        if (!isset($GLOBALS['pre_thumb_unlimited']) || !isset($GLOBALS['pre_thumb_limit'])) {
            if (isset($_conf['pre_thumb_limit']) && $_conf['pre_thumb_limit'] >= 0) {
                $GLOBALS['pre_thumb_limit'] = $_conf['pre_thumb_limit'];
                $GLOBALS['pre_thumb_unlimited'] = FALSE;
            } else {
                $GLOBALS['pre_thumb_limit'] = NULL; // �k���l����isset()��FALSE��Ԃ�
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

        // �������[�h������
        if ($_exconf['liveView']['*'] == 1 || ($_exconf['liveView']['*'] == 2 &&
            (preg_match('/^live\d+\.2ch\.net/', $this->thread->host) || $this->thread->bbs == 'liveplus'))
        ) {
            ExpackLoader::initLiveView($this);
        }

        // �񓯊����X�|�b�v�A�b�v�ESPM������
        $jsObjId = md5($this->thread->keydat);
        $this->asyncObjName = 'asp_' . $jsObjId;
        $this->spmObjName = 'spm_' . $jsObjId;

    }

    /**
     * ��Dat��HTML�ɕϊ��\������
     */
    function datToHtml()
    {
        if (!$this->thread->resrange) {
            echo '<b>p2 error: {$this->resrange} is FALSE at datToHtml()</b>';
            return false;
        }

        $start = $this->thread->resrange['start'];
        $to = $this->thread->resrange['to'];
        $nofirst = $this->thread->resrange['nofirst'];

        $status_title = htmlspecialchars($this->thread->itaj).' / '.$this->thread->ttitle_hd;
        $status_title = str_replace("'", "\'", $status_title);
        $status_title = str_replace('"', "\'\'", $status_title);
        echo "<dl onmouseover=\"window.top.status='{$status_title}';\">";

        // �܂� 1 ��\��
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
        echo "</dl>\n";

        // $s2e = array($start, $i-1);
        // return $s2e;
        return true;
    }


    /**
     * �� Dat���X��HTML���X�ɕϊ�����
     *
     * ���� - ���X�ԍ�
     */
    function transRes($i)
    {
        global $_conf, $_exconf;
        global $STYLE, $mae_msg, $res_filter, $word_fm;
        global $ngaborns_hits;

        $tores = '';
        $rpop = '';
        if (basename($_SERVER['SCRIPT_NAME']) != basename($_conf['read_new_php'])) {
            $resAnchor = " id=\"r{$i}\"";
        } else {
            $resAnchor = '';
        }
        $resID = $i . 'of' . $this->thread->key;
        $resBodyID = 'rb' . $resID;

        $name = $this->pDatLines[$i]['name'];
        $mail = $this->pDatLines[$i]['mail'];
        $date_id = $this->transDateId($i);
        $msg = $this->pDatLines[$i]['msg'];

        // {{{ transRes - �t�B���^�����O�E���ځ[��ENG�EAA�`�F�b�N

        // �t�B���^�����O
        if (!empty($_conf['filtering']) && !$this->filterMatch($i)) {
            return '';
        }

        // ���ځ[��`�F�b�N
        $aborned_res = "<dt{$resAnchor} class=\"aborned\"><span>&nbsp;</span></dt>\n"; // ���O
        $aborned_res .= "<!-- <dd class=\"aborned\">&nbsp;</dd> -->\n"; // ���e
        if (($aborn_hit_field = $this->checker->abornCheck($i)) !== FALSE) {
            $ngaborns_hits["aborn_{$aborn_hit_field}"]++;
            return $aborned_res;
        }

        // NG�`�F�b�N
        $ng_fields = $this->checker->ngCheck($i);
        foreach ($ng_fields as $ng_hit_field => $ng_hit_value) {
            $ngaborns_hits["ng_{$ng_hit_field}"]++;
        }

        // AA�`�F�b�N
        if ($this->am_aaryaku && $this->activemona->detectAA($msg)) {
            if ($this->am_aaryaku == 2) {
                return $aborned_res;
            } else {
                $ngaborns_hits['ng_aa']++;
                $ng_fields['aa'] = TRUE;
            }
        }

        // }}}

        //=============================================================
        // ���X���|�b�v�A�b�v�\��
        //=============================================================
        if ($_conf['quote_res_view'] && !$_exconf['etc']['async_respop']) {
            $this->quote_check_depth = 0;
            $quote_res_nums = $this->checkQuoteResNums($i, $name, $msg);

            foreach ($quote_res_nums as $rnv) {
                if (!isset($this->quote_res_nums_done[$rnv])) {
                    $ds = $this->qRes($rnv);
                    $onPopUp_at = " onmouseover=\"showResPopUp('q{$rnv}of{$this->thread->key}',event)\" onmouseout=\"hideResPopUp('q{$rnv}of{$this->thread->key}')\"";
                    $rpop .=  "<dd id=\"q{$rnv}of{$this->thread->key}\" class=\"respopup\"{$onPopUp_at}><i>" . rtrim($ds) . "</i></dd>\n";
                    $this->quote_res_nums_done[$rnv] = true;
                }
            }
        }

        // transRes - �܂Ƃ߂ďo��
        //=============================================================
        // �܂Ƃ߂ďo��
        //=============================================================

        $name = $this->transName($name); // ���OHTML�ϊ�
        $msg = "<div id=\"{$resBodyID}\">" . $this->transMsg($msg, $i) . "</div>";

        // {{{ transRes - ActiveMona
        // �A�N�e�B�u���i�[
        if ($this->am_enabled && $_exconf['aMona']['*']) {
            $mona = $this->activemona->transAM($msg, $resBodyID, $this->thread->bbs);
        } else {
            $mona = '';
        }
        // }}}

        // {{{ transRes - NG���[�h�ϊ�

        // NG�u���b�N�pID��ݒ�
        // AA��
        if (isset($ng_fields['aa'])) {
            $ng_msg_type = $this->am_aaryaku_msg;
            $ng_msg_id = 'aang' . $ngaborns_hits['ng_aa'];

        // �s�������������������[�h�łȂ��i�������[�h�͕ʂɐݒ荀�ڂ�����j
        } elseif (isset($ng_fields['lines']) && !$this->lv_enabled) {
            $ng_msg_type = $ng_fields['lines'] . 'lines';
            $ng_msg_id = 'ngl' . $ngaborns_hits['ng_lines'];

        // ���b�Z�[�W��NG���[�h���܂�
        } elseif (isset($ng_fields['msg'])) {
            $ng_msg_type = 'NG���[�h�F' . $ng_fields['msg'];
            $ng_msg_id = 'ngmsg' . $ngaborns_hits['ng_msg'];

        // ���ځ[�񃌃X���Q��
        } elseif (isset($ng_fields['aborn'])) {
            $ng_msg_type = '�A��NG(���ځ[��)�F&gt;&gt;' . $ng_fields['aborn'];
            $ng_msg_id = 'nga' . $ngaborns_hits['ng_aborn'];

        // NG���X���Q��
        } elseif (isset($ng_fields['chain'])) {
            $ng_msg_type = '�A��NG�F&gt;&gt;' . $ng_fields['chain'];
            $ng_msg_id = 'ngc' . $ngaborns_hits['ng_chain'];

        // ���O��NG���[�h���܂�
        } elseif (isset($ng_fields['name'])) {
            $ng_msg_id = 'ngn' . $ngaborns_hits['ng_name'];

        // ���[����NG���[�h���܂�
        } elseif (isset($ng_fields['mail'])) {
            $ng_msg_id = 'ngm' . $ngaborns_hits['ng_mail'];

        // ID��NG���[�h���܂�
        } elseif (isset($ng_fields['id'])) {
            $ng_msg_id = 'ngid' . $ngaborns_hits['ng_id'];
        }

        $ng_format = "<s class=\"ngword\" onmouseover=\"document.getElementById('%s').style.display = 'block';\">%s</s>";

        // NG���b�Z�[�W�ϊ�
        if (isset($ng_msg_type)) {
            $show_ngmsg = sprintf($ng_format, $ng_msg_id, $ng_msg_type);
        } else {
            $show_ngmsg = '';
        }
        if (isset($ng_msg_id)) {
            $msg = "{$show_ngmsg}<div id=\"{$ng_msg_id}\" style=\"display:none;\">{$msg}</div>";
        }

        // NG�l�[���ϊ�
        if (isset($ng_fields['name'])) {
            $name = sprintf($ng_format, $ng_msg_id, $name);
        }

        // NG���[���ϊ�
        if (isset($ng_fields['mail'])) {
            $mail = sprintf($ng_format, $ng_msg_id, $mail);
        }

        // NGID�ϊ�
        if (isset($ng_fields['id'])) {
            $date_id = sprintf($ng_format, $ng_msg_id, $date_id);
        }

        // }}}

        /*
        // �u��������V���v�摜��}�� ========================
        if ($i == $this->thread->readnum + 1) {
            $tores .= "\n<div><img src=\"img/image.png\" alt=\"�V�����X\" border=\"0\" vspace=\"4\"></div>\n";
        }
        */

        // {{{ transRes - SPM
        // �X�}�[�g�|�b�v�A�b�v���j���[
        if ($_exconf['spm']['*'] == 2) {
            $spmEventHandler = " onclick=\"showSPM({$this->spmObjName},{$i},'{$resBodyID}',event);return false\"";
        } elseif ($_exconf['spm']['*']) {
            $spmEventHandler = " onmouseover=\"showSPM({$this->spmObjName},{$i},'{$resBodyID}',event)\" onmouseout=\"hideResPopUp('{$this->spmObjName}_spm')\"";
        } else {
            $spmEventHandler = '';
        }
        // }}}

        // {{{ transRes - Bookmark
        // �������}��
        if ($_exconf['bookmark']['*'] && $i > 0 && $i == $this->thread->readhere) {
            $msg .= "<table id=\"readhere\"><tr><td>{$_exconf['bkmk']['marker']}</td></tr></table>";
        }
        // }}}

        // {{{ transRes - LiveView
        // �������[�h
        if ($this->lv_enabled) {
            // ���b�Z�[�W�𕪊�
            $live_regex = '/^'
                . '(?P<ngword><s[^>]*?>.+?<\\/s>)?'
                . '(?P<fullbody>'
                    . '(?P<ngbegin><div id="(?P<ngid>ng[\\w\\-]+?)"[^>]*?>)?'
                    . '(?P<msgbegin><div id="(?P<msgid>rb[\\w\\-]+?)"[^>]*?>)?'
                    . '(?P<msgbody>.+?)'
                    . '(?(4)(?P<msgend><\\/div>))'
                    . '(?(2)(?P<ngend><\\/div>))'
                . ')'
                . '(?P<bkmk><table id="readhere">.+<\\/table>)?'
                . '$/';
            if (preg_match($live_regex, $msg, $live_match)) {
                // Live2ch�������_�����O
                include (P2EX_LIBRARY_DIR . '/liveview.inc.php');
                // ���������꒼��
                if ($live_match['bkmk']) {
                    $jikkyo_tores .= "<dd class=\"jikkyo\">{$live_match['bkmk']}</dd>\n";
                }
                return $jikkyo_tores;
            } else {
                // �p�^�[���Ƀ}�b�`���Ȃ������Ƃ��̓G���[��\��
                $tores .= '<dd><b>�������[�h error: ���b�Z�[�W�̕����Ɏ��s</b></dd>';
            }
        }
        // }}}

        $tores .= "<dt{$resAnchor}>";
        if ($this->thread->onthefly) {
            $GLOBALS['newres_to_show_flag'] = true;
            $tores .= "<span class=\"ontheflyresorder\">{$i}</span> �F"; //�ԍ��i�I���U�t���C���j
        } elseif ($i > $this->thread->readnum) {
            $GLOBALS['newres_to_show_flag'] = true;
            $tores .= "<a href=\"javascript:void(0);\" class=\"newres\"{$spmEventHandler}>{$i}</a> �F"; //�ԍ��i�V�����X���j
        } else {
            $tores .= "<a href=\"javascript:void(0);\" class=\"resnum\"{$spmEventHandler}>{$i}</a> �F"; //�ԍ�
        }
        $tores .= "<span class=\"name\"><b>{$name}</b></span>�F"; //���O

        // ���[��
        if ($mail) {
            if (strstr($mail, "sage") && $STYLE['read_mail_sage_color']) {
                $tores .= "<span class=\"sage\">{$mail}</span> �F";
            } elseif ($STYLE['read_mail_color']) {
                $tores .= "<span class=\"mail\">{$mail}</span> �F";
            } else {
                $tores .= $mail." �F";
            }
        }

        $tores .= $date_id; // ���t��ID
        $tores .= $mona; // AA�{�^��
        $tores .= "</dt>\n";
        $tores .= $rpop; // ���X�|�b�v�A�b�v�p���p
        $tores .= "<dd style=\"margin-bottom:2em\">{$msg}</dd>\n"; // ���e

        // �܂Ƃ߂ăt�B���^�F����
        if ($word_fm && $res_filter['match'] == 'on') {
            $tores = StrCtl::filterMarking($word_fm, $tores);
        }

        // }}}

        return $tores;
    }


    /**
     * >>1 ��\������ (���p�|�b�v�A�b�v�p)
     */
    function quoteOne()
    {
        global $_conf, $_exconf;

        if (!$_conf['quote_res_view']) {
            return false;
        }

        if ($_exconf['etc']['async_respop']) {
            $rpop = '';
        } else {
            $dummy_msg = '';
            $this->quote_check_depth = 0;
            $quote_res_nums = $this->checkQuoteResNums(0, "1", $dummy_msg);
            $rpop = '';
            foreach ($quote_res_nums as $rnv) {
                if (!isset($this->quote_res_nums_done[$rnv])) {
                    $ds = '';
                    if ($this->thread->ttitle_hd) {
                        $ds = "<b>{$this->thread->ttitle_hd}</b><br><br>";
                    }
                    $ds .= $this->qRes($rnv);
                    $onPopUp_at = " onmouseover=\"showResPopUp('q{$rnv}of{$this->thread->key}',event)\" onmouseout=\"hideResPopUp('q{$rnv}of{$this->thread->key}')\"";
                    $rpop .= "<div id=\"q{$rnv}of{$this->thread->key}\" class=\"respopup\"{$onPopUp_at}><i>" . $ds . "</i></div>\n";
                    $this->quote_res_nums_done[$rnv]=true;
                }
            }
        }
        $res1['q'] = $rpop;

        $m1 = '&gt;&gt;1';
        $res1['body'] = $this->transMsg($m1, 1);
        return $res1;
    }

    /**
     * ���X���pHTML
     */
    function qRes($i)
    {
        global $_conf, $_exconf, $word_fm;

        if (!Isset($this->pDatLines[$i])) {
            return false;
        }

        $name = $this->transName($this->pDatLines[$i]['name']);
        $mail = $this->pDatLines[$i]['mail'];
        $date_id = $this->transDateId($i);
        $msg = $this->pDatLines[$i]['msg'];

        $qresid = "qr{$i}of{$this->thread->key}";
        $msg = "<div id=\"{$qresid}\">" . $this->transMsg($msg, $i) . "</div>";

        // {{{ qRes - ActiveMona
        // �A�N�e�B�u���i�[
        if ($this->am_enabled) {
            if ($_exconf['aMona']['*']) {
                $mona = $this->activemona->transAM($msg, $qresid, $this->thread->bbs);
            }
        } else {
            $mona = '';
        }
        // }}}

        // {{{ qRes - SPM
        // �X�}�[�g�|�b�v�A�b�v���j���[
        if ($_exconf['spm']['*']) {
            if ($_exconf['spm']['*'] == 2) {
                $spmEventHandler = " onclick=\"showSPM({$this->spmObjName},{$i},'{$qresid}',event);return false;\"";
            } else {
                $spmEventHandler = " onmouseover=\"showSPM({$this->spmObjName},{$i},'{$qresid}',event)\" onmouseout=\"hideResPopUp('{$this->spmObjName}_spm')\"";
            }
            $i = "<a href=\"javascript:void(0);\" class=\"resnum\"{$spmEventHandler}>{$i}</a>";
        }
        // }}}

        // $tores�ɂ܂Ƃ߂ďo��
        $tores = "{$i} �F"; //�ԍ�
        $tores .= "<b>{$name}</b> �F"; //���O
        if ($mail) { $tores .= $mail." �F"; } //���[��
        $tores .= $date_id; //���t��ID
        $tores .= $mona; //AA�{�^��
        $tores .= "<br>";
        $tores .= $msg."<br>\n"; //���e

        // �܂Ƃ߂ăt�B���^�F�����i���\���ȁH�j
        if ($word_fm && $res_filter['match'] == 'on') {
            $tores = StrCtl::filterMarking($word_fm, $tores);
        }

        return $tores;
    }

    /**
     * ���O��HTML�p�ɕϊ�����
     */
    function transName($name)
    {
        global $_conf, $_exconf;
        $nameID = '';

        // ID�t�Ȃ番������
        if (preg_match("/(.*)(��.*)/", $name, $matches)) {
            $name = $matches[1];
            $nameID = $matches[2];
        }

        // ���������p���X�����N��
        // </b>�`<b> �́A�z�X�g��g���b�v�Ȃ̂Ń}�b�`�����Ȃ�
        // $pettern = '/(?!<\/b>[^>]*)([1-9][0-9]{0,3})+(?![^<]*<b>)/';
        $pettern = '/^( ?(?:&gt;|��)* ?)?([1-9]\d{0,3})(?=\\D|$)/';
        $name && $name = preg_replace_callback($pettern, array($this, 'quote_res_callback'), $name, 1);

        if ($nameID) { $name = $name . $nameID; }

        $name = $name.' '; // �����������

        return $name;
    }


    /**
     * dat�̃��X���b�Z�[�W��HTML�\���p���b�Z�[�W�ɕϊ�����
     * string transMsg(string str)
     */
    function transMsg($msg, $mynum)
    {
        global $_conf, $_exconf;
        global $res_filter, $word_fm;
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

        // �V�����X�̉摜�͕\�������𖳎�����ݒ�Ȃ�
        if ($mynum > $this->thread->readnum && $_exconf['imgCache']['newres_ignore_limit']) {
            $pre_thumb_ignore_limit = TRUE;
        }

        // ���p��URL�Ȃǂ������N
        $msg = preg_replace_callback($this->str_to_link_regex, array($this, 'link_callback'), $msg);

        $pre_thumb_ignore_limit = FALSE;

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
            $url = preg_replace('/^t?(tps?)$/', 'ht$1', $s[8]) . '://' . $s[9];
            $str = $s['url'];

        // ID
        } elseif ($s['id'] && $_exconf['flex']['idpopup']) {
            return $this->idfilter_callback(array($s['id'], $s[11]));

        // ���̑��i�\���j
        } else {
            return strip_tags($s[0]);
        }

        // }}}
        // {{{ URL�̑O����

        // ime.nu���O��
        $url = preg_replace('|^([a-z]+://)ime\\.nu/|', '$1', $url);

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
     * �����p�ϊ��i�P�Ɓj
     */
    function quote_res_callback($s)
    {
        global $_conf, $_exconf;

        list($full, $qsign, $appointed_num) = $s;
        $qnum = intval($appointed_num);
        if ($qnum < 1 || $qnum > $this->pDatCount) {
            return $full;
        }

        $read_url = $this->read_url_base . '&amp;offline=1&amp;ls=' . $appointed_num;
        $attributes = $_conf['bbs_win_target_at'];
        $loadpopup_js = ($_exconf['etc']['async_respop']) ? "loadResPopUp({$this->asyncObjName},{$qnum});" : '';
        if ($_conf['quote_res_view']) {
            $attributes .= " onmouseover=\"{$loadpopup_js}showResPopUp('q{$qnum}of{$this->thread->key}',event)\"";
            $attributes .= " onmouseout=\"hideResPopUp('q{$qnum}of{$this->thread->key}')\"";
        }
        return "<a href=\"{$read_url}\"{$attributes}>{$qsign}{$appointed_num}</a>";
    }

    /**
     * �����p�ϊ��i�͈́j
     */
    function quote_res_range_callback($s)
    {
        global $_conf, $_exconf;

        list($full, $qsign, $appointed_num) = $s;
        if ($appointed_num == '-') {
            return $full;
        }

        $read_url = $this->read_url_base . '&amp;offline=1&amp;ls=' . $appointed_num . 'n';

        // from-to��W�J���Ĉ��p���X�|�b�v�A�b�v��
        if ($_conf['quote_res_view'] && $_exconf['etc']['async_respop'] &&
            preg_match('/^([1-9]\d*)-([1-9]\d*)$/', $appointed_num, $m) &&
            $m[1] < $m[2] && $m[2] < $this->pDatLines &&
            $m[2] - $m[1] < $_exconf['etc']['async_rangepop']
        ) {
            $popId = "rp{$m[1]}to{$m[2]}of{$this->thread->key}";
            $attributes = $_conf['bbs_win_target_at'];
            $attributes .= ' onmouseover="';
            $attributes .= "makeRangeResPopUp({$this->asyncObjName},{$m[1]},{$m[2]});";
            $attributes .= "showResPopUp('{$popId}',event);";
            $attributes .= '"';
            $attributes .= " onmouseout=\"hideResPopUp('{$popId}');\"";
            return "<a href=\"{$read_url}\"{$attributes}>{$qsign}{$appointed_num}</a>";
        }

        // HTML�|�b�v�A�b�v
        if ($_conf['iframe_popup']) {
            $pop_url = $read_url . '&amp;renzokupop=true';
            return $this->iframe_popup(array($read_url, $pop_url), $full, $_conf['bbs_win_target_at']);
        }

        // ���ʂɃ����N
        return "<a href=\"{$read_url}\"{$_conf['bbs_win_target_at']}>{$qsign}{$appointed_num}</a>";
    }

    /**
     * ��HTML�|�b�v�A�b�v�ϊ��i�R�[���o�b�N�p�C���^�[�t�F�[�X�j
     */
    function iframe_popup_callback($s)
    {
        return $this->iframe_popup($s[1], $s[3], $s[2]);
    }

    /**
     * ��HTML�|�b�v�A�b�v�ϊ�
     */
    function iframe_popup($url, $str, $attr = '', $mode = NULL)
    {
        global $_conf, $_exconf;

        // �����N�pURL�ƃ|�b�v�A�b�v�pURL
        if (is_array($url)) {
            $link_url = $url[0];
            $pop_url = $url[1];
        } else {
            $link_url = $url;
            $pop_url = $url;
        }

        // �����N������ƃ|�b�v�A�b�v�̈�
        if (is_array($str)) {
            $link_str = $str[0];
            $pop_str = $str[1];
        } else {
            $link_str = $str;
            $pop_str = NULL;
        }

        // �����N�̑���
        if (is_array($attr)) {
            $_attr = $attr;
            $attr = '';
            foreach ($_attr as $key => $value) {
                $attr .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
            }
        } elseif ($attr !== '' && substr($attr, 0, 1) != ' ') {
            $attr = ' ' . $attr;
        }

        // �����N�̑�����HTML�|�b�v�A�b�v�p�̃C�x���g�n���h����������
        $pop_attr = $attr;
        $pop_attr .= " onmouseover=\"showHtmlPopUp('{$pop_url}',event,{$_conf['iframe_popup_delay']})\"";
        $pop_attr .= " onmouseout=\"offHtmlPopUp()\"";

        // �ŏI����
        if (is_null($mode)) {
            $mode = $_conf['iframe_popup'];
        }
        if ($mode == 2 && !is_null($pop_str)) {
            $mode = 3;
        } elseif ($mode == 3 && is_null($pop_str)) {
            global $skin, $STYLE;
            $custom_pop_img = "skin/{$skin}/pop.png";
            if (file_exists($custom_pop_img)) {
                $pop_img = htmlspecialchars($custom_pop_img);
                $x = $STYLE['iframe_popup_mark_width'];
                $y = $STYLE['iframe_popup_mark_height'];
            } else {
                $pop_img = 'img/pop.png';
                $y = $x = 12;
            }
            $pop_str = "<img src=\"{$pop_img}\" width=\"{$x}\" height=\"{$y}\" hspace=\"2\" vspace=\"0\" border=\"0\" align=\"top\">";
        }

        // �����N�쐬
        switch ($mode) {
            // �}�[�N����
            case 1:
                return "<a href=\"{$link_url}\"{$pop_attr}>{$link_str}</a>";
            // (p)�}�[�N
            case 2:
                return "(<a href=\"{$link_url}\"{$pop_attr}>p</a>)<a href=\"{$link_url}\"{$attr}>{$link_str}</a>";
            // [p]�摜�A�T���l�C���Ȃ�
            case 3:
                return "<a href=\"{$link_url}\"{$pop_attr}>{$pop_str}</a><a href=\"{$link_url}\"{$attr}>{$link_str}</a>";
            // �|�b�v�A�b�v���Ȃ�
            default:
                return "<a href=\"{$link_url}\"{$attr}>{$link_str}</a>";
        }
    }

    /**
     * ��ID�t�B���^�����O�|�b�v�A�b�v�ϊ�
     */
    function idfilter_callback($s)
    {
        global $_conf, $_exconf;

        $idstr = $s[0]; // ID:xxxxxxxxxx
        $id = $s[1];    // xxxxxxxxxx

        if (isset($this->thread->idcount[$id]) && $this->thread->idcount[$id] > 0) {
            $num_ht = '('.$this->thread->idcount[$id].')';
        } else {
            return $id;
        }

        $filter_url = $this->read_url_base . '&amp;ls=all&amp;offline=1&amp;idpopup=1&amp;field=id&amp;method=just&amp;match=on&amp;word=' . rawurlencode($id);

        if ($_conf['iframe_popup']) {
            return $this->iframe_popup($filter_url, $idstr . $num_ht, $_conf['bbs_win_target_at']);
        }
        return "<a href=\"{$filter_url}\"{$_conf['bbs_win_target_at']}>{$idstr}{$num_ht}</a>";
    }

    // }}}
    // {{{ ���[�e�B���e�B���\�b�h

    /**
     * HTML���b�Z�[�W���̈��p���X�̔ԍ����ċA�`�F�b�N����
     */
    function checkQuoteResNums($res_num, $name, $msg)
    {
        // �ċA���~�b�^
        if ($this->quote_check_depth > 30) {
            return array();
        } else {
            $this->quote_check_depth++;
        }

        $quote_res_nums = array();

        $name = preg_replace('/(��.*)/', '', $name, 1);

        // ���O
        if (preg_match('/[1-9]\d*/', $name, $matches)) {
            $a_quote_res_num = (int)$matches[0];

            if ($a_quote_res_num && isset($this->pDatLines[$a_quote_res_num])) {
                $quote_res_nums[] = $a_quote_res_num;

                // �������g�̔ԍ��Ɠ���łȂ���΁A
                if ($a_quote_res_num != $res_num) {
                    // �`�F�b�N���Ă��Ȃ��ԍ����ċA�`�F�b�N
                    if (!isset($this->quote_res_nums_checked[$a_quote_res_num])) {
                        $this->quote_res_nums_checked[$a_quote_res_num] = true;

                        $quote_name = $this->pDatLines[$a_quote_res_num]['name'];
                        $quote_msg = $this->pDatLines[$a_quote_res_num]['msg'];
                        $quote_res_nums = array_merge($quote_res_nums, $this->checkQuoteResNums($a_quote_res_num, $quote_name, $quote_msg) );
                    }
                }
            }
            // $name = preg_replace("/([0-9]+)/", "", $name, 1);
        }

        // >>1�̃����N����������O��
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;[1-9][\\d\\-]*)</[Aa]>}', '$1', $msg);

        // echo $msg;
        if (preg_match_all('/(?:&gt;|��)+ ?([1-9](?:[0-9\\- ,=.]|�A)*)/', $msg, $out, PREG_PATTERN_ORDER)) {

            foreach ($out[1] as $numberq) {
                // echo $numberq;
                if (preg_match_all('/[1-9]\\d*/', $numberq, $matches, PREG_PATTERN_ORDER)) {

                    foreach ($matches[0] as $a_quote_res_num) {

                        // echo $a_quote_res_num;
                        $a_quote_res_num = (int)$a_quote_res_num;

                        if (!$a_quote_res_num) { break; }
                        $quote_res_nums[] = $a_quote_res_num;

                        // �������g�̔ԍ��Ɠ���łȂ���΁A
                        if ($a_quote_res_num != $res_num && isset($this->pDatLines[$a_quote_res_num])) {
                        // �`�F�b�N���Ă��Ȃ��ԍ����ċA�`�F�b�N
                            if (!isset($this->quote_res_nums_checked[$a_quote_res_num])) {
                                $this->quote_res_nums_checked[$a_quote_res_num] = true;

                                $quote_name = $this->pDatLines[$a_quote_res_num]['name'];
                                $quote_msg = $this->pDatLines[$a_quote_res_num]['msg'];
                                $quote_res_nums = array_merge($quote_res_nums, $this->checkQuoteResNums($a_quote_res_num, $quote_name, $quote_msg) );
                            }
                        }

                    }

                }

            }

        }

        return array_unique($quote_res_nums);
    }

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
        if ($_exconf['etc']['datetime_rewrite']) {
            if (isset($p['timestamp'])) {
                $epoch = $p['timestamp'];
            } else {
                $epoch = $p['timestamp'] = $this->datetimeToEpoch($p['date'], $p['time']);
            }
            if ($epoch != -1) {
                $date_id = date($_exconf['etc']['datetime_format'], $epoch);
                if (strstr($_exconf['etc']['datetime_format'], '%w%')) {
                    $date_id = preg_replace('/%([0-6])%/e', '$_exconf["etc"]["datetime_weekday"][$1]', $date_id);
                }
            } else {
                $date_id = $p['date'].' '.$p['time'];
            }
        } else {
            $date_id = $p['date'].' '.$p['time'];
        }

        // ID
        if (isset($p['id'])) {
            if ($_exconf['flex']['idpopup'] == 1 && $this->thread->idcount[$p['id']] > 1) {
                $date_id .= ' '. $this->idfilter_callback(array('ID:'.$p['id'], $p['id']));
            } else {
                $date_id .= ' ID:' . $p['id'];
            }
            if (isset($p['idopt'])) {
                $date_id .= $p['idopt'];
            }
        }

        // BE
        if (isset($p['be'])) {
            $be_prof_ref = rawurlencode('http://' . $this->thread->host . '/test/read.cgi/' . $this->thread->bbs . '/' . $this->thread->key . '/' . $GLOBALS['ls']);
            $be_prof_url = 'http://be.2ch.net/test/p.php?i=' . $p['beid'] . '&u=d:' . $be_prof_ref;
            $be_prof_lv  = 'Lv.' . $p['belv'];
            if ($_conf['iframe_popup']) {
                $be_prof_link = $this->iframe_popup($be_prof_url, $be_prof_lv, $_conf['ext_win_target_at']);
            } else {
                $be_prof_link = "<a href=\"{$be_prof_url}\"{$_conf['ext_win_target_at']}>{$be_prof_lv}</a>";
            }
            $date_id .= ' ' . $be_prof_link;
        }

        return $date_id;
    }

    /**
     * �摜��HTML�|�b�v�A�b�v&�|�b�v�A�b�v�E�C���h�E�T�C�Y�ɍ��킹��
     */
    function imageHtmpPopup($img_url, $img_tag, $link_str)
    {
        global $_conf, $_exconf;

        if ($_exconf['fitImage']['*']) {
            $fimg_url = str_replace('&amp;', '&', $img_url);
            $popup_url = "fitimage.php?url=" . rawurlencode($fimg_url);
        } else {
            $popup_url = $img_url;
        }

        $pops = ($_conf['iframe_popup'] == 1) ? $img_tag . $link_str : array($link_str, $img_tag);
        return $this->iframe_popup(array($img_url, $popup_url), $pops, $_conf['ext_win_target_at']);
    }

    /**
     * ���X�|�b�v�A�b�v��񓯊����[�h�ɉ��H����
     */
    function respop_to_async($str)
    {
        $respop_regex = '/(onmouseover)=\"(showResPopUp\(\'(q(\d+)of\d+)\',event\).*?)\"/';
        $respop_replace = '$1="loadResPopUp(' . $this->asyncObjName . ', $4);$2"';
        return preg_replace($respop_regex, $respop_replace, $str);
    }

    /**
     * �񓯊��ǂݍ��݂ŗ��p����JavaScript�I�u�W�F�N�g���o�͂���
     */
    function printASyncObjJs()
    {
        global $_conf, $_exconf;
        static $done = array();

        if (isset($done[$this->asyncObjName])) {
            return;
        }
        $done[$this->asyncObjName] = TRUE;

        echo <<<EOJS
<script type="text/javascript">
var {$this->asyncObjName} = {
    host:"{$this->thread->host}", bbs:"{$this->thread->bbs}", key:"{$this->thread->key}",
    readPhp:"{$_conf['read_php']}", readTarget:"{$_conf['bbs_win_target']}"
};
</script>\n
EOJS;
    }

    /**
     * �X�}�[�g�|�b�v�A�b�v���j���[�𐶐�����JavaScript�R�[�h���o�͂���
     */
    function printSPMObjJs()
    {
        global $_conf, $_exconf;
        global $STYLE;
        static $done = array();

        if (isset($done[$this->spmObjName])) {
            return;
        }
        $done[$this->spmObjName] = true;

        $ttitle_en = base64_encode($this->thread->ttitle);
        $ttitle_urlen = rawurlencode($ttitle_en);
        $isClickOnOff = ($_exconf['spm']['*'] == 2) ? 'true' : 'false';

        if ($_exconf['spm']['flex_target'] == '' || $_exconf['spm']['flex_target'] == 'read') {
            $_exconf['spm']['flex_target'] = '_self';
        }

        $motothre_url = str_replace('"', '\\"', $this->thread->getMotoThread());
        $ttitle = str_replace('"', '\\"', $this->thread->ttitle);

        echo <<<EOJS
<script type="text/javascript">
// ��ȃX���b�h���Ɗe��ݒ���v���p�e�B�Ɏ��I�u�W�F�N�g
var {$this->spmObjName} = {
    objName:"{$this->spmObjName}", rc:"{$this->thread->rescount}",
    title:"{$ttitle}",
    ttitle_en:"{$ttitle_urlen}",
    url:"{$motothre_url}",
    host:"{$this->thread->host}", bbs:"{$this->thread->bbs}", key:"{$this->thread->key}",
    spmHeader:"{$_exconf['spm']['header']}",
    spmOption:[{$_exconf['spm']['confirm']},{$_exconf['spm']['kokores']},{$_exconf['bookmark']['*']},{$_exconf['spm']['aborn']},{$_exconf['spm']['ng']},{$_exconf['spm']['with_aMona']},{$_exconf['spm']['with_flex']},{$_exconf['spm']['fortune']}]
};
//�X�}�[�g�|�b�v�A�b�v���j���[����
var spmFlexTarget = "{$_exconf['spm']['flex_target']}";
makeSPM({$this->spmObjName},{$isClickOnOff});
</script>\n
EOJS;
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
            // ime
            if ($_conf['through_ime']) {
                $link_url = P2Util::throughIme($url);
            } else {
                $link_url = $url;
            }

            // HTML�|�b�v�A�b�v
            if ($_conf['iframe_popup'] && preg_match('/https?/', $purl['scheme'])) {
                // p2pm �w��̏ꍇ�̂݁A���ʂ�m�w���ǉ�����
                if ($_conf['through_ime'] == 'p2pm') {
                    $pop_url = preg_replace('/\\?(enc=1&amp;)url=/', '?$1m=1&amp;url=', $link_url);
                } else {
                    $pop_url = $link_url;
                }
                $link = $this->iframe_popup(array($link_url, $pop_url), $str, $_conf['ext_win_target_at']);
            } else {
                $link = "<a href=\"{$link_url}\"{$_conf['ext_win_target_at']}>{$str}</a>";
            }

            // �u���N���`�F�b�J
            if ($_conf['brocra_checker_use'] && preg_match('/https?/', $purl['scheme'])) {
                $brocra_checker_url = $_conf['brocra_checker_url'] . '?' . $_conf['brocra_checker_query'] . '=' . rawurlencode($url);
                // �u���N���`�F�b�J�Eime
                if ($_conf['through_ime']) {
                    $brocra_checker_url = P2Util::throughIme($brocra_checker_url);
                }
                // �u���N���`�F�b�J�EHTML�|�b�v�A�b�v
                if ($_conf['iframe_popup']) {
                    // p2pm �w��̏ꍇ�̂݁A���ʂ�m�w���ǉ�����
                    if ($_conf['through_ime'] == 'p2pm') {
                        $brocra_pop_url = preg_replace('/\\?(enc=1&amp;)url=/', '?$1m=1&amp;url=', $brocra_checker_url);
                    } else {
                        $brocra_pop_url = $brocra_checker_url;
                    }
                    $brocra_checker_link = $this->iframe_popup(array($brocra_checker_url, $brocra_pop_url), '�`�F�b�N', $_conf['ext_win_target_at']);
                } else {
                    $brocra_checker_link = "<a href=\"{$brocra_checker_url}\"{$_conf['ext_win_target_at']}>�`�F�b�N</a>";
                }
                $link .= ' [' . $brocra_checker_link . ']';
            }

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
            return "<a href=\"{$url}\" target=\"subject\">{$str}</a> [<a href=\"{$subject_url}\" target=\"subject\">��p2�ŊJ��</a>]";
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
            if ($_conf['iframe_popup']) {
                if (preg_match('/^[0-9n\\-]+$/', $m[4])) {
                    $pop_url = $url;
                } else {
                    $pop_url = $read_url . '&amp;one=true';
                }
                return $this->iframe_popup(array($read_url, $pop_url), $str, $_conf['bbs_win_target_at']);
            }
            return "<a href=\"{$read_url}\"{$_conf['bbs_win_target_at']}>{$str}</a>";
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
            if ($_conf['iframe_popup']) {
                $pop_url = $read_url . '&amp;one=true';
                return $this->iframe_popup(array($read_url, $pop_url), $str, $_conf['bbs_win_target_at']);
            }
            return "<a href=\"{$read_url}\"{$_conf['bbs_win_target_at']}>{$str}</a>";
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
            if ($_conf['iframe_popup']) {
                $pop_url = $url;
                return $this->iframe_popup(array($read_url, $pop_url), $str, $_conf['bbs_win_target_at']);
            }
            return "<a href=\"{$read_url}\"{$_conf['bbs_win_target_at']}>{$str}</a>";
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
            if ($_conf['iframe_popup']) {
                $pop_url = $url;
                return $this->iframe_popup(array($read_url, $pop_url), $str, $_conf['bbs_win_target_at']);
            }
            return "<a href=\"{$read_url}\"{$_conf['bbs_win_target_at']}>{$str}</a>";
        }
        return FALSE;
    }

    /**
     * �摜�|�b�v�A�b�v�ϊ�
     */
    function plugin_viewImage($url, $purl, $str)
    {
        global $_conf, $_exconf;
        global $pre_thumb_unlimited, $pre_thumb_limit;

        // �\������
        if (!$pre_thumb_unlimited && empty($pre_thumb_limit)) {
            return FALSE;
        }

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url) && empty($purl['query'])) {
            $pre_thumb_limit--; // �\�������J�E���^��������
            $img_tag = "<img class=\"thumbnail\" src=\"{$url}\" height=\"{$_conf['pre_thumb_height']}\" weight=\"{$_conf['pre_thumb_width']}\" hspace=\"4\" vspace=\"4\" align=\"middle\">";

            if ($_conf['iframe_popup']) {
                $view_img = $this->imageHtmpPopup($url, $img_tag, $str);
            } else {
                $view_img = "<a href=\"{$url}\"{$_conf['ext_win_target_at']}>{$img_tag}{$str}</a>";
            }

            // �u���N���`�F�b�J �i�v���r���[�Ƃ͑��e��Ȃ��̂ŃR�����g�A�E�g�j
            /*if ($_conf['brocra_checker_use']) {
                $link_url_en = rawurlencode($url);
                $view_img .= " [<a href=\"{$_conf['brocra_checker_url']}?{$_conf['brocra_checker_query']}={$link_url_en}\"{$_conf['ext_win_target_at']}>�`�F�b�N</a>]";
            }*/

            return $view_img;
        }
        return FALSE;
    }

    /**
     * ImageCache2�T���l�C���ϊ�
     */
    function plugin_imageCache2($url, $purl, $str)
    {
        global $_conf, $_exconf;
        global $pre_thumb_unlimited, $pre_thumb_ignore_limit, $pre_thumb_limit;
        static $serial = 0;

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url) && empty($purl['query'])) {
            // ����
            $serial++;
            $thumb_id = 'thumbs' . $serial . '_' . P2_REQUEST_ID;
            $tmp_thumb = './img/ic_load.png';
            $url_en = rawurlencode($url);

            $icdb = &new IC2DB_Images;

            // r=0:�����N;r=1:���_�C���N�g;r=2:PHP�ŕ\��
            // t=0:�I���W�i��;t=1:PC�p�T���l�C��;t=2:�g�їp�T���l�C��;t=3:���ԃC���[�W
            $img_url = 'ic2.php?r=1&amp;uri=' . $url_en;
            $thumb_url = 'ic2.php?r=1&amp;t=1&amp;uri=' . $url_en;

            // DB�ɉ摜��񂪓o�^����Ă����Ƃ�
            if ($icdb->get($url)) {

                // �E�B���X�Ɋ������Ă����t�@�C���̂Ƃ�
                if ($icdb->mime == 'clamscan/infected') {
                    return "<img class=\"thumbnail\" src=\"./img/x04.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\"> <s>{$str}</s>";
                }
                // ���ځ[��摜�̂Ƃ�
                if ($icdb->rank < 0) {
                    return "<img class=\"thumbnail\" src=\"./img/x01.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\"> <s>{$str}</s>";
                }

                // �I���W�i�����L���b�V������Ă���Ƃ��͉摜�𒼐ړǂݍ���
                $_img_url = $this->thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_img_url)) {
                    $img_url = $_img_url;
                    $cached = TRUE;
                } else {
                    $cached = FALSE;
                }

                // �T���l�C�����쐬����Ă��Ă���Ƃ��͉摜�𒼐ړǂݍ���
                $_thumb_url = $this->thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_thumb_url)) {
                    $thumb_url = $_thumb_url;
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
                }

                // �T���l�C���̉摜�T�C�Y
                $thumb_size = $this->thumbnailer->calc($icdb->width, $icdb->height);
                $thumb_size = preg_replace('/(\d+)x(\d+)/', 'width="$1" height="$2"', $thumb_size);
                $tmp_thumb = './img/ic_load1.png';

            // �摜���L���b�V������Ă��Ȃ��Ƃ�
            // �����X���^�C�����@�\��ON�Ȃ�N�G����UTF-8�G���R�[�h�����X���^�C���܂߂�
            } else {
                // �摜���u���b�N���X�gor�G���[���O�ɂ��邩�m�F
                if (FALSE !== ($errcode = $icdb->ic2_isError($url))) {
                    return "<img class=\"thumbnail\" src=\"./img/{$errcode}.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\"> <s>{$str}</s>";
                }

                $cached = FALSE;

                $img_url .= $this->img_memo_query;
                $thumb_url .= $this->img_memo_query;
                $thumb_size = '';
                $tmp_thumb = './img/ic_load2.png';
            }

            // �L���b�V������Ă��炸�A�\�����������L���̂Ƃ�
            if (!$cached && !$pre_thumb_unlimited && !$pre_thumb_ignore_limit) {
                // �\�������𒴂��Ă�����A�\�����Ȃ�
                // �\�������𒴂��Ă��Ȃ���΁A�\�������J�E���^��������
                if ($pre_thumb_limit <= 0) {
                    $show_thumb = FALSE;
                } else {
                    $show_thumb = TRUE;
                    $pre_thumb_limit--;
                }
            } else {
                $show_thumb = TRUE;
            }

            // �\�����[�h
            if ($show_thumb) {
                $img_tag = "<img class=\"thumbnail\" src=\"{$thumb_url}\" {$thumb_size} hspace=\"4\" vspace=\"4\" align=\"middle\">";
                if ($_conf['iframe_popup']) {
                    $view_img = $this->imageHtmpPopup($img_url, $img_tag, $str);
                } else {
                    $view_img = "<a href=\"{$img_url}\"{$_conf['ext_win_target_at']}>{$img_tag}{$str}</a>";
                }
            } else {
                $img_tag = "<img id=\"{$thumb_id}\" class=\"thumbnail\" src=\"{$tmp_thumb}\" hspace=\"4\" vspace=\"4\" align=\"middle\">";
                $view_img = "<a href=\"{$img_url}\" onclick=\"return loadThumb('{$thumb_url}','{$thumb_id}')\"{$_conf['ext_win_target_at']}>{$img_tag}</a><a href=\"{$img_url}\"{$_conf['ext_win_target_at']}>{$str}</a>";
            }

            // �\�[�X�ւ̃����N��ime�t���ŕ\��
            if ($_exconf['imgCache']['*'] && $_exconf['imgCache']['through_ime']) {
                $ime_url = P2Util::throughIme($url);
                $view_img .= " <a class=\"img_through_ime\" href=\"{$ime_url}\"{$_conf['ext_win_target_at']}>[ime]</a>";
            }

            return $view_img;
        }
        return FALSE;
    }

    // }}}

}
?>
