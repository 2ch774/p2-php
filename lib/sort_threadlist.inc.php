<?php
/**
 * rep2 - �X���b�h���X�g���\�[�g����
 */

require_once P2_LIB_DIR . '/ThreadList.php';

// {{{ sort_threadlist()

/**
 * �X���b�h���X�g���\�[�g����
 *
 * @param   ThreadList $aThreadList
 * @return  void
 */
function sort_threadlist(ThreadList $aThreadList)
{
    global $_conf;

    if (!$aThreadList->threads) {
        return;
    }

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('sort');

    $do_benchmark = false;
    $use_multisort = true;
    $reverse = !empty($_REQUEST['rsort']);
    $cmp = null;

    if (!empty($GLOBALS['wakati_words'])) {
        $GLOBALS['now_sort'] = 'title';
        $cmp = 'cmp_similarity';
    } else {
        switch ($GLOBALS['now_sort']) {
        case 'midoku':
            if ($aThreadList->spmode == 'soko') {
                $cmp = 'cmp_key';
            } else {
                $cmp = 'cmp_midoku';
            }
            break;
        case 'ikioi':
        case 'spd':
            if ($_conf['cmp_dayres_midoku']) {
                $cmp = 'cmp_dayres_midoku';
            } else {
                $cmp = 'cmp_dayres';
            }
            break;
        case 'no':
            if ($aThreadList->spmode == 'soko') {
                $cmp = 'cmp_key';
            } else {
                $cmp = 'cmp_no';
            }
            break;
        case 'bd':
            $cmp = 'cmp_key';
            break;
        case 'fav':
        case 'ita':
        case 'res':
        case 'title':
            $cmp = 'cmp_' . $GLOBALS['now_sort'];
            break;
        }
    }

    if ($cmp) {
        if ($do_benchmark) {
            $before = microtime(true);
        }

        if ($use_multisort) {
            $cmp = 'multi_' . $cmp;
            $cmp($aThreadList, $reverse);
        } else {
            usort($aThreadList->threads, $cmp);
        }
    }

    if (!($cmp && $use_multisort) && $reverse) {
        $aThreadList->threads = array_reverse($aThreadList->threads);
    }

    if ($cmp && $do_benchmark) {
        $after = microtime(true);
        $count = count($aThreadList->threads);
        $GLOBALS['_info_msg_ht'] .= sprintf(
            '<p class="info-msg" style="font-family:monospace">%s(%d thread%s)%s = %0.6f sec.</p>',
            $cmp,
            number_format($count),
            ($count > 1) ? 's' : '',
            $reverse ? '+reverse' : '',
            $after - $before);
    }

    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sort');
}

// }}}
// {{{ �V���\�[�g: cmp_midoku(), multi_cmp_midoku()

