<?php
/*
replaceLinkToHTML(url, src) ���C���֐�
save(array)                 �f�[�^��ۑ�
load()                      �f�[�^��ǂݍ���ŕԂ�(�����I�Ɏ��s�����)
clear()                     �f�[�^���폜
*/

require_once __DIR__ . '/WikiPluginCtlBase.php';

class LinkPluginCtl extends WikiPluginCtlBase
{
    protected $filename = 'p2_plugin_link.txt';
    protected $data = array();

    public function clear()
    {
        global $_conf;

        $path = $_conf['pref_dir'] . '/' . $this->filename;

        return @unlink($path);
    }

    public function load()
    {
        global $_conf;

        $lines = array();
        $path = $_conf['pref_dir'] . '/' . $this->filename;
        if ($lines = @file($path)) {
            foreach ($lines as $l) {
                $lar = explode("\t", trim($l));
                if (strlen($lar[0]) == 0) {
                    continue;
                }
                $ar = array(
                    'match'   => $lar[0], // �Ώە�����
                    'replace' => $lar[1], // �u��������
                );

                $this->data[] = $ar;
            }
        }

        return $this->data;
    }

    /*
    $data[$i]['match']       Match
    $data[$i]['replace']     Replace
    $data[$i]['del']         �폜
    */
    public function save($data)
    {
        global $_conf;

        $path = $_conf['pref_dir'] . '/' . $this->filename;

        $newdata = '';

        foreach ($data as $na_info) {
            $a[0] = strtr(trim($na_info['match'], "\t\r\n"), "\t\r\n", "   ");
            $a[1] = strtr(trim($na_info['replace'], "\t\r\n"), "\t\r\n", "   ");
            if ($na_info['del'] || ($a[0] === '' || $a[1] === '')) {
                continue;
            }
            $newdata .= implode("\t", $a) . "\n";
        }

        return FileCtl::file_write_contents($path, $newdata);
    }

    public function replaceLinkToHTML($url, $str)
    {
        $this->setup();

        $src = false;
        foreach ($this->data as $v) {
            if (preg_match('{'.$v['match'].'}', $url)) {
                $src = @preg_replace ('{'.$v['match'].'}', $v['replace'], $url);
                if (strstr($v['replace'], '$ime_url')) {
                    $src = str_replace('$ime_url', P2Util::throughIme($url), $src);
                }
                if (strstr($v['replace'], '$str')) {
                    $src = str_replace('$str', $str, $src);
                }
                break;
            }
        }
        return $src;
    }

}
