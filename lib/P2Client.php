<?php
require_once 'HTTP/Client.php';
require_once dirname(__FILE__) . '/P2DOM.php';
require_once dirname(__FILE__) . '/P2KeyValueStore/Serializing.php';

// {{{ P2Client

/**
 * p2.2ch.net �N���C�A���g
 *
 * �v���o�C�_�K�����ɏ������ނ��߂ɐ݌v�����B
 * �����^�|�������dat�擾�����̖���dat�����X���b�h��
 * ��dat���擾�ł���悤�ɂȂ����Ȃ�A���Ή�����B
 */
class P2Client
{
    // {{{ constants

    /**
     * Cookie��ۑ�����SQLite3�f�[�^�x�[�X�̃t�@�C����
     */
    const COOKIE_STORE_NAME = 'p2_2ch_net_cookie.sq3';

    /**
     * ����P2��URI�Ɗe�G���g���|�C���g
     */
    const P2_ROOT_URI = 'http://p2.2ch.net/p2/';
    const SCRIPT_NAME_READ = 'read.php';
    const SCRIPT_NAME_POST = 'post.php';

    /**
     * User-Agent
     */
    const HTTP_USER_AGENT = 'Monazilla/1.0 (rep2-expack-p2client)';

    /**
     * �t�H�[���̃p�����[�^��
     */
    const FIELD_NAME_LOGIN_ID   = 'form_login_id';
    const FIELD_NAME_LOGIN_PASS = 'form_login_pass';
    const FIELD_NAME_POPUP      = 'popup';
    const FIELD_NAME_FROM       = 'FROM';
    const FIELD_NAME_MAIL       = 'mail';
    const FIELD_NAME_MESSAGE    = 'MESSAGE';
    const FIELD_NAME_BERES      = 'submit_beres';

    /**
     * �������ݐ��۔���̂��߂̐��K�\��
     */
    const REGEX_SUCCESS = '{<title>.*(?:����(?:��|��)�݂܂���|�������ݏI�� - SubAll BBS).*</title>}is';
    const REGEX_COOKIE  = '{<!-- 2ch_X:cookie -->|<title>�� �������݊m�F ��</title>|>�������݊m�F�B<}';

    // }}}
    // {{{ properties

    /**
     * p2.2ch.net/�����^�| ���O�C��ID (���[���A�h���X)
     *
     * @var string
     */
    private $_loginId;

    /**
     * p2.2ch.net/�����^�| ���O�C���p�X���[�h
     *
     * @var string
     */
    private $_loginPass;

    /**
     * Cookie��ۑ�����Key-Value Store�I�u�W�F�N�g
     *
     * @var P2KeyValueStore_Serializing
     */
    private $_cookieStore;

    /**
     * Cookie���Ǘ�����I�u�W�F�N�g
     *
     * @var HTTP_Client_CookieManager
     */
    private $_cookieManager;

    /**
     * HTTP�N���C�A���g�I�u�W�F�N�g
     *
     * @var HTTP_Client
     */
    private $_httpClient;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @param string $loginId
     * @param string $loginPass
     * @param string $cookieSaveDir
     * @throws P2Exception
     */
    public function __construct($loginId, $loginPass, $cookieSaveDir)
    {
        try {
            $cookieSavePath = $cookieSaveDir . DIRECTORY_SEPARATOR . self::COOKIE_STORE_NAME;
            $cookieStore = P2KeyValueStore::getStore($cookieSavePath, 'Serializing');
        } catch (Exception $e) {
            throw new P2Exception(get_class($e) . ': ' . $e->getMessage());
        }

        if ($cookieManager = $cookieStore->get($loginId)) {
            if (!$cookieManager instanceof HTTP_Client_CookieManager) {
                throw new Exception('Cannot restore the cookie manager.');
            }
        } else {
            $cookieManager = new HTTP_Client_CookieManager;
        }

        $this->_loginId = $loginId;
        $this->_loginPass = $loginPass;
        $this->_cookieStore = $cookieStore;
        $this->_cookieManager = $cookieManager;

        $defaultHeaders = array(
            'User-Agent' => self::HTTP_USER_AGENT,
        );
        $this->_httpClient = new HTTP_Client(null, $defaultHeaders, $cookieManager);
    }

