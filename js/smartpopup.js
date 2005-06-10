/* p2 - �X�}�[�g�|�b�v�A�b�v���j���[JavaScript�t�@�C�� */

/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

var spmResNum     = new Number(); // �|�b�v�A�b�v�ŎQ�Ƃ��郌�X�ԍ�
var spmBlockID    = new String(); // �t�H���g�ύX�ŎQ�Ƃ���ID
var spmSelected   = new String(); // �I�𕶎�����ꎞ�I�ɕۑ�
var spmFlexTarget = new String(); // �t�B���^�����O���ʂ��J���E�C���h�E

// makeSPM -- �X�}�[�g�|�b�v�A�b�v���j���[�𐶐�����
function makeSPM(aThread, isClickOnOff)
{
	var thread_id = aThread.objName;
	var a_tag  = "<a href=\"javascript:void(spmOpenSubWin(" + thread_id + ",";
	var numbox = "";
	if (document.getElementById || document.all) {
		numbox = "<span id=\"" + thread_id + "_numbox\"></span>";
	}

	// �|�b�v�A�b�v���j���[�𐶐�

	document.writeln("<div id=\"" + thread_id + "_spm\" class=\"spm\"" + makeOnPopUp(thread_id+"_spm", false) + ">");
	// �w�b�_
	if (aThread.spmHeader != "") {
		if (isClickOnOff) {
			document.writeln("<a href=\"javascript:void(0);\" onclick=\"closeSPM(" + thread_id + ");\" class=\"closebox\">�~</a>");
		}
		document.write("<p>" + aThread.spmHeader.replace("resnum", numbox) + "</p>");
	}
	// �R�s�y�p�t�H�[��
	document.writeln("<a href=\"javascript:void(spmInvite(" + thread_id + "));\">���̃��X���R�s�y</a>");
	// ����Ƀ��X
	if (aThread.spmOption[1] == 1) {
		document.writeln(a_tag + "'post_form.php',''));\">����Ƀ��X</a>");
		document.writeln(a_tag + "'post_form.php','inyou=1'));\">���p���ă��X</a>");
	} else if (aThread.spmOption[1] == 2) {
		document.writeln(a_tag + "'post_form.php','inyou=2'));\">����Ƀ��X</a>");
		document.writeln(a_tag + "'post_form.php','inyou=3'));\">���p���ă��X</a>");
	}
	// ������
	if (aThread.spmOption[2] == 1) {
		document.writeln(a_tag + "'info_sp.php','mode=readhere'));\">�����܂œǂ�</a>");
	}
	// ���ځ[�񃏁[�h
	if (aThread.spmOption[3] == 1) {
		document.writeln(a_tag + "'info_sp.php','mode=aborn_res'));\">���ځ[�񂷂�</a>");
		document.writeln("<a href=\"javascript:void(0);\"" + makeOnPopUp(thread_id+"_ab", true) + ">���ځ[�񃏁[�h</a>");
	}
	// NG���[�h
	if (aThread.spmOption[4] == 1) {
		document.writeln("<a href=\"javascript:void(0);\"" + makeOnPopUp(thread_id+"_ng", true) + ">NG���[�h</a>");
	}
	// �A�N�e�B�u���i�[
	if (aThread.spmOption[5] == 1) {
		document.writeln("<a href=\"javascript:void(0);\"" + makeOnPopUp(thread_id+"_ds", true) + ">�t�H���g�ݒ�</a>");
	} else if (aThread.spmOption[5] == 2) {
		makeDynamicStyleSPM(thread_id+"_ds", false);
	}
	// �t�B���^�����O
	if (aThread.spmOption[6] == 1) {
		document.writeln("<a href=\"javascript:void(0);\"" + makeOnPopUp(thread_id+"_fl", true) + ">�t�B���^�����O</a>");
	}
	// �_���肢
	if (aThread.spmOption[7] == 1) {
		document.writeln(a_tag + "'tentori.php',''));\">���݂���������</a>");
	}
	// �N���[�Y���j���[
	if (aThread.spmHeader == "" && isClickOnOff) {
		document.writeln("<a href=\"javascript:void(0);\" onclick=\"closeSPM(" + thread_id + ");\" class=\"closemenu\">����</a>");
	}
	// �u���b�N�����
	document.writeln("</div>");

	// /�T�u���j���[�𐶐�

	// ���ځ[�񃏁[�h�E�T�u���j���[
	if (aThread.spmOption[3] == 1) {
		makeAbornSPM(thread_id+"_ab", a_tag);
	}
	// NG���[�h�E�T�u���j���[
	if (aThread.spmOption[4] == 1) {
		makeAbornSPM(thread_id+"_ng", a_tag);
	}
	// �t�H���g�ݒ�E�T�u���j���[
	if (aThread.spmOption[5] == 1) {
		makeDynamicStyleSPM(thread_id+"_ds", true);
	}
	// �t�B���^�����O�E�T�u���j���[
	if (aThread.spmOption[6] == 1) {
		makeFilterSPM(thread_id+"_fl", thread_id);
	}

	return false;
}


