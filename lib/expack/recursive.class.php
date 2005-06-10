<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// �g���p�b�N�E�ėp�ċA�����N���X

class Recursive
{
	var $_handler;
	var $_limit;
	var $_checked;

	//�R���X�g���N�^
	function Recursive($handler = null, $limit = 5)
	{
		$this->setHandler($handler);
		$this->setLimit($limit);
	}

	//map���\�b�h�̈�������������֐��E���\�b�h��ݒ�
	function setHandler($handler = null)
	{
		$this->_handler = $handler;
		$this->_checked = false;
	}

	//�ő�ċA�񐔂�ݒ�
	function setLimit($limit)
	{
		$this->_limit = $limit;
	}

	//���������ċA�I�ɏ�������
	function map($value, $count = 0)
	{
		//�ċA�񐔂̃`�F�b�N
		if ($count > $this->_limit) {
			return $value;
		}
		//�L���Ȋ֐��������̓��\�b�h���`�F�b�N
		/*if (!$this->_checked) {
			if (is_string($this->_handler)) {
				if (function_exists($this->_handler)) {
					$this->_checked = true;
				} else {
					trigger_error("expack-Recursive:: Function {$this->_handler} is not exists.", E_USER_ERROR);
				}
			} elseif (is_array($this->_handler) && is_object($this->_handler[0]) && is_string($this->_handler[1])) {
				if (method_exists($this->_handler[0], $this->_handler[1])) {
					$this->_checked = true;
				} else {
					trigger_error("expack-Recursive:: Function {$this->_handler} is not exists.", E_USER_ERROR);
				}
			} else {
				trigger_error("expack-Recursive:: Invalid handler was given.", E_USER_ERROR);
			}
		}*/
		//�ċA�I�ɏ���
		if (is_object($value)) {
			$properties = get_object_vars($value);
			if (count($properties) == 0) { return $value; }
			foreach ($properties as $p => $v) {
				$value->$p = $this->map($v, $count+1);
			}
			return $value;
		} elseif (is_array($value)) {
			if (count($value) == 0) { return $value; }
			foreach ($value as $k => $v) {
				$value[$k] = $this->map($v, $count+1);
			}
			return $value;
		} else {
			if (is_array($this->_handler)) {
				$object = &$this->_handler[0];
				$method = $this->_handler[1];
				return $object->$method($value);
			} else {
				$function = $this->_handler;
				return $function($value);
			}
		}
	}
}

?>