    // }}}
    // {{{ destructor

    /**
     * �f�[�^�x�[�X��Cookie��ۑ�����
     *
     * @param void
     */
    public function __destruct()
    {
        $this->_cookieStore->set($this->_loginId, $this->_cookieManager);
    }

    // }}}
    // {{{ login()

    /**
     * ����p2�Ƀ��O�C������
     *
     * @param string $uri
     * @param array $data
     * @param P2DOM $dom
     * @param DOMElement $form
     * @return array HTTP���X�|���X
     * @throws P2Exception
     */
    public function login($uri = null, array $data = array(),
                          P2DOM $dom = null, DOMElement $form = null)
    {
        if ($uri === null) {
            $uri = self::P2_ROOT_URI;
        }

        if ($dom === null) {
            $response = $this->httpGet($uri);
            $dom = new P2DOM($response['body']);
        }

        if ($form === null) {
            $form = $this->getLoginForm($dom);
            if ($form === null) {
                throw new P2Exception('Login form not found.');
            }
        }

        $postData = array();
        foreach ($data as $name => $value) {
            $postData[$name] = rawurlencode($value);
        }
        $postData = $this->getFormValues($dom, $form, $postData);
        $postData[self::FIELD_NAME_LOGIN_ID] = rawurlencode($this->_loginId);
        $postData[self::FIELD_NAME_LOGIN_PASS] = rawurlencode($this->_loginPass);

        return $this->httpPost($uri, $postData, true);
    }

    // }}}
    // {{{ readThread()

    /**
     * �X���b�h��ǂ�
     *
     * @param string $host
     * @param string $bbs
     * @param string $key
     * @param string|integer $ls
     * @param mixed &$response
     * @return string HTTP���X�|���X�{�f�B
     * @throws P2Exception
     */
    public function readThread($host, $bbs, $key, $ls = 1, &$response = null)
    {
        $getData = array(
            'host'  => (string)$host,
            'bbs'   => (string)$bbs,
            'key'   => (string)$key,
            'ls'    => (string)$ls,
        );

        $uri = self::P2_ROOT_URI . self::SCRIPT_NAME_READ;
        $response = $this->httpGet($uri, $getData);
        $dom = new P2DOM($response['body']);

        if ($form = $this->getLoginForm($dom)) {
            $response = $this->login($uri, $getData, $dom, $form);
            $dom = new P2DOM($response['body']);
            if ($this->getLoginForm($dom)) {
                throw new P2Exception('Login failed.');
            }
        }

        return $response['body'];
    }

    // }}}
    // {{{ post()

    /**
     * �X���b�h�ɏ�������
     *
     * csrfId���擾���A������p2�̊��ǂ��ŐV�̏�Ԃɂ��邽�߁A
     * �܂� read.php ��@���B
     * �ʐM�ʂ�ߖ�ł���悤�� ls=l1n �Ƃ��Ă���B
     * popup=1 �͏������݌�̃y�[�W�Ƀ��_�C���N�g�����Ȃ����߁B
     *
     * @param string $host
     * @param string $bbs
     * @param string $key
     * @param string $from
     * @param string $mail
     * @param string $message
     * @param bool $beRes
     * @param mixed &$response
     * @return bool
     * @throws P2Exception
     */
    public function post($host, $bbs, $key, $from, $mail, $message,
                         $beRes = false, &$response = null)
    {
        $dom = new P2DOM($this->readThread($host, $bbs, $key, 'l1n', $response));
        if ($form = $this->getPostForm($dom)) {
            $uri = self::P2_ROOT_URI . self::SCRIPT_NAME_POST;

            $postData = $this->getFormValues($dom, $form);
            $postData[self::FIELD_NAME_POPUP]   = '1';
            $postData[self::FIELD_NAME_FROM]    = rawurlencode($from);
            $postData[self::FIELD_NAME_MAIL]    = rawurlencode($mail);
            $postData[self::FIELD_NAME_MESSAGE] = rawurlencode($message);
            if ($beRes) {
                $postData[self::FIELD_NAME_BERES] = '1';
            } elseif (array_key_exists(self::FIELD_NAME_BERES, $postData)) {
                unset($postData[self::FIELD_NAME_BERES]);
            }

            $response = $this->httpPost($uri, $postData, true);

            if (preg_match(self::REGEX_COOKIE, $response['body'])) {
                $dom = new P2DOM($response['body']);
                $expression = './/form[contains(@action, "' . self::SCRIPT_NAME_POST . '")]';
                $result = $dom->query($expression);
                if ($result instanceof DOMNodeList && $result->length > 0) {
                    $postData = $this->getFormValues($dom, $result->item(0));
                    $response = $this->httpPost($uri, $postData, true);
                }
            }

            return (bool)preg_match(self::REGEX_SUCCESS, $response['body']);
        } else {
            throw new P2Exception('Post form not found.');
        }
    }

