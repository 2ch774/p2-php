<?php
// {{{ KeyValuePersister

/**
 * �L�[/�l�̃y�A��SQLite3�̃f�[�^�x�[�X�ɕۑ�����
 */
class KeyValuePersister implements ArrayAccess, Countable, IteratorAggregate
{
    // {{{ constants

    const Q_CREATETABLE = 'CREATE TABLE kvp (
  id TEXT PRIMARY KEY ON CONFLICT REPLACE,
  value TEXT,
  mtime INTEGER NOT NULL DEFAULT (strftime(\'%s\',\'now\')),
  sort_order INTEGER NOT NULL DEFAULT 0
)';
    const Q_COUNT   = 'SELECT COUNT(*) FROM kvp LIMIT 1';
    const Q_EXSITS1 = 'SELECT 1 FROM kvp WHERE id = :key LIMIT 1';
    const Q_EXSITS2 = 'SELECT 1 FROM kvp WHERE id = :key AND mtime >= :expires LIMIT 1';
    const Q_GET     = 'SELECT value FROM kvp WHERE id = :key LIMIT 1';
    const Q_GETALL  = 'SELECT * FROM kvp';
    const Q_SAVE    = 'INSERT INTO kvp (id, value, sort_order) VALUES (:key, :value, :order)';
    const Q_UPDATE0 = 'UPDATE kvp SET sort_order = :order WHERE id = :key';
    const Q_UPDATE1 = 'UPDATE kvp SET value = :value, mtime = strftime(\'%s\',\'now\'), sort_order = :order WHERE id = :key';
    const Q_REMOVE  = 'DELETE FROM kvp WHERE id = :key';
    const Q_CLEAN   = 'DELETE FROM kvp';
    const Q_GC      = 'DELETE FROM kvp WHERE mtime < :expires';

    // }}}
    // {{{ staric private properties

    /**
     * �f�[�^�x�[�X���Ɉ�ӂ�KeyValuePersister�̃C���X�^���X��ێ�����z��
     *
     * @var array
     */
    static private $_persisters = array();

    // }}}
    // {{{ protected properties

    /**
     * PDO�̃C���X�^���X
     *
     * @var PDO
     */
    protected $_dbh;

    /**
     * �J��Ԃ��g��PDOStatement��ێ�����z��
     *
     * @var array
     */
    protected $_statements;

    // }}}
    // {{{ getPersister()

    /**
     * �V���O���g�����\�b�h
     *
     * @param string $path
     * @param string $class
     * @return KeyValuePersister
     * @throws InvalidArgumentException
     */
    static public function getPersister($path, $class = 'KeyValuePersister')
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Parameter #1 \'$path\' should be a string value');
        }
        if (!is_string($class)) {
            throw new InvalidArgumentException('Parameter #2 \'$class\' should be a string value');
        }

        if (strcasecmp($class, 'KeyValuePersister') != 0) {
            if (!class_exists($class, false)) {
                throw new InvalidArgumentException("Class '{$class}' is not declared");
            }
            if (!is_subclass_of($class, 'KeyValuePersister')) {
                throw new InvalidArgumentException("Class '{$class}' is not a subclass of KeyValuePersister");
            }
        }

        if ($path == ':memory:') {
            // pass
        } elseif (file_exists($path)) {
            if (!is_file($path)) {
                throw new InvalidArgumentException("'{$path}' is not a standard file");
            }
            $path = realpath($path);
        } else {
            if (strpos($path, '/') !== false ||
                (strncasecmp(PHP_OS, 'WIN', 3) == 0 && strpos($path, '\\') !== false))
            {
                $dir = dirname($path);
                $file = basename($path);
            } else {
                $dir = getcwd();
                $file = $path;
            }
            if (!is_string($dir) || !is_dir($dir)) {
                throw new InvalidArgumentException("No directory for '{$path}'");
            }
            $path = realpath($dir) . DIRECTORY_SEPARATOR . $file;
        }

        if (array_key_exists($path, self::$_persisters)) {
            $persister = self::$_persisters[$path];
            if (strcasecmp(get_class($persister), $class) != 0) {
                throw new InvalidArgumentException('Mismatch of $path and $class');
            }
        } else {
            $persister = new $class($path);
            self::$_persisters[$path] = $persister;
        }

