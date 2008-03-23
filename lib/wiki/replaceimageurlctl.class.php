<?php
/*
ReplaceImageURL(url)        ���C���֐�
save(array)                 �f�[�^��ۑ�
load()                      �f�[�^��ǂݍ���ŕԂ�(�����I�Ɏ��s�����)
clear()                     �f�[�^���폜
autoLoad()                  load����Ă��Ȃ���Ύ��s
*/

require_once P2_LIB_DIR . '/filectl.class.php';

class ReplaceImageURLCtl
{
    var $filename = "p2_replace_imageurl.txt";
    var $data = array();
    var $isLoaded = false;

    function clear() {
        global $_conf;
        $path = $_conf['pref_dir'] . '/' . $this->filename;

        return @unlink($path);
    }

    function autoLoad() {
        if (!$this->isLoaded) $this->load();
    }

    function load() {
        global $_conf;

        $lines = array();
        $path = $_conf['pref_dir'].'/'.$this->filename;
        if ($lines = @file($path)) {
            foreach ($lines as $l) {
                $lar = explode("\t", trim($l));
                if (strlen($lar[0]) == 0 || count($lar) < 2) {
                    continue;
                }
                $ar = array(
                    'match'   => $lar[0], // �Ώە�����
                    'replace' => $lar[1], // �u��������
                    'referer' => $lar[2], // �u��������
                    'extract' => $lar[3], // �u��������
                    'source'  => $lar[4], // �u��������
                );

                $this->data[] = $ar;
            }
        }
        $this->isLoaded = true;
        return $this->data;
    }

    /**
     * saveReplaceImageURL
     * $data[$i]['match']       Match
     * $data[$i]['replace']     Replace
     * $data[$i]['del']         �폜
     */
    function save($data)
    {
        global $_conf;

        $path = $_conf['pref_dir'] . '/' . $this->filename;

        $newdata = '';

        foreach ($data as $na_info) {
            $a[0] = strtr(trim($na_info['match']  , "\t\r\n"), "\t\r\n", "   ");
            $a[1] = strtr(trim($na_info['replace'], "\t\r\n"), "\t\r\n", "   ");
            $a[2] = strtr(trim($na_info['referer'], "\t\r\n"), "\t\r\n", "   ");
            $a[3] = strtr(trim($na_info['extract'], "\t\r\n"), "\t\r\n", "   ");
            $a[4] = strtr(trim($na_info['source'] , "\t\r\n"), "\t\r\n", "   ");
            if ($na_info['del'] || ($a[0] === '' || $a[1] === '')) {
                continue;
            }
            $newdata .= implode("\t", $a) . "\n";
        }
        return FileCtl::file_write_contents($path, $newdata);
    }

    /**
     * replaceImageURL
     * �����N�v���O�C�������s
     * return array
     *      $ret[$i]['url']     $i�Ԗڂ�URL
     *      $ret[$i]['referer'] $i�Ԗڂ̃��t�@��
     */
    function replaceImageURL($url) {
        // http://janestyle.s11.xrea.com/help/first/ImageViewURLReplace.html
        $this->autoLoad();
        $src = FALSE;

        foreach ($this->data as $v) {
            if (preg_match('{^'.$v['match'].'$}', $url)) {
                $v['replace'] = str_replace('$&', '$0', $v['replace']);
                $v['referer'] = str_replace('$&', '$0', $v['referer']);
                // ���u��(Match��Replace, Match��Referer)
                $replaced = @preg_replace ('{'.$v['match'].'}', $v['replace'], $url);
                $referer =  @preg_replace ('{'.$v['match'].'}', $v['referer'], $url);
                // $EXTRACT������ꍇ
                // ��:$COOKIE, $COOKIE={URL}, $EXTRACT={URL}�ɂ͖��Ή�
                // $EXTRACT={URL}�̎����͗e��
                if (strstr($v['extract'], '$EXTRACT')){
                    $v['source'] =  @preg_replace ('{'.$v['match'].'}', $v['source'], $url);
                    preg_match_all('{' . $v['source'] . '}', P2Util::getWebPage($url, $errmsg), $extracted);
                    foreach ($extracted[1] as $i => $extract) {
                        $return[$i]['url']     = str_replace('$EXTRACT', $extract, $replaced);
                        $return[$i]['referer'] = str_replace('$EXTRACT', $extract, $referer);
                    }
                } else {
                    $return[0]['url']     = $replaced;
                    $return[0]['referer'] = $referer;
                }
                break;
            }
        }
        /* plugin_imageCache2�ŏ��������邽�߃R�����g�A�E�g
        // �q�b�g���Ȃ������ꍇ
        if (!$return[0]) {
            // �摜���ۂ�URL�̏ꍇ
            if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url)) {
                $return[0]['url']     = $url;
                $return[0]['referer'] = '';
            }
        }
        */
        return $return;
    }

}