/**
 * �V���\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function cmp_midoku($a, $b)
{
    if ($a->new == $b->new) {
        if (($a->unum == $b->unum) or ($a->unum < 0) && ($b->unum < 0)) {
            return ($a->torder > $b->torder) ? 1 : -1;
        } else {
            return ($a->unum < $b->unum) ? 1 : -1;
        }
    } else {
        return ($a->new < $b->new) ? 1 : -1;
    }
}

/**
 * �V���\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_midoku(ThreadList $aThreadList, $reverse = false)
{
    $new = array();
    $fallen = array();
    $unum = array();
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $new[] = $t->new;
        $unum[] = $t->unum;
        $torder[] = $t->torder;
    }

    array_multisort($new,       SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $unum,      SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ ���X���\�[�g: cmp_res(), multi_cmp_res()

/**
 * ���X���\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function cmp_res($a, $b)
{
    if ($a->rescount == $b->rescount) {
        return ($a->torder > $b->torder) ? 1 : -1;
    } else {
        return ($a->rescount < $b->rescount) ? 1 : -1;
    }
}

/**
 * ���X���\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_res(ThreadList $aThreadList, $reverse = false)
{
    $rescount = array();
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $rescount[] = $t->rescount;
        $torder[] = $t->torder;
    }

    array_multisort($rescount,  SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �^�C�g���\�[�g: cmp_title(), multi_cmp_title()

/**
 * �^�C�g���\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function cmp_title($a, $b)
{
    if ($a->ttitle == $b->ttitle) {
        return ($a->torder > $b->torder) ? 1 : -1;
    } else {
        return strcmp($a->ttitle, $b->ttitle);
    }
}

/**
 * �^�C�g���\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_title(ThreadList $aThreadList, $reverse = false)
{
    $ttitle = array();
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $ttitle[] = $t->ttitle;
        $torder[] = $t->torder;
    }

    array_multisort($ttitle,    SORT_STRING,    $reverse ? SORT_DESC : SORT_ASC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �\�[�g: cmp_ita(), multi_cmp_ita()

/**
 * �\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function cmp_ita($a, $b)
{
    if ($a->host != $b->host) {
        return strcmp($a->host, $b->host);
    } else {
        if ($a->itaj != $b->itaj) {
            return strcmp($a->itaj, $b->itaj);
        } else {
            return ($a->torder > $b->torder) ? 1 : -1;
        }
    }
}

/**
 * �\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_ita(ThreadList $aThreadList, $reverse = false)
{
    $host = array();
    $itaj = array();
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $host[] = $t->host;
        $itaj[] = $t->itaj;
        $torder[] = $t->torder;
    }

    array_multisort($host,      SORT_STRING,    $reverse ? SORT_DESC : SORT_ASC,
                    $itaj,      SORT_STRING,    $reverse ? SORT_DESC : SORT_ASC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ ���C�Ƀ\�[�g: cmp_fav(), multi_cmp_fav()

/**
 * ���C�Ƀ\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function cmp_fav($a, $b)
{
    if ($a->fav == $b->fav) {
        return ($a->torder > $b->torder) ? 1 : -1;
    } else {
        return strcmp($b->fav, $a->fav);
    }
}

/**
 * ���C�Ƀ\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_fav(ThreadList $aThreadList, $reverse = false)
{
    $fav = array();
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $fav[] = $t->fav;
        $torder[] = $t->torder;
    }

    array_multisort($fav,       SORT_STRING,    $reverse ? SORT_ASC : SORT_DESC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �V�����X�D��̐����\�[�g: cmp_dayres_midoku(), multi_cmp_dayres_midoku()

/**
 * �V�����X�D��̐����\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function cmp_dayres_midoku($a, $b)
{
    if ($a->new == $b->new) {
        if (($a->unum == $b->unum) or ($a->unum >= 1) && ($b->unum >= 1)) {
            return ($a->dayres < $b->dayres) ? 1 : -1;
        } else {
            return ($a->unum < $b->unum) ? 1 : -1;
        }
    } else {
        return ($a->new < $b->new) ? 1 : -1;
    }
}

/**
 * �V�����X�D��̐����\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_dayres_midoku(ThreadList $aThreadList, $reverse = false)
{
    $new = array();
    $hasu = array();
    $dayres = array();

    foreach ($aThreadList->threads as $t) {
        $new[] = $t->new;
        $hasu[] = ($t->unum >= 1) ? 1 : $t->unum;
        $dayres[] = $t->dayres;
    }

    array_multisort($new,       SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $hasu,      SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $dayres,    SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �����\�[�g: cmp_dayres(), multi_cmp_dayres()

/**
 * �����\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function cmp_dayres($a, $b)
{
    if ($a->new == $b->new) {
        return ($a->dayres < $b->dayres) ? 1 : -1;
    } else {
        return ($a->new < $b->new) ? 1 : -1;
    }
}

/**
 * �����\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_dayres(ThreadList $aThreadList, $reverse = false)
{
    $new = array();
    $dayres = array();

    foreach ($aThreadList->threads as $t) {
        $new[] = $t->new;
        $dayres[] = $t->dayres;
    }

    array_multisort($new,       SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $dayres,    SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ cmp_key(), multi_cmp_key()

/**
 * key�\�[�g (usort�̃R�[���o�b�N�֐�)
 */
function cmp_key($a, $b)
{
    return ($a->key < $b->key) ? 1 : -1;
}

/**
 * key�\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_key(ThreadList $aThreadList, $reverse = false)
{
    $key = array();

    foreach ($aThreadList->threads as $t) {
        $key[] = $t->key;
    }

    array_multisort($key,       SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ No.�\�[�g: cmp_no(), multi_cmp_no()

/**
 * No.�\�[�g (usort�̃R�[���o�b�N�֐�)
 */
function cmp_no($a, $b)
{
    return ($a->torder > $b->torder) ? 1 : -1;
}

/**
 * No.�\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_no(ThreadList $aThreadList, $reverse = false)
{
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $torder[] = $t->torder;
    }

    array_multisort($torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �ގ����\�[�g: cmp_similarity(), multi_cmp_similarity()

/**
 * �ގ����\�[�g (usort�̃R�[���o�b�N�֐�)
 */
function cmp_similarity($a, $b)
{
    if ($a->similarity == $b->similarity) {
        return ($a->key < $b->key) ? 1 : -1;
    } else {
        return ($a->similarity < $b->similarity) ? 1 : -1;
    }
}

/**
 * �ގ����\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function multi_cmp_similarity(ThreadList $aThreadList, $reverse = false)
{
    $similarity = array();
    $key = array();

    foreach ($aThreadList->threads as $t) {
        $similarity[] = $t->similarity;
        $key[] = $t->key;
    }

    array_multisort($similarity,    SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $key,           SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $aThreadList->threads
                    );
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