        return $persister;
    }

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     * getPersister()�Ńf�[�^�x�[�X�̃p�X�����؁E���K�����Ă���Ăяo�����
     *
     * @param string $path
     * @throws PDOException
     */
    protected function __construct($path)
    {
        $init = !file_exists($path);
        $this->_dbh = $dbh = new PDO('sqlite:' . $path);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($init) {
            $dbh->exec(self::Q_CREATETABLE);
        }
        $this->_statements = array();
    }

    // }}}
    // {{{ _getStatement()

    /**
     * �X�e�[�g�����g���쐬����Ă��Ȃ���΍쐬���ĕԂ�
     *
     * @param string $query
     * @return PDOStatement
     */
    protected function _getStatement($query)
    {
        if (!array_key_exists($query, $this->_statements)) {
            $this->_statements[$query] = $this->_dbh->prepare($query);
        }
        return $this->_statements[$query];
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
    public function exists($key, $lifeTime = -1)
    {
        if ($lifeTime == -1) {
            $sth = $this->_getStatement(self::Q_EXSITS1);
            $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        } else {
            $sth = $this->_getStatement(self::Q_EXSITS2);
            $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
            $sth->bindValue(':expires', time() - $lifeTime, PDO::PARAM_INT);
        }
        $sth->execute();
        $ret = (bool)$sth->fetchColumn();
        $sth->closeCursor();
        return $ret;
    }

    // }}}
    // {{{ getAll()

    /**
     * �S�Ẵ��R�[�h��A�z�z��Ƃ��ĕԂ�
     *
     * @param array $orders
     * @param int $limit
     * @param int $offset
     * @param bool $whole
     * @return array
     */
    public function getAll(array $orders = null, $limit = null, $offset = null, $whole = false)
    {
        $orderBy = 'sort_order ASC, id ASC';

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

        $query = self::Q_GETALL . ' ORDER BY ' . $orderBy;

        if ($limit !== null) {
            $query .= sprintf(' LIMIT %d', $limit);
            if ($offset !== null) {
                $query .= sprintf(' OFFSET %d', $offset);
            }
        }

        $sth = $this->_dbh->query($query);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $values = array();
        if ($whole) {
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
    // {{{ get()

    /**
     * �L�[�ɑΉ�����l���擾����
     *
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        $sth = $this->_getStatement(self::Q_GET);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->execute();
        $value = $sth->fetchColumn();
        $sth->closeCursor();
        if ($value === false) {
            return null;
        } else {
            return $this->_decodeValue($value);
        }
    }

    // }}}
    // {{{ save()

    /**
     * �f�[�^��ۑ�����
     *
     * @param string $key
     * @param string $value
     * @param int $order
     * @return void
     */
    public function save($key, $value, $order = 0)
    {
        $sth = $this->_getStatement(self::Q_SAVE);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->bindValue(':value', $this->_encodeValue($value), PDO::PARAM_STR);
        $sth->bindValue(':order', (int)$order, PDO::PARAM_INT);
        $sth->execute();
    }

    // }}}
    // {{{ update()

    /**
     * �f�[�^���X�V����
     *
     * @param string $key
     * @param string $value
     * @param int $order
     * @return void
     */
    public function update($key, $value, $order = 0)
    {
        $sth = $this->_getStatement(self::Q_UPDATE1);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->bindValue(':value', $this->_encodeValue($value), PDO::PARAM_STR);
        $sth->bindValue(':order', (int)$order, PDO::PARAM_INT);
        $sth->execute();
    }

    // }}}
    // {{{ setOrder()

    /**
     * �f�[�^�̕��я� (sort_order�J�����̒l) ��ݒ肷��
     *
     * @param string $key
     * @param int $order
     * @return void
     */
    public function setOrder($key, $order)
    {
        $sth = $this->_getStatement(self::Q_UPDATE0);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->bindValue(':order', (int)$order, PDO::PARAM_INT);
        $sth->execute();
    }

    // }}}
    // {{{ remvoe()

    /**
     * �L�[�ɑΉ����郌�R�[�h���폜����
     *
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        $sth = $this->_getStatement(self::Q_REMOVE);
        $sth->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $sth->execute();
    }

    // }}}
    // {{{ clean()

    /**
     * ���ׂẴ��R�[�h���폜����
     *
     * @param void
     * @return void
     */
    public function clean()
    {
        $this->_dbh->exec(self::Q_CLEAN);
    }

    // }}}
    // {{{ gc()

    /**
     * �����؂�̃��R�[�h���폜����
     *
     * @param int $lifeTime
     * @return void
     */
    public function gc($lifeTime)
    {
        $sth = $this->_dbh->prepare(self::Q_GC);
        $sth->bindValue(':expires', time() - $lifeTime, PDO::PARAM_INT);
        $sth->execute();
    }

    // }}}
    // {{{ vacuum()

    /**
     * �쐬�ς݃X�e�[�g�����g���N���A���AVACUUM�𔭍s����
     * ���̃v���Z�X�������f�[�^�x�[�X���J���Ă���Ƃ��Ɏ��s���ׂ��ł͂Ȃ�
     *
     * @param void
     * @return void
     */
    public function vacuum()
    {
        $this->_statements = array();
        $this->_dbh->exec('VACUUM');
    }

    // }}}
    // {{{ count()

    /**
     * �S���R�[�h�����擾����
     * Countable::count()
     *
     * @param void
     * @return string
     */
    public function count()
    {
        $sth = $this->_getStatement(self::Q_COUNT);
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
        $this->remove($offset);
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
