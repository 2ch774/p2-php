<?php

class P2UtilWiki {

    /**
     * +Wiki:�v���t�B�[��ID����BEID���v�Z����
     *
     * @return integer|0 ����������BEID��Ԃ��B���s������0��Ԃ��B
     */
    function calcBeId($prof_id) {
        $found = false;
        for ($y = 2; $y <= 9 && !$found; $y++) {
            for ($x = 2; $x <= 9 && !$found; $x++) {
                $id = (($prof_id - $x*10.0 - $y)/100.0 + $x - $y - 5.0)/(3.0 * $x * $y);
                if ($id == floor($id)) $found = true;
            }
        }
        return ($found ? $id : 0);
    }

    /**
     * Wiki:����URL�ɃA�N�Z�X�ł��邩�m�F����
     */
    function isURLAccessible($url, $timeout = 7)
    {
        $code = P2UtilWiki::getResponseCode($url);
        return ($code == 200 || $code == 206) ? true : false;
    }

    /**
     * URL���C���s�^�Ȃ�true��Ԃ�
     */
    function isUrlImepita($url)
    {
        return preg_match('{^http://imepita\.jp/}', $url);
    }

    function getResponseCode($url) {
        require_once 'HTTP/Client.php';
        $client = &new HTTP_Client;
        $client->setRequestParameter('timeout', $timeout);
        $client->setDefaultHeader('User-Agent', 'Monazilla/1.00');
        if (!empty($_conf['proxy_use'])) {
            $client->setRequestParameter('proxy_host', $_conf['proxy_host']);
            $client->setRequestParameter('proxy_port', $_conf['proxy_port']);
        }
        return $client->head($url);
    }

    /**
     * Wiki:Last-Modified���`�F�b�N���ăL���b�V������
     * time:�`�F�b�N���Ȃ�����(unixtime)
     */
    function cacheDownload($url, $path, $time = 0)
    {
        global $_conf;
        $filetime = @filemtime($path);
        
        // �L���b�V���L�����ԂȂ�`�F�b�N���Ȃ�
        if ($filetime > 0 && $filetime > time() - $time) return;
        
        if (!class_exists('HTTP_Request', false)) {
            require 'HTTP/Request.php';
        }
        $req = & new HTTP_Request($url, array('timeout' => $_conf['fsockopen_time_limit']));
        $now = time();
        $req->sendRequest();
        $unixtime = strtotime($req->getResponseHeader('Last-Modified'));

        // ����Ă���Ύ擾
        if($unixtime !== $filetime){ 
            P2Util::fileDownload($url, $path);
            // �ŏI�X�V������ݒ�
            // touch($path, $unixtime);
        } else {
            // touch($path, $now);
        }
        touch($path, $now);
    }

}
