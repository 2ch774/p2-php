<?php
//�R�e�n�����g���b�v�I�����j���[��ǉ�
//�t�H�[���̐���

$htm['handle_ht'] =<<<EOSS
\n	<select id="HANDLE" name="HANDLE" onkeyup="{$dp_setname}" onChange="inputHandle(this);{$dp_setname}">
		<option value="">�R�e�n�����g���b�v</option>\n
EOSS;
foreach (array_map('htmlspecialchars', $_exconf['handle']) as $handle_key => $handle_value) {
	if($handle_key != "*"){
		$htm['handle_ht'] .=<<<EOO
		<option value="{$handle_value}">{$handle_key}</option>\n
EOO;
	}
}
$htm['handle_ht'] .=<<<EOSE
	</select>
	<br>\n
EOSE;

?>
