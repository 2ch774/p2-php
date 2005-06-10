<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 - StrCtl -- �����񑀍�N���X
*/

//define('P2_MBREGEX_AVAILABLE_TEST', 0);

class StrCtl{

    /**
     * �t�H�[�����瑗���Ă������[�h���}�b�`�֐��ɓK��������
     *
     * @return string $word_fm �K���p�^�[���BSJIS�ŕԂ��B
     */
    function wordForMatch($word, $method = '')
    {
        $word_fm = $word;

        // �u���̂܂܁v�łȂ���΁A�S�p�󔒂𔼊p�󔒂ɋ���
        if ($method != 'just') {
            $word_fm = mb_convert_kana($word_fm, 's');
        }

        // ���K�\����SJIS��2�o�C�g�����������͉̂����Ɩ�肪�����̂ŁAUTF-8�ɂ��ď���
        $word_fm = mb_convert_encoding($word_fm, 'UTF-8', 'SJIS-win');

        $word_fm = trim($word_fm);
        $word_fm = htmlspecialchars($word_fm, ENT_NOQUOTES);

        // �u���K�\���v�łȂ���΁A���K�\���̓��ꕶ�����G�X�P�[�v
        if (in_array($method, array('and', 'or', 'just'))) {
            if (P2_MBREGEX_AVAILABLE == 1) {
                $word_fm = preg_quote($word_fm);
            } else {
                $word_fm = preg_quote($word_fm, '/');
            }

        // �u���K�\���v�Ȃ�
        } else {
            if (P2_MBREGEX_AVAILABLE == 0) {
                $word_fm = preg_replace('/\\//u', '\\/', $word_fm);
            }
        }

        $word_fm = mb_convert_encoding($word_fm, 'SJIS-win', 'UTF-8');

        return $word_fm;
    }

    /**
     * �p�^�[�������}�b�`���O�p�ɍœK�����A�X�^�e�B�b�N�ϐ��ɃL���b�V������
     */
    function patternForMultiMatch($pattern)
    {
        static $patterns = array();

        $key = $pattern;
        if (isset($patterns[$key])) {
            return $patterns[$key];
        }

        if (P2_MBREGEX_AVAILABLE == 0) {
            $pattern = mb_convert_encoding($pattern, 'UTF-8', 'SJIS-win');
            $encoding = 'UTF-8';
        } else {
            $encoding = 'SJIS-win';
        }

        // �啶�������ׂď������ɂ���
        // ������ő啶��/�������̋�ʂȂ��}�b�`����킯�ł͂Ȃ�...
        $pattern = mb_strtolower($pattern, $encoding);

        // �S�p/���p���i������x�j��ʂȂ��}�b�`
        $_patterns = array();
        $_patterns[0] = $pattern;
        $_patterns[1] = mb_convert_kana($pattern, 'rnKV', $encoding); // �����ƃA���t�@�x�b�g�͔��p�A�J�^�J�i�͑S�p
        $_patterns[2] = mb_convert_kana($pattern, 'rnk',  $encoding); // �S�Ĕ��p
        $_patterns[3] = mb_convert_kana($pattern, 'RNKV', $encoding); // �S�đS�p
        //$_patterns[4] = mb_convert_kana($_patterns[2], 'rnKV', $encoding); // �S�p�J�^�J�i+���_���܂Ƃ߂�(1)
        //$_patterns[5] = mb_convert_kana($_patterns[2], 'RNKV', $encoding); // �S�p�J�^�J�i+���_���܂Ƃ߂�(2)
        $pattern = implode('|', array_unique($_patterns));

        // HTML�v�f�Ƀ}�b�`�����Ȃ����߂̔ے��ǂ݃p�^�[����t����
        // ��ǂ݃p�^�[���̓}�b�`���ʂɊ܂܂�Ȃ��̂ŁA$0��$1�ɂ͓��������񂪃L���v�`�������
        $pattern = '(' . $pattern . ')(?![^<]*>)';

        // �O����X���b�V��(���K�\���f���~�^)�ň͂݁Ai(PCRE_CASELESS)�C���q��u(PCRE_UTF8)�C���q��t����
        if (P2_MBREGEX_AVAILABLE == 0) {
            $pattern = '/' . $pattern . '/iu';
        }

        $patterns[$key] = $pattern;
        return $pattern;
    }

    /**
     * �}���`�o�C�g�Ή��Ő��K�\���}�b�`����
     *
     * @param string $pattern �}�b�`������BP2_MBREGEX_AVAILABLE��1�Ȃ�SJIS�A0�Ȃ�UTF-8�œ����Ă���B
     * @param string $target �����Ώە�����BSJIS�œ����Ă���B
     *
     * @return boolean
     */
    function filterMatch($pattern, &$target)
    {
        $pattern = StrCtl::patternForMultiMatch($pattern);

        if (P2_MBREGEX_AVAILABLE ==1) {
            $result = @mb_eregi($pattern, $target);
        } else {
            $utf8txt = mb_convert_encoding($target, 'UTF-8', 'SJIS-win');
            $result = @preg_match($pattern, $utf8txt);
        }

        return (boolean)$result;
    }

    /**
     * �}���`�o�C�g�Ή��Ń}�[�L���O����
     *
     * @param string $pattern �}�b�`������BP2_MBREGEX_AVAILABLE��1�Ȃ�SJIS�A0�Ȃ�UTF-8�œ����Ă���B
     * @param string $target �u���Ώە�����BSJIS�œ����Ă���B
     *
     * @retun string $result �u���ςݕ�����
     */
    function filterMarking($pattern, &$target, $marker = '<b class="filtering">\\1</b>')
    {
        $pattern = StrCtl::patternForMultiMatch($pattern);

        if (P2_MBREGEX_AVAILABLE ==1) {
            $result = @mb_eregi_replace($pattern, $marker, $target);
        } else {
            $utf8txt = mb_convert_encoding($target, 'UTF-8', 'SJIS-win');
            $result = @preg_replace($pattern, $marker, $utf8txt);
            $result = mb_convert_encoding($result, 'SJIS-win', 'UTF-8');
        }

        if ($result === FALSE) {
            return $target;
        }
        return $result;
    }

}

?>
