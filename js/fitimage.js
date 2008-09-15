/**
 * ImageCache2::FitImage
 */

// {{{ FitImage �I�u�W�F�N�g

/*
 * �R���X�g���N�^
 *
 * @param String id     �摜��id
 * @param Number width  �摜�̕�
 * @param Number height �摜�̍���
 */
function FitImage(id, width, height)
{
	// {{{ properties

	this.picture = document.getElementById(id);
	this.imgX = width;
	this.imgY = height;
	this.currentMode = '_init_';

	// }}}
	// {{{ FitImage.fitTo()

	/*
	 * �摜���E�C���h�E�Ƀt�B�b�g�����郁�\�b�h
	 */
	this.fitTo = function(mode)
	{
		var winX, winY, cssX, cssY;

		if (document.all) { //IE�p
			var _body = (document.compatMode == 'CSS1Compat') ? document.documentElement : document.body;
			winX = _body.clientWidth;
			winY = _body.clientHeight;
		} else {
			winX = window.innerWidth;
			winY = window.innerHeight;
		}

		if (this.currentMode == mode) {
			this.currentMode = 'auto';
			this.picture.style.width = 'auto';
			this.picture.style.height = 'auto';
		} else {
			var autofit = false;

			if (mode == 'autofit') {
				autofit = true;
				if (winX / winY > this.imgX / this.imgY) {
					mode = 'height'
					this.currentMode = (winY < this.imgY) ? 'height' : 'auto';
				} else {
					mode = 'width'
					this.currentMode = (winX < this.imgX) ? 'width' : 'auto';
				}
			} else {
				this.currentMode = mode;
			}

			if (autofit) {
				cssX = Math.min(winX, this.imgX).toString() + 'px';
				cssY = Math.min(winY, this.imgY).toString() + 'px';
			} else {
				cssX = winX.toString() + 'px';
				cssY = winY.toString() + 'px';
			}

			switch (mode) {
				/*
				case 'full':
					this.picture.style.width = cssX;
					this.picture.style.height = cssY;
					break;
				*/
				case 'width':
					this.picture.style.width = cssX;
					this.picture.style.height = 'auto';
					break;
				case 'height':
					this.picture.style.width = 'auto';
					this.picture.style.height = cssY;
					break;
				default:
			}
		}
	}

	// }}}
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
