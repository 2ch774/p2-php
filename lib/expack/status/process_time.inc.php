<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

//-----------------------------------------------------
// function getprocess_time( $CPU_start )
// conf.php�̌Ăяo���J�n���猻�݂܂ł̎��Ԃ��擾����B
//-----------------------------------------------------
// ��  ���F�v���Z�X�J�n���ԁi$CPU_start�jconf.php�Q��
// �߂�l�F�v���Z�X�����܂łɗv��������
//-----------------------------------------------------

function getprocess_time( $CPU_start )
{

    list($tmp1,$tmp2)=split(" ",$CPU_start); // �v���Z�X�^�C�����擾�iread.php�̋N�����猻�݂܂ŏ����ɗv�������ԁj
    list($tmp3,$tmp4)=split(" ",microtime());

    return sprintf("%.3f",$tmp4-$tmp2+$tmp3-$tmp1);;
}

?>
