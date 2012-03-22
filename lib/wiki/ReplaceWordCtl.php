<?php
/*
ReplaceImageURL(url)        ���C���֐�
save(array)                 �f�[�^��ۑ�
load()                      �f�[�^��ǂݍ���ŕԂ�(�����I�Ɏ��s�����)
clear()                     �f�[�^���폜
*/

class ReplaceWordCtl
{
    protected $isLoaded = false;
    protected $data = array();

    public function setup()
    {
        if (!$this->isLoaded) {
            $this->load();
            $this->isLoaded = true;
        }
    }

    // �t�@�C������Ԃ�
    public function filename($cont)
    {
        return 'p2_replace_' . $cont . '.txt';
    }

    // �t�@�C�����폜
    public function clear($cont)
    {
        global $_conf;

        $path = $_conf['pref_dir'] . '/' . $this->filename($cont);

        return @unlink($path);
    }

    // �S�Ẵf�[�^��ǂݍ���
    public function load()
    {
        $this->loadFile('name');
        $this->loadFile('mail');
        $this->loadFile('date');
        $this->loadFile('msg');

        return $this->data;
    }

    // �t�@�C����ǂݍ���
    public function loadFile($cont)
    {
        global $_conf;

        $lines = array();
        $path = $_conf['pref_dir'].'/'.$this->filename($cont);
        if ($lines = @file($path)) {
            foreach ($lines as $l) {
                if (substr($l, 0, 1) === ';' || substr($l, 0, 1) === "'" ||
                    substr($l, 0, 1) === '#' || substr($l, 0, 2) === '//') {
                    //"#" ";" "'" "//"����n�܂�s�̓R�����g
                    continue;
                }
                $lar = explode("\t", trim($l));
                // Match�͕K�v����Replace�͋�ł��ǂ�
                if (strlen($lar[0]) == 0)  continue;

                $ar = array(
                    'match'   => $lar[0], // �Ώە�����
                    'replace' => $lar[1], // �u��������
                    'mode'    => $lar[2]  // ���[�h(0:����, 1:PC, 2:�g��)
                );

                $this->data[$cont][] = $ar;
            }
        }
        return $this->data[$cont];
    }

    // �t�@�C����ۑ�
    public function save($data)
    {
        global $_conf;

        $path = $_conf['pref_dir'] . '/' . $this->filename($cont);

        $newdata = '';

        foreach ($data as $na_info) {
            $a[0] = strtr(trim($na_info['match']  , "\t\r\n"), "\t\r\n", "   ");
            $a[1] = strtr(trim($na_info['replace'], "\t\r\n"), "\t\r\n", "   ");
            $a[2] = strtr(trim($na_info['mode']   , "\t\r\n"), "\t\r\n", "   ");
            if ($na_info['del'] || ($a[0] === '' || $a[1] === '')) {
                continue;
            }
            $newdata .= implode("\t", $a) . "\n";
        }
        return FileCtl::file_write_contents($path, $newdata);
    }

    /*
    $cont:�Ώ�
          name:���O
          mail:���[��
          date:���t���̑�
          msg:���b�Z�[�W
    $aThread
          Thread�N���X�I�u�W�F�N�g���w��(showthread.inc.php�Ȃ�$this->thread)
    $ares:���X�̓��e
    $i:���X�ԍ�
    */
    public function replace($cont, $aThread, $ares, $i)
    {
        global $_conf;

        $this->setup();

        $resar   = $aThread->explodeDatLine($ares);
        $name    = $resar[0];
        $mail    = $resar[1];
        $date_id = $resar[2];
        $msg     = $resar[3];
        
        switch ($cont) {
            case 'name':
                $word = $name;
                break;
            case 'mail':
                $word = $mail;
                break;
            case 'date':
                $word = $date_id;
                break;
            case 'msg':
                $word = $msg;
                break;
            // �G���[
            default:
                // ���̂܂ܕԂ�
                return $word;
        }
        // �u���ݒ肪�����ꍇ�͂��̂܂ܕԂ�
        if (!isset($this->data[$cont])) {
            return $word;
        }

        preg_match('|ID: ?([0-9A-Za-z/.+]{8,11})|',$date_id, $matches);
        $id = $matches[1];
        foreach ($this->data[$cont] as $v) {
            // �g�у��[�h�Ńf�[�^��PC�p�Ȃ��΂�
            if ($_conf['ktai']  && $v['mode'] == 1) continue;
            // PC���[�h�Ńf�[�^���g�їp�Ȃ��΂�
            if (!$_conf['ktai'] && $v['mode'] == 2) continue;

            /* Match�p�̕ϐ��W�J(�p�r���v�������΂Ȃ��̂ŃR�����g�A�E�g)
            $v['match'] = str_replace ('$i',         $i, $v['match']);
            $v['match'] = str_replace ('$ttitle',    $aThread->ttitle, $v['match']);
            $v['match'] = str_replace ('$ttitle_hd', $aThread->ttitle_hd, $v['match']);
            $v['match'] = str_replace ('$host',      $aThread->host, $v['match']);
            $v['match'] = str_replace ('$bbs',       $aThread->bbs,  $v['match']);
            $v['match'] = str_replace ('$key',       $aThread->key,  $v['match']);
            $v['match'] = str_replace ('$name',      $name,  $v['match']);
            $v['match'] = str_replace ('$mail',      $mail,  $v['match']);
            $v['match'] = str_replace ('$date_id',   $date_id,  $v['match']);
            $v['match'] = str_replace ('$msg',       $msg,  $v['match']);
            $v['match'] = str_replace ('$id_base64', base64_encode($id),  $v['match']);
            $v['match'] = str_replace ('$id',        $id,  $v['match']);
            */
            /*
            ���ꎩ�̂ɐ��K�\���������Ă�����ǂ����悤�B
            �����I�Ɏg���̂�$i, $host, $bbs, $key, $date_id���炢��������Ȃ����낤���ǁB
            */
            $v['replace'] = str_replace ('$ttitle_hd', $aThread->ttitle_hd, $v['replace']);
            $v['replace'] = str_replace ('$ttitle',    $aThread->ttitle, $v['replace']);
            $v['replace'] = str_replace ('$host',      $aThread->host, $v['replace']);
            $v['replace'] = str_replace ('$bbs',       $aThread->bbs,  $v['replace']);
            $v['replace'] = str_replace ('$key',       $aThread->key,  $v['replace']);
            // $v['replace'] = str_replace ('$name',      $name,  $v['replace']);
            // $v['replace'] = str_replace ('$mail',      $mail,  $v['replace']);
            // $v['replace'] = str_replace ('$date_id',   $date_id,  $v['replace']);
            // $v['replace'] = str_replace ('$msg',       $msg,  $v['replace']);
            $v['replace'] = str_replace ('$id_base64', base64_encode($id),  $v['replace']);
            $v['replace'] = str_replace ('$id',        $id,  $v['replace']);
            $v['replace'] = str_replace ('$i',         $i, $v['replace']);

            $word = @preg_replace ('{'.$v['match'].'}', $v['replace'], $word);
        }

        return $word;
    }
}
