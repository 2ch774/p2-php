<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/* ImageCache2 - �摜�L���b�V���ꗗ */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once 'conf/conf.php';

authorize();

if ($_exconf['imgCache']['*'] == 0) {
    exit('<html><body><p>ImageCache2�͖����ł��B<br>conf/conf_user_ex.php�̐ݒ��ς��Ă��������B</p></body></html>');
}

// }}}
// {{{ ������


$debug = FALSE;

// ���C�u�����ǂݍ���
require_once 'PEAR.php';
require_once 'DB.php';
require_once 'DB/DataObject.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ObjectFlexy.php';
require_once 'HTML/Template/Flexy.php';
require_once 'HTML/Template/Flexy/Element.php';
if ($debug) {
    require_once 'Var_Dump.php';
    require_once (P2EX_LIBRARY_DIR . '/ic2/findexec.inc.php');
}
require_once (P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/database.class.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/db_images.class.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/thumbnail.class.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/quickrules.class.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/editform.class.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/managedb.inc.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/getvalidvalue.inc.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/buildimgcell.inc.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/matrix.class.php');


// }}}
// {{{ config


// �ݒ�t�@�C���ǂݍ���
$ini = ic2_loadconfig();

// DB_DataObject�̐ݒ�
$_dbdo_options = &PEAR::getStaticProperty('DB_DataObject','options');
$_dbdo_options = array('database' => $ini['General']['dsn'], 'debug' => FALSE, 'quote_identifiers' => TRUE);

// Exif�\�����L�����H
$show_exif = ($ini['Viewer']['exif'] && extension_loaded('exif'));

// �t�H�[���̃f�t�H���g�l
$_defaults = array(
    'page'  => 1,
    'cols'  => $ini['Viewer']['cols'],
    'rows'  => $ini['Viewer']['rows'],
    'order' => $ini['Viewer']['order'],
    'sort'  => $ini['Viewer']['sort'],
    'field' => $ini['Viewer']['field'],
    'key'   => '',
    'threshold' => $ini['Viewer']['threshold'],
    'compare' => '>=',
    'mode' => 0,
);

// �t�H�[���̌Œ�l
$_constants = array(
    'start'   => '<<',
    'prev'    => '<',
    'next'    => '>',
    'end'     => '>>',
    'jump'    => 'Go',
    'search'  => '����',
    'cngmode' => '�ύX',
    'hint'    => '����',
);

// 臒l��r���@
$_compare = array(
    '>=' => '&gt;=',
    '='  => '=',
    '<=' => '&lt;=',
);

// 臒l
$_threshold = array(
    '-1' => '-1',
    '0' => '0',
    '1' => '1',
    '2' => '2',
    '3' => '3',
    '4' => '4',
    '5' => '5',
);

// �\�[�g�
$_order = array(
    'time' => '�L���b�V����������',
    'uri'  => 'URL',
    'name' => '�t�@�C����',
    'size' => '�t�@�C���T�C�Y',
);

// �\�[�g����
$_sort = array(
    'ASC'  => '����',
    'DESC' => '�~��',
);

// �����t�B�[���h
$_field = array(
    'uri'  => 'URL',
    'name' => '�t�@�C����',
    'memo' => '����',
);

// ���[�h
$_mode = array(
    '0' => '�ꗗ',
    '1' => '�ꊇ�ύX',
    '2' => '�ʊǗ�',
);


// }}}
// {{{ prepare (DB & Cache)


// DB_DataObject���p������DAO
$icdb = &new IC2DB_Images;
$db = &$icdb->getDatabaseConnection();

// �T���l�C���쐬�N���X
$thumb = &new ThumbNailer(1);

if ($ini['Viewer']['cache']) {
    require_once 'Cache.php';
    require_once 'Cache/Function.php';
    // �f�[�^�L���b�V���ɂ�Cache_Container_db(Cache 1.5.4)���n�b�N����MySQL�ȊO�ɂ��Ή������A
    // �R���X�g���N�^��DB_xxx(DB_mysql�Ȃ�)�̃C���X�^���X���󂯎���悤�ɂ������̂��g���B
    // �i�t�@�C�����E�N���X���͓����ŁAinclude_path�𒲐�����
    //   �I���W�i����Cache/Container/db.php�̑���ɂ���j
    $cache_options = array(
        'dsn'           => $ini['General']['dsn'],
        'cache_table'   => $ini['Cache']['table'],
        'highwater'     => (int)$ini['Cache']['highwater'],
        'lowwater'      => (int)$ini['Cache']['lowwater'],
        'db' => &$db
    );
    $cache = &new Cache_Function('db', $cache_options, (int)$ini['Cache']['expires']);
    // �L�������؂�L���b�V���̃K�[�x�b�W�R���N�V�����Ȃ�
    if (isset($_GET['cache_clean'])) {
        $cache_clean = $_GET['cache_clean'];
    } elseif (isset($_POST['cache_clean'])) {
        $cache_clean = $_POST['cache_clean'];
    } else {
        $cache_clean = FALSE;
    }
    switch ($cache_clean) {
        // �L���b�V����S�폜
        case 'all':
            $sql = sprintf('DELETE FROM %s', $db->quoteIdentifier($ini['Cache']['table']));
            $result = &$db->query($sql);
            if (DB::isError($result)) {
                die($result->getMessage());
            }
            $vacuumdb = TRUE;
            break;
        // �����I�ɃK�[�x�b�W�R���N�V����
        case 'gc':
            $cache->garbageCollection(TRUE);
            $vacuumdb = TRUE;
            break;
        // gc_probability(�f�t�H���g��1)/100�̊m���ŃK�[�x�b�W�R���N�V����
        default:
            // $cache->gc_probability = 1;
            $cache->garbageCollection();
            $vacuumdb = FALSE;
    }
    // SQLite�Ȃ�VACUUM�����s�iPostgreSQL�͕���cron��vacuumdb����̂ł����ł͂��Ȃ��j
    if ($vacuumdb && is_a($db, 'DB_sqlite')) {
        $result = &$db->query('VACUUM');
        if (DB::isError($result)) {
            die($result->getMessage());
        }
    }
    $enable_cache = TRUE;
} else {
    $enable_cache = FALSE;
}


// }}}
// {{{ prepare (Form & Template)


// conf.php�ňꊇstripslashes()���Ă��邯�ǁAHTML_QuickForm�ł��Ǝ���stripslashes()����̂ŁB
// ���ꂼ�o�b�h�m�E�n�E
if (get_magic_quotes_gpc()) {
    array_walk_recursive($_REQUEST, 'addslashes_cb');
}

// �y�[�W�J�ڗp�t�H�[����ݒ�
// �y�[�W�J�ڂ�GET�ōs�����A�摜���̍X�V��POST�ōs���̂łǂ���ł��󂯓����悤�ɂ���
// �i�����_�����O�O�� $qf->updateAttributes(array('method' => 'get')); �Ƃ���j
$_attribures = array('accept-charset' => 'UTF-8,Shift_JIS');
$_method = ($_SERVER['REQUEST_METHOD'] == 'GET') ? 'get' : 'post';
$qf = &new HTML_QuickForm('go', $_method, $_SERVER['PHP_SELF'], '_self', $_attribures);
$qf->registerRule('numRange', null, 'RuleNumericRange');
$qf->registerRule('inArray', null, 'RuleInArray');
$qf->registerRule('inArrayKeys', null, 'RuleInArrayKeys');
$qf->setDefaults($_defaults);
$qf->setConstants($_constants);
$qfe = array();

// �t�H�[���v�f�̒�`

// �y�[�W�ړ��̂��߂�submit�v�f
$qfe['start'] = &$qf->addElement('button', 'start');
$qfe['prev']  = &$qf->addElement('button', 'prev');
$qfe['next']  = &$qf->addElement('button', 'next');
$qfe['end']   = &$qf->addElement('button', 'end');
$qfe['jump']  = &$qf->addElement('button', 'jump');

// �\�����@�Ȃǂ��w�肷��input�v�f
$qfe['page']      = &$qf->addElement('text', 'page', '�y�[�W�ԍ����w��', array('size' => 3));
$qfe['cols']      = &$qf->addElement('text', 'cols', '��', array('size' => 3, 'maxsize' => 2));
$qfe['rows']      = &$qf->addElement('text', 'rows', '�c', array('size' => 3, 'maxsize' => 2));
$qfe['order']     = &$qf->addElement('select', 'order', '���я�', $_order);
$qfe['sort']      = &$qf->addElement('select', 'sort', '����', $_sort);
$qfe['field']     = &$qf->addElement('select', 'field', '�t�B�[���h', $_field);
$qfe['key']       = &$qf->addElement('text', 'key', '�L�[���[�h', array('size' => 20));
$qfe['compare']   = &$qf->addElement('select', 'compare', '��r���@', $_compare);
$qfe['threshold'] = &$qf->addElement('select', 'threshold', '�������l', $_threshold);

// �����R�[�h����̃q���g�ɂ���B��input�v�f
$qfe['hint'] = &$qf->addElement('hidden', 'hint');

// ���������s����submit�v�f
$qfe['search'] = &$qf->addElement('submit', 'search');

// ���[�h�ύX������select�v�f
$qfe['mode'] = &$qf->addElement('select', 'mode', '���[�h', $_mode);

// ���[�h�ύX���m�肷��submit�v�f
$qfe['cngmode'] = &$qf->addElement('submit', 'cngmode');

// �t�H�[���̃��[��
$qf->addRule('cols', '1 to 20',  'numRange', array('min' => 1, 'max' => 20),  'client', TRUE);
$qf->addRule('rows', '1 to 100', 'numRange', array('min' => 1, 'max' => 100), 'client', TRUE);
$qf->addRule('order', 'invalid order.', 'inArrayKeys', $_order);
$qf->addRule('sort',  'invalid sort.',  'inArrayKeys', $_sort);
$qf->addRule('field', 'invalid field.', 'inArrayKeys', $_field);
$qf->addRule('threshold', '-1 to 5', 'numRange', array('min' => -1, 'max' => 5));
$qf->addRule('compare', 'invalid compare.', 'inArrayKeys', $_compare);
$qf->addRule('mode', 'invalid mode.', 'inArrayKeys', $_mode);

// Flexy
$_flexy_options = array(
    'locale' => 'ja',
    'compileDir' => $ini['General']['cachedir'] . '/' . $ini['General']['compiledir'],
    'templateDir' => P2EX_LIBRARY_DIR . '/ic2/templates',
    'numberFormat' => '', // ",0,'.',','" �Ɠ���
    'plugins' => array('P2Util' => P2_LIBRARY_DIR . '/p2util.class.php')
);

$flexy = &new HTML_Template_Flexy($_flexy_options);

$flexy->setData('php_self', $_SERVER['PHP_SELF']);
$flexy->setData('skin', $skin_en);

if ($debug) {
    $flexy->setData('debug', TRUE);
    $dumper = &Var_Dump::singleton();
    $flexy->setData('dumper', $dumper);
}


// }}}
// {{{ validate


// ����
$qf->validate();
$sv = $qf->getSubmitValues();
$page      = getValidValue('page',   $_defaults['page'], 'intval');
$cols      = getValidValue('cols',   $_defaults['cols'], 'intval');
$rows      = getValidValue('rows',   $_defaults['rows'], 'intval');
$order     = getValidValue('order',  $_defaults['order']);
$sort      = getValidValue('sort',   $_defaults['sort'] );
$field     = getValidValue('field',  $_defaults['field']);
$key       = getValidValue('key',    $_defaults['key']);
$threshold = getValidValue('threshold', $_defaults['threshold'], 'intval');
$compare   = getValidValue('compare',   $_defaults['compare']);
$mode      = getValidValue('mode',      $_defaults['mode'], 'intval');


// }}}
// {{{ query


// 臒l�Ńt�B���^�����O
if (!($threshold == -1 && $compate == '>=')) {
    $icdb->whereAddQuoted('rank', $compare, $threshold);
}

// �L�[���[�h����������Ƃ�
if ($key !== '') {
    $keys = explode(' ', $icdb->uniform($key, 'SJIS-win'));
    foreach ($keys as $k) {
        $operator = 'LIKE';
        $wildcard = '%';
        if (preg_match('/[%_]/', $k)) {
            // SQLite2��LIKE���Z�q�̉E�ӂŃo�b�N�X���b�V���ɂ��G�X�P�[�v��
            // ESCAPE�ŃG�X�P�[�v�������w�肷�邱�Ƃ��ł��Ȃ��̂�GLOB���Z�q���g��
            if (strtolower(get_class($db)) == 'db_sqlite') {
                if (preg_match('/[*?]/', $k)) {
                    die('ImageCache2 - Warning:�u%�܂���_�v�Ɓu*�܂���?�v�����݂���L�[���[�h�͎g���܂���B');
                } else {
                    $operator = 'GLOB';
                    $wildcard = '*';
                }
            } else {
                $k = preg_replace('/[%_]/', '\\\\$0', $k);
            }
        }
        $expr = $wildcard . $k . $wildcard;
        $icdb->whereAddQuoted($field, $operator, $expr);
    }
    $qfe['key']->setValue($key);
}

// �d���摜���X�L�b�v����Ƃ�
// �����𐳂����J�E���g���邽�߂ɃT�u�N�G�����g��
// �T�u�N�G���ɑΉ����Ă��Ȃ��o�[�W����4.1������MySQL�ł͏d���摜�̃X�L�b�v�͖���
$dc = 0; // �����I�p�����[�^�A�o�^���R�[�h��������ȏ�̉摜�݂̂𒊏o
$mysql = preg_match('/^mysql:/', $ini['General']['dsn']); // MySQL 4.1.2�ȍ~��phptype��"mysqli"
if ($mysql == 0 && ($ini['Viewer']['unique'] || $dc > 2)) {
    $subq = 'SELECT ' . (($sort == 'ASC') ? 'MIN' : 'MAX') . '(id) FROM ';
    $subq .= $icdb->_db->quoteIdentifier($ini['General']['table']);
    if (isset($keys)) {
        // �T�u�N�G�����Ńt�B���^�����O����̂Őe�N�G����WHERE����p�N���Ă��ă��Z�b�g
        $subq .= $icdb->_query['condition'];
        $icdb->whereAdd();
    }
    // md5�����ŃO���[�v�����Ă��\���Ƃ͎v�����ǁA�ꉞ�B
    $subq .= ' GROUP BY size, md5, mime';
    if ($dc > 1) {
        $subq .= ' HAVING COUNT(*) >= ' . $dc;
    }
    // echo '<!--', mb_convert_encoding($subq, 'SJIS-win', 'UTF-8'), '-->';
    $icdb->whereAdd("id IN ($subq)");
}

// �f�[�^�x�[�X���X�V����Ƃ�
if (isset($_POST['edit_submit']) && !empty($_POST['change'])) {

    $target = array_unique(array_map('intval', $_POST['change']));

    switch ($mode) {

    // �ꊇ�Ńp�����[�^�ύX
    case 1:
        // �����N��ύX
        $newrank = intoRange($_POST['setrank'], -1, 5);
        manageDB_setRank($target, $newrank);
        // ������ǉ�
        if (!empty($_POST['addmemo'])) {
            $newmemo = get_magic_quotes_gpc() ? stripslashes($_POST['addmemo']) : $_POST['addmemo'];
            $newmemo = $icdb->uniform($newmemo, 'SJIS-win');
            if ($newmemo !== '') {
                 manageDB_addMemo($target, $newmemo);
            }
        }
        break;

    // �ʂɃp�����[�^�ύX
    case 2:
        // �X�V�p�̃f�[�^���܂Ƃ߂�
        $updated = array();
        $removed = array();
        $to_blacklist = FALSE;
        $no_blacklist = FALSE;

        foreach ($target as $id) {
            if (!empty($_POST['img'][$id]['remove'])) {
                if (!empty($_POST['img'][$id]['black'])) {
                    $to_blacklist = TRUE;
                    $removed[$id] = TRUE;
                } else {
                    $no_blacklist = TRUE;
                    $removed[$id] = FALSE;
                }
            } else {
                $newmemo = get_magic_quotes_gpc() ? stripslashes($_POST['img'][$id]['memo']) : $_POST['img'][$id]['memo'];
                $data = array(
                    'rank' => intval($_POST['img'][$id]['rank']),
                    'memo' => $icdb->uniform($newmemo, 'SJIS-win')
                );
                if (0 < $id && -1 <= $data['rank'] && $data['rank'] <= 5) {
                    $updated[$id] = $data;
                }
            }
        }

        // �����X�V
        if (count($updated) > 0) {
            manageDB_update($updated);
        }

        // �폜�i���u���b�N���X�g����j
        if (count($removed) > 0) {
            $removed_files = array();
            foreach ($removed as $id => $to_blacklist) {
                $removed_files = array_merge($removed_files, manageDB_remove(array($id), $to_blacklist));
            }
            $flexy->setData('removedFiles', $removed_files);
            if ($to_blacklist) {
                if ($no_blacklist) {
                    $flexy->setData('toBlackListAll', FALSE);
                    $flexy->setData('toBlackListPartial', TRUE);
                } else {
                    $flexy->setData('toBlackListAll', TRUE);
                    $flexy->setData('toBlackListPartial', FALSE);
                }
            } else {
                $flexy->setData('toBlackListAll', FALSE);
                $flexy->setData('toBlackListPartial', FALSE);
            }
        }
        break;

    } // endswitch

// �ꊇ�ŉ摜���폜����Ƃ�
} elseif ($mode == 1 && isset($_POST['edit_remove']) && !empty($_POST['change'])) {
    $target = array_unique(array_map('intval', $_POST['change']));
    $to_blacklist = !empty($_POST['edit_toblack']);
    $removed_files = manageDB_remove($target, $to_blacklist);
    $flexy->setData('removedFiles', $removed_files);
    $flexy->setData('toBlackList', $to_blacklist);
}


// }}}
// {{{ build


// �����R�[�h���𐔂���
//$db->setFetchMode(DB_FETCHMODE_ORDERED);
//$all = (int)$icdb->count('*', TRUE);
//$db->setFetchMode(DB_FETCHMODE_ASSOC);
$sql = sprintf('SELECT COUNT(*) FROM %s %s', $db->quoteIdentifier($ini['General']['table']), $icdb->_query['condition']);
$all = $db->getOne($sql);
if (DB::isError($all)) {
    die($all->getMessage());
}

// �}�b�`���郌�R�[�h���Ȃ�������G���[��\���A���R�[�h������Ε\���p�I�u�W�F�N�g�ɒl����
if ($all == 0) {

    // ���R�[�h�Ȃ�
    $flexy->setData('nomatch', TRUE);
    $flexy->setData('reset', $_SERVER['PHP_SELF']);
    $qfe['start']->updateAttributes('disabled');
    $qfe['prev']->updateAttributes('disabled');
    $qfe['next']->updateAttributes('disabled');
    $qfe['end']->updateAttributes('disabled');
    $qfe['page']->updateAttributes('disabled');
    $qfe['jump']->updateAttributes('disabled');

} else {

    // ���R�[�h����
    $flexy->setData('nomatch', FALSE);

    // �\���͈͂�ݒ�
    $ipp = $cols * $rows; // images per page
    $last_page = ceil($all / $ipp);

    // �y�[�W�J�ڗp�p�����[�^������
    if (isset($sv['search']) || isset($sv['cngmode'])) {
        $page = 1;
    } elseif (isset($sv['page'])) {
        $page = max(1, min((int)$sv['page'], $last_page));
    } else {
        $page = 1;
    }
    $prev_page = max(1, $page - 1);
    $next_page = min($page + 1, $last_page);

    $mf_hiddens = array(
        'hint' => '����', 'mode' => $mode,
        'page' => $page, 'cols' => $cols, 'rows' => $rows,
        'order' => $order, 'sort' => $sort,
        'field' => $field, 'key' => $key, 
        'compare' => $compare, 'threshold' => $threshold
    );
    $pager_q = $mf_hiddens;
    mb_convert_variables('UTF-8', 'SJIS-win', $pager_q);

    // �y�[�W�ԍ����X�V
    $qfe['page']->setValue($page);
    $qf->addRule('page', "1 to {$last_page}", 'numRange', array('min' => 1, 'max' => $last_page), 'client', TRUE);

    // �ꎞ�I�Ƀp�����[�^��؂蕶���� & �ɂ��Č��݂̃y�[�W��URL�𐶐�
    $pager_separator = ini_get('arg_separator.output');
    ini_set('arg_separator.output', '&');
    $flexy->setData('current_page', $_SERVER['PHP_SELF'] . '?' . http_build_query($pager_q));
    ini_set('arg_separator.output', $pager_separator);
    unset($pager_q, $pager_separator);

    // �y�[�W����ړ��{�^���̑������X�V
    if ($page == 1) {
        $qfe['start']->updateAttributes('disabled');
        $qfe['prev']->updateAttributes('disabled');
    } else {
        $qfe['start']->updateAttributes(array('onclick' => "pageJump(1)"));
        $qfe['prev']->updateAttributes(array('onclick' => "pageJump({$prev_page})"));
    }

    // �y�[�W�O���ړ��{�^���̑������X�V
    if ($page == $last_page) {
        $qfe['next']->updateAttributes('disabled');
        $qfe['end']->updateAttributes('disabled');
    } else {
        $qfe['next']->updateAttributes(array('onclick' => "pageJump({$next_page})"));
        $qfe['end']->updateAttributes(array('onclick' => "pageJump({$last_page})"));
    }

    // �y�[�W�w��ړ��p�{�^���̑������X�V
    if ($last_page == 1) {
        $qfe['jump']->updateAttributes('disabled');
    } else {
        $qfe['jump']->updateAttributes(array('onclick' => "if(validate_go(this.form))pageJump(this.form.page.value)"));
    }


    // �ҏW���[�h�p�t�H�[���𐶐�
    if ($mode == 1 || $mode == 2) {
        $flexy->setData('editFormHeader', EditForm::header($mf_hiddens, $mode));
        if ($mode == 1) {
            $flexy->setData('editFormCheckAllOn', EditForm::checkAllOn());
            $flexy->setData('editFormCheckAllOff', EditForm::checkAllOff());
            $flexy->setData('editFormCheckAllReverse', EditForm::checkAllReverse());
            $flexy->setData('editFormSelect', EditForm::selectRank($_threshold));
            $flexy->setData('editFormText', EditForm::textMemo());
            $flexy->setData('editFormSubmit', EditForm::submit());
            $flexy->setData('editFormReset', EditForm::reset());
            $flexy->setData('editFormRemove', EditForm::remove());
            $flexy->setData('editFormBlackList', EditForm::toblack());
        } elseif ($mode == 2) {
            $editForm = &new EditForm;
            $flexy->setData('editForm', $editForm);
        }
    }


    // DB����擾����͈͂�ݒ肵�Č���
    $from = ($page - 1) * $ipp;
    $icdb->orderByArray(array($order => $sort, 'id' => $sort));
    $icdb->limit($from, $ipp);
    $found = $icdb->find();

    // �e�[�u���̃u���b�N�ɕ\������l��fetch&�I�u�W�F�N�g�ɑ��
    $flexy->setData('all',  $all);
    $flexy->setData('cols', $cols);
    $flexy->setData('last', $last_page);
    $flexy->setData('from', $from + 1);
    $flexy->setData('to',   $from + $found);
    $flexy->setData('submit', array());
    $flexy->setData('reset', array());

    $popup = ($mode == 2) ? FALSE : TRUE;
    $items = array();
    while ($icdb->fetch()) {
        // �������ʂ�z��ɂ��A�����_�����O�p�̗v�f��t��
        // �z��ǂ����Ȃ�+���Z�q�ŗv�f��ǉ��ł���
        // �i�L�[�̏d������l���㏑���������Ƃ���array_merge()���g���j
        $img = $icdb->toArray();
        // �����N�E�����͕ύX����邱�Ƃ������A�ꗗ�p�̃f�[�^�L���b�V���ɉe����^���Ȃ��悤�ɕʂɏ�������
        $status = array();
        $status['rank'] = $img['rank'];
        $status['rank_f'] = ($img['rank'] == -1) ? '���ځ[��' : $img['rank'];
        $status['memo'] = mb_convert_encoding($img['memo'], 'SJIS-win', 'UTF-8');
        unset($img['rank'], $img['memo']);

        // �\���p�ϐ���ݒ�
        if ($enable_cache) {
            $add = $cache->call('buildImgCell', $img);
            if ($mode == 1) {
                $chk = EditForm::imgChecker($img); // ��r�I�y���̂ŃL���b�V�����Ȃ�
                $add += $chk;
            } elseif ($mode == 2) {
                $mng = $cache->call('EditForm::imgManager', $img, $status);
                $add += $mng;
            }
        } else {
            $add = buildImgCell($img);
            if ($mode == 1) {
                $chk = EditForm::imgChecker($img);
                $add += $chk;
            } elseif ($mode == 2) {
                $mng = EditForm::imgManager($img, $status);
                $add += $mng;
            }
        }
        if (!file_exists($add['thumb'])) {
            // �����_�����O���Ɏ�����htmlspecialchars()�����̂�&amp;�ɂ��Ȃ�
            $add['thumb'] = 'ic2.php?r=1&t=1&uri=' . rawurlencode($img['uri']);
        }
        $item = array_merge($img, $add, $status);

        // Exif�����擾
        if ($show_exif && file_exists($add['src']) && $img['mime'] == 'image/jpeg') {
            $item['exif'] = $enable_cache ? $cache->call('ic2_read_exif', $add['src']) : ic2_read_exif($add['src']);
        } else {
            $item['exif'] = NULL;
        }

        $items[] = $item;
    }

    $i = count($items); // == $found
    // �e�[�u���̗]���𖄂߂邽�߂�NULL��}��
    if ($i > $cols && ($j = $i % $cols) > 0) {
        for ($k = 0; $k < $cols - $j; $k++) {
            $items[] = NULL;
            $i++;
        }
    }
    // ���̎��_�� $i == $cols * ���R��

    $flexy->setData('items', $items);
    $flexy->setData('popup', $popup);
    $flexy->setData('matrix', new MatrixManager($cols, $rows, $i));
}

// }}}
// {{{ output


// ���[�h�ʂ̍ŏI����
switch ($mode) {
    case 2:
        $title = $ini['Manager']['title'];
        $list_template = 'iv2m.tpl.html';
        break;
    case 1:
        $title = $ini['Viewer']['title'];
        $list_template = 'iv2a.tpl.html';
        break;
    default:
        $title = $ini['Viewer']['title'];
        $list_template = 'iv2.tpl.html';
}

// �t�H�[�����ŏI�������A�e���v���[�g�p�I�u�W�F�N�g�ɕϊ�
$r = &new HTML_QuickForm_Renderer_ObjectFlexy($flexy);
//$r->setLabelTemplate('_label.tpl.html');
//$r->setHtmlTemplate('_html.tpl.html');
$qf->updateAttributes(array('method' => 'get')); // ���N�G�X�g��POST�ł��󂯓���邽�߁A�����ŕύX
$qf->accept($r);
$qfObj = &$r->toObject();

// �ϐ���Assign
$flexy->setData('title', $title);
$flexy->setData('mode', $mode);
$flexy->setData('js', $qf->getValidationScript());
$flexy->setData('move', $qfObj);

// �y�[�W��\��
$flexy->compile($list_template);
$flexy->output();


// }}}
// {{{ debug output


if ($debug) {
    $dump = array(
        'get' => $_GET,
        'post' => $_POST,
        'ini' => $ini,
        'convert_path' => findexec('convert', $ini['General']['magick']),
        'convert_env' => findexec('convert'),
        'clamscan_path' => findexec('clamscan', $ini['Getter']['clamav']),
        'clamscan_env' => findexec('clamscan'),
        'cache' => $cache_options,
        'sample' => $items[0]
    );
    Var_Dump::display($dump);
}


// }}}

?>