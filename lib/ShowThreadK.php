<?php
/**
 * rep2 - �g�їp�ŃX���b�h��\������ �N���X
 */

require_once P2_LIB_DIR . '/ShowThread.php';
require_once P2_LIB_DIR . '/StrCtl.php';
require_once P2EX_LIB_DIR . '/ExpackLoader.php';

ExpackLoader::loadAAS();
ExpackLoader::loadActiveMona();
ExpackLoader::loadImageCache();

// {{{ ShowThreadK

class ShowThreadK extends ShowThread
{
    // {{{ properties

    static private $_spm_objects = array();

    public $BBS_NONAME_NAME = '';

    public $am_autong = false; // ����AA�������邩�ۂ�

    public $aas_rotate = '90����]'; // AAS ��]�����N������

    public $respopup_at = '';  // ���X�|�b�v�A�b�v�E�C�x���g�n���h��
    public $target_at = '';    // ���p�A�ȗ��AID�ANG���̃����N�^�[�Q�b�g
    public $check_st = '�m';   // �ȗ��ANG���̃����N������

    public $spmObjName; // �X�}�[�g�|�b�v�A�b�v���j���[�pJavaScript�I�u�W�F�N�g��

    private $_dateIdPattern;    // ���t���������̌����p�^�[��
    private $_dateIdReplace;    // ���t���������̒u��������

    private $_lineBreaksReplace; // �A��������s�̒u��������

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct(ThreadRead $aThread, $matome = false)
    {
        parent::__construct($aThread, $matome);

        global $_conf, $STYLE;

        if ($_conf['iphone']) {
            $this->respopup_at = ' onclick="return iResPopUp(this, event);"';
            $this->target_at = ' target="_blank"';
            $this->check_st = '&#x2620;';
        }

        $this->_url_handlers = array(
            'plugin_link2ch',
            'plugin_linkMachi',
            'plugin_linkJBBS',
            'plugin_link2chKako',
            'plugin_link2chSubject',
        );
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            $this->_url_handlers[] = 'plugin_imageCache2';
        } elseif ($_conf['mobile.use_picto']) {
            $this->_url_handlers[] = 'plugin_viewImage';
        }
        if ($_conf['mobile.link_youtube']) {
            $this->_url_handlers[] = 'plugin_linkYouTube';
        }
        $this->_url_handlers[] = 'plugin_linkURL';

        $this->BBS_NONAME_NAME = null;
        if (!$_conf['mobile.bbs_noname_name']) {
            if (!class_exists('SettingTxt', false)) {
                require_once P2_LIB_DIR . '/SettingTxt.php';
            }
            $st = new SettingTxt($this->thread->host, $this->thread->bbs);
            $st->setSettingArray();
            if (!empty($st->setting_array['BBS_NONAME_NAME'])) {
                $this->BBS_NONAME_NAME = $st->setting_array['BBS_NONAME_NAME'];
            }
        }

        if ($_conf['mobile.date_zerosuppress']) {
            $this->_dateIdPattern = '~^(?:' . date('Y|y') . ')/(?:0(\\d)|(\\d\\d))?(?:(/)0)?~';
            $this->_dateIdReplace = '$1$2$3';
        } else {
            $this->_dateIdPattern = '~^(?:' . date('Y|y') . ')/~';
            $this->_dateIdReplace = '';
        }

        // �A��������s�̒u���������ݒ�
        if ($_conf['mobile.strip_linebreaks']) {
            $ngword_color = $GLOBALS['STYLE']['mobile_read_ngword_color'];
            if (strpos($ngword_color, '\\') === false && strpos($ngword_color, '$') === false) {
                $this->_lineBreaksReplace = "<br><s><font color=\"{$ngword_color}\">***</font></s><br>";
            } else {
                $this->_lineBreaksReplace = '<br><s>***</s><br>';
            }
        } else {
            $this->_lineBreaksReplace = null;
        }

        // �T���l�C���\����������ݒ�
        if (!isset($GLOBALS['pre_thumb_unlimited']) || !isset($GLOBALS['expack.ic2.pre_thumb_limit_k'])) {
            if (isset($_conf['expack.ic2.pre_thumb_limit_k']) && $_conf['expack.ic2.pre_thumb_limit_k'] > 0) {
                $GLOBALS['pre_thumb_limit_k'] = $_conf['expack.ic2.pre_thumb_limit_k'];
                $GLOBALS['pre_thumb_unlimited'] = false;
            } else {
                $GLOBALS['pre_thumb_limit_k'] = null;   // �k���l����isset()��FALSE��Ԃ�
                $GLOBALS['pre_thumb_unlimited'] = true;
            }
        }
        $GLOBALS['pre_thumb_ignore_limit'] = false;

        // �A�N�e�B�u���i�[������
        if (P2_ACTIVEMONA_AVAILABLE) {
            ExpackLoader::initActiveMona($this);
        }

