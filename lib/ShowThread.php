<?php
/**
 * rep2- �X���b�h��\������ �N���X
 */

$GLOBALS['last_hit_resnum'] = null;

// {{{ ShowThread

abstract class ShowThread
{
    // {{{ constants

    /**
     * �����N�Ƃ��Ĉ����p�^�[��
     *
     * @var string
     */
    const LINK_REGEX = '{
(?P<link>(<[Aa][ ].+?>)(.*?)(</[Aa]>)) # �����N�iPCRE�̓�����A�K�����̃p�^�[�����ŏ��Ɏ��s����j
|
(?:
  (?P<quote> # ���p
    ((?:&gt;|��){1,2}[ ]?) # ���p��
    (
      (?:[1-9]\\d{0,3}) # 1�ڂ̔ԍ�
      (?:
        (?:[ ]?(?:[,=]|�A)[ ]?[1-9]\\d{0,3})+ # �A��
        |
        -(?:[1-9]\\d{0,3})? # �͈�
      )?
    )
    (?=\\D|$)
  ) # ���p�����܂�
|                                  # PHP 5.3����ɂ���Ȃ�A����\'�̃G�X�P�[�v���O���ANOWDOC�ɂ���
  (?P<url>(ftp|h?t?tps?)://([0-9A-Za-z][\\w;/?:@=&$\\-_.+!*\'(),#%\\[\\]^~]+)) # URL
  ([^\\s<>]*) # URL�̒���A�^�Oor�z���C�g�X�y�[�X�������܂ł̕�����
|
  (?P<id>ID:[ ]?([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$)) # ID�i8,10�� +PC/�g�ю��ʃt���O�j
)
}x';

    /**
     * ���_�C���N�^�̎��
     *
     * @var int
     */
    const REDIRECTOR_NONE = 0;
    const REDIRECTOR_IMENU = 1;
    const REDIRECTOR_PINKTOWER = 2;
    const REDIRECTOR_MACHIBBS = 3;

    /**
     * NG���ځ[��̎��
     *
     * @var int
     */
    const ABORN = -1;
    const NG_NONE = 0;
    const NG_NAME = 1;
    const NG_MAIL = 2;
    const NG_ID = 4;
    const NG_MSG = 8;
    const NG_FREQ = 16;
    const NG_CHAIN = 32;
    const NG_AA = 64;

    // }}}
    // {{{ static properties

    /**
     * �܂Ƃߓǂ݃��[�h���̃X���b�h��
     *
     * @var int
     */
    static private $_matome_count = 0;

    /**
     * �{���ȊO��NG���ځ[��Ƀq�b�g��������
     *
     * @var int
     */
    static protected $_ngaborns_head_hits = 0;

    /**
     * �{����NG���ځ[��Ƀq�b�g��������
     *
     * @var int
     */
    static protected $_ngaborns_body_hits = 0;

    /**
     * getAnchorRegex() �̃L���b�V��
     *
     * @var array
     */
    static private $_anchorRegexes = array();

    /**
     * _getAnchorRegexParts() �̃L���b�V��
     *
     * @var array
     */
    static private $_anchorRegexParts = null;

    // }}}
    // {{{ properties

    /**
     * �܂Ƃߓǂ݃��[�h���̃X���b�h�ԍ�
     *
     * @var int
     */
    protected $_matome;

    /**
     * URL����������֐��E���\�b�h���Ȃǂ��i�[����z��
     * (�g�ݍ���)
     *
     * @var array
     */
    protected $_url_handlers;

    /**
     * URL����������֐��E���\�b�h���Ȃǂ��i�[����z��
     * (���[�U��`�A�g�ݍ��݂̂��̂��D��)
     *
     * @var array
     */
    protected $_user_url_handlers;

    /**
     * �p�oID�����ځ[�񂷂�
     *
     * @var bool
     */
    protected $_ngaborn_frequent;

    /**
     * NG or ���ځ[�񃌃X�����邩�ǂ���
     *
     * @var bool
     */
    protected $_has_ngaborns;

    /**
     * ���ځ[�񃌃X�ԍ������NG���X�ԍ����i�[����z��
     * array_intersect()�������悭�s�����߁A�Y�����郌�X�ԍ��͕�����ɃL���X�g���Ċi�[����
     *
     * @var array
     */
    protected $_aborn_nums;
    protected $_ng_nums;

    /**
     * ���_�C���N�^�̎��
     *
     * @var int
     */
    protected $_redirector;

    /**
     * �X���b�h�I�u�W�F�N�g
     *
     * @var ThreadRead
     */
    public $thread;

    /**
     * �A�N�e�B�u���i�[�E�I�u�W�F�N�g
     *
     * @var ActiveMona
     */
    public $activeMona;

    /**
     * �A�N�e�B�u���i�[���L�����ۂ�
     *
     * @var bool
     */
    public $am_enabled = false;

    /**
     * ���p���Ă��郌�X�ԍ���o�^�����z��
     *
     * @var array
     */
    protected $_quote_res_nums;


    /**
     * ���p�`�F�b�N�ς݃��X�ԍ��̔z��
     *
     * @var array
     */
    protected $_quote_res_nums_checked;

    /**
     * ���p�ϊ��ς݃��X�ԍ��̔z��
     *
     * @var array
     */
    protected $_quote_res_nums_done;

    /**
     * ���X�ԍ��`�F�b�N�̍ċA�̐[��
     *
     * @var int
     */
    private $_quote_check_depth;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    protected function __construct(ThreadRead $aThread, $matome = false)
    {
        global $_conf;

        // �X���b�h�I�u�W�F�N�g��o�^
        $this->thread = $aThread;

        // �܂Ƃߓǂ݃��[�h���ۂ�
        if ($matome) {
            $this->_matome = ++self::$_matome_count;
        } else {
            $this->_matome = false;
        }

        $this->_url_handlers = array();
        $this->_user_url_handlers = array();

        $this->_ngaborn_frequent = 0;
        if ($_conf['ngaborn_frequent']) {
            if ($_conf['ngaborn_frequent_dayres'] == 0) {
                $this->_ngaborn_frequent = $_conf['ngaborn_frequent'];
            } elseif ($this->thread->setDayRes() && $this->thread->dayres < $_conf['ngaborn_frequent_dayres']) {
                $this->_ngaborn_frequent = $_conf['ngaborn_frequent'];
            }
        }

        $this->_has_ngaborns = false;
        $this->_aborn_nums = array();
        $this->_ng_nums = array();

        if (P2Util::isHostBbsPink($this->thread->host)) {
            $this->_redirector = self::REDIRECTOR_PINKTOWER;
        } elseif (P2Util::isHost2chs($this->thread->host)) {
            $this->_redirector = self::REDIRECTOR_IMENU;
        } elseif (P2Util::isHostMachiBbs($this->thread->host)) {
            $this->_redirector = self::REDIRECTOR_MACHIBBS;
        } else {
            $this->_redirector = self::REDIRECTOR_NONE;
        }

        $this->_quote_res_nums = array();
        $this->_quote_res_nums_checked = array();
        $this->_quote_res_nums_done = array();
    }

    // }}}
    // {{{ getDatToHtml()

    /**
     * Dat��HTML�ϊ��������̂��擾����
     *
     * @param   bool $is_fragment
     * @return  bool|string
     */
    public function getDatToHtml($is_fragment = false)
    {
        return $this->datToHtml(true, $is_fragment);
    }

    // }}}
    // {{{ datToHtml()

    /**
     * Dat��HTML�ɕϊ����ĕ\������
     *
     * @param   bool $capture       true�Ȃ�ϊ����ʂ��o�͂����ɕԂ�
     * @param   bool $is_fragment   true�Ȃ�<div class="thread"></div>�ň͂܂Ȃ�
     * @return  bool|string
     */
    public function datToHtml($capture = false, $is_fragment = false)
    {
        global $_conf, $filter_hits, $last_hit_resnum;

        $aThread = $this->thread;

        // �\�����X�͈͂��w�肳��Ă��Ȃ����
        if (!$aThread->resrange) {
            $error = '<p><b>p2 error: {$this->resrange} is FALSE at datToHtml()</b></p>';
            if ($capture) {
                return $error;
            } else {
                echo $error;
                return false;
            }
        }

        $start = $aThread->resrange['start'];
        $to = $aThread->resrange['to'];
        $nofirst = $aThread->resrange['nofirst'];

        $is_ktai = $_conf['ktai'];
        $resFilter = ResFilter::getFilter();
        if ($resFilter && $resFilter->hasWord()) {
            $do_filtering = true;
            $nofirst = true;
        } else {
            $do_filtering = false;
        }

        $datlines = $aThread->datlines;
        $count = count($datlines);

        $buf = $is_fragment ? '' : "<div class=\"thread\">\n";

        // �܂� 1 ��\��
        if (!$nofirst) {
            $buf .= $this->transRes($datlines[0], 1);
        }

        // �A���̂��߁A�͈͊O��NG���ځ[��`�F�b�N
        if ($_conf['ngaborn_chain_all'] && empty($_GET['nong'])) {
            $pre = min($count, $start);
            for ($i = ($nofirst) ? 0 : 1; $i < $pre; $i++) {
                $n = $i + 1;
                list($name, $mail, $date_id, $msg) = $aThread->explodeDatLine($datlines[$i]);
                if (($id = $aThread->ids[$n]) !== null) {
                    $date_id = str_replace($aThread->idp[$n] . $id, "ID:$id", $date_id);
                }
                $this->_ngAbornCheck($n, strip_tags($name), $mail, $date_id, $id, $msg);
            }
        }

        // �t�B���^�����O
        if ($do_filtering) {
            $datlines = $resFilter->apply($this);
            $filter_hits = $resFilter->hits;
            $last_hit_resnum = $resFilter->last_hit_resnum;
        }

        // �w��͈͂�\��
        $i = 0;
        $n = 0;
        $rn = 0;

        if ($do_filtering) {
            if (!empty($resFilter->range)) {
                $start = $resFilter->range['start'];
                $to = $resFilter->range['to'];
            }
            $pattern = $resFilter->getPattern();
        } else {
            $pattern = null;
        }

        foreach ($datlines as $i => $ares) {
            if ($ares === null) {
                continue;
            }
            $n++;
            if ($i === 0 && !$nofirst) {
                continue;
            }
            if ($n < $start) {
                continue;
            }
            if ($n > $to) {
                break;
            }
            $rn = $i + 1;
            $buf .= $this->transRes($ares, $rn, $pattern);
            if (!$capture && $n % 10 == 0) {
                echo $buf;
                if ($do_filtering && !$is_ktai) {
                    echo "<script type=\"text/javascript\">filterCount({$n});</script>\n";
                }
                flush();
                $buf = '';
            }
        }

        if ($this->thread->readnum < $rn) {
            $this->thread->readnum = $rn;
        }

        if ($do_filtering && !$is_ktai) {
            $buf .= "<script type=\"text/javascript\">filterCount({$filter_hits});</script>\n";
        }

        if (!$is_fragment) {
            $buf .= "</div>\n";
        }

        if ($capture) {
            return $buf;
        } else {
            echo $buf;
            flush();
            return true;
        }
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
    abstract public function transRes($ares, $i);

    // }}}
    // {{{ transName()

    /**
     * ���O��HTML�p�ɕϊ�����
     *
     * @param   string  $name   ���O
     * @return  string
     */
    abstract public function transName($name);

    // }}}
    // {{{ transMsg()

    /**
     * dat�̃��X���b�Z�[�W��HTML�\���p���b�Z�[�W�ɕϊ�����
     *
     * @param   string  $msg    ���b�Z�[�W
     * @param   int     $mynum  ���X�ԍ�
     * @return  string
     */
    abstract public function transMsg($msg, $mynum);

    // }}}
    // {{{ replaceBeId()

    /**
     * BE�v���t�@�C�������N�ϊ�
     */
    public function replaceBeId($date_id, $i)
    {
        global $_conf;

        $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>Lv.\$2</a>";

        //<BE:23457986:1>
        $be_match = '|<BE:(\d+):(\d+)>|i';
        if (preg_match($be_match, $date_id)) {
            $date_id = preg_replace($be_match, $beid_replace, $date_id);

        } else {

            $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>?\$2</a>";
            $date_id = preg_replace('|BE: ?(\d+)-(#*)|i', $beid_replace, $date_id);
        }

        return $date_id;
    }

    // }}}
    // {{{ _ngAbornCheck()

    /**
     * NG���ځ[��`�F�b�N
     *
     * @param   int     $i          ���X�ԍ�
     * @param   string  $name       ���O��
     * @param   string  $mail       ���[����
     * @param   string  $date_id    ���t�EID��
     * @param   string  $id         ID
     * @param   string  $msg        ���X�{��
     * @param   bool    $nong       NG�`�F�b�N�����邩�ǂ���
     * @param   array  &$info       NG�̗��R���i�[�����ϐ��̎Q��
     * @return  int NG�^�C�v�BShowThread::NG_XXX �̃r�b�g�a�� ShowThread::ABORN
     */
    protected function _ngAbornCheck($i, $name, $mail, $date_id, $id, $msg, $nong = false, &$info = null)
    {
        global $_conf, $ngaborns_hits;

        $info = array();
        $type = self::NG_NONE;

        // {{{ �p�oID�`�F�b�N

        if ($this->_ngaborn_frequent && $id && $this->thread->idcount[$id] >= $_conf['ngaborn_frequent_num']) {
            if (!$_conf['ngaborn_frequent_one'] && $id == $this->thread->ids[1]) {
                // >>1 �͂��̂܂ܕ\��
            } elseif ($this->_ngaborn_frequent == 1) {
                $ngaborns_hits['aborn_freq']++;
                return $this->_markNgAborn($i, self::ABORN, false);
            } elseif (!$nong) {
                $ngaborns_hits['ng_freq']++;
                $type |= $this->_markNgAborn($i, self::NG_FREQ, false);
                $info[] = sprintf('�p�oID:%s(%d)', $id, $this->thread->idcount[$id]);
            }
        }

        // }}}
        // {{{ �A���`�F�b�N

        if ($_conf['ngaborn_chain'] && $this->_has_ngaborns &&
            preg_match_all('/(?:&gt;|��)([1-9][0-9\\-,]*)/', $msg, $matches)
        ) {
            $references = array_unique(preg_split('/[-,]+/',
                                                  trim(implode(',', $matches[1]), '-,'),
                                                  -1,
                                                  PREG_SPLIT_NO_EMPTY));
            $intersections = array_intersect($references, $this->_aborn_nums);
            $info_suffix = '';

            if ($intersections) {
                if ($_conf['ngaborn_chain'] == 1) {
                    $ngaborns_hits['aborn_chain']++;
                    return $this->_markNgAborn($i, self::ABORN, true);
                }
                if ($nong) {
                    $intersections = null;
                } else {
                    $info_suffix = '(' . (($_conf['ktai']) ? '����' : '���ځ[��') . ')';
                }
            } elseif (!$nong) {
                $intersections = array_intersect($references, $this->_ng_nums);
            }

            if ($intersections) {
                $ngaborns_hits['ng_chain']++;
                $type |= $this->_markNgAborn($i, self::NG_CHAIN, true);
                $info[] = sprintf('�A��NG:&gt;&gt;%d%s', current($intersections), $info_suffix);
            }
        }

        // }}}
        // {{{ ���ځ[��`�F�b�N

        // ���ځ[�񃌃X
        if ($this->abornResCheck($i) !== false) {
            $ngaborns_hits['aborn_res']++;
            return $this->_markNgAborn($i, self::ABORN, false);
        }

        // ���ځ[��l�[��
        if ($this->ngAbornCheck('aborn_name', $name) !== false) {
            $ngaborns_hits['aborn_name']++;
            return $this->_markNgAborn($i, self::ABORN, false);
        }

        // ���ځ[�񃁁[��
        if ($this->ngAbornCheck('aborn_mail', $mail) !== false) {
            $ngaborns_hits['aborn_mail']++;
            return $this->_markNgAborn($i, self::ABORN, false);
        }

        // ���ځ[��ID
        if ($this->ngAbornCheck('aborn_id', $date_id) !== false) {
            $ngaborns_hits['aborn_id']++;
            return $this->_markNgAborn($i, self::ABORN, false);
        }

        // ���ځ[�񃁃b�Z�[�W
        if ($this->ngAbornCheck('aborn_msg', $msg) !== false) {
            $ngaborns_hits['aborn_msg']++;
            return $this->_markNgAborn($i, self::ABORN, true);
        }

        // }}}

        if ($nong) {
            return $type;
        }

        // {{{ NG�`�F�b�N

        // NG�l�[���`�F�b�N
        if ($this->ngAbornCheck('ng_name', $name) !== false) {
            $ngaborns_hits['ng_name']++;
            $type |= $this->_markNgAborn($i, self::NG_NAME, false);
        }

        // NG���[���`�F�b�N
        if ($this->ngAbornCheck('ng_mail', $mail) !== false) {
            $ngaborns_hits['ng_mail']++;
            $type |= $this->_markNgAborn($i, self::NG_MAIL, false);
        }

        // NGID�`�F�b�N
        if ($this->ngAbornCheck('ng_id', $date_id) !== false) {
            $ngaborns_hits['ng_id']++;
            $type |= $this->_markNgAborn($i, self::NG_ID, false);
        }

        // NG���b�Z�[�W�`�F�b�N
        $a_ng_msg = $this->ngAbornCheck('ng_msg', $msg);
        if ($a_ng_msg !== false) {
            $ngaborns_hits['ng_msg']++;
            $type |= $this->_markNgAborn($i, self::NG_MSG, true);
            $info[] = sprintf('NG%s:%s',
                              ($_conf['ktai']) ? 'ܰ��' : '���[�h',
                              htmlspecialchars($a_ng_msg, ENT_QUOTES));
        }

        // }}}

        return $type;
    }

    // }}}
    // {{{ _markNgAborn()

    /**
     * NG���ځ[��Ƀq�b�g�������X�ԍ����L�^����
     *
     * @param   int $num        ���X�ԍ�
     * @param   int $type       NG���ځ[��̎��
     * @param   bool $isBody    �{���Ƀq�b�g�������ǂ���
     * @return  int $type�Ɠ����l
     */
    protected function _markNgAborn($num, $type, $isBody)
    {
        if ($type) {
            if ($isBody) {
                self::$_ngaborns_body_hits++;
            } else {
                self::$_ngaborns_head_hits++;
            }

            // array_intersect()�������悭�s�����߁A���X�ԍ��𕶎���^�ɃL���X�g����
            $str = (string)$num;
            if ($type == self::ABORN) {
                $this->_aborn_nums[$num] = $str;
            } else {
                $this->_ng_nums[$num] = $str;
            }

            $this->_has_ngaborns = true;
        }

        return $type;
    }

    // }}}
    // {{{ ngAbornCheck()

    /**
     * NG���ځ[��`�F�b�N
     */
    public function ngAbornCheck($code, $resfield, $ic = false)
    {
        global $ngaborns;

        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('ngAbornCheck()');

        if (isset($ngaborns[$code]['data']) && is_array($ngaborns[$code]['data'])) {
            $bbs = $this->thread->bbs;
            $title = $this->thread->ttitle_hc;

            foreach ($ngaborns[$code]['data'] as $k => $v) {
                // �`�F�b�N
                if (isset($v['bbs']) && in_array($bbs, $v['bbs']) == false) {
                    continue;
                }

                // �^�C�g���`�F�b�N
                if (isset($v['title']) && stripos($title, $v['title']) === false) {
                    continue;
                }

                // ���[�h�`�F�b�N
                // ���K�\��
                if ($v['regex']) {
                    $re_method = $v['regex'];
                    /*if ($re_method($v['word'], $resfield, $matches)) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return htmlspecialchars($matches[0], ENT_QUOTES);
                    }*/
                     if ($re_method($v['word'], $resfield)) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
               // �啶���������𖳎�
                } elseif ($ic || $v['ignorecase']) {
                    if (stripos($resfield, $v['word']) !== false) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
                // �P���ɕ����񂪊܂܂�邩�ǂ������`�F�b�N
                } else {
                    if (strpos($resfield, $v['word']) !== false) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
                }
            }
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
        return false;
    }

    // }}}
    // {{{ abornResCheck()

    /**
     * ���背�X�̓������ځ[��`�F�b�N
     */
    public function abornResCheck($resnum)
    {
        global $ngaborns;

        $target = $this->thread->host . '/' . $this->thread->bbs . '/' . $this->thread->key . '/' . $resnum;

        if (isset($ngaborns['aborn_res']['data']) && is_array($ngaborns['aborn_res']['data'])) {
            foreach ($ngaborns['aborn_res']['data'] as $k => $v) {
                if ($ngaborns['aborn_res']['data'][$k]['word'] == $target) {
                    $this->ngAbornUpdate('aborn_res', $k);
                    return true;
                }
            }
        }
        return false;
    }

    // }}}
    // {{{ ngAbornUpdate()

    /**
     * NG/���ځ`������Ɖ񐔂��X�V
     */
    public function ngAbornUpdate($code, $k)
    {
        global $ngaborns;

        if (isset($ngaborns[$code]['data'][$k])) {
            $ngaborns[$code]['data'][$k]['lasttime'] = date('Y/m/d G:i'); // HIT���Ԃ��X�V
            if (empty($ngaborns[$code]['data'][$k]['hits'])) {
                $ngaborns[$code]['data'][$k]['hits'] = 1; // ��HIT
            } else {
                $ngaborns[$code]['data'][$k]['hits']++; // HIT�񐔂��X�V
            }
        }
    }

    // }}}
    // {{{ addURLHandler()

    /**
     * ���[�U��`URL�n���h���i���b�Z�[�W����URL������������֐��j��ǉ�����
     *
     * �n���h���͍ŏ��ɒǉ����ꂽ���̂��珇�ԂɎ��s�����
     * URL�̓n���h���̕Ԃ�l�i������j�Œu�������
     * FALSE���A�����ꍇ�͎��̃n���h���ɏ������ς˂���
     *
     * ���[�U��`URL�n���h���̈�����
     *  1. string $url  URL
     *  2. array  $purl URL��parse_url()��������
     *  3. string $str  �p�^�[���Ƀ}�b�`����������AURL�Ɠ������Ƃ�����
     *  4. object $aShowThread �Ăяo�����̃I�u�W�F�N�g
     * �ł���
     * ���FALSE��Ԃ��A�����ŏ������邾���̊֐���o�^���Ă��悢
     *
     * @param   callback $function  �R�[���o�b�N���\�b�h
     * @return  void
     * @access  public
     * @todo    ���[�U��`URL�n���h���̃I�[�g���[�h�@�\������
     */
    public function addURLHandler($function)
    {
        $this->_user_url_handlers[] = $function;
    }

    // }}}
    // {{{ stripLineBreaks()

    /**
     * �����̉��s�ƘA��������s����菜��
     *
     * @param string $msg
     * @param string $replacement
     * @return string
     */
    public function stripLineBreaks($msg, $replacement = ' <br><br> ')
    {
        if (P2_MBREGEX_AVAILABLE) {
            $msg = mb_ereg_replace('(?:[\\s�@]*<br>)+[\\s�@]*$', '', $msg);
            $msg = mb_ereg_replace('(?:[\\s�@]*<br>){3,}', $replacement, $msg);
        } else {
            mb_convert_variables('UTF-8', 'CP932', $msg, $replacement);
            $msg = preg_replace('/(?:[\\s\\x{3000}]*<br>)+[\\s\\x{3000}]*$/u', '', $msg);
            $msg = preg_replace('/(?:[\\s\\x{3000}]*<br>){3,}/u', $replacement, $msg);
            $msg = mb_convert_encoding($msg, 'CP932', 'UTF-8');
        }

        return $msg;
    }

    // }}}
    // {{{ transLink()

    /**
     * �����N�Ώە������ϊ�����
     *
     * @param   string $str
     * @return  string
     */
    public function transLink($str)
    {
        return preg_replace_callback(self::LINK_REGEX, array($this, 'transLinkDo'), $str);
    }

    // }}}
    // {{{ transLinkDo()

    /**
     * �����N�Ώە�����̎�ނ𔻒肵�đΉ������֐�/���\�b�h�ɓn��
     *
     * @param   array   $s
     * @return  string
     */
    public function transLinkDo(array $s)
    {
        global $_conf;

        $orig = $s[0];
        $following = '';

        // PHP 5.2.7 ������ preg_replace_callback() �ł͖��O�t���ߊl���W�����g���Ȃ��̂�
        /*
        if (!array_key_exists('link', $s)) {
            $s['link']  = $s[1];
            $s['quote'] = $s[5];
            $s['url']   = $s[8];
            $s['id']    = $s[12];
        }
        */

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
                return $this->quoteResRange($s['quote'], $s[6], $s[7]);
            }
            return preg_replace_callback('/((?:&gt;|��)+ ?)?([1-9]\\d{0,3})(?=\\D|$)/',
                                         array($this, 'quoteResCallback'), $s['quote']);

        // http or ftp ��URL
        } elseif ($s['url']) {
            if ($_conf['ktai'] && $s[9] == 'ftp') {
                return $orig;
            }
            $url = preg_replace('/^t?(tps?)$/', 'ht$1', $s[9]) . '://' . $s[10];
            $str = $s['url'];
            $following = $s[11];
            if (strlen($following) > 0) {
                // �E�B�L�y�f�B�A���{��ł�URL�ŁASJIS��2�o�C�g�����̏�ʃo�C�g
                // (0x81-0x9F,0xE0-0xEF)�������Ƃ�
                if (P2Util::isUrlWikipediaJa($url)) {
                    $leading = ord($following);
                    if ((($leading ^ 0x90) < 32 && $leading != 0x80) || ($leading ^ 0xE0) < 16) {
                        $url .= rawurlencode(mb_convert_encoding($following, 'UTF-8', 'CP932'));
                        $str .= $following;
                        $following = '';
                    }
                } elseif (strpos($following, 'tp://') !== false) {
                    // �S�p�X�y�[�X+URL���̏ꍇ������̂ōă`�F�b�N
                    $following = $this->transLink($following);
                }
            }

        // ID
        } elseif ($s['id'] && $_conf['flex_idpopup']) { // && $_conf['flex_idlink_k']
            return $this->idFilter($s['id'], $s[13]);

        // ���̑��i�\���j
        } else {
            return strip_tags($orig);
        }

        // ���_�C���N�^���O��
        switch ($this->_redirector) {
            case self::REDIRECTOR_IMENU:
                $url = preg_replace('{^([a-z]+://)ime\\.nu/}', '$1', $url);
                break;
            case self::REDIRECTOR_PINKTOWER:
                $url = preg_replace('{^([a-z]+://)pinktower\\.com/}', '$1', $url);
                break;
            case self::REDIRECTOR_MACHIBBS:
                $url = preg_replace('{^[a-z]+://machi(?:bbs\\.com|\\.to)/bbs/link\\.cgi\\?URL=}', '', $url);
                break;
        }

        // �G�X�P�[�v����Ă��Ȃ����ꕶ�����G�X�P�[�v
        $url = htmlspecialchars($url, ENT_QUOTES, 'Shift_JIS', false);
        $str = htmlspecialchars($str, ENT_QUOTES, 'Shift_JIS', false);
        // ���ԎQ�ƁE���l�Q�Ƃ����S�Ƀf�R�[�h���悤�Ƃ���ƕ��ׂ��傫�����A
        // "&"�ȊO�̓��ꕶ���͂قƂ�ǂ̏ꍇURL�G���R�[�h����Ă���͂��Ȃ̂�
        // ���r���[�ɋÂ��������͂����A"&amp;"��"&"�̂ݍĕϊ�����B
        $raw_url = str_replace('&amp;', '&', $url);

        // URL���p�[�X�E�z�X�g������
        $purl = @parse_url($raw_url);
        if (!$purl || !array_key_exists('host', $purl) ||
            strpos($purl['host'], '.') === false ||
            $purl['host'] == '127.0.0.1' ||
            //HostCheck::isAddressLocal($purl['host']) ||
            //HostCheck::isAddressPrivate($purl['host']) ||
            P2Util::isHostExample($purl['host']))
        {
            return $orig;
        }
        // URL�̃}�b�`���O��"&amp;"���l�����Ȃ��čςނ悤�ɁA����URL��o�^���Ă���
        $purl[0] = $raw_url;

        // URL������
        foreach ($this->_user_url_handlers as $handler) {
            if (false !== ($link = call_user_func($handler, $url, $purl, $str, $this))) {
                return $link . $following;
            }
        }
        foreach ($this->_url_handlers as $handler) {
            if (false !== ($link = $this->$handler($url, $purl, $str))) {
                return $link . $following;
            }
        }

        return $orig;
    }

    // }}}
    // {{{ idFilter()

    /**
     * ID�t�B���^�����O�ϊ�
     *
     * @param   string  $idstr  ID:xxxxxxxxxx
     * @param   string  $id        xxxxxxxxxx
     * @return  string
     */
    abstract public function idFilter($idstr, $id);

    // }}}
    // {{{ idFilterCallback()

    /**
     * ID�t�B���^�����O�ϊ�
     *
     * @param   array   $s  ���K�\���Ƀ}�b�`�����v�f�̔z��
     * @return  string
     */
    final public function idFilterCallback(array $s)
    {
        return $this->idFilter($s[0], $s[1]);
    }

    // }}}
    // {{{ quoteRes()

    /**
     * ���p�ϊ��i�P�Ɓj
     *
     * @param   string  $full           >>1
     * @param   string  $qsign          >>
     * @param   string  $appointed_num    1
     * @return  string
     */
    abstract public function quoteRes($full, $qsign, $appointed_num);

    // }}}
    // {{{ quoteResCallback()

    /**
     * ���p�ϊ��i�P�Ɓj
     *
     * @param   array   $s  ���K�\���Ƀ}�b�`�����v�f�̔z��
     * @return  string
     */
    final public function quoteResCallback(array $s)
    {
        return $this->quoteRes($s[0], $s[1], $s[2]);
    }

    // }}}
    // {{{ quoteResRange()

    /**
     * ���p�ϊ��i�͈́j
     *
     * @param   string  $full           >>1-100
     * @param   string  $qsign          >>
     * @param   string  $appointed_num    1-100
     * @return  string
     */
    abstract public function quoteResRange($full, $qsign, $appointed_num);

    // }}}
    // {{{ quoteResRangeCallback()

    /**
     * ���p�ϊ��i�͈́j
     *
     * @param   array   $s  ���K�\���Ƀ}�b�`�����v�f�̔z��
     * @return  string
     */
    final public function quoteResRangeCallback(array $s)
    {
        return $this->quoteResRange($s[0], $s[1], $s[2]);
    }

    // }}}
    // {{{ checkQuoteResNums()

    /**
     * HTML���b�Z�[�W���̈��p���X�̔ԍ����ċA�`�F�b�N����
     */
    public function checkQuoteResNums($res_num, $name, $msg)
    {
        $this->_quote_check_depth = 0;
        return $this->_checkQuoteResNums($res_num, $name, $msg);
    }

    // }}}
    // {{{ _checkQuoteResNums()

    /**
     * HTML���b�Z�[�W���̈��p���X�̔ԍ����ċA�`�F�b�N����
     */
    private function _checkQuoteResNums($res_num, $name, $msg)
    {
        // �ċA���~�b�^
        if ($this->_quote_check_depth > 30) {
            return array();
        } else {
            $this->_quote_check_depth++;
        }

        if (array_key_exists($res_num, $this->_quote_res_nums)) {
            return $this->_quote_res_nums[$res_num];
        }

        $aThread = $this->thread;

        $quote_res_nums = array();

        $name = preg_replace('/(��.*)/', '', $name, 1);

        // ���O
        if (preg_match('/[1-9]\\d*/', $name, $matches)) {
            $a_quote_res_num = (int)$matches[0];
            $a_quote_res_idx = $a_quote_res_num - 1;

            if ($a_quote_res_num) {
                $quote_res_nums[] = $a_quote_res_num;

                // �������g�̔ԍ��Ɠ���łȂ���΁A
                if ($a_quote_res_num != $res_num) {
                    // �`�F�b�N���Ă��Ȃ��ԍ����ċA�`�F�b�N
                    if (!isset($this->_quote_res_nums_checked[$a_quote_res_num])) {
                        $this->_quote_res_nums_checked[$a_quote_res_num] = true;
                        if (isset($aThread->datlines[$a_quote_res_idx])) {
                            $datalinear = $aThread->explodeDatLine($aThread->datlines[$a_quote_res_idx]);
                            $quote_name = $datalinear[0];
                            $quote_msg = $aThread->datlines[$a_quote_res_idx];
                            $quote_res_nums = array_merge($quote_res_nums,
                                                          $this->_checkQuoteResNums($a_quote_res_num,
                                                                                    $quote_name,
                                                                                    $quote_msg));
                        }
                     }
                 }
             }
            // $name=preg_replace("/([0-9]+)/", "", $name, 1);
        }

        // >>1�̃����N����������O��
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;[1-9][\\d\\-]*)</[Aa]>}', '$1', $msg);