    // }}}
    // {{{ httpGet()

    /**
     * HTTP_Client::get() �̃��b�p�[���\�b�h
     *
     * @param string $uri
     * @param mixed $data
     * @param bool $preEncoded
     * @param array $headers
     * @return array HTTP���X�|���X
     * @throws P2Exception
     */
    protected function httpGet($uri, $data = null, $preEncoded = false,
                               $headers = array())
    {
        $code = $this->_httpClient->get($uri, $data, $preEncoded, $headers);
        P2Exception::pearErrorToP2Exception($code);
        if ($code < 200 || $code >= 300) {
            throw new P2Exception('HTTP Error: '. $code);
        }
        return $this->_httpClient->currentResponse();
    }

    // }}}
    // {{{ httpPost()

    /**
     * HTTP_Client::post() �̃��b�p�[���\�b�h
     *
     * @param string $uri
     * @param mixed $data
     * @param bool $preEncoded
     * @param array $files
     * @param array $headers
     * @return array HTTP���X�|���X
     * @throws P2Exception
     */
    protected function httpPost($uri, $data, $preEncoded = false,
                                $files = array(), $headers = array())
    {
        $code = $this->_httpClient->post($uri, $data, $preEncoded, $files, $headers);
        P2Exception::pearErrorToP2Exception($code);
        if ($code < 200 || $code >= 300) {
            throw new P2Exception('HTTP Error: '. $code);
        }
        return $this->_httpClient->currentResponse();
    }

    // }}}
    // {{{ getLoginForm()

    /**
     * ���O�C���t�H�[���𒊏o����
     *
     * @paramP2DOM $dom
     * @return DOMElement|null
     */
    protected function getLoginForm(P2DOM $dom)
    {
        $result = $dom->query('.//form[@action and @id="login"]');
        if ($result instanceof DOMNodeList && $result->length > 0) {
            return $result->item(0);
        }
        return null;
    }

    // }}}
    // {{{ getPostForm()

    /**
     * read.php/post_form.php �̏o�͂��珑�����݃t�H�[���𒊏o����
     *
     * @paramP2DOM $dom
     * @return DOMElement|null
     */
    protected function getPostForm(P2DOM $dom)
    {
        $result = $dom->query('.//form[@action and @id="resform"]');
        if ($result instanceof DOMNodeList && $result->length > 0) {
            return $result->item(0);
        }
        return null;
    }

    // }}}
    // {{{ getFormValues()

    /**
     * �t�H�[������input�v�f�𒊏o���A�A�z�z��𐶐�����
     *
     * select�v�f��textarea�v�f�͖�������B
     * �܂��A<input type="checkbox" name="foo[]" value="bar"> �̂悤��
     * name�����Ŕz����w�����Ă�����̂͐����������Ȃ��B
     * (���̃N���X���̂������������v�f�������K�v�̂���ꍇ���l�����Ă��Ȃ�)
     *
     * @param P2DOM $dom
     * @param DOMElement $form
     * @param array $data
     * @return array
     */
    protected function getFormValues(P2DOM $dom, DOMElement $form,
                                     array $data = array())
    {
        $fields = $dom->query('.//input[@name and @value]', $form);
        foreach ($fields as $field) {
            $name = $field->getAttribute('name');
            $value = $field->getAttribute('value');
            $value = rawurlencode(mb_convert_encoding($value, 'SJIS-win', 'UTF-8'));
            $data[$name] = $value;
        }

        return $data;
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
