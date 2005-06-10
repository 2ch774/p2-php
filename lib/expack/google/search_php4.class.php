<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once 'PEAR.php';
require_once 'SOAP/Client.php';
require_once dirname(__FILE__) . '/search.class.php';

class GoogleSearch_PHP4 extends GoogleSearch_Common
{
    // {{{ properties

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @return void
     * @access public
     */
    function GoogleSearch_PHP4()
    {
    }

    // }}}
    // {{{ init()

    /**
     * SOAP�N���C�A���g�̃C���X�^���X�𐶐�����
     *
     * @param string $wsdl  Google Search WSDL�t�@�C���̃p�X
     * @param string $key   Google Web APIs �̃��C�Z���X�L�[
     * @return boolean
     * @access public
     */
    function init($wsdl, $key)
    {
        if (!file_exists($wsdl)) {
            return PEAR::raiseError('GoogleSearch.wsdl not found.');
        }

        $this->setConf($wsdl, $key);

        $soapClient = &new SOAP_Client($this->wsdl, TRUE);

        if (PEAR::isError($soapClient)) {
            return $soapClient;
        }

        $this->soapClient = $soapClient;

        return TRUE;
    }

    // }}}
    // {{{ doSearch()

    /**
     * ���������s����
     *
     * @param string  $q  �����L�[���[�h
     * @param integer $start  �������ʂ��擾����ʒu
     * @param integer $maxResults  �������ʂ��擾����ő吔
     * @return object ��������
     * @access public
     */
    function &doSearch($q, $maxResults = 10, $start = 0)
    {
        $params = $this->prepareParams($q, $maxResults, $start);
        $result = &$this->soapClient->call('doGoogleSearch', $params);
        return $result;
    }

    // }}}
}

?>