        // ImageCache2������
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            ExpackLoader::initImageCache($this);
        }

        // AAS ������
        if (P2_AAS_AVAILABLE) {
            ExpackLoader::initAAS($this);
        }

        // SPM������
        //if ($this->_matome) {
        //    $this->spmObjName = sprintf('t%dspm%u', $this->_matome, crc32($this->thread->keydat));
        //} else {
            $this->spmObjName = sprintf('spm%u', crc32($this->thread->keydat));
        //}
    }

    // }}}
    // {{{ datToHtml()

    /**
     * Dat��HTML�ɕϊ��\������
     */
    public function datToHtml()
    {
        if (!$this->thread->resrange) {
            echo '<p><b>p2 error: {$this->resrange} is FALSE at datToHtml()</b></p>';
        }

        $start = $this->thread->resrange['start'];
        $to = $this->thread->resrange['to'];
        $nofirst = $this->thread->resrange['nofirst'];

        // 1��\��
        if (!$nofirst) {
            echo $this->transRes($this->thread->datlines[0], 1);
        }

        for ($i = $start; $i <= $to; $i++) {
            if (!$nofirst and $i == 1) {
                continue;
            }
            if (!$this->thread->datlines[$i-1]) {
                $this->thread->readnum = $i-1;
                break;
            }
            echo $this->transRes($this->thread->datlines[$i-1], $i);
            flush();
        }

        //$s2e = array($start, $i-1);
        //return $s2e;
        return true;
    }

    // }}}
    // {{{ transRes()

    /**
     * Dat���X��HTML���X�ɕϊ�����
     *
     * @param   string  $ares   dat��1���C��
     * @param   int     $i      ���X�ԍ�
     * @return  string
     */
    public function transRes($ares, $i)
    {
        global $_conf, $STYLE, $mae_msg, $res_filter;
        global $ngaborns_hits;
        static $ngaborns_head_hits = 0;
        static $ngaborns_body_hits = 0;

        $resar = $this->thread->explodeDatLine($ares);
        $name = $resar[0];
        $mail = $resar[1];
        $date_id = str_replace('ID: ', 'ID:', $resar[2]);
        $msg = $resar[3];

        $id = $this->thread->ids[$i];

        // �f�t�H���g�̖��O�Ɠ����Ȃ�ȗ�
        if ($name === $this->BBS_NONAME_NAME) {
            $name = '';
        }

        // {{{ �t�B���^�����O

        if (isset($_REQUEST['word']) && strlen($_REQUEST['word']) > 0) {
            if (strlen($GLOBALS['word_fm']) <= 0) {
                return '';
            // �^�[�Q�b�g�ݒ�i��̂Ƃ��̓t�B���^�����O���ʂɊ܂߂Ȃ��j
            } elseif (!$target = $this->getFilterTarget($ares, $i, $name, $mail, $date_id, $msg)) {
                return '';
            // �}�b�`���O
            } elseif (!$this->filterMatch($target, $i)) {
                return '';
            }
        }

        // }}}

        $tores = '';
        if ($this->_matome) {
            $res_id = "t{$this->_matome}r{$i}";
        } else {
            $res_id = "r{$i}";
        }

        $isNgName = false;
        $isNgMail = false;
        $isNgId = false;
        $isNgMsg = false;
        $isFreq = false;
        $isChain = false;
        $isAA = false;

        // {{{ ���ځ[��`�F�b�N

        $ng_msg_info = array();

        // �p�oID���ځ[��
        if ($this->_ngaborn_frequent && $id && $this->thread->idcount[$id] >= $_conf['ngaborn_frequent_num']) {
            if (!$_conf['ngaborn_frequent_one'] && $id == $this->thread->ids[1]) {
                // >>1 �͂��̂܂ܕ\��
            } elseif ($this->_ngaborn_frequent == 1) {
                $ngaborns_hits['aborn_freq']++;
                $this->_aborn_nums[] = $i;
                return $this->_abornedRes($res_id);
            } elseif (!$nong) {
                $ngaborns_hits['ng_freq']++;
                $ngaborns_body_hits++;
                $this->_ng_nums[] = $i;
                $isFreq = true;
                $ng_msg_info[] = sprintf('�p�oID:%s(%d)', $id, $this->thread->idcount[$id]);
            }
        }

        // �A�����ځ[��
        if ($_conf['ngaborn_chain'] && preg_match_all('/(?:&gt;|��)([1-9][0-9\\-,]*)/', $msg, $matches)) {
            $chain_nums = array_unique(array_map('intval', split('[-,]+', trim(implode(',', $matches[1]), '-,'))));
            if (array_intersect($chain_nums, $this->_aborn_nums)) {
                if ($_conf['ngaborn_chain'] == 1) {
                    $ngaborns_hits['aborn_chain']++;
                    $this->_aborn_nums[] = $i;
                    return $this->_abornedRes($res_id);
                } else {
                    $a_chain_num = array_shift($chain_nums);
                    $ngaborns_hits['ng_chain']++;
                    $this->_ng_nums[] = $i;
                    $ngaborns_body_hits++;
                    $isChain = true;
                    $ng_msg_info[] = sprintf('�A��NG:&gt;&gt;%d(����)', $a_chain_num);
                }
            } elseif (array_intersect($chain_nums, $this->_ng_nums)) {
                $a_chain_num = array_shift($chain_nums);
                $ngaborns_hits['ng_chain']++;
                $ngaborns_body_hits++;
                $this->_ng_nums[] = $i;
                $isChain = true;
                $ng_msg_info[] = sprintf('�A��NG:&gt;&gt;%d', $a_chain_num);
            }
        }

        // ���ځ[�񃌃X
        if ($this->abornResCheck($i) !== false) {
            $ngaborns_hits['aborn_res']++;
            $this->_aborn_nums[] = $i;
            return $this->_abornedRes($res_id);
        }

        // ���ځ[��l�[��
        if ($this->ngAbornCheck('aborn_name', $name) !== false) {
            $ngaborns_hits['aborn_name']++;
            $this->_aborn_nums[] = $i;
            return $this->_abornedRes($res_id);
        }

        // ���ځ[�񃁁[��
        if ($this->ngAbornCheck('aborn_mail', $mail) !== false) {
            $ngaborns_hits['aborn_mail']++;
            $this->_aborn_nums[] = $i;
            return $this->_abornedRes($res_id);
        }

        // ���ځ[��ID
        if ($this->ngAbornCheck('aborn_id', $date_id) !== false) {
            $ngaborns_hits['aborn_id']++;
            $this->_aborn_nums[] = $i;
            return $this->_abornedRes($res_id);
        }

        // ���ځ[�񃁃b�Z�[�W
        if ($this->ngAbornCheck('aborn_msg', $msg) !== false) {
            $ngaborns_hits['aborn_msg']++;
            $this->_aborn_nums[] = $i;
            return $this->_abornedRes($res_id);
        }

        // NG�`�F�b�N ========
        if (empty($_GET['nong'])) {
            // NG�l�[���`�F�b�N
            if ($this->ngAbornCheck('ng_name', $name) !== false) {
                $ngaborns_hits['ng_name']++;
                $ngaborns_head_hits++;
                $this->_ng_nums[] = $i;
                $isNgName = true;
            }

            // NG���[���`�F�b�N
            if ($this->ngAbornCheck('ng_mail', $mail) !== false) {
                $ngaborns_hits['ng_mail']++;
                $ngaborns_head_hits++;
                $this->_ng_nums[] = $i;
                $isNgMail = true;
            }

            // NGID�`�F�b�N
            if ($this->ngAbornCheck('ng_id', $date_id) !== false) {
                $ngaborns_hits['ng_id']++;
                $ngaborns_head_hits++;
                $this->_ng_nums[] = $i;
                $isNgId = true;
            }

            // NG���b�Z�[�W�`�F�b�N
            $a_ng_msg = $this->ngAbornCheck('ng_msg', $msg);
            if ($a_ng_msg !== false) {
                $ngaborns_hits['ng_msg']++;
                $ngaborns_body_hits++;
                $this->_ng_nums[] = $i;
                $isNgMsg = true;
                $ng_msg_info[] = sprintf('NGܰ��:%s', htmlspecialchars($a_ng_msg, ENT_QUOTES));
            }

            // AA�`�F�b�N
            if ($this->am_autong && $this->activeMona->detectAA($msg)) {
                $this->_ng_nums[] = $i;
                $ngaborns_body_hits++;
                $isAA = true;
                $ng_msg_info[] = '&lt;AA��&gt;';
            }
        }

        // }}}
        // {{{ ���t�EID�𒲐�

        // ���݂̔N���͏ȗ��J�b�g����B�����̐擪0���J�b�g�B
        $date_id = preg_replace($this->_dateIdPattern, $this->_dateIdReplace, $date_id);

        // �j���Ǝ��Ԃ̊Ԃ��l�߂�
        $date_id = str_replace(') ', ')', $date_id);

        // �b���J�b�g
        if ($_conf['mobile.clip_time_sec']) {
            $date_id = preg_replace('/(\\d\\d:\\d\\d):\\d\\d(?:\\.\\d\\d)?/', '$1', $date_id);
        }

        // ID
        if ($id !== null) {
            $id_id = 'ID:' . $id;
            $id_suffix = substr($id, -1);

            if ($_conf['mobile.underline_id'] && strlen($id) % 2 && $id_suffix == 'O') {
                $do_underline_id_suffix = true;
            } else {
                $do_underline_id_suffix = false;
            }

            if ($this->thread->idcount[$id] > 1) {
                if ($_conf['flex_idpopup'] == 1) {
                    $date_id = str_replace($id_id, $this->idfilter_callback(array($id_id, $id)), $date_id);
                }
                if ($do_underline_id_suffix) {
                    $date_id = str_replace($id_id, substr($id_id, 0, -1) . '<u>O</u>', $date_id);
                }
            } else {
                if ($_conf['mobile.clip_unique_id']) {
                    if ($do_underline_id_suffix) {
                        $date_id = str_replace($id_id, 'ID:*<u>O</u>', $date_id);
                    } else {
                        $date_id = str_replace($id_id, 'ID:*' . $id_suffix, $date_id);
                    }
                } else {
                    if ($do_underline_id_suffix) {
                        $date_id = str_replace($id_id, substr($id_id, 0, -1) . '<u>O</u>', $date_id);
                    }
                }
            }
        } else {
            if ($_conf['mobile.clip_unique_id']) {
                $date_id = str_replace('ID:???', 'ID:?', $date_id);
            }
        }

        // }}}

        //=============================================================
        // �܂Ƃ߂ďo��
        //=============================================================

        if ($name) {
            $name = $this->transName($name); // ���OHTML�ϊ�
        }
        $msg = $this->transMsg($msg, $i); // ���b�Z�[�WHTML�ϊ�

        // BE�v���t�@�C�������N�ϊ�
        $date_id = $this->replaceBeId($date_id, $i);

        // NG���b�Z�[�W�ϊ�
        if ($ng_msg_info) {
            $ng_type = implode(', ', $ng_msg_info);
            $msg = <<<EOMSG
<s><font color="{$STYLE['mobile_read_ngword_color']}">$ng_type</font></s> <a class="button" href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}"{$this->respopup_at}{$this->target_at}>{$this->check_st}</a>
EOMSG;
            // AAS
            if ($isAA && P2_AAS_AVAILABLE) {
                $aas_url = "aas.php?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;resnum={$i}{$_conf['k_at_a']}";
                if (P2_AAS_AVAILABLE == 2) {
                    $aas_txt = "<img src=\"{$aas_url}&amp;inline=1\">";
                } else {
                    $aas_txt = "AAS";
                }
                $msg .= " <a class=\"aas\" href=\"{$aas_url}\"{$this->target_at}>{$aas_txt}</a>";
                $msg .= " <a class=\"button\" href=\"{$aas_url}&amp;rotate=1\"{$this->target_at}>{$this->aas_rotate}</a>";
            }
        }

        // NG�l�[���ϊ�
        if ($isNgName) {
            $name = <<<EONAME
<s><font color="{$STYLE['mobile_read_ngword_color']}">{$name}</font></s>
EONAME;
            $msg = <<<EOMSG
<a class="button" href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}"{$this->respopup_at}{$this->target_at}>{$this->check_st}</a>
EOMSG;

        // NG���[���ϊ�
        } elseif ($isNgMail) {
            $mail = <<<EOMAIL
<s class="ngword" onmouseover="document.getElementById('ngn{$ngaborns_head_hits}').style.display = 'block';">{$mail}</s>
EOMAIL;
            $msg = <<<EOMSG
<div id="ngn{$ngaborns_head_hits}" style="display:none;">{$msg}</div>
EOMSG;

        // NGID�ϊ�
        } elseif ($isNgId) {
            $date_id = <<<EOID
<s><font color="{$STYLE['mobile_read_ngword_color']}">{$date_id}</font></s>
EOID;
            $msg = <<<EOMSG
<a class="button" href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}"{$this->respopup_at}{$this->target_at}>{$this->check_st}</a>
EOMSG;
        }

        /*
        //�u��������V���v�摜��}��
        if ($i == $this->thread->readnum +1) {
            $tores .= <<<EOP
                <div><img src="img/image.png" alt="�V�����X" border="0" vspace="4"></div>
EOP;
        }
        */

        if ($_conf['iphone']) {
            $tores .= "<div id=\"{$res_id}\" class=\"res\"><div class=\"res-header\">";

            $no_class = 'no';
            $no_onclick = '';

            // �I���U�t���C��
            if ($this->thread->onthefly) {
                $GLOBALS['newres_to_show_flag'] = true;
                $no_class .= ' onthefly';
            // �V�����X��
            } elseif ($i > $this->thread->readnum) {
                $GLOBALS['newres_to_show_flag'] = true;
                $no_class .= ' newres';
            }

            // SPM
            if ($_conf['expack.spm.enabled']) {
                $no_onclick = " onclick=\"{$this->spmObjName}.show({$i},'{$res_id}',event)\"";
            }

            // �ԍ�
            $tores .= "<span class=\"{$no_class}\"{$no_onclick}>{$i}</span>";
            // ���O
            $tores .= " <span class=\"name\">{$name}</span>";
            // ���[��
            $tores .= " <span class=\"mail\">{$mail}</span>";
            // ���t��ID
            $tores .= " <span class=\"date-id\">{$date_id}</span></div>\n";
            // ���e
            $tores .= "<div class=\"message\">{$msg}</div></div>\n";
        } else {
            // �ԍ��i�I���U�t���C���j
            if ($this->thread->onthefly) {
                $GLOBALS['newres_to_show_flag'] = true;
                $tores .= "<div id=\"{$res_id}\" name=\"{$res_id}\">[<font color=\"{$STYLE['mobile_read_onthefly_color']}'\">{$i}</font>]";
            // �ԍ��i�V�����X���j
            } elseif ($i > $this->thread->readnum) {
                $GLOBALS['newres_to_show_flag'] = true;
                $tores .= "<div id=\"{$res_id}\" name=\"{$res_id}\">[<font color=\"{$STYLE['mobile_read_newres_color']}\">{$i}</font>]";
            // �ԍ�
            } else {
                $tores .= "<div id=\"{$res_id}\" name=\"{$res_id}\">[{$i}]";
            }

            // ���O
            if ($name) {
                $tores .= "{$name}: "; }
            // ���[��
            if ($mail) {
                $tores .= "{$mail}: ";
            }
            // ���t��ID
            $tores .= "{$date_id}<br>\n";
            // ���e
            $tores .= "{$msg}</div><hr>\n";
        }

        // �܂Ƃ߂ăt�B���^�F����
        if ($GLOBALS['word_fm'] && $GLOBALS['res_filter']['match'] != 'off') {
            if (is_string($_conf['k_filter_marker'])) {
                $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores, $_conf['k_filter_marker']);
            } else {
                $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores);
            }
        }

        // �S�p�p���X�y�[�X�J�i�𔼊p��
        if (!empty($_conf['mobile.save_packet'])) {
            $tores = mb_convert_kana($tores, 'rnsk'); // CP932 ���� ask �� �� �� < �ɕϊ����Ă��܂��悤��
        }

        return $tores;
    }

    // }}}
    // {{{ transName()

    /**
     * ���O��HTML�p�ɕϊ�����
     *
     * @param   string  $name   ���O
     * @return  string
     */
    public function transName($name)
    {
        $name = strip_tags($name);

        // �g���b�v��z�X�g�t���Ȃ番������
        if (($pos = strpos($name, '��')) !== false) {
            $trip = substr($name, $pos);
            $name = substr($name, 0, $pos);
        } else {
            $trip = null;
        }

        // ���������p���X�|�b�v�A�b�v�����N��
        $name = preg_replace_callback('/^( ?(?:&gt;|��)* ?)?([1-9]\\d{0,3})(?=\\D|$)/',
                                      array($this, 'quote_res_callback'), $name, 1);

        if ($trip) {
            $name .= $trip;
        } elseif ($name) {
            // �����������
            $name = $name . ' ';
            //if (in_array(0xF0 & ord(substr($name, -1)), array(0x80, 0x90, 0xE0))) {
            //    $name .= ' ';
            //}
        }

        return $name;
    }

    // }}}
    // {{{ transMsg()

    /**
     * dat�̃��X���b�Z�[�W��HTML�\���p���b�Z�[�W�ɕϊ�����
     *
     * @param   string  $msg    ���b�Z�[�W
     * @param   int     $mynum  ���X�ԍ�
     * @return  string
     */
    public function transMsg($msg, $mynum)
    {
        global $_conf;
        global $res_filter, $word_fm;
        global $pre_thumb_ignore_limit;

        $ryaku = false;

        // 2ch���`����dat
        if ($this->thread->dat_type == '2ch_old') {
            $msg = str_replace('���M', ',', $msg);
            $msg = preg_replace('/&amp(?=[^;])/', '&', $msg);
        }

        // &�␳
        $msg = preg_replace('/&(?!#?\\w+;)/', '&amp;', $msg);

        // >>1�̃����N����������O��
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;[1-9][\\d\\-]*)</[Aa]>}', '$1', $msg);

        // �傫������
        if (empty($_GET['k_continue']) && strlen($msg) > $_conf['mobile.res_size']) {
            // <br>�ȊO�̃^�O���������A������؂�l�߂�
            $msg = strip_tags($msg, '<br>');
            $msg = mb_strcut($msg, 0, $_conf['mobile.ryaku_size']);
            $msg = preg_replace('/ *<[^>]*$/', '', $msg);

            // >>1, >1, ��1, ����1�����p���X�|�b�v�A�b�v�����N��
            $msg = preg_replace_callback('/((?:&gt;|��){1,2})([1-9](?:[0-9\\-,])*)+/', array($this, 'quote_res_callback'), $msg);

            $msg .= "<a href=\"{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$mynum}&amp;k_continue=1&amp;offline=1{$_conf['k_at_a']}\"{$this->respopup_at}{$this->target_at}>��</a>";
            return $msg;
        }

        // �V�����X�̉摜�͕\�������𖳎�����ݒ�Ȃ�
        if ($mynum > $this->thread->readnum && $_conf['expack.ic2.newres_ignore_limit_k']) {
            $pre_thumb_ignore_limit = TRUE;
        }

        // �����̉��s�ƘA��������s������
        if ($_conf['mobile.strip_linebreaks']) {
            $msg = $this->stripLineBreaks($msg, 3, $this->_lineBreaksReplace);
        }

        // ���p��URL�Ȃǂ������N
        $msg = preg_replace_callback($this->_str_to_link_regex, array($this, 'link_callback'), $msg);

        return $msg;
    }

    // }}}
    // {{{ _abornedRes()

    /**
     * ���ځ[�񃌃X��HTML���擾����
     *
     * @param  string $res_id
     * @return string
     */
    protected function _abornedRes($res_id)
    {
        return <<<EOP
<div id="{$res_id}" name="{$res_id}" class="res aborned">&nbsp;</div>\n
EOP;
    }

    // }}}
    // {{{ getSpmObjJs()

    /**
     * �X�}�[�g�|�b�v�A�b�v���j���[�ɕK�v�ȃX���b�h�����i�[����JavaScript�R�[�h���擾
     */
    public function getSpmObjJs($retry = false)
    {
        global $_conf;

        if (isset(self::$_spm_objects[$this->spmObjName])) {
            return $retry ? self::$_spm_objects[$this->spmObjName] : '';
        }

        $ttitle_en = rawurlencode(base64_encode($this->thread->ttitle));

        $motothre_url = $this->thread->getMotoThread();
        $motothre_url = substr($motothre_url, 0, strlen($this->thread->ls) * -1);

        // �G�X�P�[�v
        $_spm_title = StrCtl::toJavaScript($this->thread->ttitle_hc);
        $_spm_url = addslashes($motothre_url);
        $_spm_host = addslashes($this->thread->host);
        $_spm_bbs = addslashes($this->thread->bbs);
        $_spm_key = addslashes($this->thread->key);
        $_spm_ls = addslashes($this->thread->ls);
        $_spm_b = ($_conf['view_forced_by_query']) ? "&b={$_conf['b']}" : '';

        $code = <<<EOJS
<script type="text/javascript">
//<![CDATA[
var {$this->spmObjName} = {
    'objName':'{$this->spmObjName}',
    'query':'&host={$_spm_host}&bbs={$_spm_bbs}&key={$_spm_key}&rescount={$this->thread->rescount}&ttitle_en={$ttitle_en}{$_spm_b}',
    'rc':'{$this->thread->rescount}',
    'title':'{$_spm_title}',
    'ttitle_en':'{$ttitle_en}',
    'url':'{$_spm_url}',
    'host':'{$_spm_host}',
    'bbs':'{$_spm_bbs}',
    'key':'{$_spm_key}',
    'ls':'{$_spm_ls}',
    'client':['{$_conf['b']}','{$_conf['client_type']}']
};
{$this->spmObjName}.show = (function(no,id,evt){SPM.show({$this->spmObjName},no,id,evt);});
{$this->spmObjName}.hide = SPM.hide; // (function(evt){SPM.hide(evt);});
//]]>
</script>\n
EOJS;

        self::$_spm_objects[$this->spmObjName] = $code;

        return $code;
    }

    // }}}
    // {{{ getSpmElementHtml()

    /**
     * �X�}�[�g�|�b�v�A�b�v���j���[�p��HTML�𐶐�����
     */
    static public function getSpmElementHtml()
    {
        global $_conf;

        return <<<EOP
<div id="spm">
<div id="spm-reply">
    <span id="spm-reply-quote" onclick="SPM.replyTo(true)">&gt;&gt;<span id="spm-num">???</span>�Ƀ��X</span>
    <span id="spm-reply-noquote" onclick="SPM.replyTo(false)">[���p�Ȃ�]</span>
</div>
<div id="spm-action"><select id="spm-select-target">
    <option value="name">���O</option>
    <option value="mail">���[��</option>
    <option value="id" selected>ID</option>
    <option value="msg">�{��</option>
</select>��<select id="spm-select-action">
    <option value="aborn" selected>���ځ[��</option>
    <option value="ng">NG</option>
<!-- <option value="search">����</option> -->
</select><input type="button" onclick="SPM.doAction()" value="OK"></div>
<img id="spm-closer" src="img/iphone/close.png" width="24" height="26" onclick="SPM.hide(event)">
</div>
EOP;
    }

    // }}}
    // {{{ �R�[���o�b�N���\�b�h
    // {{{ link_callback()

    /**
     * �������N�Ώە�����̎�ނ𔻒肵�đΉ������֐�/���\�b�h�ɓn��
     */
    public function link_callback($s)
    {
        global $_conf;

        $following = '';

        // preg_replace_callback()�ł͖��O�t���ŃL���v�`���ł��Ȃ��H
        if (!isset($s['link'])) {
            $s['link']  = $s[1];
            $s['quote'] = $s[5];
            $s['url']   = $s[8];
            $s['id']    = $s[12];
        }

        // �}�b�`�����T�u�p�^�[���ɉ����ĕ���
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
            if (strpos($s[7], '-') !== false) {
                return $this->quote_res_range_callback(array($s['quote'], $s[6], $s[7]));
            }
            return preg_replace_callback('/((?:&gt;|��)+ ?)?([1-9]\\d{0,3})(?=\\D|$)/', array($this, 'quote_res_callback'), $s['quote']);

        // http or ftp ��URL
        } elseif ($s['url']) {
            if ($s[9] == 'ftp') {
                return $s[0];
            }
            $url = preg_replace('/^t?(tps?)$/', 'ht$1', $s[9]) . '://' . $s[10];
            $str = $s['url'];
            $following = $s[11];
            // �E�B�L�y�f�B�A���{��ł�URL�ŁASJIS��2�o�C�g�����̏�ʃo�C�g(0x81-0x9F,0xE0-0xEF)�������Ƃ�
            if (P2Util::isUrlWikipediaJa($url) && strlen($following) > 0) {
                $leading = ord($following);
                if ((($leading ^ 0x90) < 32 && $leading != 0x80) || ($leading ^ 0xE0) < 16) {
                    $url .= rawurlencode(mb_convert_encoding($following, 'UTF-8', 'CP932'));
                    $str .= $following;
                    $following = '';
                }
            }

        // ID
        } elseif ($s['id'] && $_conf['flex_idpopup']) { // && $_conf['flex_idlink_k']
            return $this->idfilter_callback(array($s['id'], $s[13]));

        // ���̑��i�\���j
        } else {
            return strip_tags($s[0]);
        }

        // ime.nu���O��
        $url = preg_replace('|^([a-z]+://)ime\\.nu/|', '$1', $url);

        // URL���p�[�X
        $purl = @parse_url($url);
        if (!$purl || !isset($purl['host']) || strpos($purl['host'], '.') === false || $purl['host'] == '127.0.0.1') {
            return $str . $following;
        }

        // �G�X�P�[�v����Ă��Ȃ����ꕶ�����G�X�P�[�v
        $url = htmlspecialchars($url, ENT_QUOTES, 'Shift_JIS', false);
        $str = htmlspecialchars($str, ENT_QUOTES, 'Shift_JIS', false);
        //$following = htmlspecialchars($following, ENT_QUOTES, 'Shift_JIS', false);

        // URL������
        foreach ($this->_user_url_handlers as $handler) {
            if (FALSE !== ($link = call_user_func($handler, $url, $purl, $str, $this))) {
                return $link . $following;
            }
        }
        foreach ($this->_url_handlers as $handler) {
            if (FALSE !== ($link = call_user_func(array($this, $handler), $url, $purl, $str))) {
                return $link . $following;
            }
        }

        return $str . $following;
    }

    // }}}
    // {{{ ktai_exturl_callback()

    /**
     * ���g�їp�O��URL�ϊ�
     */
    public function ktai_exturl_callback($s)
    {
        global $_conf;

        $in_url = $s[1];

        // �ʋ΃u���E�U
        $tsukin_link = '';
        if ($_conf['mobile.use_tsukin']) {
            $tsukin_url = 'http://www.sjk.co.jp/c/w.exe?y=' . rawurlencode($in_url);
            if ($_conf['through_ime']) {
                $tsukin_url = P2Util::throughIme($tsukin_url);
            }
            $tsukin_link = '<a href="'.$tsukin_url.'">��</a>';
        }

        // jig�u���E�UWEB http://bwXXXX.jig.jp/fweb/?_jig_=
        $jig_link = '';
        /*
        $jig_url = 'http://bwXXXX.jig.jp/fweb/?_jig_=' . rawurlencode($in_url);
        if ($_conf['through_ime']) {
            $jig_url = P2Util::throughIme($jig_url);
        }
        $jig_link = '<a href="'.$jig_url.'">j</a>';
        */

        $sepa = '';
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

    // }}}
    // {{{ quote_res_callback()

    /**
     * �����p�ϊ�
     */
    public function quote_res_callback($s)
    {
        global $_conf;

        list($full, $qsign, $appointed_num) = $s;
        if ($appointed_num == '-') {
            return $s[0];
        }
        $qnum = intval($appointed_num);
        if ($qnum < 1 || $qnum > $this->thread->rescount) {
            return $s[0];
        }

        $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$appointed_num}";
        return "<a href=\"{$read_url}{$_conf['k_at_a']}\"{$this->respopup_at}{$this->target_at}>{$qsign}{$appointed_num}</a>";
    }

    // }}}
    // {{{ quote_res_range_callback()

    /**
     * �����p�ϊ��i�͈́j
     */
    public function quote_res_range_callback($s)
    {
        global $_conf;

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
            $to = min($from + $_conf['mobile.rnum_range'] - 1, $this->thread->rescount);
        } else {
            $to = min($to, $from + $_conf['mobile.rnum_range'] - 1, $this->thread->rescount);
        }

        $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$from}-{$to}";

        return "<a href=\"{$read_url}{$_conf['k_at_a']}\"{$this->target_at}>{$qsign}{$appointed_num}</a>";
    }

    // }}}
    // {{{ idfilter_callback()

    /**
     * ID�t�B���^�����O�����N�ϊ�
     *
     * @param   array   $s  ���K�\���Ƀ}�b�`�����v�f�̔z��
     * @return  string
     * @access  public
     */
    public function idfilter_callback($s)
    {
        global $_conf;

        $idstr = $s[0]; // ID:xxxxxxxxxx
        $id = $s[1];    // xxxxxxxxxx
        $idflag = '';   // �g��/PC���ʎq
        // ID��8���܂���10��(+�g��/PC���ʎq)�Ɖ��肵��
        /*
        if (strlen($id) % 2 == 1) {
            $id = substr($id, 0, -1);
            $idflag = substr($id, -1);
        } elseif (isset($s[2])) {
            $idflag = $s[2];
        }
        */

        $filter_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls=all&amp;offline=1&amp;idpopup=1&amp;field=id&amp;method=just&amp;match=on&amp;word=" . rawurlencode($id).$_conf['k_at_a'];

        if (isset($this->thread->idcount[$id]) && $this->thread->idcount[$id] > 0) {
            $num_ht = "(<a href=\"{$filter_url}\"{$this->target_at}>{$this->thread->idcount[$id]}</a>)";
        } else {
            return $idstr;
        }

        return "{$idstr}{$num_ht}";
    }

    // }}}
    // }}}
    // {{{ link_callback()����Ăяo�����URL�����������\�b�h
    /**
     * �����̃��\�b�h�͈����������Ώۃp�^�[���ɍ��v���Ȃ���FALSE��Ԃ��A
     * link_callback()��FALSE���Ԃ��Ă����$_url_handlers�ɓo�^����Ă��鎟�̊֐�/���\�b�h�ɏ��������悤�Ƃ���B
     */
    // {{{ plugin_linkURL()

    /**
     * URL�����N
     */
    public function plugin_linkURL($url, $purl, $str)
    {
        global $_conf;

        if (isset($purl['scheme'])) {
            // �g�їp�O��URL�ϊ�
            if ($_conf['mobile.use_tsukin']) {
                return $this->ktai_exturl_callback(array('', $url, $str));
            }
            // ime
            if ($_conf['through_ime']) {
                $link_url = P2Util::throughIme($url);
            } else {
                $link_url = $url;
            }
            return "<a href=\"{$link_url}\">{$str}</a>";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_link2chSubject()

    /**
     * 2ch bbspink �����N
     */
    public function plugin_link2chSubject($url, $purl, $str)
    {
        global $_conf;

        if (preg_match('{^http://(\\w+\\.(?:2ch\\.net|bbspink\\.com))/([^/]+)/$}', $url, $m)) {
            $subject_url = "{$_conf['subject_php']}?host={$m[1]}&amp;bbs={$m[2]}";
            return "<a href=\"{$url}\">{$str}</a> [<a href=\"{$subject_url}{$_conf['k_at_a']}\">��p2�ŊJ��</a>]";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_link2ch()

    /**
     * 2ch bbspink �X���b�h�����N
     */
    public function plugin_link2ch($url, $purl, $str)
    {
        global $_conf;

        if (preg_match('{^http://(\\w+\\.(?:2ch\\.net|bbspink\\.com))/test/read\\.cgi/([^/]+)/([0-9]+)(?:/([^/]+)?)?$}', $url, $m)) {
            if (!isset($m[4])) {
                $m[4] = '';
            }
            $read_url = "{$_conf['read_php']}?host={$m[1]}&amp;bbs={$m[2]}&amp;key={$m[3]}&amp;ls={$m[4]}";
            return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$str}</a>";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_link2chKako()

    /**
     * 2ch�ߋ����Ohtml
     */
    public function plugin_link2chKako($url, $purl, $str)
    {
        global $_conf;

        if (preg_match('{^http://(\\w+(?:\\.2ch\\.net|\\.bbspink\\.com))(?:/[^/]+/)?/([^/]+)/kako/\\d+(?:/\\d+)?/(\\d+)\\.html$}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}&amp;bbs={$m[2]}&amp;key={$m[3]}&amp;kakolog=" . rawurlencode($url);
            return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$str}</a>";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_linkMachi()

    /**
     * �܂�BBS / JBBS���������  �������N
     */
    public function plugin_linkMachi($url, $purl, $str)
    {
        global $_conf;

        if (preg_match('{^http://((\\w+\\.machibbs\\.com|\\w+\\.machi\\.to|jbbs\\.livedoor\\.(?:jp|com)|jbbs\\.shitaraba\\.com)(/\\w+)?)/bbs/read\\.(?:pl|cgi)\\?BBS=(\\w+)(?:&amp;|&)KEY=([0-9]+)(?:(?:&amp;|&)START=([0-9]+))?(?:(?:&amp;|&)END=([0-9]+))?(?=&|$)}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}&amp;bbs={$m[4]}&amp;key={$m[5]}";
            if ($m[6] || $m[7]) {
                $read_url .= "&amp;ls={$m[6]}-{$m[7]}";
            }
            return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$str}</a>";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_linkJBBS()

    /**
     * JBBS���������  �������N
     */
    public function plugin_linkJBBS($url, $purl, $str)
    {
        global $_conf;

        if (preg_match('{^http://(jbbs\\.livedoor\\.(?:jp|com)|jbbs\\.shitaraba\\.com)/bbs/read\\.cgi/(\\w+)/(\\d+)/(\\d+)(?:/((\\d+)?-(\\d+)?|[^/]+)|/?)$}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}/{$m[2]}&amp;bbs={$m[3]}&amp;key={$m[4]}&amp;ls={$m[5]}";
            return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$str}</a>";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_linkYouTube()

    /**
     * YouTube�����N�ϊ��v���O�C��
     *
     * Zend_Gdata_Youtube���g���΃T���l�C�����̑��̏����ȒP�Ɏ擾�ł��邪...
     *
     * @param   string $url
     * @param   array $purl
     * @param   string $str
     * @return  string|false
     */
    public function plugin_linkYouTube($url, $purl, $str)
    {
        global $_conf;

        // http://www.youtube.com/watch?v=Mn8tiFnAUAI
        if (preg_match('{^http://(www|jp)\\.youtube\\.com/watch\\?v=([0-9a-zA-Z_\\-]+)}', $url, $m)) {
            $subd = $m[1];
            $id = $m[2];

            if ($_conf['mobile.link_youtube'] == 2) {
                $link = $str;
            } else {
                $link = $this->plugin_linkURL($url, $purl, $str);
                if ($link === false) {
                    // plugin_linkURL()�������Ƌ@�\���Ă�����肱���ɂ͗��Ȃ�
                    if ($_conf['through_ime']) {
                        $link_url = P2Util::throughIme($url);
                    } else {
                        $link_url = $url;
                    }
                    $link = "<a href=\"{$link_url}\">{$str}</a>";
                }
            }

            return <<<EOP
{$link}<br><img src="http://img.youtube.com/vi/{$id}/default.jpg" alt="YouTube {$id}">
EOP;
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_viewImage()

    /**
     * �摜�����N�ϊ�
     */
    public function plugin_viewImage($url, $purl, $str)
    {
        global $_conf;

        if (P2Util::isUrlWikipediaJa($url)) {
            return FALSE;
        }

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

    // }}}
    // {{{ plugin_imageCache2()

    /**
     * �摜URL��ImageCache2�ϊ�
     */
    public function plugin_imageCache2($url, $purl, $str)
    {
        global $_conf;
        global $pre_thumb_unlimited, $pre_thumb_ignore_limit, $pre_thumb_limit_k;

        if (P2Util::isUrlWikipediaJa($url)) {
            return FALSE;
        }

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url) && empty($purl['query'])) {
            // �C�����C���v���r���[�̗L������
            if ($pre_thumb_unlimited || $pre_thumb_ignore_limit || $pre_thumb_limit_k > 0) {
                $inline_preview_flag = TRUE;
                $inline_preview_done = FALSE;
            } else {
                $inline_preview_flag = FALSE;
                $inline_preview_done = FALSE;
            }

            $url_ht = $url;
            $url = str_replace('&amp;', '&', $url);
            $url_en = rawurlencode($url);
            $img_str = null;

            $icdb = new IC2_DataObject_Images;

            // r=0:�����N;r=1:���_�C���N�g;r=2:PHP�ŕ\��
            // t=0:�I���W�i��;t=1:PC�p�T���l�C��;t=2:�g�їp�T���l�C��;t=3:���ԃC���[�W
            $img_url = 'ic2.php?r=0&amp;t=2&amp;uri=' . $url_en;
            $img_url2 = 'ic2.php?r=0&amp;t=2&amp;id=';
            $src_exists = FALSE;

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

                // �I���W�i���̗L�����m�F
                $_src_url = $this->thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_src_url)) {
                    $src_exists = TRUE;
                    $img_url = $img_url2 . $icdb->id;
                } else {
                    $img_url = $this->thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                }

                // �C�����C���v���r���[���L���̂Ƃ�
                $prv_url = null;
                if ($this->thumbnailer->ini['General']['inline'] == 1) {
                    // PC��read_new_k.php�ɃA�N�Z�X�����Ƃ���
                    if (!isset($this->inline_prvw) || !is_object($this->inline_prvw)) {
                        $this->inline_prvw = $this->thumbnailer;
                    }
                    $prv_url = $this->inline_prvw->thumbPath($icdb->size, $icdb->md5, $icdb->mime);

                    // �T���l�C���\���������ȓ��̂Ƃ�
                    if ($inline_preview_flag) {
                        // �v���r���[�摜������Ă��邩�ǂ�����img�v�f�̑���������
                        if (file_exists($prv_url)) {
                            $prv_size = explode('x', $this->inline_prvw->calc($icdb->width, $icdb->height));
                            $img_str = "<img src=\"{$prv_url}\" width=\"{$prvw_size[0]}\" height=\"{$prvw_size[1]}\">";
                        } else {
                            $r_type = ($this->thumbnailer->ini['General']['redirect'] == 1) ? 1 : 2;
                            if ($src_exists) {
                                $prv_url = "ic2.php?r={$r_type}&amp;t=1&amp;id={$icdb->id}";
                            } else {
                                $prv_url = "ic2.php?r={$r_type}&amp;t=1&amp;uri={$url_en}";
                            }
                            $img_str = "<img src=\"{$prv_url}\">";
                        }
                        $inline_preview_done = TRUE;
                    } else {
                        $img_str = '[p2:�����摜(�ݸ:' . $icdb->rank . ')]';
                    }
                }

                // �����X���^�C�����@�\��ON�ŃX���^�C���L�^����Ă��Ȃ��Ƃ���DB���X�V
                if (!is_null($this->img_memo) && strpos($icdb->memo, $this->img_memo) === false){
                    $update = new IC2_DataObject_Images;
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
                    $img_str = '<img src="ic2.php?r=2&amp;t=1&amp;uri=' . $url_en . $this->img_memo_query . '">';
                    $inline_preview_done = TRUE;
                } else {
                    $img_url .= $this->img_memo_query;
                }
            }

            // �\�����������f�N�������g
            if ($inline_preview_flag && $inline_preview_done) {
                $pre_thumb_limit_k--;
            }

            if (!empty($_SERVER['REQUEST_URI'])) {
                $backto = '&amp;from=' . rawurlencode($_SERVER['REQUEST_URI']);
            } else {
                $backto = '';
            }

            if (is_null($img_str)) {
                return sprintf('<a href="%s%s">[IC2:%s:%s]</a>',
                               $img_url,
                               $backto,
                               htmlspecialchars($purl['host']),
                               htmlspecialchars(basename($purl['path']))
                               );
            }

            if ($_conf['iphone']) {
                return "<a href=\"{$img_url}{$backto}\" target=\"_blank\">{$img_str}</a>"
                   //. ' <img class="ic2-show-info" src="img/s2a.png" width="16" height="16" onclick="ic2info.show('
                     . ' <input type="button" class="ic2-show-info" value="i" onclick="ic2info.show('
                     . "'{$url_ht}', '{$img_url}', '{$prv_url}', event)\">";
            } else {
                return "<a href=\"{$img_url}{$backto}\">{$img_str}</a>";
            }
        }

        return FALSE;
    }

    // }}}
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
