/**
 * ImageCache2::FitImage
 */

// {{{ FitImage �I�u�W�F�N�g

/*
 * �R���X�g���N�^
 *
 * @param String id     �摜��id�܂���DOM�v�f
 * @param Number width  �摜�̕�
 * @param Number height �摜�̍���
 */
function FitImage(id, width, height)
{
	this.picture = (typeof id == 'string') ? document.getElementById(id) : id;
	this.imgX = width;
	this.imgY = height;
	this.ratio = width / height;
	this.currentMode = 'init';
	this.defaultMode = (this.getFieldWidth() > width && this.getFieldHeight() > height) ? 'expand' : 'contract';
}

// }}}
// {{{ FitImage.getFieldWidth()

/*
 * �E�C���h�E�̕����擾����
 *
 * @return Number
 */
FitImage.prototype.getFieldWidth = function()
{
	if (document.all) { //IE�p
		return ((document.compatMode == 'CSS1Compat') ? document.documentElement : document.body).clientWidth;
	} else {
		return window.innerWidth;
	}
}

// }}}
// {{{ FitImage.getFieldHeight()

/*
 * �E�C���h�E�̍������擾����
 *
 * @return Number
 */
FitImage.prototype.getFieldHeight = function()
{
	if (document.all) { //IE�p
		return ((document.compatMode == 'CSS1Compat') ? document.documentElement : document.body).clientHeight;
	} else {
		return window.innerHeight;
	}
}

// }}}
// {{{ FitImage.fitTo()

/*
 * �摜���E�C���h�E�Ƀt�B�b�g������
 *
 * @param String mode
 * @return void
 */
FitImage.prototype.fitTo = function(mode)
{
	if (this.currentMode == mode || (this.currentMode == 'init' && this.defaultMode == 'expand')) {
		// ���̑傫���ɖ߂�
		this.currentMode = 'auto';
		this.picture.style.width = 'auto';
		this.picture.style.height = 'auto';
	} else {
		var winX, winY, cssX, cssY;

		winX = this.getFieldWidth();
		winY = this.getFieldHeight();

		// �E�C���h�E�ɍ��킹�Ċg��E�k������
		switch (mode) {
		  case 'contract':
			if (winX / winY > this.ratio) {
				mode = 'height'
				this.currentMode = (winY < this.imgY) ? 'height' : 'auto';
			} else {
				mode = 'width'
				this.currentMode = (winX < this.imgX) ? 'width' : 'auto';
			}
			cssX = Math.min(winX, this.imgX).toString() + 'px';
			cssY = Math.min(winY, this.imgY).toString() + 'px';
			break;

		  case 'expand':
			if (winX / winY > this.ratio) {
				mode = 'height'
				this.currentMode = (winY > this.imgY) ? 'height' : 'auto';
			} else {
				mode = 'width'
				this.currentMode = (winX > this.imgX) ? 'width' : 'auto';
			}
			cssX = Math.max(winX, this.imgX).toString() + 'px';
			cssY = Math.max(winY, this.imgY).toString() + 'px';
			break;

		  default:
			this.currentMode = mode;
			cssX = winX.toString() + 'px';
			cssY = winY.toString() + 'px';
		}

		// ���ۂɃ��T�C�Y
		switch (mode) {
		  case 'full':
			this.picture.style.width = cssX;
			this.picture.style.height = cssY;
			break;

		  case 'width':
			this.picture.style.width = cssX;
			this.picture.style.height = 'auto';
			break;

		  case 'height':
			this.picture.style.width = 'auto';
			this.picture.style.height = cssY;
			break;

		  default:
			break;
		}
	}
}

// }}}
// {{{ fiShowHide()

/*
 * �{�^���̕\���E��\����؂�ւ���
 */
function fiShowHide()
{
	var sw = document.getElementById('btn');
	if (!sw) {
		return;
	}
	if (sw.style.display == 'block') {
		sw.style.display = 'none';
	} else {
		sw.style.display = 'block';
	}
}

// }}}
// {{{ fiTrigger() (disabled)

/*
 * �L�[����ő��̊֐����Ăяo�� (����)
 */
/*
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
*/
// }}}
// {{{ fiGetImageInfo()

/*
 * �f�[�^�x�[�X����摜�����擾����
 */
function fiGetImageInfo(type, value)
{
	var info = getImageInfo(type, value);
	if (!info) {
		alert('�摜�����擾�ł��܂���ł���');
		return;
	}

	var info_array = info.split(',');

	if (info_array.length < 6) {
		alert('�摜�����擾�ł��܂���ł���');
		return;
	}

	var id     = parseInt(info_array[0]);
	var width  = parseInt(info_array[1]);
	var height = parseInt(info_array[2]);
	var size   = parseInt(info_array[3]);
	var rank   = parseInt(info_array[4]);
	var memo   = info_array[5];

	for (var i = 6; i < info_array.length; i++) {
		memo += ',' + info_array[i];
	}

	fiSetRank(rank);
	document.getElementById('fi_id').value = id.toString();
	//document.getElementById('fi_memo').value = memo;
}

// }}}
// {{{ fiSetRank()

/*
 * �����N�\�����X�V����
 *
 * @param Number rank
 * @return void
 */
function fiSetRank(rank)
{
	var images = document.getElementById('fi_stars').getElementsByTagName('img');
	var pos = rank + 1;
	images[0].setAttribute('src', 'img/sn' + ((rank == -1) ? '1' : '0') + '.png');
	for (var i = 2; i < images.length; i++) {
		images[i].setAttribute('src', 'img/s' + ((i > pos) ? '0' : '1') + '.png');
	}
}

// }}}
// {{{ fiUpdateRank()

/*
 * �f�[�^�x�[�X�ɋL�^����Ă��郉���N���X�V����
 *
 * @param Number rank
 * @return Boolean  always returns false.
 */
function fiUpdateRank(rank)
{
	var id = document.getElementById('fi_id').value;
	if (!id) {
		alert('�摜ID���ݒ肳��Ă��܂���');
		return false;
	}

	var objHTTP = getXmlHttp();
	if (!objHTTP) {
		alert('Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B') ;
		return false;
	}
	var url = 'ic2_setrank.php?id=' + id + '&rank=' + rank.toString();
	var res = getResponseTextHttp(objHTTP, url, 'nc');
	if (res == '1') {
		fiSetRank(rank);
	}
	return false;
}

// }}}

//�C�x���g�n���h����ݒ�E�E�E���Ȃ�
//document.onkeydown = fiTrigger;

/*
 * Local Variables:
 * mode: javascript
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: t
 * End:
 */
/* vim: set syn=javascript fenc=cp932 ai noet ts=4 sw=4 sts=4 fdm=marker: */