// makeOnPopUp -- �}�E�X�I�[�o�[/�A�E�g���Ɏ��s�����X�N���v�g�𐶐�����
function makeOnPopUp(popup_id, isSubMenu)
{
	// �x������
	var spmPopUpDelay = "delaySec=(0.3*1000);";
	if (isSubMenu) {
		spmPopUpDelay = "delaySec=0;";
	}
	// ���[���I�[�o�[
	var spmPopUpEvent  = " onmouseover=\"" + spmPopUpDelay + "showResPopUp('" + popup_id + "',event);\"";
	// ���[���A�E�g
		spmPopUpEvent += " onmouseout=\""  + spmPopUpDelay + "hideResPopUp('" + popup_id + "');\"";
	return spmPopUpEvent;
}


// makeAbornSPM -- ���ځ[��/NG�T�u���j���[�𐶐�����
function makeAbornSPM(menu_id, a_tag)
{
	var mode = "aborn";
	if (menu_id.substr(menu_id.lastIndexOf("_"), 3) == "_ng") {
		mode = "ng";
	}
	document.writeln("<div id=\"" + menu_id + "\" class=\"spm\"" + makeOnPopUp(menu_id, true) + ">");
	document.writeln(a_tag + "'info_sp.php','mode=" + mode + "_name'));\">���O</a>");
	document.writeln(a_tag + "'info_sp.php','mode=" + mode + "_mail'));\">���[��</a>");
	document.writeln(a_tag + "'info_sp.php','mode=" + mode + "_msg'));\">�{��</a>");
	document.writeln(a_tag + "'info_sp.php','mode=" + mode + "_id'));\">ID</a>");
	document.writeln("</div>");
}


// makeDynamicStyleSPM -- �t�H���g�ݒ�T�u���j���[�𐶐�����
function makeDynamicStyleSPM(menu_id, isSubMenu)
{
	var spmActiveMona  = "<div class=\"spmMona\">�@�i";
		spmActiveMona += "<a href=\"javascript:void(spmDynamicStyle('12px'));\">�L</a>";
		spmActiveMona += "<a href=\"javascript:void(spmDynamicStyle('14px'));\">��</a>";
		spmActiveMona += "<a href=\"javascript:void(spmDynamicStyle('16px'));\">�M</a>";
		spmActiveMona += "�j</div>";
	if (isSubMenu) {
		document.writeln("<div id=\"" + menu_id + "\" class=\"spm\"" + makeOnPopUp(menu_id, true) + ">");
		document.writeln(spmActiveMona);
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('normal'));\">�W���t�H���g</a>");
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('monospace'));\">�����t�H���g</a>");
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('larger'));\">�傫��</a>");
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('smaller'));\">������</a>");
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('rewind'));\">���ɖ߂�</a>");
		document.writeln("</div>");
	} else {
		document.writeln(spmActiveMona);
	}
}


