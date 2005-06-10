<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 - �X���b�h��\������ �N���X PC�p
*/

require_once (P2_LIBRARY_DIR . '/showthreadpc.class.php');

// {{{ class ShowThreadTree

class ShowThreadTree extends ShowThreadPc {

    // {{{ properties

    /**
     * �c���[�\����ۑ�����z��
     *
     * @access  public
     * @var array
     */
    var $tree;

    /**
     * �c���[�쐬��⏕����z��
     * �c���[������背�X�z���̕��������o���̂ɂ��g����
     *
     * @access  public
     * @var array
     */
    var $node;

    /**
     * �m�[�h�̃}�[�J�[
     * dt�v�f�̍ŏ��ɕ\������
     *
     * @access  private
     * @var array
     */
    var $marker;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^ (PHP4 style)
     *
     * @param   object  $aThread    �X���b�h �I�u�W�F�N�g
     */
    function ShowThreadTree(&$aThread)
    {
        $this->__construct($aThread);
    }

    /**
     * �R���X�g���N�^ (PHP5 style)
     *
     * @param   object  $aThread    �X���b�h �I�u�W�F�N�g
     */
    function __construct(&$aThread)
    {
        parent::__construct($aThread);
        $this->mkTree();
        $this->marker = array();
        //$this->marker['root'] = '&clubs;';
        //$this->marker['root'] = '��';
        $this->marker['root'] = '';
        $this->marker['branch'] = '��';
        $this->marker['suekko'] = '��';
    }

    // }}}
    // {{{ mkTree()

    /**
     * dat�̃c���[�\������͂���
     *
     * @access  private
     * @return  void
     */
    function mkTree()
    {
        $this->tree = array();
        $this->node = array();

        $this->node[0] = &$this->tree;

        for ($i = 1; $i <= $this->pDatCount; $i++) {
            if (($parent = $this->pDatLines[$i]['parent']) == 0) {
                $this->tree[$i] = array();
                $this->node[$i] = &$this->tree[$i];
            } else {
                $this->node[$parent][$i] = array();
                $this->node[$i] = &$this->node[$parent][$i];
            }
        }
    }

    // }}}
    // {{{ datToTree()

    /**
     * dat���c���[�\������
     *
     * @access  public
     * @return  void
     */
    function datToTree()
    {
        if (!$this->thread->resrange) {
            echo '<b>p2 error: {$this->resrange} is false at datToHtml() in lib/threadread.class.php</b>';
        }

        $status_title = htmlspecialchars($this->thread->itaj).' / '.$this->thread->ttitle_hd;
        $status_title = str_replace("'", "\'", $status_title);
        $status_title = str_replace('"', "\'\'", $status_title);
        echo "<dl onmouseover=\"window.top.status='{$status_title}';\">\n";

        $this->transNode();

        echo '</dl>'."\n";
    }

    // }}}
    // {{{ transNode()

