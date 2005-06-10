<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// {{{ class NgAbornChk

/**
 * p2expack - NG/���ځ[�񔻒������N���X
 * ShowThread�N���X�Ɍp��������
*/
class NgAbornChk
{
    // {{{ properties

    // �X���b�h�I�u�W�F�N�g����󂯎��v���p�e�B
    var $host;
    var $bbs;
    var $key;
    var $rescount;

    // �p�[�X�ς�dat�̔z��
    var $pDatLines;

    // ���ځ[��Ώۂ̃��X�ԍ���ۑ�����z��
    var $aborn_hit_cache;

    // NG�Ώۂ̃��X�ԍ���ۑ�����z��
    var $ng_hit_cache;

    // �A�����ځ[��Ώۂ̃��X�ԍ���ۑ�����z��
    var $chain_aborn_resnum;

    // �A��NG�Ώۂ̃��X�ԍ���ۑ�����z��
    var $chain_ng_resnum;

    // �A����������郌�X�ԍ��̃L���b�V��
    var $chain_pre_num;
    var $chain_pre_refs;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^ (PHP4 style)
     */
    function NgAbornChk(&$aThread, $pDatLines)
    {
        $this->__construct($aThread, $pDatLines);
    }

    /**
     * �R���X�g���N�^ (PHP5 style)
     */
    function __construct(&$aThread, $pDatLines)
    {
        $this->host = $aThread->host;
        $this->bbs  = $aThread->bbs;
        $this->key  = $aThread->key;
        $this->rescount = $aThread->rescount;
        $this->pDatLines    = $pDatLines;
        $this->aborn_hit_cache      = array();
        $this->ng_hit_cache         = array();
        $this->chain_aborn_resnum   = array();
        $this->chain_ng_resnum      = array();
    }

    // }}}
    // {{{ ngAbornPrepare()

    /**
     * NG/���ځ[��`�F�b�N�p�Ƀt�H�[�}�b�g����
     */
    function ngAbornPrepare($resnum, $fname)
    {
        switch ($fname) {
            case 'msg':
                $field = strip_tags($this->pDatLines[$resnum]['msg'], '<br>');
                break;
            case 'id':
                $field = $this->pDatLines[$resnum]['p_dateid']['id'];
                break;
            default:
                $field = strip_tags($this->pDatLines[$resnum][$fname]);
        }
        return $field;
    }

    // }}}
    // {{{ ngAbornWordCheck()

    /**
     * NG/���ځ[�񃏁[�h�`�F�b�N
     */
    function ngAbornWordCheck($code, $resfield, $ic = FALSE)
    {
        global $ngaborns;

        $method = $ic ? 'stristr' : 'strstr';

        if (isset($ngaborns[$code]['data']) && is_array($ngaborns[$code]['data'])) {
            foreach ($ngaborns[$code]['data'] as $k => $v) {
                if (strlen($v['word']) == 0) {
                    continue;
                }
                // <�֐�:�I�v�V����>�p�^�[�� �`���̍s�͐��K�\���Ƃ��Ĉ���
                // �o�C�i���Z�[�t�łȂ��i���{��ŃG���[���o�邱�Ƃ�����j�̂�ereg()�n�͎g��Ȃ�
                if (preg_match('/^<(mb_ereg|preg_match|regex)(:[imsxeADSUXu]+)?>(.+)$/', $v['word'], $re)) {
                    // "regex"�̂Ƃ��͎����ݒ�
                    if ($re[1] == 'regex') {
                        if (P2_MBREGEX_AVAILABLE) {
                            $re_method = 'mb_ereg';
                            $re_pattern = $re[3];
                        } else {
                            $re_method = 'preg_match';
                            $re_pattern = '/' . str_replace('/', '\\/', $re[3]) . '/';
                        }
                    } else {
                        $re_method = $re[1];
                        $re_pattern = $re[3];
                    }
                    // �啶���������𖳎�
                    if ($re[2] && strstr($re[2], 'i')) {
                        if ($re_method == 'preg_match') {
                            $re_pattern .= 'i';
                        } else {
                            $re_method .= 'i';
                        }
                    }
                    // �}�b�`
                    if ($re_method($re_pattern, $resfield)) {
                        $this->ngAbornUpdate($code, $k);
                        return $v['word'];
                    //if ($re_method($re_pattern, $resfield, $matches)) {
                        //return htmlspecialchars($matches[0]);
                    }

                // �P���ɕ����񂪊܂܂�邩�ǂ������`�F�b�N
                } elseif ($method($resfield, $v['word'])) {
                    $this->ngAbornUpdate($code, $k);
                    return $v['word'];
                }
            }
        }
        return FALSE;
    }