// makeFilterSPM -- �t�B���^�����O�T�u���j���[�𐶐�����
function makeFilterSPM(menu_id, thread_id)
{
	var filter_anchor = "<a href=\"javascript:void(spmOpenFilter(" + thread_id;
	document.writeln("<div id=\"" + menu_id + "\" class=\"spm\"" + makeOnPopUp(menu_id, true) + ">");
	document.writeln(filter_anchor + ",'name','on'));\">�������O</a>");
	document.writeln(filter_anchor + ",'mail','on'));\">�������[��</a>");
	document.writeln(filter_anchor + ",'date','on'));\">�������t</a>");
	document.writeln(filter_anchor + ",'id','on'));\">����ID</a>");
	document.writeln(filter_anchor + ",'name','off'));\">�قȂ閼�O</a>");
	document.writeln(filter_anchor + ",'mail','off'));\">�قȂ郁�[��</a>");
	document.writeln(filter_anchor + ",'date','off'));\">�قȂ���t</a>");
	document.writeln(filter_anchor + ",'id','off'));\">�قȂ�ID</a>");
	document.writeln("</div>");
}


// showSPM -- �X�}�[�g�|�b�v�A�b�v���j���[���|�b�v�A�b�v�\������
function showSPM(aThread, resnum, resid, evt)
{
	var evt = (evt) ? evt : ((window.event) ? event : null);
	spmResNum  = resnum;
	spmBlockID = resid;
	if (window.getSelection) {
		spmSelected = window.getSelection();
	} else if (document.selection) {
		spmSelected = document.selection.createRange().text;
	}
	var numbox;
	if (numbox = document.getElementById(aThread.objName + "_numbox")) {
		numbox.innerHTML = resnum;
	}
	closeSPM(aThread);
	showResPopUp(aThread.objName + "_spm" ,evt);
	return false;
}


// makeSPM -- �X�}�[�g�|�b�v�A�b�v���j���[��x���[���ŕ���
function closeSPM(aThread)
{
	document.getElementById(aThread.objName + "_spm").style.visibility = "hidden";
	return false;
}


/* ==================== �o������ ====================
 * <a href="javascript:void(0);" onclick="foo()">��
 * <a href="javascript:void(foo());">�Ɠ����B
 * JavaScript��URI�𐶐�����Ƃ��A&��&amp;�Ƃ��Ă͂����Ȃ��B
 * ================================================== */


// spmOpenSubWin -- URI�̏��������A�|�b�v�A�b�v�E�C���h�E���J��
function spmOpenSubWin(aThread, inUrl, option)
{
	var inWidth  = 650; // �|�b�v�A�b�v�E�C���h�E�̕�
	var inHeight = 350; // �|�b�v�A�b�v�E�C���h�E�̍���
	var boolS = 1; // �X�N���[���o�[��\���ioff:0, on:1�j
	var boolR = 0; // �������T�C�Y�ioff:0, on:1�j
	var popup = 1; // �|�b�v�A�b�v�E�C���h�E���ۂ��ino:0, yes:1, yes&�^�C�}�[�ŕ���:2�j
	if (inUrl == "info_sp.php") {
		inWidth  = 480;
		inHeight = 240;
		boolS = 0;
		if (aThread.spmOption[0] == 0) {
			popup = 2; // ������,���ځ[��/NG���[�h�o�^�̊m�F�����Ȃ��Ƃ�
		}
		if (option.indexOf("_msg") != -1 && spmSelected != '') {
			option += "&selected_string=" + encodeURIComponent(spmSelected);
		}
	} else if (inUrl == "post_form.php") {
		if (aThread.spmOption[1] == 2) {
			// inHeight = 450;
		}
		if (read_new > 0) {
			if (option == "") {
				option = "from_read_new=1";
			} else {
				option += "&from_read_new=1";
			}
		}
	} else if (inUrl == "tentori.php") {
		inWidth  = 450;
		inHeight = 150;
		popup = 2;
	}
	inUrl += "?host=" + aThread.host + "&bbs=" + aThread.bbs + "&key=" + aThread.key;
	inUrl += "&rc=" + aThread.rc + "&ttitle_en=" + aThread.ttitle_en;
	inUrl += "&resnum=" + spmResNum + "&popup=" + popup;
	if (option != "") {
		inUrl += "&" + option;
	}
	OpenSubWin(inUrl, inWidth, inHeight, boolS, boolR);
	return true;
}