        //echo $msg;
        if (preg_match_all('/(?:&gt;|��)+ ?([1-9](?:[0-9\\- ,=.]|�A)*)/', $msg, $out, PREG_PATTERN_ORDER)) {

            foreach ($out[1] as $numberq) {
                //echo $numberq;
                if (preg_match_all('/[1-9]\\d*/', $numberq, $matches, PREG_PATTERN_ORDER)) {

                    foreach ($matches[0] as $a_quote_res_num) {
                        $a_quote_res_num = (int)$a_quote_res_num;
                        $a_quote_res_idx = $a_quote_res_num - 1;

                        //echo $a_quote_res_num;

                        if (!$a_quote_res_num) {break;}
                        $quote_res_nums[] = $a_quote_res_num;

                        // �������g�̔ԍ��Ɠ���łȂ���΁A
                        if ($a_quote_res_num != $res_num) {
                            // �`�F�b�N���Ă��Ȃ��ԍ����ċA�`�F�b�N
                            if (!isset($this->_quote_res_nums_checked[$a_quote_res_num])) {
                                $this->_quote_res_nums_checked[$a_quote_res_num] = true;
                                if (isset($aThread->datlines[$a_quote_res_idx])) {
                                    $datalinear = $aThread->explodeDatLine($aThread->datlines[$a_quote_res_idx]);
                                    $quote_name = $datalinear[0];
                                    $quote_msg = $aThread->datlines[$a_quote_res_idx];
                                    $quote_res_nums = array_merge($quote_res_nums,
                                                                  $this->_checkQuoteResNums($a_quote_res_num,
                                                                                            $quote_name,
                                                                                            $quote_msg));
                                }
                             }
                         }

                     }

                }

            }

        }

