<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2�@�\�g���p�b�N - �p�P�b�g�ߖ�֐�

/**
 * �ݒ�
 */

// �ϊ��ΏۊO�̃X�N���v�g��
// �l���v���Z�b�g����Ă���t�H�[����摜�i�o�C�i���j������X�N���v�g
$GLOBALS['ps_ignore_files'] = array('post.php', 'post_form.php', 'read_copy_k.php', 'imgcache.php', 'ic2.php', 'ic2_mkthumb.php', 'tgrepc.php');

// Tidy�̐ݒ�
$GLOBALS['ps_tidy_config'] = array(
    'doctype' => 'omit',
    'drop-empty-paras' => TRUE,
    'indent' => FALSE,
    'newline' => 'LF',
    'output-bom' => FALSE,
    'output-xhtml' => FALSE,
    'wrap' => 0
);

// Tidy�̕����R�[�h
// Tidy�̓V�t�gJIS�������ł��邪�A�V�t�gJIS�̂܂� $GLOBALS['ps_tidy_encoding'] = 'shiftjis' ��
// tidy_repair_string�ɂ�����ƁA�Ȃ����V���܂Ƃߓǂ݂��������\���ł��Ȃ��̂�UTF-8�ɂ��Ă��珈��
$GLOBALS['ps_tidy_encoding'] = 'utf8';

// �O��̃X�y�[�X���������Ă������_�����O���ʂ��ς��Ȃ�HTML�v�f�̐��K�\��
$GLOBALS['ps_clean_tags'] = 'html|head|meta|title|link|script|style|body|h[1-6]|p|div|address|blockquote|form|fieldset|legend|optgroup|option|ul|ol|li|dl|dt|dd|table|caption|thead|tbody|tfoot|tr|th|td|center|hr|br';


/**
 * �p�P�b�g�ߖ�֐�
 */
function packet_saver($buffer)
{
    global $ps_ignore_files;

    $script = basename($_SERVER['SCRIPT_NAME']);
    if (in_array($script, $ps_ignore_files) || defined('P2_NO_SAVE_PACKET')) {
        return $buffer;
    }

    // ���K�\���Ɋm���Ƀ}�b�`����悤�ɁAUTF-8�ɕϊ�
    $buffer = mb_convert_encoding($buffer, 'UTF-8', 'SJIS-win');

    // HTML�\�[�X�œK���֐��ŏ���
    if (extension_loaded('tidy')) {
        global $ps_tidy_config, $ps_tidy_encoding;
        if (version_compare(phpversion(), '5.0.0', 'ge')) {
            $buffer = packet_saver_tidy2($buffer, $ps_tidy_config, $ps_tidy_encoding);
            //$buffer .= '<!-- Tidy/PHP5 -->';
        } else {
            $buffer = packet_saver_tidy($buffer, $ps_tidy_config, $ps_tidy_encoding);
            //$buffer .= '<!-- Tidy/PHP4 -->';
        }
        // meta�v�f��charset���w�肵�Ă���Ƃ��͒����Ă����񂾂��ǁA���̏ꍇ�͗]�v�Ȃ����b
        /*$buffer = preg_replace(
            '/<meta http-equiv="Content-Type" content="text\/html; ?charset=utf-8">/u',
            '<meta http-equiv="Content-Type" content="text/html; charset=shift_jis">',
            $buffer);*/
    } else {
        global $ps_clean_tags;
        $buffer = packet_saver_preg($buffer, $ps_clean_tags);
        //$buffer .= '<!-- PCRE -->';
    }
    // �g�т�SJIS���ߑł��Ȃ̂�charset����Ȃ�
    $buffer = preg_replace('/<meta http-equiv="Content-Type" content="text\/html; ?charset=\w+">/u', '', $buffer);

    // �ꕔ�S�p�L���𔼊p�̎��̎Q�Ƃɕϊ�
    $zenkakuSpChars = array('/��/u', '/��/u', '/��/u');
    $hankakuSpChars = array('&amp;', '&lt;', '&gt;');
    mb_convert_variables('UTF-8', 'SJIS-win', $zenkakuSpChars);
    $buffer = preg_replace($zenkakuSpChars, $hankakuSpChars, $buffer);
    // �S�p�p�����E�J�^�J�i�𔼊p�ɕϊ�
    $buffer = mb_convert_kana($buffer, 'ka', 'UTF-8');

    //��jig�A�v���𗘗p���Ă��āA�����N�����������Ȃ�ꍇ�̓R�����g�A�E�g����������
    //$buffer = preg_replace_callback('/<a ([^<>]+ )?href="([^"]+)"/u', 'jig_unhtmlspecialchars', $buffer);

    // SJIS�ɖ߂�
    $buffer = mb_convert_encoding($buffer, 'SJIS-win', 'UTF-8');

    return $buffer;
}


/**
 * preg_replace()���g���ĕs�v�ȃC���f���g����������
 *
 * �A������z���C�g�X�y�[�X���܂Ƃ߁A�u���b�N���x���v�f��<br>�ȂǑO��̃X�y�[�X������Ă�
 * �����_�����O���ʂ��ς��Ȃ����̂͑O��̃X�y�[�X����������
 * pre�v�f�̒��g�܂őΏۂɂȂ��Ă��܂����ǁA���r�L�^�X�ł͎g���Ă��Ȃ��̂ŋC�ɂ��Ȃ�
 */
function packet_saver_preg($buffer, $clean_tags)
{
    $buffer = preg_replace('/\s+/u', ' ', $buffer);
    $buffer = preg_replace('/ (<\/?('.$clean_tags.')( [^<>]*)?>)/u', '$1', $buffer);
    $buffer = preg_replace('/(<\/?('.$clean_tags.')( [^<>]*)?>) /u', '$1', $buffer);
    return $buffer;
}


/**
 * Tidy���g����HTML�\�[�X���œK������ (PHP4 - PECL tidy [1.x])
 */
function packet_saver_tidy($buffer, $config, $encoding)
{
    tidy_set_encoding($encoding);
    foreach ($config as $key => $value) {
        tidy_setopt($key, $value);
    }
    $buffer = tidy_repair_string($buffer);
    $buffer = str_replace("\n", '', $buffer);
    return $buffer;
}


/**
 * Tidy���g����HTML�\�[�X���œK������ (PHP5 - ext/tidy [2.x])
 */
function packet_saver_tidy2($buffer, $config, $encoding)
{
    $buffer = tidy_repair_string($buffer, $config, $encoding);
    $buffer = str_replace("\n", '', $buffer);
    return $buffer;
}


/**
 * jig���}�[�u�̃R�[���o�b�N�֐�
 *
 * jig�̃v���L�V�T�[�o�̓N�G�������񒆂�&�������I��&amp;�ɂ���炵���A
 * ���̂܂܂ł�&amp;��&amp;amp;�ɂ����GET�Œl�𐳂����n���Ȃ��Ȃ�̂�&amp;��&�ɖ߂�
 */
function jig_unhtmlspecialchars($amp)
{
    $link = '<a ' . $amp[1] . 'href="' . str_replace('&amp;', '&', $amp[2]) . '"';
    return $link;
}

?>
