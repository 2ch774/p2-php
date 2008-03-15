/* vim: set fileencoding=cp932 ai noet ts=4 sw=4 sts=4: */
/* mi: charset=Shift_JIS */

/* expack - Google�����̗v����|�b�v�A�b�v���邽�߂�JavaScript */
/* respopup.js�̃T�u�Z�b�g */

var zNum = 0;

//==============================================================
// gShowPopUp -- �v��|�b�v�A�b�v��\������֐�
//==============================================================

function gShowPopUp(divID, ev)
{
	zNum++;

	var popOBJ = document.getElementById(divID);
	var x_adjust = 10; //x���ʒu����
	var y_adjust = 10; //y���ʒu����

	if (popOBJ && popOBJ.style.visibility != "visible") {
		popOBJ.style.zIndex = zNum;
		if (document.all) { //IE�p
			var body = (document.compatMode=='CSS1Compat') ? document.documentElement : document.body;
			x = body.scrollLeft+event.clientX; //���݂̃}�E�X�ʒu��X���W
			y = body.scrollTop+event.clientY; //���݂̃}�E�X�ʒu��Y���W
			popOBJ.style.pixelLeft  = x + x_adjust; //�|�b�v�A�b�v�ʒu
			popOBJ.style.pixelTop  = y + y_adjust;

			if ((popOBJ.offsetTop + popOBJ.offsetHeight) > (body.scrollTop + body.clientHeight)) {
				popOBJ.style.pixelTop = body.scrollTop + body.clientHeight - popOBJ.offsetHeight -20;
			}
			if (popOBJ.offsetTop < body.scrollTop) {
				popOBJ.style.pixelTop = body.scrollTop -2;
			}

		} else if (document.getElementById) { //DOM�Ή��p�iMozilla�j
			x = ev.pageX; //���݂̃}�E�X�ʒu��X���W
			y = ev.pageY; //���݂̃}�E�X�ʒu��Y���W
			popOBJ.style.left = x + x_adjust + "px"; //�|�b�v�A�b�v�ʒu
			popOBJ.style.top = y + y_adjust + "px";
			//alert(window.pageYOffset);
			//alert(popOBJ.offsetTop);

			if ((popOBJ.offsetTop + popOBJ.offsetHeight) > (window.pageYOffset + window.innerHeight)) {
				popOBJ.style.top = window.pageYOffset + window.innerHeight - popOBJ.offsetHeight -20 + "px";
			}
			if (popOBJ.offsetTop < window.pageYOffset) {
				popOBJ.style.top = window.pageYOffset -2 + "px";
			}

		}
		popOBJ.style.visibility = "visible"; //���X�|�b�v�A�b�v�\��
	}
}

//==============================================================
// gHidePopUp -- �v��|�b�v�A�b�v���\���ɂ���֐�
//==============================================================

function gHidePopUp(divID)
{
	var popOBJ = document.getElementById(divID);
	if (popOBJ) {
		popOBJ.style.visibility = "hidden"; //���X�|�b�v�A�b�v��\��
	}
}
