<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

//-----------------------------------------------------
// function getdirfile( $datdir )
// �w�肵���f�B���N�g���ȉ��̃t�@�C���T�C�Y���擾����B
//-----------------------------------------------------
// ��  ���F�f�B���N�g��������������
// �߂�l�F�C�Ӄf�B���N�g���̃t�@�C���T�C�Y���v�l
// ���̑��F�P�N���X�^�� 4096Bytes �Ƃ��ĎZ�o����ׁA
// �@�@�@�@�����̌덷���o��
//-----------------------------------------------------

function getdirfile( $targetdir )
{
	if( !is_dir( $targetdir ) )   // �f�B���N�g���łȂ���� false ��Ԃ�
		return false;

	if( $handle = opendir( $targetdir ) )
	{
		while ( false !== $file = readdir( $handle ) )
		{
			// �������g�Ə�ʊK�w�̃f�B���N�g�������O
			if( $file != "." && $file != ".." )
			{
				if( is_dir( $targetdir."/".$file ) )
				{
					// �f�B���N�g���Ȃ�ċA�ďo����
					$tree[ $file ] = getdirfile( $targetdir."/".$file );
				}else{
					// �t�@�C���Ȃ�t�@�C���T�C�Y���Q��
					static $data_size;
					$file_size = filesize($targetdir."/".$file);
					$tmp_1 = $file_size / 4096; // �N���X�^�T�C�Y
					$tmp_2 = ceil($tmp_1);
					$tmp_3 = $tmp_2 * 4096; // �N���X�^�T�C�Y�ŕ␳
					$data_size = $data_size + $tmp_3;
				}
			}
		}
		closedir( $handle );
	}
	// �P�ʂ�B����MB�ɕ␳(1K=1024)
	return sprintf( "%.2f",($data_size / 1048576)); 
}

?>
