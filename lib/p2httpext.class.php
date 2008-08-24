<?php
/**
 * rep2expack feat. pecl_http
 */

require_once P2_LIB_DIR . '/filectl.class.php';
require_once P2_LIB_DIR . '/p2util.class.php';

// {{{ P2HttpCallback

/**
 * �R�[���o�b�N�p�C���^�[�t�F�[�X
 */
interface P2HttpCallback
{
    /**
     * �R�[���o�b�N���\�b�h
     *
     * @param P2HttpGet $req
     * @return void
     */
    public function execute(P2HttpGet $req);
}

// }}}
// {{{ P2HttpCallback_SaveEucjpAsSjis

/**
 * EUC-JP�̃��X�|���X�{�f�B��Shift_JIS�ɕϊ����ăt�@�C���ɕۑ�����
 */
class P2HttpCallback_SaveEucjpAsSjis implements P2HttpCallback
{
    // {{{ execute()

    /**
     * EUC-JP�̃��X�|���X�{�f�B��Shift_JIS�ɕϊ����ăt�@�C���ɕۑ�����
     *
     * CP51932�̃T�|�[�g��PHP 5.2.1����
     *
     * @param P2HttpGet $req
     * @return void
     */
    public function execute(P2HttpGet $req)
    {
        $save_path = $req->getSavePath();
        file_put_contents($save_path,
                          mb_convert_encoding($req->getResponseBody(), 'CP932', 'CP51932'),
                          LOCK_EX
                          );
        chmod($save_path, $req->getSavePermission());
    }

    // }}}
}

// }}}
// {{{ P2HttpCallback_SaveUtf8AsSjis

/**
 * UTF-8�̃��X�|���X�{�f�B��Shift_JIS�ɕϊ����ăt�@�C���ɕۑ�����
 */
class P2HttpCallback_SaveUtf8AsSjis implements P2HttpCallback
{
    // {{{ execute()

    /**
     * UTF-8�̃��X�|���X�{�f�B��Shift_JIS�ɕϊ����ăt�@�C���ɕۑ�����
     *
     * @param P2HttpGet $req
     * @return void
     */
    public function execute(P2HttpGet $req)
    {
        $save_path = $req->getSavePath();
        file_put_contents($save_path,
                          mb_convert_encoding($req->getResponseBody(), 'CP932', 'UTF-8'),
                          LOCK_EX
                          );
        chmod($save_path, $req->getSavePermission());
    }

    // }}}
}

// }}}
// {{{ P2HttpGet

/**
 * HTTP GET
 */