// spmOpenFilter -- URI�̏��������A�t�B���^�����O���ʂ�\������
function spmOpenFilter(aThread, field, match)
{
	var inUrl = "read_filter.php?bbs=" + aThread.bbs + "&key=" + aThread.key + "&host=" + aThread.host;
	inUrl += "&rc=" + aThread.rc + "&ttitle_en=" + aThread.ttitle_en + "&resnum=" + spmResNum;
	inUrl += "&ls=all&field=" + field + "&method=just&match=" + match + "&offline=1";
	
	switch (spmFlexTarget) {
		case "_self":
			location.href = inUrl;
			break;
		case "_parent":
			parent.location.href = inUrl;
			break;
		case "_top":
			top.location.href = inUrl;
			break;
		case "_blank":
			window.open(inUrl, "", "");
			break;
		default:
			if (parent.spmFlexTarget.location.href) {
				parent.spmFlexTarget.location.href = inUrl;
			} else {
				window.open(inUrl, spmFlexTarget, "")
			}
	}
	
	return true;
}

// spmDynamicStyle -- �ΏۃI�u�W�F�N�g��ݒ肵�A������ς���
function spmDynamicStyle(mode)
{
	var dsTarget     = document.getElementById(spmBlockID);
	var dsFontSize   = dsTarget.style.fontSize;
	var isAutoMona   = false;
	if (dsTarget.className == "AutoMona") {
		isAutoMona   = true;
	}
	var isPopUp      = false;
	if (spmBlockID.charAt(0) == "q") {
		isPopUp      = true;
	}
	// �Đݒ�
	if (dsFontSize.length   < 1) {
		if (isAutoMona) {
			dsFontSize = "14px";
		} else if (isPopUp) {
			dsFontSize = am_respop_fontSize;
		} else {
			dsFontSize = am_read_fontSize;
		}
	}
	// ����
	switch (mode) {
		// �A�N�e�B�u���i�[
		case "16px":
		case "14px":
		case "12px":
			activeMona(spmBlockID, mode);
			return true;
		// ���̃t�H���g�T�C�Y�ɖ߂�
		case "rewind":
			if (isQuoteBlock) {
				dsTarget.style.fontSize = am_respop_fontSize;
			} else {
				dsTarget.style.fontSize = am_read_fontSize;
			}
			// ���������W���t�H���g�ɂ���
		// �W���t�H���g�ɂ���
		case "normal":
			if (spmBlockID.charAt(0) == "q") {
				dsTarget.className = "NoMonaQ";
			} else {
				dsTarget.className = "NoMona";
			}
			return true;
		// �����t�H���g�ɂ���
		case "monospace":
			dsTarget.className = "spmMonoSpace";
			return true;
		// �t�H���g�T�C�Y��ς���
		case "larger":
		case "smaller":
			var newFontSize  = new Number;
			var curFontSize  = new Number(dsFontSize.match(/^\d+/));
			var FontSizeUnit = new String(dsFontSize.match(/\D+$/));
			if (mode == "larger") {
				newFontSize = curFontSize * 1.25;
			} else {
				newFontSize = curFontSize * 0.8;
			}
			dsTarget.style.fontSize   = newFontSize.toString() + FontSizeUnit;
			return true;
		// ...
		default:
			return false;
	}
}

// spmInvite -- �R�s�y�p�ɃX�������|�b�v�A�b�v���� (for SPM)
function spmInvite(aThread)
{
	Invite(aThread.title, aThread.url, aThread.host, aThread.bbs, aThread.key, spmResNum);
}
