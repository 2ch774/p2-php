/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

//�摜�T�C�Y�t���O��������
var psize = "auto";

//�摜���E�C���h�E�Ƀt�B�b�g������֐�
function fitimage(mode)
{
	var picture = document.getElementById("picture");
	if (!picture) {
		return;
	}
	if (psize == mode) {
		psize = "auto";
		picture.style.width = "auto";
		picture.style.height = "auto";
	} else {
		psize = mode;
		switch (mode) {
			case "full":
				picture.style.width = "100%";
				picture.style.height = "100%";
				break;
			case "width":
				picture.style.width = "100%";
				picture.style.height = "auto";
				break;
			case "height":
				picture.style.width = "auto";
				picture.style.height = "100%";
				break;
			default:
		}
	}
}


//�ǂݍ��񂾂Ƃ��Ɏ����ŉ摜���E�C���h�E�Ƀt�B�b�g������֐�
function autofitimage(mode, imgX, imgY)
{
	if (document.all) { //IE�p
		var body = (document.compatMode == 'CSS1Compat') ? document.documentElement : document.body;
		var winX = body.clientWidth;
		var winY = body.clientHeight;
	} else if (document.getElementById) {
		var winX = window.innerWidth
		var winY = window.innerHeight;
	} else {
		return;
	}
	if (!imgX || !imgY) {
		return;
	}
	if (mode == "auto") {
		if (winX / winY > imgX / imgY) {
			mode = "height"
		} else {
			mode = "width"
		}
	}
	if ((mode == "width" && imgX <= winX) || (mode == "height" && imgY <= winY)) {
		return;
	}
	fitimage(mode);
}

//�{�^���̕\���E��\����؂�ւ���֐�
function fiShowHide()
{
	var sw = document.getElementById("btn");
	if (!sw) {
		return;
	}
	if (sw.style.display == "block") {
		sw.style.display = "none";
	} else {
		sw.style.display = "block";
	}
}

//�L�[����ő��̊֐����Ăяo���֐�
function fiTrigger(evt)
{
	var evt = (evt) ? evt : ((window.event) ? event : null);
	if (!evt || !evt.keyCode) {
		return;
	}
	focus();
	switch (evt.keyCode) {
		case 16: // Shift
		case 73: // I
			fiShowHide(); // �X�C�b�`�\����On/Off
			break;
		case 65: // A
			fitimage(psize); // ���̃T�C�Y�ŕ\��
			break;
		case 70: // F
			fitimage("full"); // �摜�T�C�Y���E�C���h�E�T�C�Y�Ƀt�B�b�g
			break;
		case 87: // W
			fitimage("width"); // �摜�T�C�Y���E�C���h�E���Ƀt�B�b�g
			break;
		case 72: // H
			fitimage("height"); // �摜�T�C�Y���E�C���h�E�����Ƀt�B�b�g
			break;
		case 82: // R
			switch (psize) { // �摜�T�C�Y�����Ԃɐ؂�ւ�
				case "auto":
				case "full":
					fitimage("width");
					break;
				case "width":
					fitimage("height");
					break;
				case "height":
					fitimage("full");
					break;
				default:
					fitimage(psize);
			}
			break;
		default:
			//alert(evt.keyCode);
	}
}

//�C�x���g�n���h�����`
document.onkeydown = fiTrigger;