    /**
     * dat�̑S���܂��͈ꕔ���ċA�I�ɕ\������
     *
     * @access  public
     * @param   mixed   $nodeID ���X�ԍ��i�����j�������͔C�ӂ̃��X�z���̃m�[�h�i�������̔z��j
     * @param   array   $ancestors  �e���X�A�e���X�̐e���X�A...�̃��X�ԍ��������Ă���z��
     * @param   integer $loops  �o�O�ɂ�閳�����[�v���Ď����邽�߂̍ċA��
     * @return void
     */
    function transNode($nodeID = NULL, $ancestors = array(), $loops = 0)
    {
        global $_conf, $_exconf, $res_filter, $word_fm, $STYLE;
        static $hits = 0; // �t�B���^�����Ƀ}�b�`�������Ԃɕt������B

        if (is_array($nodeID)) {
            $parent_node = $nodeID;
        } elseif (is_null($nodeID)) {
            $parent_node = $this->tree;
        } elseif (is_int($nodeID) && isset($this->node[$nodeID])) {
            $parent_node = $this->node[$nodeID];
        } else {
            trigger_error('ShowThreadTree::transNode() - Invalid node given.', E_USER_WARNING);
            return;
        }

        if ($loops > 1000) {
            trigger_error('ShowThreadTree::transNode() - �������[�v�̉\��������܂��B', E_USER_WARNING);
            return;
        }

        foreach ($parent_node as $resnum => $node) {
            // {{{ transNode - ����

            /*if (!isset($this->pDatLines[$resnum])) {
                return;
            }*/

            $resID = $resnum . 'of' . $this->thread->key;
            $pedigree = $ancestors;
            $pedigree[] = $resID;

            //$children = count($node);
            //$children = count($node) + array_sum(array_map('count', $node));
            $children = $this->countRecursive($node);

            if (!empty($_conf['filtering'])) {
                if ($this->filterMatch($resnum)) {
                    $filtermarking = TRUE;
                    $showcontent = TRUE;
                    $hits++;
                } else {
                    $filtermarking = FALSE;
                    $showcontent = FALSE;
                }
            } elseif ($resnum == 1 && !$this->thread->resrange['nofirst']) {
                $filtermarking = FALSE;
                $showcontent = TRUE;
            } else {
                $filtermarking = FALSE;
                $showcontent = FALSE;
            }

            // }}}
            // {{{ transNode - �w�b�_�\��

            // �ϐ��W�J
            $name = $this->transName($this->pDatLines[$resnum]['name']);
            $mail = $this->pDatLines[$resnum]['mail'];
            $date_id = $this->transDateId($resnum);
            $resBodyID = 'rb'.$resID;

            // �J�n���h��
            $nodeEventHandler = " onclick=\"return showHideNode({$this->asyncObjName},'content{$resID}',1,event);\"";

            // SPM�n���h��
            if ($_exconf['spm']['*'] == 2) {
                $spmEventHandler = " onclick=\"showSPM({$this->spmObjName},{$resnum},'{$resBodyID}',event);return false;\"";
            } elseif ($_exconf['spm']['*']) {
                $spmEventHandler = " onmouseover=\"showSPM({$this->spmObjName},{$resnum},'{$resBodyID}',event)\" onmouseout=\"hideResPopUp('{$this->spmObjName}_spm')\"";
            } else {
                $spmEventHandler = '';
            }

            // �c���[�L��
            $parent_num = $this->pDatLines[$resnum]['parent'];
            if ($parent_num) {
                if ($resnum == end(array_keys($parent_node))) {
                    $marker = $this->marker['suekko'];
                } else {
                    $marker = $this->marker['branch'];
                }
            } else {
                $marker = $this->marker['root'];
            }

            // ���O
            $head = '<span class="name"><b>'.$name.'</b></span> : ';
            // ���[��
            if ($mail) {
                if (strstr($mail, 'sage') && $STYLE['read_mail_sage_color']) {
                    $head .= '<span class="sage">'.$mail.'</span>';
                } elseif ($STYLE['read_mail_color']) {
                    $head .= '<span class="mail">'.$mail.'</span>';
                } else {
                    $head .= $mail;
                }
                $head .= ' : ';
            }
            // ���t�EID
            $head .= $date_id;

            // �}�[�L���O
            if ($filtermarking && $res_filter['field'] != 'msg') {
                $head = StrCtl::filterMarking($word_fm, $head);
            }

            // �\���J�n
            echo '<dt'
                . (($filtermarking) ? " id=\"hitNo{$hits}\"" : '')
                . (($parent_num) ? '' : ' style="margin-top:0.5em;"')
                . '>';

            if ($marker !== '') {
                echo '<span class="node_marker"'.$nodeEventHandler.'>'.$marker.'</span>'."\n";
            }

            // ���X�ԍ��ƊJ�{�^��
            echo '<a href="javascript:void(0);" class="resnum"'.$spmEventHandler.'>'.$resnum.'</a> ';
            echo '<span id="opener'.$resID.'" class="node_opener"'.$nodeEventHandler.'>';
            echo ($showcontent) ? '-' : '+';
            if ($children) {
                echo '['.$children.']';
            }
            echo '</span> : ';

            echo $head;

            echo '</dt>'."\n";

            // }}}
            // {{{ transNode - ���b�Z�[�W�E�q���X�\��

            echo '<dd id="content'.$resID.'" style="' . (($showcontent) ? '' : 'display:none;') . 'margin-bottom:1em;">'."\n";

            // ���e��\��
            if ($showcontent) {
                echo '<div id="'.$resBodyID.'">';
                $body =  $this->transMsg($this->pDatLines[$resnum]['msg'], $resnum);
                if ($filtermarking && ($res_filter['field'] == 'msg' || $res_filter['field'] == 'hole')) {
                    $body = StrCtl::filterMarking($word_fm, $body);
                }
                echo $body;
                echo '</div>'."\n";

                // �t�B���^�����O����
                if ($filtermarking) {
                    // �O��Ƀq�b�g�������X�Ɉړ�����
                    echo '<div style="margin-top:1em;">';
                    if ($hits > 1) {
                        echo '[<a href="#hitNo'.($hits - 1).'">��Prev</a>] / ';
                    }
                    echo '[<a href="#hitNo'.($hits + 1).'">��Next</a>]';
                    echo '</div>'."\n";
                    // �e���X�̃w�b�_���ċA�I�ɕ\��������JavaScript
                    if ($ancestors) {
                        $this->printShowAncestorsJs($ancestors);
                    }
                }

            // ���e��\�����Ȃ�
            } else {
                // �{����ǂݍ��݁A�\��������{�^��
                echo '<div id="rbr'.$resID.'">';
                echo '<input type="button" onclick="loadResBody('.$this->asyncObjName.','.$resnum.');" value="�\��">';
                echo '</div>'."\n";
            }

            // �q���X������΍ċA
            if ($children) {
                echo '<dl id="children'.$resID.'" style="margin-top:1em;">'."\n";
                $this->transNode($node, $pedigree, $loops + 1);
                echo '</dl>'."\n";
            }

            echo '</dd>'."\n";

            // }}}
            if ($loops == 0) {
                flush();
            }
        }

    }

