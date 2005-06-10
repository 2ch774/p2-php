;<?php /*
; vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker:
; mi: charset=Shift_JIS

;{{{ -------- �S�� --------
[General]

;�L���b�V���ۑ��f�B���N�g���̃p�X
cachedir = "./cache"

;�R���p�C���σe���v���[�g�ۑ��f�B���N�g����
;�icachedir�̃T�u�f�B���N�g���j
compiledir = compile

;DSN (DB�ɐڑ����邽�߂̃f�[�^�\�[�X��)
;@link http://jp.pear.php.net/manual/ja/package.database.db.intro-dsn.php
;��1 (SQLite): dsn = "sqlite:///./cache/imgcache.sqlite"
;��2 (PosrgreSQL): dsn = "pgsql://username:password@localhost:5432/database"
;��3 (MySQL): dsn = "mysql://username:password@localhost:3306/database"
;��1: username,password,database�͎��ۂ̂��̂Ɠǂݑւ���B
;��2: MySQL,PosrgreSQL�ł͗\�߃f�[�^�x�[�X������Ă����B
dsn = ""

;DB�Ŏg���e�[�u����
table = imgcache

;�폜�ς݁��ă_�E�����[�h���Ȃ��摜���X�g�̃e�[�u����
blacklist_table = ic2_blacklist

;�G���[���L�^����e�[�u����
error_table = ic2_errors

;�G���[���L�^����ő�̍s��
error_log_num = 100

;�摜��URL���\��ꂽ�X���b�h�̃^�C�g���������ŋL�^���� (off:0;on:1)
automemo = 1

;�摜����������v���O���� (GD | ImageMagick | ImageMagick6)
;GD��PHP�̃C���[�W�֐��𗘗p�AImageMagick(6)�͊O���v���O�����𗘗p
;�������߂�ImageMagick6
driver = GD

;ImageMagick�̃p�X�iconvert������g�f�B���N�g���́h�p�X�j
;httpd�̊��ϐ��Ńp�X���ʂ��Ă���Ȃ��̂܂܂ł悢
;�p�X�𖾎��I�Ɏw�肷��ꍇ�́A�X�y�[�X������ƃT���l�C�����쐬�ł��Ȃ��̂Œ���
magick = ""

;���߉摜���T���l�C��������ۂ̔w�i�F (GD�̂ݗL���A16�i6���Ŏw��)
bgcolor = "#FFFFFF"

;�g�тł��T���l�C�����C�����C���\������ (off:0;on:1)
;���̂Ƃ��̑傫����PC�Ɠ���
inline = 0


;}}}
;{{{ -------- �f�[�^�L���b�V�� --------
[Cache]

;�f�[�^���L���b�V�����邽�߂̃e�[�u����
table = datacache

;�L���b�V���̗L�������i�b�j
;1����=3600
;1��=86400
;1�T��=604800
expires = 3600

;�L���b�V������f�[�^�̍ő�ʁi�o�C�g�j
highwater = 2048000

;�L���b�V�������f�[�^��highwater�𒴂����Ƃ��A���̒l�܂Ō��炷�i�o�C�g�j
lowwater = 1536000


;}}}
;{{{ -------- �ꗗ --------
[Viewer]

;�y�[�W�^�C�g��
title = "ImageCache2::Viewer"

;�\���p�ɒ��������摜�����L���b�V�� (off:0;on:1)
;�L���b�V���̗L�������Ȃǂ�[Cache]�̍��Őݒ�
cache = 0

;�d���摜���ŏ��Ƀq�b�g����1�������\�� (on:0;off:1)
;�T�u�N�G�����g�����߃o�[�W����4.1������MySQL�ł͖���
unique = 0

;Exif����\�� (off:0;on:1)
exif = 0

;--�ȉ��̐ݒ�͂̓f�t�H���g�l�ŁA�c�[���o�[�ŕύX�ł���--

;1�y�[�W������̗�
cols = 8

;1�y�[�W������̍s��
rows = 5

;�������l (-1 ~ 5)
threshold = 0

;���ёւ�� (time | uri | name | size)
order = time

;���ёւ����� (ASC | DESC)
sort = DESC

;�����t�B�[���h (uri | name | memo)
field = memo


;}}}
;{{{ -------- �Ǘ� --------
[Manager]

;�y�[�W�^�C�g��
title = "ImageCache2::Manager"

;�����L������1�s������̔��p������
cols = 40

;�����L�����̍s��
rows = 5


;}}}
;{{{ -------- �_�E�����[�h --------
[Getter]

;�y�[�W�^�C�g��
title = "ImageCache2::Getter"

;�G���[���O�ɂ���摜�̓_�E�����[�h�����݂Ȃ� (no:0;yes:1)
checkerror = 1

;�f�t�H���g��URL+.html�̋U���t�@���𑗂� (no:0;yes:1)
sendreferer = 0

;sendreferer = 0 �̂Ƃ��A��O�I�Ƀ��t�@���𑗂�z�X�g�i�J���}��؂�j
refhosts = ""

;sendreferer = 1 �̂Ƃ��A��O�I�Ƀ��t�@���𑗂�Ȃ��z�X�g�i�J���}��؂�j
norefhosts = ""

;�������ځ[��̃z�X�g�i�J���}��؂�j
reject = "rotten.com,shinrei.net";

;�E�B���X�X�L���������� (no:0;clamscan:1;clamdscan:2)
;�iClam AntiVirus�𗘗p�j
;ImageCache2��蓮�X�L�����ɂ���ClamAV���g��Ȃ��Ȃ�1��clamscan�̕�������Ǝv����
virusscan = 0

;ClamAV�̃p�X�iclam(d)scan������g�f�B���N�g���́h�p�X�j
;httpd�̊��ϐ��Ńp�X���ʂ��Ă���Ȃ��̂܂܂ł悢
;�p�X�𖾎��I�Ɏw�肷��ꍇ�́A�X�y�[�X������ƃE�B���X�X�L�����ł��Ȃ��̂Œ���
clamav = ""


;}}}
;{{{ -------- �v���L�V --------
[Proxy]

;�摜�̃_�E�����[�h�Ƀv���L�V���g�� (no:0;yes:1)
enabled = 0

;�z�X�g
host = ""

;�|�[�g
port = ""

;���[�U��
user = ""

;�p�X���[�h
pass = ""


;}}}
;{{{ -------- �\�[�X --------
[Source]

;�ۑ��p�T�u�f�B���N�g����
name = src

;�L���b�V������ő�f�[�^�T�C�Y�i������z����Ƌ֎~���X�g�s���A0�͖������j
maxsize = 10000000

;�L���b�V������ő�̕��i��ɓ������j
maxwidth = 4000

;�L���b�V������ő�̍����i�V�j
maxheight = 4000


;}}}
;{{{ -------- �T���l�C�� --------
[Thumb1]

;�ݒ薼�i���ۑ��p�T�u�f�B���N�g�����j
name = 6464

;�T���l�C���̍ő啝�i���̐����j
width = 64

;�T���l�C���̍ő卂���i���̐����j
height = 64

;�T���l�C����JPEG�i���i���̐����A1~100�ȊO�ɂ����PNG�j
quality = 80


;}}}
;{{{ -------- �g�уt���X�N���[�� --------
[Thumb2]

;�ݒ薼
name = qvga_v

;�T���l�C���̍ő啝
width = 240

;�T���l�C���̍ő卂��
height = 320

;�T���l�C����JPEG�i��
quality = 80


;}}}
;{{{ -------- ���ԃC���[�W --------
[Thumb3]

;�ݒ薼
name = vga

;�T���l�C���̍ő啝
width = 640

;�T���l�C���̍ő卂��
height = 480

;�T���l�C����JPEG�i��
quality = 80


;}}}
;*/ ?>
