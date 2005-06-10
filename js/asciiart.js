/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

/* p2 - AA�␳JavaScript�t�@�C�� */

//activeMona -- ���i�[�t�H���g�ɐ؂�ւ��A�s�̍������k�߂�
function activeMona(blockId, size)
{
	var amTarget = document.getElementById(blockId);
	if (!amTarget) {
		return;
	}
	if (size == "normal" || (amTarget.className.match(/(Auto|Active)Mona/) && amTarget.style.fontSize == size)) {
		if (blockId.charAt(0) == "q") {
			amTarget.className = "NoMonaQ";
			amTarget.style.fontSize = am_respop_fontSize;
		} else {
			amTarget.className = "NoMona";
			amTarget.style.fontSize = am_read_fontSize;
		}
	} else {
		amTarget.className = "ActiveMona";
		amTarget.style.fontSize = size;
	}
}

//activeMonaForm -- �A�N�e�B�u���i�[ on �t�H�[��
function activeMonaForm(size)
{
	var message, mail;
	if (size == "") {
		return;
	}
	if (dpreview_ok) {
		var dp = document.getElementById("dpreview");
		if (dp) {
			if (dp.style.display == "none") {
				DPInit();
				dp.style.display = "block";
			}
			activeMona("dp_msg", size);
			return;
		} else {
			message = document.getElementById("MESSAGE");
			mail = document.getElementById("mail");
		}
	} else {
		message = document.getElementById("MESSAGE");
		mail = document.getElementById("mail");
	}
	if (!message || !mail) {
		return;
	}
	if (size == "normal") {
		message.style.fontFamily = mail.style.fontFamily;
		message.style.fontSize = mail.style.fontSize;
	} else {
		message.style.fontFamily = am_aa_fontFamily;
		message.style.fontSize = size;
	}
}
