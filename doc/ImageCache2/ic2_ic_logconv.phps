<?php
// ImageCache to ImageCacahe2.
// Web���ł͏����Ɏ��Ԃ������肷����̂�CLI�Ŏ��s���邱�ƁI

require_once 'conf/conf.php';

/* $dummy->enterSection('initialize'); */


error_reporting(E_ALL ^ E_NOTICE);
if (function_exists('header')) { header('Content-Type:text/plain'); }
$_start = explode(' ', microtime());
mb_language('Japanese');
mb_internal_encoding('SJIS');

// �^�C���A�E�g�𖳌���
@set_time_limit(0);

// ���C�u�����ǂݍ���
require_once 'PEAR.php';
require_once 'DB/DataObject.php';
require_once (P2EX_LIBRARY_DIR . '/ic2/db_images.class.php');
require_once (P2EX_LIBRARY_DIR . '/ic2/thumbnail.class.php');


/* $dummy->leaveSection('initialize'); */
/* $dummy->enterSection('p2conf'); */


// p2�̐ݒ���C���|�[�g
$p2imgcachedir = $_exconf['imgCache']['cachedir'];
$p2imgthumbdir = $_exconf['imgCache']['thumbdir'];
$p2imgcachelist = $_conf['pref_dir'] . '/p2_cached_img.txt';


/* $dummy->leaveSection('p2conf'); */
/* $dummy->enterSection('config'); */


// MIME�^�C�v�Ɗg���q�̑Ή��\
$mimemap = array('image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif');

// �ݒ�t�@�C���ǂݍ���
require_once (P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php');
$ini = ic2_loadconfig();

// DB_DataObject�̐ݒ�
$options = &PEAR::getStaticProperty('DB_DataObject','options');
$options = array('database' => $ini['General']['dsn'], 'debug' => false, 'quote_identifiers' => true);


/* $dummy->leaveSection('config'); */
/* $dummy->enterSection('prepare'); */


$icdb = &new IC2DB_images;
$pcthumb = &new ThumbNailer(1);
$kthumb = &new ThumbNailer(2);
$vgathumb = &new ThumbNailer(3);
$db = &$icdb->getDatabaseConnection();
$class = get_class($db);
$transaction = true;

// �g�����U�N�V�����J�n
if ($transaction) {
    switch ($class) {
        case 'db_pgsql': $icdb->query('BEGIN'); break;
        case 'db_sqlite': $db->query('BEGIN;'); break;
    }
}

// ���X�g�ǂݍ���
$list = array();
$lines = file($p2imgcachelist);
sort($lines);
foreach ($lines as $line) {
    $line = trim($line);
    $part = explode("\t", $line);
    $list[] = $part[0];
}


/* $dummy->leaveSection('prepare'); */
/* $dummy->enterSection('exec'); */


// ���Ԃɏ���
$i = 1;
$cp = array();
foreach ($list as $uri) {
    $oldpath = $p2imgcachedir . '/' . preg_replace('|^http://|', '', $uri);

    // �摜���𒲂ׂ�B
    $info = @getimagesize($oldpath);
    if (!$info) {
        echo(" ERROR! �s���ȃt�@�C�� ($oldpath)\n");
        continue;
    }
    if (isset($info['mime'])) {
        // >= PHP4.3.0
        $mime = $info['mime'];
    } else {
        // < PHP4.3.0
        switch ($info[2]) {
            case 1: $mime = 'image/gif'; break;
            case 2: $mime = 'image/jpeg'; break;
            case 3: $mime = 'image/png'; break;
            default: $mime = 'application/octet-stream';
            // ���ۂ�4:swf;5:psd;6:bmp;7:tiff(���g���G���f�B�A��);8:tiff(�r�b�O�G���f�B�A��);etc..
        }
    }
    if (!in_array($mime, array_keys($mimemap))) {
        echo(" ERROR! �s���ȃt�@�C���^�C�v ($oldpath = $mime)");
        continue;
    }

    // ���K�̉摜�Ȃ�t�@�C���T�C�Y��MD5�`�F�b�N�T�����v�Z
    $pURL = parse_url($uri);
    $host = $pURL['host'];
    $name = basename($oldpath);
    $size = filesize($oldpath);
    $md5  = md5_file($oldpath);
    $width  = $info[0];
    $height = $info[1];
    $time = filemtime($oldpath);

    // �V�����f�B���N�g���ɃR�s�[����
    $newfile = $size . '_' . $md5 . $mimemap[$mime];
    if (!isset($cp[$newfile])) {
        $newdir = $ini['General']['cachedir'] . '/' . $ini['Source']['name'] . '/';
        $newdir .= str_pad(ceil($i / 1000), 5, 0, STR_PAD_LEFT) . '/';
        if (!is_dir($newdir) && !@mkdir($newdir)) {
            exit(" ERROR! �f�B���N�g���쐬���s ($newdir)\n");
        }
        $newpath = $newdir . $newfile;
        if (!@copy($oldpath, $newpath)) {
            echo(" ERROR! �R�s�[���s ($oldpath -> $newpath)\n");
            continue;
        }
        @chmod($newpath, 0644);
    }

    // �f�[�^�x�[�X�Ƀt�@�C�������L�^����
    $record = new IC2DB_images;
    $record->uri = $uri;
    $record->host = $host;
    $record->name = $name;
    $record->size = $size;
    $record->md5 = $md5;
    $record->width = $width;
    $record->height = $height;
    $record->mime = $mime;
    $record->time = $time;
    $record->insert();
    unset($record);

    // �T���l�C���쐬
    if (!isset($cp[$newfile])) {
        ob_start();
        $pcthumb->convert($size, $md5, $mime, $width, $height);
//      $kthumb->convert($size, $md5, $mime, $width, $height);
//      $vgathumb->convert($size, $md5, $mime, $width, $height);
        ob_end_clean();
    }

    // �}�[�J�[�o��
    if ($i % 10 == 0) {
        echo '.';
        if ($i % 100 == 0) {
            echo $i;
            if ($transaction) {
                switch ($class) {
                    case 'db_pgsql':
                        $icdb->query('COMMIT');
                        $icdb->query('BEGIN');
                        break;
                    case 'db_sqlite':
                        $db->query('COMMIT;');
                        $db->query('BEGIN;');
                        break;
                }
            }
        }
    }

    // �R�s�[�σt���O�𗧂āA�J�E���^���C���N�������g
    $cp[$newfile] = true;
    $i++;
}


/* $dummy->leaveSection('exec'); */
/* $dummy->enterSection('finish'); */


// �g�����U�N�V�����I��
if ($transaction) {
    switch ($class) {
        case 'db_pgsql': $icdb->query('COMMIT'); break;
        case 'db_sqlite': $db->query('COMMIT;'); break;
    }
}

$_end = explode(' ', microtime());
$_time = floatval(intval($_end[1]) - intval($_start[1])) + (floatval($_end[0]) - floatval($_start[0]));
$_conv = $i - 1;
echo " done! ({$_conv}images, {$_time}sec)\n";



/* $dummy->leaveSection('finish'); */


?>