function setFocus(ID){
	if (obj = document.getElementById(ID)) {
		if (obj.disabled != true) {
			obj.focus();
		}
	}
}

// sage�`�F�b�N�ɍ��킹�āA���[�����̓��e������������
function mailSage(){
	if (cbsage = document.getElementById('sage')) {
		if (mailran = document.getElementById('mail')) {
			if (cbsage.checked == true) {
				mailran.value = "sage";
			} else {
				if (mailran.value == "sage") {
					mailran.value = "";
				}
			}
		}
	}
}

// ���[�����̓��e�ɉ����āAsage�`�F�b�N��ON OFF����
function checkSage(){
	if (mailran = document.getElementById('mail')) {
		if (cbsage = document.getElementById('sage')) {
			if (mailran.value == "sage") {
				cbsage.checked = true;
			} else {
				cbsage.checked = false;
			}
		}
	}
}

/*
// �����œǂݍ��ނ��Ƃɂ����̂ŁA�g��Ȃ�

// �O��̏������ݓ��e�𕜋A����
function loadLastPosted(from, mail, message){
	if (fromran = document.getElementById('FROM')) {
		fromran.value = from;
	}
	if (mailran = document.getElementById('mail')) {
		mailran.value = mail;
	}
	if (messageran = document.getElementById('MESSAGE')) {
		messageran.value = message;
	}
	checkSage();
}
*/

// ���[�����̓��e�ɉ����āAsage�`�F�b�N��ON OFF����
function checkSage() {
	if (mailran = document.getElementById('mail')) {
		if (cbsage = document.getElementById('sage')) {
			if (mailran.value == "sage") {
				cbsage.checked = true;
			} else {
				cbsage.checked = false;
			}
		}
	}
}

// ��^����}������
function inputConstant(obj) {
	var msg = document.getElementById('MESSAGE');
	msg.value = msg.value + obj.options[obj.selectedIndex].value;
	msg.focus();
	obj.options[0].selected = true;
}

// �������ݓ��e�����؂���
function validateAll(doValidateMsg, doValidateSage) {
	if (doValidateMsg && !validateMsg()) {
		return false;
	}
	if (doValidateSage && !validateSage()) {
		return false;
	}
	return true;
}

// �{������łȂ������؂���
function validateMsg() {
	if (document.getElementById('MESSAGE').value.length == 0) {
		alert('�{��������܂���B');
		return false;
	}
	return true;
}

// sage�Ă��邩���؂���
function validateSage() {
	if (document.getElementById('mail').value.indexOf('sage') == -1) {
		if (window.confirm('sage�Ă܂����H')) {
			return true;
		} else {
			return false;
		}
	}
	return true;
}
