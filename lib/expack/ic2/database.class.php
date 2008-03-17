<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once 'DB.php';
require_once 'DB/DataObject.php';
require_once P2EX_LIB_DIR . '/ic2/loadconfig.inc.php';

class IC2DB_Skel extends DB_DataObject
{
    // {{{ properties

    var $_db;
    var $_ini;

    // }}}
    // {{{ constcurtor

    function IC2DB_Skel()
    {
        $this->__construct();
    }

    function __construct()
    {
        static $set_to_utf8 = false;

        // �ݒ�̓ǂݍ���
        $ini = ic2_loadconfig();
        $this->_ini = $ini;
        if (!$ini['General']['dsn']) {
            die("<p><b>Error:</b> DSN���ݒ肳��Ă��܂���B</p>");
        }

        // �f�[�^�x�[�X�֐ڑ�
        $this->_database_dsn = $ini['General']['dsn'];
        $this->_db = &$this->getDatabaseConnection();
        if (DB::isError($this->_db)) {
            die($this->_db->getMessage());
        }

        // �N���C�A���g�̕����Z�b�g�� UTF-8 ���w��
        if (!$set_to_utf8) {
            $driver = strtolower($dbextension);

            switch ($driver) {
            case 'mysql':
            case 'mysqli':
                if ($driver == 'mysql' && function_exists('mysql_set_charset')) {
                    mysql_set_charset('utf8', $this->_db->connection);
                } elseif ($driver == 'mysqli' && function_exists('mysqli_set_charset')) {
                    mysqli_set_charset($this->_db->connection, 'utf8');
                } else {
                    $this->_db->query("SET NAMES utf8");
                }
                break;
            case 'pgsql':
                if (function_exists('pg_set_client_encoding')) {
                    pg_set_client_encoding($this->_db->connection, 'UNICODE');
                } else {
                    $this->_db->query("SET CLIENT_ENCODING TO 'UNICODE'");
                }
                break;
            }

            $set_to_utf8 = true;
        }
    }

    // }}}
    // {{{ whereAddQuoted()

    // WHERE�������
    function whereAddQuoted($key, $cmp, $value, $logic = 'AND')
    {
        $types = $this->table();
        $col = $this->_db->quoteIdentifier($key);
        if ($types[$key] != DB_DATAOBJECT_INT) {
            $value = $this->_db->quoteSmart($value);
        }
        $cond = sprintf('%s %s %s', $col, $cmp, $value);
        return $this->whereAdd($cond, $logic);
    }

    // }}}
    // {{{ orderByArray()

    // ORDER BY�������
    function orderByArray($sort)
    {
        $order = array();
        foreach ($sort as $k => $d) {
            if (!is_string($k)) {
                if ($d && is_string($d)) {
                    $k = $d;
                    $d = 'ASC';
                } else {
                    continue;
                }
            }
            $k = $this->_db->quoteIdentifier($k);
            if (!$d || strtoupper($d) == 'DESC') {
                $order[] = $k . ' DESC';
            } else {
                $order[] = $k . ' ASC';
            }
        }
        if (!count($order)) {
            return FALSE;
        }
        return $this->orderBy(implode(', ', $order));
    }

    // }}}
}
