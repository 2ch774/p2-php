<?php

// {{{ MatomeCacheList

/**
 * �܂Ƃߓǂ݃L���b�V�����X�g�N���X
 */
class MatomeCacheList
{
    // {{{ add()

    /**
     * �V�����G���g����ǉ�����
     *
     * @param string $content
     * @param array $metaData
     * @return $key
     */
    static public function add($content, array $metaData)
    {
        $key = sprintf('%s%0.6f', self::getKeyPrefix(), microtime(true));
        MatomeCacheDataStore::set($key, $content);
        MatomeCacheMetaDataStore::set($key, $metaData);
        return $key;
    }

    // }}}
    // {{{ getKeyPrefix()

    /**
     * �L�[�ړ������擾����
     *
     * @param string $type
     * @param bool $forSearch
     * @return array
     */
    static public function getKeyPrefix($type = null)
    {
        global $_conf, $_login;

        if ($type === null) {
            if ($_conf['iphone']) {
                $type = 'iphone';
            } elseif ($_conf['ktai']) {
                $type = 'ktai';
            } else {
                $type = 'pc';
            }
        }

        return $_login->user_u . '/' . $type . '/';
    }

    // }}}
    // {{{ getList()

    /**
     * �܂Ƃߓǂ݃L���b�V���̃��X�g���擾����
     *
     * @param string $type
     * @return array
     */
    static public function getList($type = null)
    {
        $prefix = self::getKeyPrefix($type);
        $orderBy = array('mtime' => 'DESC', 'key' => 'DESC');

        return MatomeCacheMetaDataStore::getKVS()->getAll($prefix, $orderBy);
    }

    // }}}
    // {{{ getAllList()

    /**
     * �S�܂Ƃߓǂ݃L���b�V���̃��X�g���擾����
     *
     * @param string $type
     * @return array
     */
    static public function getAllList()
    {
        $types = array('pc', 'ktai', 'iphone');
        $lists = array();
        foreach ($types as $type) {
            $lists[$type] = self::getList($type);
        }
        return $list;
    }

    // }}}
    // {{{ trim()

    /**
     * �c�������w�肵�ăL���b�V�����폜����
     *
     * @param int $number
     * @return int
     */
    static public function trim($length, $type = null)
    {
        // $length�������̏ꍇ�͍폜���Ȃ�
        if ($length < 0) {
            return false;
        }

        $prefix = self::getKeyPrefix($type);

        // $length���[���̏ꍇ�͑S���폜
        if ($length == 0) {
            MatomeCacheDataStore::clear($prefix);
            MatomeCacheMetaDataStore::clear($prefix);
            return true;
        }

        // �X�V�������Ƀ\�[�g����$length+1�Ԗڂ̃��R�[�h���擾
        $kvs = MatomeCacheDataStore::getKVS();
        $orderBy = array('mtime' => 'DESC', 'key' => 'DESC');
        $result = $kvs->getAll($prefix, $orderBy, 1, $length, true);
        if (empty($result)) {
            return 0;
        }

        $key = key($result);
        $mtime = current($result)->mtime;
        $query = 'DELETE FROM $__table WHERE '
               . P2KeyValueStore::C_KEY_BEGINS
               . ' AND $__mtime <= :mtime';

        // �����������R�[�h�ƁA������X�V�������Â��f�[�^���폜
        $stmt = $kvs->prepare($query);
        $kvs->bindValueForPrefixSearch($stmt, $prefix);
        $stmt->bindValue(':mtime', $mtime, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $numRemoved = $stmt->rowCount();
        } else {
            return false;
        }

        // ���^�f�[�^���폜
        $kvs = MatomeCacheMetaDataStore::getKVS();
        /*
         * ���^�f�[�^�̕�����u�x��đ}������邽�߁A�����H�Ƀf�[�^��mtime��
         * ���^�f�[�^��mtime���قȂ�\��������B���̂Ƃ��f�[�^��mtime��
         * ���̂܂܎g����getList()�̌��ʂɃf�[�^�����݂��Ȃ����R�[�h��
         * �܂܂�邱�ƂɂȂ�̂ŁA�����h�����߂Ƀf�[�^�Ɠ���L�[��
         * ���^�f�[�^��mtime���擾����B
         */
        if ($record = $kvs->getRaw($key)) {
            $mtime = $record->mtime;
        }
        $stmt = $kvs->prepare($query);
        $kvs->bindValueForPrefixSearch($stmt, $prefix);
        $stmt->bindValue(':mtime', $mtime, PDO::PARAM_INT);
        $stmt->execute();

        // �폜�����f�[�^����Ԃ�
        return $numRemoved;
    }

    // }}}
    // {{{ optimize()
    
    /**
     * �܂Ƃߓǂ݃L���b�V�����œK������
     *
     * @param void
     * @return void
     */
    static public function optimize()
    {
        MatomeCacheDataStore::getKVS()->optimize();
        MatomeCacheMetaDataStore::getKVS()->optimize();
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