    // }}}
    // {{{ transMsg()

    /**
     * ShowThreadPc::transMsg()�����s���A���̌��ʂ����X�|�b�v�A�b�v��
     * �����I�ɔ񓯊����[�h�ɂȂ�悤�ɉ��H����
     *
     * @access  public
     * @see lib/showthreadpc.class.php
     * @param   string  $msg    ���b�Z�[�W���e
     * @param   integer $mynum  ���X�ԍ�
     * @return  string
     */
    function transMsg($msg, $mynum)
    {
        $msg = parent::transMsg($msg, $mynum);
        if (!$GLOBALS['_exconf']['etc']['async_respop']) {
            $msg = $this->respop_to_async($msg);
        }
        return $msg;
    }

    // }}}
    // {{{ countRecursive()

    /**
     * �q���X�̐����ċA�I�ɃJ�E���g����
     *
     * transNode()�ŗ��p
     *
     * @access  private
     * @param   mixed   $node   �C�ӂ̃��X�z���̃m�[�h
     * @return  integer
     */
    function countRecursive($node, $c = 0)
    {
        if (is_array($node)) {
            $c += count($node);
            foreach ($node as $n) {
                $c = $this->countRecursive($n, $c);
            }
        }
        return $c;
    }

    // }}}
    // {{{ printShowAncestorsJs()

    /**
     * �e���X�̃w�b�_���ċA�I�ɕ\��������JavaScript���o�͂���
     *
     * transNode()�ŗ��p
     *
     * @access  private
     * @param   array   $ancestors  �e���X�A�e���X�̐e���X�A...�̃��X�ԍ��������Ă���z��
     * @return  void
     */
    function printShowAncestorsJs($ancestors)
    {
        $ancestors_js = "['" . implode("','", $ancestors) . "']";
        echo <<<EOJS
<script type="text/javascript">
showAncestors({$this->asyncObjName}, {$ancestors_js});
</script>\n
EOJS;
    }

    // }}}

}

// }}}

?>