        if (count($quote_res_nums)) {
            sort($quote_res_nums, SORT_NUMERIC);
            $this->_quote_res_nums[$res_num] = array_unique($quote_res_nums);
            $quote_res_nums = $this->_quote_res_nums[$res_num];
        }

        return $quote_res_nums;
    }

    // }}}
    // {{{ getAnchorRegex()

    /**
     * �A���J�[�̐��K�\����Ԃ�
     *
     * @param   string  $pattern  ex)'/%full%/'
     * @param   boolean $unicode
     * @return  string
     */
    static public function getAnchorRegex($pattern, $unicode = false)
    {
        if (!array_key_exists($pattern, self::$_anchorRegexes)) {
            self::$_anchorRegexes[$pattern] = strtr($pattern, self::_getAnchorRegexParts());
            // �卷�͂Ȃ��� compileMobile2chUriCallBack() �̂悤�� preg_relplace_callback()���Ă����������B
        }
        if ($unicode) {
            return StrSjis::toUnicodePattern($_anchorRegexes[$pattern]);
        }
        return self::$_anchorRegexes[$pattern];
    }

    // }}}
    // {{{ _getAnchorRegexParts()

    /**
     * �A���J�[�̍\���v�f�i���K�\���p�[�c�̔z��j��Ԃ�
     *
     * @param   void
     * @return  string
     */
    static private function _getAnchorRegexParts()
    {
        if (!is_null(self::$_anchorRegexParts)) {
            return self::$_anchorRegexParts;
        }

        $anchor = array();

        // �A���J�[�̍\���v�f�i���K�\���p�[�c�̔z��j

        // �󔒕���
        $anchor_space = '(?:[ ]|�@)';
        //$anchor[' '] = '';

        // �A���J�[���p�q >>
        $anchor['prefix'] = "(?:&gt;|��|&lt;|��|�r|�t|��){1,2}{$anchor_space}*\.?";

        // ����
        $anchor['a_digit'] = '(?:\\d|�O|�P|�Q|�R|�S|�T|�U|�V|�W|�X)';
        /*
        $anchor[0] = '(?:0|�O)';
        $anchor[1] = '(?:1|�P)';
        $anchor[2] = '(?:2|�Q)';
        $anchor[3] = '(?:3|�R)';
        $anchor[4] = '(?:4|�S)';
        $anchor[5] = '(?:5|�T)';
        $anchor[6] = '(?:6|�U)';
        $anchor[7] = '(?:7|�V)';
        $anchor[8] = '(?:8|�W)';
        $anchor[9] = '(?:9|�X)';
        */

        // �͈͎w��q
        // -|�]|�[ = HYPHEN-MINUS | HYPHEN | KATAKANA-HIRAGANA PROLONGED SOUND MARK
        $anchor['range_delimiter'] = '(?:-|\\x81\\x5d|\\x81\\x5b)'; // [\\-\\x{2010}\\x{30fc}]

        // �񋓎w��q
        $anchor['delimiter'] = "{$anchor_space}?(?:[,=+]|�A|�E|��|�C){$anchor_space}?";

        // ���ځ[��p�A���J�[���p�q
        //$anchor['prefix_abon'] = "&gt;{1,2}{$anchor_space}?";

        // ���X�ԍ�
        $anchor['a_num'] = sprintf('%s{1,4}', $anchor['a_digit']);

        // ���X�͈�
        /*
        $anchor['a_range'] = sprintf('%s(?:%s%s)?',
            $anchor['a_num'], $anchor['range_delimiter'], $anchor['a_num']
        );
        */
        $anchor['a_range'] = sprintf('%s(?:%s(?:%s)?%s)?',
            $anchor['a_num'], $anchor['range_delimiter'], $anchor['prefix'], $anchor['a_num']
        );

        // ���X�͈̗͂�
        $anchor['ranges'] = sprintf('%s(?:%s%s)*(?!%s)',
            $anchor['a_range'], $anchor['delimiter'], $anchor['a_range'], $anchor['a_digit']
        );

        // ���X�ԍ��̗�
        $anchor['nums'] = sprintf('%s(?:%s%s)*(?!%s)',
            $anchor['a_num'], $anchor['delimiter'], $anchor['a_num'], $anchor['a_digit']
        );

        // �A���J�[�S��
        $anchor['full'] = sprintf('(%s)(%s)', $anchor['prefix'], $anchor['ranges']);

        // getAnchorRegex() �� strtr() �u���p��key�� '%key%' �ɕϊ�����
        foreach ($anchor as $k => $v) {
            $anchor['%' . $k . '%'] = $v;
            unset($anchor[$k]);
        }

        self::$_anchorRegexParts = $anchor;

        return self::$_anchorRegexParts;
    }

    // }}}
    // {{{_buildStrToLinkRegex()

    /**
     * �����N�Ƃ��Ĉ����p�^�[����Ԃ�
     *
     * @param   void
     * @return  string
     */
    static protected function _buildStrToLinkRegex()
    {
        return '{'
            . '(?P<link>(<[Aa] .+?>)(.*?)(</[Aa]>))' // �����N�iPCRE�̓�����A�K�����̃p�^�[�����ŏ��Ɏ��s����j
            . '|'
            . '(?:'
            .   '(?P<quote>' // ���p
            .       $this->getAnchorRegex('%full%')
            .   ')'
            . '|'
            .   '(?P<url>'
            .       '(ftp|h?ttps?|tps?)://([0-9A-Za-z][\\w!#%&+*,\\-./:;=?@\\[\\]^~]+)' // URL
            .   ')'
            . '|'
            .   '(?P<id>ID: ?([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$))' // ID�i8,10�� +PC/�g�ю��ʃt���O�j
            . ')'
            . '}';
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
