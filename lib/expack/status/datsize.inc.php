<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

//-----------------------------------------------------
// Function getthread_dir( $host )
// ���ݕ\�����Ă���X���̃t�@�C���T�C�Y���擾����B
//-----------------------------------------------------
// ��  ���F�f�B���N�g�������߂邽�߂̌f�����i$host�j
// �߂�l�F���ݕ\�����Ă���X���̃t�@�C���T�C�Y
// ���̑��F1024Bytes = 1KB �Ƃ��Ċ��Z����
//-----------------------------------------------------

function getthread_dir( $host, $bbs, $key ){
    $datdir_host=P2Util::datdirOfHost($host);
    $thread_file=$datdir_host."/".$bbs."/".$key.".dat";
    return $thread_size = sprintf("%.2f",
	((file_exists($thread_file))? (filesize($thread_file)/1024) : 0)
    );
}

?>
<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

//-----------------------------------------------------
// Function getthread_dir( $host )
// ���ݕ\�����Ă���X���̃t�@�C���T�C�Y���擾����B
//-----------------------------------------------------
// ��  ���F�f�B���N�g�������߂邽�߂̌f�����i$host�j
// �߂�l�F���ݕ\�����Ă���X���̃t�@�C���T�C�Y
// ���̑��F1024Bytes = 1KB �Ƃ��Ċ��Z����
//-----------------------------------------------------

function getthread_dir( $host, $bbs, $key ){
    $datdir_host=P2Util::datdirOfHost($host);
    $thread_file=$datdir_host."/".$bbs."/".$key.".dat";
    return $thread_size = sprintf("%.2f",
	((file_exists($thread_file))? (filesize($thread_file)/1024) : 0)
    );
}

?>
