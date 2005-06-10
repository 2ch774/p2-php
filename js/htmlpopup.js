/* p2 - HTML���|�b�v�A�b�v���邽�߂�JavaScript */

/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

//showHtmlDelaySec = 0.2 * 1000; //HTML�\���f�B���C�^�C���B�}�C�N���b�B

showHtmlTimerID = 0;
node_div = false;
node_close = false;
tUrl = ""; //URL�e���|�����ϐ�
gUrl = ""; //URL�O���[�o���ϐ�
rUrl = ""; //���t�@����URL�ϐ�
gX = 0;
gY = 0;
ecX = 0;
ecY = 0;

//==============================================================
// showHtmlPopUp -- HTML�v�A�b�v��\������֐�
// ���p���X�Ԃ� onMouseover �ŌĂяo�����
//==============================================================

function showHtmlPopUp(url,ev,showHtmlDelaySec)
{
	if (!document.createElement) { return; } // DOM��Ή�

	// �܂� onLoad ����Ă��Ȃ��A�R���e�i���Ȃ���΁A������
	if (!gIsPageLoaded && !document.getElementById('popUpContainer')) {
		return;
	}

	showHtmlDelaySec = showHtmlDelaySec * 1000;

	if (!node_div || url != gUrl) {
		tUrl = url;
		gX = ev.pageX;
		gY = ev.pageY;
		if(document.all){ // IE
			ecX = event.clientX;
			ecY = event.clientY;
		}
		showHtmlTimerID = setTimeout("showHtmlPopUpDo()", showHtmlDelaySec); // HTML�\���f�B���C�^�C�}�[
	}
}

function showHtmlPopUpDo()
{
	hideHtmlPopUp();

	gUrl = tUrl;
	var x_adjust = 7; // x���ʒu����
	var y_adjust = -46; // y���ʒu����
	var closebox_width = 18;

	if (gUrl.indexOf("kanban.php?") != -1) { x_adjust += 23; }
	var iv = 0;
	if (document.URL.indexOf("iv.php") != -1) {
		if (ivIframeWidth > 0 && ivIframeHeight > 0) {
			iv = 1;
			x_adjust += 43;
		} else {
			iv = 50;
		}
	}

	if(! node_div){
		node_div = document.createElement('div');
		node_div.id = "iframespace";

		node_close=document.createElement('div');
		node_close.setAttribute('id', "closebox");
		//node_close.setAttribute('onMouseover', "hideHtmlPopUp()");

		// IE�p
		if (document.all) {
			var body = (document.compatMode=='CSS1Compat') ? document.documentElement : document.body;
			gX = body.scrollLeft + ecX; // ���݂̃}�E�X�ʒu��X���W
			gY = body.scrollTop + ecY; // ���݂̃}�E�X�ʒu��Y���W
			var cX = gX + x_adjust - closebox_width;
			if (iv == 1) {
				var yokohaba = ivIframeWidth;
				gX = Math.min((gX + x_adjust), Math.max((body.clientWidth - yokohaba - 20), 50));
				node_div.style.left = gX;
				node_close.style.left = gX - closebox_width;
				var tatehaba = ivIframeHeight;
				if ((body.clientHeight - tatehaba) > 20) {
					node_div.style.top = Math.min(Math.max((gY - 50), (body.scrollTop + 50)),
						(body.scrollTop + (body.clientHeight - tatehaba - 20)));
				} else {
					node_div.style.top = Math.max((gY - 50), (body.scrollTop + 50));
				}
				node_close.style.top = node_div.style.top;
			} else {
				node_div.style.pixelLeft  = gX + x_adjust; // �|�b�v�A�b�v�ʒu
				node_close.style.pixelLeft  = cX; // �|�b�v�A�b�v�ʒu
				node_div.style.pixelTop  = body.scrollTop + iv; // gY + y_adjust;
				node_close.style.pixelTop  = body.scrollTop + iv; // gY + y_adjust;
				var yokohaba = body.clientWidth - node_div.style.pixelLeft - 20; //�������t
				var tatehaba = body.clientHeight - 20 - iv;
			}
		
		// DOM�Ή��p�iMozilla�j
		} else if(document.getElementById) {
			var cX = gX + x_adjust - closebox_width;
			if (iv == 1) {
				var yokohaba = ivIframeWidth;
				gX = Math.min((gX + x_adjust), Math.max((window.innerWidth - yokohaba - 20), 50));
				node_div.style.left = gX + "px";
				node_close.style.left = (gX - closebox_width) + "px";
				var tatehaba = ivIframeHeight;
				if ((window.innerHeight - tatehaba) > 20) {
					node_div.style.top = Math.min(Math.max((gY - 50), (window.pageYOffset + 50)),
						(window.pageYOffset + (window.innerHeight - tatehaba - 20))) + "px";
				} else {
					node_div.style.top = Math.max((gY - 50), (window.pageYOffset + 50)) + "px";
				}
				node_close.style.top = node_div.style.top;
			} else {
				var yokohaba = window.innerWidth - gX - x_adjust -20; // �������t
				node_div.style.left = (gX + x_adjust) + "px"; // �|�b�v�A�b�v�ʒu
				node_close.style.left = cX + "px"; // �|�b�v�A�b�v�ʒu
				var tatehaba = window.innerHeight - 20 - iv;
				node_div.style.top = window.pageYOffset + iv + "px"; //gY + y_adjust + "px";
				node_close.style.top = window.pageYOffset + iv + "px"; //gY + y_adjust + "px";
			}
		}

		pageMargin = "";
		// �摜�̏ꍇ�̓}�[�W�����[����
		if( gUrl.match(/(jpg|jpeg|gif|png)$/) ){
			pageMargin=" marginheight=\"0\" marginwidth=\"0\" hspace=\"0\" vspace=\"0\"";
		}
		node_div.innerHTML = "<iframe src=\""+gUrl+"\" frameborder=\"1\" border=\"1\" style=\"background-color:#fff;\" width=" + yokohaba + " height=" + tatehaba + pageMargin +">&nbsp;</iframe>";

		node_close.innerHTML = "<b onMouseover=\"hideHtmlPopUp()\">�~</b>";

		var popUpContainer = document.getElementById("popUpContainer");
		if (popUpContainer) {
			popUpContainer.appendChild(node_div);
			popUpContainer.appendChild(node_close);
		} else {
			document.body.appendChild(node_div);
			document.body.appendChild(node_close);
		}
	}
}

//==============================================================
// hideHtmlPopUp -- HTML�|�b�v�A�b�v���\���ɂ���֐�
// ���p���X�Ԃ��� onMouseout �ŌĂяo�����
//==============================================================

function hideHtmlPopUp()
{
	if (! document.createElement) { return; } // DOM��Ή�
	if (showHtmlTimerID) { clearTimeout(showHtmlTimerID); } // HTML�\���f�B���C�^�C�}�[������
	if (node_div) {
		node_div.style.visibility = "hidden";
		node_div.parentNode.removeChild(node_div);
		node_div = false;
	}
	if (node_close) {
		node_close.style.visibility = "hidden";
		node_close.parentNode.removeChild(node_close);
		node_close = false;
	}
}

//==============================================================
// HTML�\���^�C�}�[����������֐�
//==============================================================
function offHtmlPopUp()
{
	if (showHtmlTimerID) {
		clearTimeout(showHtmlTimerID); // HTML�\���f�B���C�^�C�}�[������
	}
}

