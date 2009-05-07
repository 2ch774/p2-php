<?php
// {{{ KeyValuePersister

/**
 * �L�[/�l�̃y�A��SQLite3�̃f�[�^�x�[�X�ɕۑ�����
 */
class KeyValuePersister implements ArrayAccess, Countable, IteratorAggregate
{
    // {{{ constants

    const Q_TABLEEXISTS = 'SELECT 1 FROM sqlite_master WHERE type = \'table\' AND name = :table LIMIT 1';
    const Q_CREATETABLE = 'CREATE TABLE $__table (
  id TEXT PRIMARY KEY ON CONFLICT REPLACE,
  value TEXT,
  mtime INTEGER NOT NULL DEFAULT (strftime(\'%s\',\'now\')),
  sort_order INTEGER NOT NULL DEFAULT 0
)';
    const Q_COUNT       = 'SELECT COUNT(*) FROM $__table LIMIT 1';
    const Q_EXSITS      = 'SELECT 1 FROM $__table WHERE id = :key AND (:expires IS NULL OR mtime >= :expires) LIMIT 1';
    const Q_GET         = 'SELECT * FROM $__table WHERE id = :key LIMIT 1';
    const Q_GETALL      = 'SELECT * FROM $__table';
    const Q_GETKEYS     = 'SELECT id FROM $__table';
    const Q_SAVE        = 'INSERT INTO $__table (id, value, sort_order) VALUES (:key, :value, :order)';
    const Q_UPDATE      = 'UPDATE $__table SET value = :value, mtime = strftime(\'%s\',\'now\'), sort_order = :order WHERE id = :key';
    const Q_TOUCH       = 'UPDATE $__table SET mtime = :time WHERE id = :key';
    const Q_SETORDER    = 'UPDATE $__table SET sort_order = :order WHERE id = :key';
    const Q_DELETE      = 'DELETE FROM $__table WHERE id = :key';
    const Q_CLEAN       = 'DELETE FROM $__table';
    const Q_GC          = 'DELETE FROM $__table WHERE mtime < :expires';

    // }}}
    // {{{ staric private properties

    /**
     * �f�[�^�x�[�X���Ɉ�ӂ�PDO,PDOStatement,KeyValuePersister�̃C���X�^���X��ێ�����z��
     *
     * @var array
     */
    static private $_objects = array();

    // }}}
    // {{{ private properties

    /**
     * PDO�̃C���X�^���X
     *
     * @var PDO
     */
    private $_dbh;

    /**
     * SQLite3�f�[�^�x�[�X�̃p�X
     *
     * @var string
     */
    private $_path;

    /**
     * �e�[�u����
     *
     * @var string
     */
    private $_tableName;

    // }}}
    // {{{ getPersister()

    /**
     * �V���O���g�����\�b�h
     *
     * @param string $fileName
     * @param string $className
     * @param string &$openedPath
     * @return KeyValuePersister
     * @throws InvalidArgumentException, UnexpectedValueException, RuntimeException, PDOException
     */
    static public function getPersister($fileName, $className = 'KeyValuePersister', &$openedPath = null)
    {
        // �����̌^���`�F�b�N
        if (!is_string($fileName)) {
            throw new InvalidArgumentException('Parameter #1 \'$fileName\' should be a string value');
        }
        if (!is_string($className)) {
            throw new InvalidArgumentException('Parameter #2 \'$className\' should be a string value');
        }

        // �N���X�����`�F�b�N
        if (strcasecmp($className, 'KeyValuePersister') != 0) {
            if (!class_exists($className, false)) {
                throw new UnexpectedValueException("Class '{$className}' is not declared");
            }
            if (!is_subclass_of($className, 'KeyValuePersister')) {
                throw new UnexpectedValueException("Class '{$className}' is not a subclass of KeyValuePersister");
            }
        }

        // �f�[�^�x�[�X�t�@�C�����`�F�b�N
        if ($fileName == ':memory:') {
            $path = $fileName;
            $createTable = true;
        } elseif (file_exists($fileName)) {
            if (!is_file($fileName)) {
                throw new RuntimeException("'{$fileName}' is not a standard file");
            }
            if (!is_writable($fileName)) {
                throw new RuntimeException("File '{$fileName}' is not writable");
            }
            $path = realpath($fileName);
            $createTable = false;
        } else {
            if (strpos($fileName, '/') !== false ||
                (strncasecmp(PHP_OS, 'WIN', 3) == 0 && strpos($fileName, '\\') !== false))
            {
                $dirName = dirname($fileName);
                $baseName = basename($fileName);
            } else {
                $dirName = getcwd();
                $baseName = $fileName;
            }
            if (!is_string($dirName) || !is_dir($dirName)) {
                throw new RuntimeException("No directory for '{$fileName}'");
            }
            if (!is_writable($dirName)) {
                throw new RuntimeException("Directory '{$dirName}' is not writable");
            }
            $path = realpath($dirName) . DIRECTORY_SEPARATOR . $baseName;
            $createTable = true;
        }

        $lcname = strtolower($className);
        $tableName = 'kvp_' . $lcname;
        $openedPath = $path;

        // �C���X�^���X���쐬���A�ÓI�ϐ��ɕێ�
        if (array_key_exists($path, self::$_objects)) {
            if (array_key_exists($lcname, self::$_objects[$path]['persisters'])) {
                $persister = self::$_objects[$path]['persisters'][$lcname];
            } else {
                $dbh = self::$_objects[$path]['connection'];
                $persister = new $className($dbh, $path, $tableName);
                self::$_objects[$path]['persisters'][$lcname] = $persister;
            }
        } else {
            $dbh = new PDO('sqlite:' . $path);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $persister = new $className($dbh, $path, $tableName);
            self::$_objects[$path] = array(
                'connection' => $dbh,
                'statements' => array(),
                'persisters' => array($lcname => $persister),
            );
        }

        return $persister;
    }

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     * getPersister()����Ăяo�����
     *
     * @param PDO $dbh
     * @param string $path
     * @param string $tableName
     * @throws PDOException
     */
    private function __construct(PDO $dbh, $path, $tableName)
    {
        $this->_dbh = $dbh;
        $this->_path = $path;
        $this->_tableName = '"' . str_replace('"', '""', $tableName) . '"';

        $sth = $dbh->prepare(self::Q_TABLEEXISTS);
        $sth->bindValue(':table', $tableName, PDO::PARAM_STR);
        $sth->execute();
        $exists = $sth->fetchColumn();
        $sth->closeCursor();
        unset($sth);
        if (!$exists) {
            $dbh->exec(str_replace('$__table', $this->_tableName, self::Q_CREATETABLE));
        }
    }

    // }}}
    // {{{ _prepare()

    /**
     * �v���y�A�[�h�X�e�[�g�����g���쐬����
     *
     * @param string $query
     * @param bool $isTemporary
     * @return PDOStatement
     * @throws PDOException
     */
    private function _prepare($query, $isTemporary = false)
    {
        $query = str_replace('$__table', $this->_tableName, $query);

        if (!$isTemporary && array_key_exists($query, self::$_objects[$this->_path]['statements'])) {
            $sth = self::$_objects[$this->_path]['statements'][$query];
        } else {
            if (strncmp($query, 'SELECT ', 7) == 0) {
                $sth = $this->_dbh->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            } else {
                $sth = $this->_dbh->prepare($query);
            }
            if (!$isTemporary) {
                self::$_objects[$this->_path]['statements'][$query] = $sth;
            }
        }

        return $sth;
    }

    // }}}
    // {{{ _generateOrderByAndLimitOffset()

    /**
     * ���R�[�h���܂Ƃ߂Ď擾����ۂ�OREDER BY���LIMIT��𐶐�����
     *
     * @param array $orders
     * @param int $limit
     * @param int $offset
     * @return string
     */
    private function _generateOrderByAndLimitOffset(array $orders = null, $limit = null, $offset = null)
    {
        $orderBy = 'sort_order ASC, id ASC';
        $limitOffset = '';

        if ($orders) {
            $terms = array();
            foreach ($orders as $column => $ascending) {
                $direction = $ascending ? 'ASC' : 'DESC';
                switch ($column) {
                    case 'key':
                        $terms[] = 'id ' . $direction;
                        break;
                    case 'value':
                        $terms[] = 'value ' . $direction;
                        break;
                    case 'mtime':
                        $terms[] = 'mtime ' . $direction;
                        break;
                    case 'order':
                        $terms[] = 'sort_order ' . $direction;
                        break;
                }
            }
            if (count($terms)) {
                $orderBy = implode(', ', $terms);
            }
        }

        if ($limit !== null) {
            $limitOffset = sprintf(' LIMIT %d', $limit);
            if ($offset !== null) {
                $limitOffset .= sprintf(' OFFSET %d', $offset);
            }
        }

        return ' ORDER BY ' . $orderBy . $limitOffset;
    }

    // }}}
    // {{{ _encodeKey()

    /**
     * �L�[��UTF-8 or US-ASCII������ɃG���R�[�h����
     *
     * @param string $key
     * @return string
     */
    protected function _encodeKey($key)
    {
        return (string)$key;
    }

    // }}}
    // {{{ _decodeKey()

    /**
     * �L�[���f�R�[�h����
     *
     * @param string $key
     * @return string
     */
    protected function _decodeKey($key)
    {
        return $key;
    }

    // }}}
    // {{{ _encodeValue()

    /**
     * �l��UTF-8 or US-ASCII������ɃG���R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _encodeValue($value)
    {
        return (string)$value;
    }

    // }}}
    // {{{ _decodeValue()

    /**
     * �l���f�R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _decodeValue($value)
    {
        return $value;
    }

    // }}}
    // {{{ exists()

    /**
     * �L�[�ɑΉ�����l���ۑ�����Ă��邩�𒲂ׂ�
     *
     * @param string $key
     * @param int $lifeTime
     * @return bool
     */
    public function exists($key, $lifeTime = null)
    {
        $sth = $this->_prepare(self::Q_EXSITS);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        if ($lifeTime === null) {
            $sth->bindValue(':expires', null, PDO::PARAM_NULL);
        } else {
            $sth->bindValue(':expires', time() - $lifeTime, PDO::PARAM_INT);
        }
        $sth->execute();
        $ret = (bool)$sth->fetchColumn();
        $sth->closeCursor();
        return $ret;
    }

    // }}}
    // {{{ get()

    /**
     * �L�[�ɑΉ�����l���擾����
     *
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        $sth = $this->_prepare(self::Q_GET);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->execute();
        $row = $sth->fetch();
        $sth->closeCursor();
        if ($row === false) {
            return null;
        } else {
            return $this->_decodeValue($row['value']);
        }
    }

    // }}}
    // {{{ getDetail()

    /**
     * �L�[�ɑΉ����郌�R�[�h���擾����
     *
     * @param string $key
     * @return array
     */
    public function getDetail($key)
    {
        $sth = $this->_prepare(self::Q_GET);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->execute();
        $row = $sth->fetch();
        $sth->closeCursor();
        if ($row === false) {
            return null;
        } else {
            return array(
                'key' => $this->_decodeKey($row['id']),
                'value' => $this->_decodeValue($row['value']),
                'mtime' => (int)$row['mtime'],
                'order' => (int)$row['sort_order']
            );
        }
    }

    // }}}
    // {{{ getAll()

    /**
     * �S�Ẵ��R�[�h��A�z�z��Ƃ��ĕԂ�
     *
     * @param array $orders
     * @param int $limit
     * @param int $offset
     * @param bool $getDetails
     * @return array
     */
    public function getAll(array $orders = null, $limit = null, $offset = null, $getDetails = false)
    {
        $query = self::Q_GETALL . $this->_generateOrderByAndLimitOffset($orders, $limit, $offset);
        $sth = $this->_prepare($query, true);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->execute();
        $values = array();
        if ($getDetail) {
            while ($row = $sth->fetch()) {
                $key = $this->_decodeKey($row['id']);
                $values[$key] = array(
                    'key' => $key,
                    'value' => $this->_decodeValue($row['value']),
                    'mtime' => (int)$row['mtime'],
                    'order' => (int)$row['sort_order']
                );
            }
        } else {
            while ($row = $sth->fetch()) {
                $values[$this->_decodeKey($row['id'])] = $this->_decodeValue($row['value']);
            }
        }
        $sth->closeCursor();
        return $values;
    }

    // }}}
    // {{{ getKeys()

    /**
     * �S�ẴL�[�̔z���Ԃ�
     *
     * @param array $orders
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getKeys(array $orders = null, $limit = null, $offset = null)
    {
        $query = self::Q_GETKEYS . $this->_generateOrderByAndLimitOffset($orders, $limit, $offset);
        $sth = $this->_prepare($query, true);
        $sth->setFetchMode(PDO::FETCH_COLUMN, 0);
        $sth->execute();
        $keys = array();
        while (($key = $sth->fetch()) !== false) {
            $keys[] = $this->_decodeKey($key);
        }
        $sth->closeCursor();
        return $keys;
    }

    // }}}
    // {{{ save()

    /**
     * �f�[�^��ۑ�����
     *
     * @param string $key
     * @param string $value
     * @param int $order
     * @return bool
     */
    public function save($key, $value, $order = 0)
    {
        $sth = $this->_prepare(self::Q_SAVE);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->bindValue(':value', $this->_encodeValue($value), PDO::PARAM_STR);
        $sth->bindValue(':order', (int)$order, PDO::PARAM_INT);
        if ($sth->execute()) {
            return $sth->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ update()

    /**
     * �f�[�^���X�V����
     *
     * @param string $key
     * @param string $value
     * @param int $order
     * @return bool
     */
    public function update($key, $value, $order = 0)
    {
        $sth = $this->_prepare(self::Q_UPDATE);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->bindValue(':value', $this->_encodeValue($value), PDO::PARAM_STR);
        $sth->bindValue(':order', (int)$order, PDO::PARAM_INT);
        if ($sth->execute()) {
            return $sth->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ touch()

    /**
     * �f�[�^�̍X�V���������ݎ����ɐݒ肷��
     *
     * @param string $key
     * @param int $time
     * @return bool
     */
    public function touch($key, $time = null)
    {
        $sth = $this->_prepare(self::Q_TOUCH);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->bindValue(':time', is_numeric($time) ? (int)$time : time(), PDO::PARAM_INT);
        if ($sth->execute()) {
            return $sth->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ setOrder()

    /**
     * �f�[�^�̕��я� (sort_order�J�����̒l) ��ݒ肷��
     *
     * @param string $key
     * @param int $order
     * @return bool
     */
    public function setOrder($key, $order)
    {
        $sth = $this->_prepare(self::Q_SETORDER);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->bindValue(':order', (int)$order, PDO::PARAM_INT);
        if ($sth->execute()) {
            return $sth->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ delete()

    /**
     * �L�[�ɑΉ����郌�R�[�h���폜����
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $sth = $this->_prepare(self::Q_DELETE);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->execute();
        if ($sth->execute()) {
            return $sth->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ clean()

    /**
     * ���ׂẴ��R�[�h���폜����
     *
     * @param void
     * @return int
     */
    public function clean()
    {
        $sth = $this->_prepare(self::Q_CLEAN, true);
        if ($sth->execute()) {
            return $sth->rowCount();
        } else {
            return false;
        }
    }

    // }}}
    // {{{ gc()

    /**
     * �����؂�̃��R�[�h���폜����
     *
     * @param int $lifeTime
     * @return int
     */
    public function gc($lifeTime)
    {
        $sth = $this->_prepare(self::Q_GC, true);
        $sth->bindValue(':expires', time() - $lifeTime, PDO::PARAM_INT);
        if ($sth->execute()) {
            return $sth->rowCount();
        } else {
            return false;
        }
    }

    // }}}
    // {{{ vacuum()

    /**
     * �쐬�ς݃v���y�A�[�h�X�e�[�g�����g���N���A���AVACUUM�𔭍s����
     * ���̃v���Z�X�������f�[�^�x�[�X���J���Ă���Ƃ��Ɏ��s���ׂ��ł͂Ȃ�
     *
     * @param void
     * @return void
     */
    public function vacuum()
    {
        self::$_objects[$this->_path]['statements'] = array();
        $this->_dbh->exec('VACUUM');
    }

    // }}}
    // {{{ count()

    /**
     * �S���R�[�h�����擾����
     * Countable::count()
     *
     * @param void
     * @return int
     */
    public function count()
    {
        $sth = $this->_prepare(self::Q_COUNT);
        $sth->execute();
        $ret = (int)$sth->fetchColumn();
        $sth->closeCursor();
        return $ret;
    }

    // }}}
    // {{{ offsetExists()

    /**
     * ArrayAccess::offsetExists()
     *
     * @param string $offset
     * @return string
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    // }}}
    // {{{ offsetGet()

    /**
     * ArrayAccess::offsetGet()
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    // }}}
    // {{{ offsetSet()

    /**
     * ArrayAccess::offsetSet()
     *
     * @param string $offset
     * @param string $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->save($offset, $value);
    }

    // }}}
    // {{{ offsetUnset()

    /**
     * ArrayAccess::offsetUnset()
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    // }}}
    // {{{ getIterator()

    /**
     * IteratorAggregate::getIterator()
     *
     * @param void
     * @return ArrayObject
     */
    public function getIterator()
    {
        return new ArrayObject($this->getAll());
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
