<?php
/*
replaceLinkToHTML(url, src) ���C���֐�
save(array)                 �f�[�^��ۑ�
load()                      �f�[�^��ǂݍ���ŕԂ�(�����I�Ɏ��s�����)
clear()                     �f�[�^���폜

��{�\��
�f�[�^�̓o�^���@
�E$this->data���X�V���鎖�ɂ��o�^
�E�ꊇ�œo�^
�E��s�����̓o�^
�f�[�^�\��
word    ignorecase  regex     bbs lasttime    hits
*/

require_once __DIR__ . '/WikiPluginCtlBase.php';

class NgThreadCtl
{
    const DATE_FORMAT = 'Y/m/d G:i';

    protected $filename = 'p2_aborn_thread.txt';
    protected $data = array();
    public $hits = 0;

    /*
    �f�[�^���N���A
    */
    public function clear()
    {
        global $_conf;

        $path = $_conf['pref_dir'] . '/' . $this->filename;

        return @unlink($path);
    }

    /*
    �f�[�^��ǂݍ���ŕԂ�
     */
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
                    'word'       => $lar[0], // �Ώە�����
                    'ignorecase' => $lar[1], // �啶���������𖳎�
                    'regex'      => $lar[2], // ���K�\��
                    'bbs'        => $lar[3], // ����
                    'lasttime'   => $lar[4] == '--' ? '' : $lar[4], // �Ō��HIT��������
                    'hits'       => (int) $lar[5], // HIT��
                );

                $this->data[] = $ar;
            }
        }

        return $this->data;
    }

    /*
    �ۑ�
    �������w�肳��Ă�˂��̃f�[�^�ŕۑ�
    $this->data���Ȃ��˕ۑ����Ȃ�
    */
    public function save($data)
    {
        global $_conf;

        if ($data) {
            $new_data = true;
            $this->data = $data;
        } elseif (!$this->isLoaded) {
            return;
        } else {
            $new_data = false;
        }
        // HIT�������̂ݍX�V����
        if ($this->hits > 0 || $new_data) {
            $cont = '';

            foreach ($this->data as $v) {
                if ($v['del']) {
                    continue;
                }

                // �K�v�Ȃ炱���ŌÂ��f�[�^�̓X�L�b�v�i�폜�j����
                if (!empty($v['lasttime']) && $_conf['ngaborn_daylimit']) {
                    if (strtotime($v['lasttime']) < time() - 60 * 60 * 24 * $_conf['ngaborn_daylimit']) {
                        continue;
                    }
                }

                $a['word'] = strtr(trim($v['word'], "\t\r\n"), "\t\r\n", "   ");
                $a['ignorecase'] = $v['ignorecase'];
                $a['regex'] = $v['regex'];
                $a['bbs'] = strtr(trim($v['bbs'], "\t\r\n"), "\t\r\n", "   ");
                $a['lasttime'] = $v['lasttime'];
                $a['hits'] = $v['hits'];

                // lasttime���ݒ肳��Ă��Ȃ������猻�ݎ��Ԃ�ݒ�(�{���Ȃ�o�^���ɂ���ׂ�)
                if (empty($v['lasttime'])) {
                    $v['lasttime'] = date(self::DATE_FORMAT);
                }

                $cont .= implode("\t", $v) . "\n";
            }

            return FileCtl::file_write_contents($_conf['pref_dir'].'/'.$this->filename);
        }
    }

    /*
    ���ځ[��`�F�b�N
    ���ځ[��Ώہ�true
    */
    public function check($aThread)
    {
        $this->setup();

        if ($aThreadList->spmode != "taborn" && isset($this->data) && is_array($this->data)) {
            foreach ($this->data as $k => $v) {
                // �`�F�b�N
                if (!in_array($aThread->bbs, explode(',', $v['bbs']))) {
                    continue;
                }
                // ���K�\��
                if (!empty($v['regex'])) {
                    if ($v['ignorecase']) {
                        $match = '{' . $v['word'] . '}i';
                    } else {
                        $match = '{' . $v['word'] . '}';
                    }
                    if (preg_match('{' . $v['word'] . '}i', $aThread->ttitle_hc)) {
                        $this->update($k);
                        return true;
                    }
                // �啶���������𖳎�
                } elseif (!empty($v['ignorecase'])) {
                    if(stristr($aThread->ttitle_hc,$v['word'])){
                        $this->update($k);
                        return true;
                    }
                // �P���ɕ����񂪊܂܂�邩�ǂ������`�F�b�N
                } else {
                    if (strstr($aThread->ttitle_hc,$v['word'])) {
                        $this->update($k);
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /*
    ���̃f�[�^�̂��ځ[������X�V
    */
    public function update($k)
    {
        $this->hits++;
        if (isset($this->data[$k])) {
            $this->data[$k]['lasttime'] = date(self::DATE_FORMAT); // HIT���Ԃ��X�V
            if (empty($v['hits'])) {
                $this->data[$k]['hits'] = 1; // ��HIT
            } else {
                $this->data[$k]['hits']++; // HIT�񐔂��X�V
            }
        }
    }

}