    // }}}
    // {{{ ngAbornUpdate()

    /**
     * NG/���ځ[������Ɖ񐔂��X�V
     */
    function ngAbornUpdate($code, $k)
    {
        global $ngaborns;

        if (isset($ngaborns[$code]['data'][$k])) {
            $v = &$ngaborns[$code]['data'][$k];
            $v['lasttime'] = date('Y/m/d G:i'); // HIT���Ԃ��X�V
            if (empty($v['hits'])) {
                $v['hits'] = 1; // ��HIT
            } else {
                $v['hits']++;   // HIT�񐔂��X�V
            }
        }
    }

    // }}}
    // {{{ abornResCheck()

    /**
     * ���背�X�̓������ځ[��`�F�b�N
     */
    function abornResCheck($host, $bbs, $key, $resnum)
    {
        global $ngaborns;

        $target = $host.'/'.$bbs.'/'.$key.'/'.$resnum;

        if (isset($ngaborns['aborn_res']['data']) && is_array($ngaborns['aborn_res']['data'])) {
            foreach ($ngaborns['aborn_res']['data'] as $k => $v) {
                if ($v['word'] == $target) {
                    $this->ngAbornUpdate('aborn_res', $k);
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    // }}}
    // {{{ abornCheck()

    /**
     * �S�t�B�[���h�̓������ځ[��`�F�b�N
     */
    function abornCheck($resnum, $is_chain = FALSE)
    {
        global $_exconf;

        if (isset($this->aborn_hit_cache[$resnum])) {
            return $this->aborn_hit_cache[$resnum];
        } elseif (!isset($this->pDatLines[$resnum])) {
            return $this->abornResultCache($resnum, FALSE);
        }

        // {{{ ���ߑł����ځ[��`�F�b�N

        if ($this->abornResCheck($this->host, $this->bbs, $this->key, $resnum)) {
            $this->chain_aborn_resnum[] = $resnum;
            return $this->abornResultCache($resnum, 'res');
        }

        // }}}
        // {{{ �s�����ځ[��`�F�b�N

        // ����ɂ͘A�������Ȃ�
        if (!$is_chain && $_exconf['aborn']['break_aborn'] && !$_exconf['aborn']['break_aborn_ng'] &&
            $this->pDatLines[$resnum]['lines'] > $_exconf['aborn']['break_aborn']
        ) {
            //$this->chain_aborn_resnum[] = $resnum;    // �s���I�[�o�[�͘A���Ώۂɂ��Ȃ�
            //return $this->abornResultCache($resnum, 'lines'); // �ċA���ɂ��ځ[�񂳂�Ȃ��悤�A���ʂ��L���b�V�����Ȃ�
            return 'lines';
        }

        // }}}
        // {{{ �L�[���[�h���ځ[��`�F�b�N

        $fields = array('name', 'mail', 'id', 'msg');
        foreach ($fields as $fname) {
            if ($this->ngAbornWordCheck('aborn_'.$fname, $this->ngAbornPrepare($resnum, $fname)) !== FALSE) {
                $this->chain_aborn_resnum[] = $resnum;
                return $this->abornResultCache($resnum, $fname);
            }
        }

        // }}}

        if (!$_exconf['aborn']['chain_aborn']) {
            return $this->abornResultCache($resnum, FALSE);
        }

        // {{{ �A�����ځ[��`�F�b�N

        $chain_aborn = FALSE;
        $chain_found = array();
        $refs = $this->getChainNums($resnum);   // �Q�Ƃ��Ă���ԍ��ŁA������菬��������
        if (!$refs) {
            return $this->abornResultCache($resnum, FALSE);
        }

        // �A���Ώۂɓo�^����Ă��Ȃ����`�F�b�N
        if ($chain_found = array_intersect($refs, $this->chain_aborn_resnum)) {
            $chain_aborn = TRUE;
        } else {
            // �e�Q�ƃ��X�ԍ����ċA�I�Ƀ`�F�b�N
            foreach ($refs as $ref) {
                // $refs�̓\�[�g����Ă���̂Ŏ����ȏ�̔ԍ�������ƁA�����ŏI��
                // ��getChainNums()�Ńt�B���^�����O����̂ł��̔���͕s�v
                /*if ($ref >= $resnum) {
                    break;
                }*/
                // �����ċA�`�F�b�N�i�ċA���A$is_chain�͌��̃��X�ԍ��j
                if ($ref == $is_chain) {
                    continue;
                }
                if ($this->abornCheck($ref, $resnum)) {
                    $chain_aborn = TRUE;
                    $chain_found[] = $ref;
                    break;
                }
            }
        }

        // �o�^
        if ($chain_aborn) {
            // �A�����ځ[���NG�����ɂ���Ƃ�
            if ($_exconf['aborn']['chain_aborn_ng']) {
                // �k���ĘA���̑ΏۂƂ���Ƃ�
                if ($_exconf['aborn']['chain_ng'] == 2) {
                    $this->chain_ng_resnum[] = $resnum;
                }
                // ����ɂ���āA����������NG�`�F�b�N�����Ƃ��Ɂu���ځ[�񃌃X�Ƀ��X�v�Ƃ����
                $this->ngResultCache($resnum, array('aborn' => implode(',', $chain_found)));
                // ���ځ[��ł͂Ȃ��̂�FALSE��o�^
                return $this->abornResultCache($resnum, FALSE);
            }
            // �k���ĘA���̑ΏۂƂ���Ƃ�
            if ($_exconf['aborn']['chain_aborn'] == 2) {
                $this->chain_aborn_resnum[] = $resnum;
            }
            return $this->abornResultCache($resnum, 'chain');
        }

        // }}}

        return $this->abornResultCache($resnum, FALSE);
    }

    // }}}
    // {{{ ngCheck()

    /**
     * �S�t�B�[���h��NG�`�F�b�N
     */
    function ngCheck($resnum, $is_chain = FALSE)
    {
        global $_exconf;

        if (isset($this->ng_hit_cache[$resnum])) {
            return $this->ng_hit_cache[$resnum];
        } elseif (!isset($this->pDatLines[$resnum])) {
            return $this->ngResultCache($resnum, array());
        }

        $ng_fields = array();
        $ng_only_line = FALSE;

        // {{{ �s��NG�`�F�b�N

        // ����ɂ͘A�������Ȃ�
        if (!$is_chain && $_exconf['aborn']['break_aborn'] && $_exconf['aborn']['break_aborn_ng'] &&
            $this->pDatLines[$resnum]['lines'] > $_exconf['aborn']['break_aborn']
        ) {
            //$this->chain_aborn_resnum[] = $resnum;    // �s���I�[�o�[�͘A���Ώۂɂ��Ȃ�
            $ng_fields['lines'] = $this->pDatLines[$resnum]['lines'];
            $ng_only_line = TRUE;
        }

        // }}}
        // {{{ �L�[���[�hNG�`�F�b�N

        $fields = array('name', 'mail', 'id', 'msg');
        foreach ($fields as $fname) {
            if (($found = $this->ngAbornWordCheck('ng_'.$fname, $this->ngAbornPrepare($resnum, $fname))) !== FALSE) {
                $this->chain_ng_resnum[] = $resnum;
                $ng_fields[$fname] = htmlspecialchars($found);
                $ng_only_line = FALSE;
            }
        }

        // }}}

        if ($ng_fields || !$_exconf['aborn']['chain_ng']) {
            if ($ng_only_line) {
                //$this->ngResultCache($resnum, array());   // �ċA����NG�Ƃ���Ȃ��悤�A���ʂ��L���b�V�����Ȃ�
                return $ng_fields;
            }
            return $this->ngResultCache($resnum, $ng_fields);
        }

        // {{{ �A��NG�`�F�b�N

        $chain_ng = FALSE;
        $chain_found = array();
        $refs = $this->getChainNums($resnum);   // �Q�Ƃ��Ă���ԍ��ŁA������菬��������
        if (!$refs) {
            return $this->ngResultCache($resnum, array());
        }

        // �A���Ώۂɓo�^����Ă��Ȃ����`�F�b�N
        if ($chain_found = array_intersect($refs, $this->chain_ng_resnum)) {
            $chain_ng = TRUE;
        } else {
            // �e�Q�ƃ��X�ԍ����ċA�I�Ƀ`�F�b�N
            foreach ($refs as $ref) {
                // $refs�̓\�[�g����Ă���̂Ŏ����ȏ�̔ԍ�������ƁA�����ŏI��
                // ��getChainNums()�Ńt�B���^�����O����̂ł��̔���͕s�v
                /*if ($ref >= $resnum) {
                    break;
                }*/
                // �����ċA�`�F�b�N�i�ċA���A$is_chain�͌��̃��X�ԍ��j
                if ($ref == $is_chain) {
                    continue;
                }
                if ($this->ngCheck($ref, $resnum)) {
                    $chain_ng = TRUE;
                    $chain_found[] = $ref;
                    break;
                }
            }
        }


        // �o�^
        if ($chain_ng) {
            // �k���ĘA���̑ΏۂƂ���Ƃ�
            if ($_exconf['aborn']['chain_ng'] == 2) {
                $this->chain_ng_resnum[] = $resnum;
            }
            $ng_fields['chain'] = implode(',', $chain_found);
        }

        // }}}

        return $this->ngResultCache($resnum, $ng_fields);
    }

    // }}}
    // {{{ ngAbornCheckAll()

    /**
     * �S���X�̂��ځ[��/NG�`�F�b�N
     */
    function ngAbornCheckAll()
    {
        for ($i = 1; $i <= $this->rescount; $i++) {
            $this->abornCheck($i) || $this->ngCheck($i);
        }
    }

    // }}}
    // {{{ abornResultCache()

    /**
     * �A���`�F�b�N�p�ɂ��ځ[��`�F�b�N�̌��ʂ��L���b�V��
     */
    function abornResultCache($resnum, $result)
    {
        $this->aborn_hit_cache[$resnum] = $result;
        return $result;
    }

    // }}}
    // {{{ ngResultCache()

    /**
     * �A���`�F�b�N�p��NG�`�F�b�N�̌��ʂ��L���b�V��
     */
    function ngResultCache($resnum, $result)
    {
        $this->ng_hit_cache[$resnum] = $result;
        return $result;
    }

    // }}}
    // {{{ getChainNums()

    /**
     * �A���`�F�b�N�p�ɎQ�ƃ��X�ԍ����O���[�v��������
     *
     * ���g�Ƃ�����傫�����X�ԍ��͏���
     */
    function getChainNums($resnum)
    {
        global $_exconf;

        // �������X�ɑ΂��ĘA���ł��ځ[��NG�`�F�b�N���������Ƃ��̓�x��Ԃ��Ȃ�
        if ($resnum == $this->chain_pre_num) {
            return $this->chain_pre_refs;
        }

        // >>n >>x,y,z ����$resnum��菬�������̂𒊏o
        $refs = array_filter($this->pDatLines[$resnum]['refs'], create_function('$n', "return (\$n < $resnum);"));

        // >>from-to ��W�J
        if ($_exconf['aborn']['chain_range'] && $this->pDatLines[$resnum]['refr']) {
            foreach ($this->pDatLines[$resnum]['refr'] as $refr) {
                // �܂����肦�Ȃ����ǁA�O�̂��߃`�F�b�N
                if (!isset($refr['from']) && !isset($refr['to'])) {
                    continue;
                }
                $x = (!empty($refr['from'])) ? max($refr['from'], 1) : 1;
                $y = (!empty($refr['to'])) ? min($refr['to'], $this->rescount) : $this->rescount;
                $z = min($y + 1, $resnum);
                for ($i = $x; $i < $z; $i++) {
                    $refs[] = $i;
                }
                // $refs����傷��̂�h�����߁A������s
                $refs = array_unique($refs);
            }
        }

        sort($refs);

        $this->chain_pre_num = $resnum;
        $this->chain_pre_refs = $refs;
        return $refs;
    }

    // }}}
}

// }}}

?>