class P2HttpGet extends HttpRequest
{
    // {{{ properties

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����p�X
     *
     * @var string
     */
    private $_savePath;

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����ۂ̃p�[�~�b�V����
     *
     * @var int
     */
    private $_savePerm;

    /**
     * �G���[���
     *
     * @var string
     */
    private $_errorInfo;

    /**
     * ���X�|���X�R�[�h��200�̎��̃R�[���o�b�N�I�u�W�F�N�g
     *
     * @var P2HttpCallback
     */
    private $_onSuccess;

    /**
     * ���X�|���X�R�[�h��200�ȊO�̎��̃R�[���o�b�N�I�u�W�F�N�g
     *
     * @var P2HttpCallback
     */
    private $_onFailure;

    /**
     * �r�����b�N�p�̃t�@�C���n���h��
     *
     * @var resource
     */
    private $_mutex;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @param string $url
     * @param string $save_path
     * @param array $options
     * @param P2HttpCallback $on_success
     * @param P2HttpCallback $on_failure
     * @param resource $mutex
     */
    public function __construct($url,
                                $save_path,
                                array $options = null,
                                P2HttpCallback $on_success = null,
                                P2HttpCallback $on_failure = null,
                                $mutex = null
                                )
    {
        global $_conf;

        if ($options === null) {
            $options = array();
        }

        if (!isset($options['connecttimeout'])) {
            $options['connecttimeout'] = $_conf['fsockopen_time_limit'];
        }

        if (!isset($options['timeout'])) {
            $options['timeout'] = $_conf['fsockopen_time_limit'] * 2;
        }

        if (!isset($options['compress'])) {
            $options['compress'] = true;
        }

        if (!isset($options['useragent'])) {
            $options['useragent'] = "Monazilla/1.00 ({$_conf['p2name']}/{$_conf['p2version']})";
        }

        if (!isset($options['lastmodified']) && file_exists($save_path)) {
            $options['lastmodified'] = filemtime($save_path);
        } else {
            FileCtl::mkdir_for($save_path);
        }

        $this->_savePath = $save_path;
        $this->_savePerm = !empty($_conf['dl_savePerm']) ? $_conf['dl_savePerm'] : 0606;
        $this->_errorInfo = null;
        $this->_onSuccess = $on_success;
        $this->_onFailure = $on_failure;
        $this->_mutex = is_resource($mutex) ? $mutex : null;

        parent::__construct($url, HttpRequest::METH_GET, $options);
    }

    // }}}
    // {{{ onFinish()

    /**
     * ���N�G�X�g�I�����Ɏ����ŌĂяo�����R�[���o�b�N���\�b�h
     *
     * @param bool $success
     * @return void
     */
    public function onFinish($success)
    {
        if ($this->_mutex) {
            flock($this->_mutex, LOCK_EX);
            //$this->setErrorInfo('locked');
        }

        try {
            if ($success) {
                if (($code = $this->getResponseCode()) == 200) {
                    if ($this->_onSuccess) {
                        $this->_onSuccess->execute($this);
                    } else {
                        file_put_contents($this->_savePath, $this->getResponseBody(), LOCK_EX);
                        chmod($this->_savePath, $this->_savePerm);
                    }
                } else {
                    if ($this->_onFailure) {
                        $this->_onFailure->execute($this);
                    } elseif ($code != 304) {
                        $this->setErrorInfo(sprintf('HTTP %d %s', $code, $this->getResponseStatus()));
                    }
                }
            } else {
                $this->setErrorInfo('HTTP Connection Error!');
            }
        } catch (Exception $e) {
            $this->setErrorInfo(sprintf('%s (%d) %s', get_class($e), $e->getCode(), $e->getMessage()));
        }

        if ($this->_mutex) {
            flock($this->_mutex, LOCK_UN);
        }
    }

    // }}}
    // {{{ getSavePath()

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����ۂ̃p�X��Ԃ�
     *
     * @return string
     */
    public function getSavePath()
    {
        return $this->_savePath;
    }

    // }}}
    // {{{ getSavePermission()

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����ۂ̃p�[�~�b�V������Ԃ�
     *
     * @return int
     */
    public function getSavePermission()
    {
        return $this->_savePerm;
    }

    // }}}
    // {{{ getErrorInfo()

    /**
     * �G���[����Ԃ�
     *
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->_errorInfo;
    }

    // }}}
    // {{{ setErrorInfo()

    /**
     * �G���[����ݒ肷��
     *
     * @param string $err
     * @return void
     */
    public function setErrorInfo($err)
    {
        $this->_errorInfo = $err;
    }

    // }}}
    // {{{ hasError()

    /**
     * �G���[�̗L����Ԃ�
     *
     * @return bool
     */
    public function hasError()
    {
        return !is_null($this->_errorInfo);
    }

    // }}}
    // {{{ fetch()

    /**
     * �ÓI�Ăяo���p���\�b�h
     *
     * @param string $url
     * @param string $save_path
     * @param array $options
     * @param P2HttpCallback $on_success
     * @param P2HttpCallback $on_failure
     * @return P2HttpGet
     */
    static public function fetch($url,
                                 $save_path,
                                 array $options = null,
                                 P2HttpCallback $on_success = null,
                                 P2HttpCallback $on_failure = null
                                 )
    {
        $req = new P2HttpGet($url, $save_path, $options, $on_success, $on_failure);
        try {
            $req->send();
        } catch (HttpException $e) {
            $req->setErrorInfo(sprintf('%s (%d) %s', get_class($e), $e->getCode(), $e->getMessage()));
        }
        return $req;
    }

    // }}}
}

// }}}
// {{{ P2HttpRequestPool

/**
 * HttpRequestPool���g��������_�E�����[�h�N���X
 *
 * @static
 */
class P2HttpRequestPool
{
    // {{{ _send()

    /**
     * �v�[���ɃA�^�b�`����Ă��郊�N�G�X�g�𑗐M����
     *
     * @param HttpRequestPool $pool
     * @return void
     */
    static protected function _send(HttpRequestPool $pool)
    {
        global $_info_msg_ht;

        while (count($pool)) {
            try {
                $pool->send();
            } catch (HttpException $e) {
                // pass
            }

            foreach ($pool->getFinishedRequests() as $req) {
                $pool->detach($req);
                if ($req instanceof P2HttpGet && $req->hasError()) {
                    $_info_msg_ht .= sprintf('<div><em>%s</em>: %s</div>',
                                             htmlspecialchars($req->getUrl(), ENT_QUOTES),
                                             htmlspecialchars($req->getErrorInfo(), ENT_QUOTES)
                                             );
                }
            }
        }
    }

    // }}}
    // {{{ fetchSubjectTxt()

    /**
     * subject.txt���ꊇ�_�E�����[�h&�ۑ�����
     *
     * @param array|string $subjects
     * @param bool $force
     * @return void
     */
    static public function fetchSubjectTxt($subjects, $force = false)
    {
        global $_conf;

        // {{{ �_�E�����[�h�Ώۂ�ݒ�

        // ���C�ɔ���.idx�`���̃t�@�C�����p�[�X
        if (is_string($subjects)) {
            $lines = FileCtl::file_read_lines($subjects, FILE_IGNORE_NEW_LINES);
            if (!$lines) {
                return;
            }

            $subjects = array();

            foreach ($lines as $l) {
                $la = explode('<>', $l);
                if (count($la) < 12) {
                    continue;
                }

                $host = $la[10];
                $bbs = $la[11];
                if ($host === '' || $bbs === '') {
                    continue;
                }

                $id = $host . '<>' . $bbs;
                if (isset($subjects[$id])) {
                    continue;
                }

                $subjects[$id] = array($host, $bbs);
            }

        // [host, bbs] �̘A�z�z�������
        } elseif (is_array($subjects)) {
            $originals = $subjects;
            $subjects = array();

            foreach ($originals as $s) {
                if (!is_array($s) || !isset($s['host']) || !isset($s['bbs'])) {
                    continue;
                }

                $id = $s['host'] . '<>' . $s['bbs'];
                if (isset($subjects[$id])) {
                    continue;
                }

                $subjects[$id] = array($s['host'], $s['bbs']);
            }

        // ��L�ȊO
        } else {
            return;
        }

        // �ŏI�`�F�b�N
        if (!count($subjects)) {
            return;
        }

        // }}}
        // {{{ HttpRequestPool���Z�b�g�A�b�v

        // HttpRequestPool����т��̑��̕ϐ���������
        $pool = new HttpRequestPool;
        $mutex = tmpfile();
        $time = time() - $_conf['sb_dl_interval'];
        $eucjp2sjis = null;

        // �esubject.txt�ւ̃��N�G�X�g���v�[���ɃA�^�b�`
        foreach ($subjects as $subject) {
            list($host, $bbs) = $subject;

            $file = P2Util::datDirOfHost($host) . '/' . $bbs . '/subject.txt';
            if (!$force && file_exists($file) && filemtime($file) > $time) {
                continue;
            }

            $url = 'http://' . $host . '/' . $bbs . '/subject.txt';

            if (P2Util::isHostJbbsShitaraba($host) || P2Util::isHostBe2chNet($host)) {
                if ($eucjp2sjis === null) {
                    $eucjp2sjis = new P2HttpCallback_SaveEucjpAsSjis;
                }
                $pool->attach(new P2HttpGet($url, $file, null, $eucjp2sjis, null, $mutex));
            } else {
                $pool->attach(new P2HttpGet($url, $file, null, null, null, $mutex));
            }
        }

        // }}}

        // ���N�G�X�g�𑗐M
        if (count($pool)) {
            self::_send($pool);
            clearstatcache();
        }
        fclose($mutex);
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
