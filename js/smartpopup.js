/* vim: set fileencoding=cp932 ai noet ts=4 sw=4 sts=4: */
/* mi: charset=Shift_JIS */

/* rep2expack - �X�}�[�g�|�b�v�A�b�v���j���[  */

var SPM = new Object();
var spmResNum     = new Number(); // �|�b�v�A�b�v�ŎQ�Ƃ��郌�X�ԍ�
var spmBlockID    = new String(); // �t�H���g�ύX�ŎQ�Ƃ���ID
var spmSelected   = new String(); // �I�𕶎�����ꎞ�I�ɕۑ�
var spmFlexTarget = new String(); // �t�B���^�����O���ʂ��J���E�C���h�E

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[�𐶐�����
 */
SPM.init = function(aThread)
{
	var threadId = aThread.objName;
	if (document.getElementById(threadId + '_spm')) {
		return false;
	}
	var opt = aThread.spmOption;

	// �|�b�v�A�b�v���j���[����
	var spm = document.createElement('div');
	spm.id = threadId + '_spm';
	spm.className = 'spm';
	SPM.setOnPopUp(spm, spm.id, false);

	// �R�s�y�p�t�H�[��
	spm.appendChild(SPM.createMenuItem('���X�R�s�[', (function(){SPM.invite(aThread)})));

	// ����Ƀ��X
	if (opt[1] == 1 || opt[1] == 2) {
		spm.appendChild(SPM.createMenuItem('����Ƀ��X', [aThread, 'post_form.php', 'inyou=' + (2 & opt[1]).toString()]));
		spm.appendChild(SPM.createMenuItem('���p���ă��X', [aThread, 'post_form.php', 'inyou=' + ((2 & opt[1]) + 1).toString()]));
	}

	// ���ځ[�񃏁[�h�ENG���[�h
	if (opt[2] == 1 || opt[2] == 2) {
		var abnId = threadId + '_ab';
		var ngId = threadId + '_ng';
		spm.appendChild(SPM.createMenuItem('���ځ[�񂷂�', [aThread, 'info_sp.php', 'mode=aborn_res']));
		spm.appendChild(SPM.createMenuItem('���ځ[�񃏁[�h', null, abnId));
		spm.appendChild(SPM.createMenuItem('NG���[�h', null, ngId));
		// �T�u���j���[����
		var spmAborn = SPM.createNgAbornSubMenu(abnId, aThread, 'aborn');
		var spmNg = SPM.createNgAbornSubMenu(ngId, aThread, 'ng');
	} else {
		var spmAborn = false, spmNg = false;
	}

	// �t�B���^�����O
	if (opt[3] == 1) {
		var filterId = threadId + '_fl';
		spm.appendChild(SPM.createMenuItem('�t�B���^�����O', null, filterId));
		// �T�u���j���[����
		var spmFilter = SPM.createFilterSubMenu(filterId, aThread);
	} else {
		var SpmFilter = false;
	}

	// �A�N�e�B�u���i�[
	if (opt[4] == 1) {
		spm.appendChild(SPM.createMenuItem('AA�p�t�H���g', (function(){activeMona(SPM.getBlockID())})));
	}

	// AAS
	if (opt[5] == 1) {
		spm.appendChild(SPM.createMenuItem('AAS', [aThread, 'aas.php']));
	}

	// �|�b�v�A�b�v���j���[���R���e���c�ɒǉ�
	document.body.appendChild(spm);

	// ���ځ[�񃏁[�h�E�T�u���j���[���R���e���c�ɒǉ�
	if (spmAborn) {
		document.body.appendChild(spmAborn);
	}
	// NG���[�h�E�T�u���j���[���R���e���c�ɒǉ�
	if (spmNg) {
		document.body.appendChild(spmNg);
	}
	// �t�B���^�����O�E�T�u���j���[���R���e���c�ɒǉ�
	if (spmFilter) {
		document.body.appendChild(spmFilter);
	}

	return false;
}

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[���|�b�v�A�b�v�\������
 */
SPM.show = function(aThread, resnum, resid, evt)
{
	var evt = (evt) ? evt : ((window.event) ? event : null);
	if (spmResNum != resnum || spmBlockID != resid) {
		SPM.hide(aThread);
	}
	spmResNum  = resnum;
	spmBlockID = resid;
	if (window.getSelection) {
		spmSelected = window.getSelection();
	} else if (document.selection) {
		spmSelected = document.selection.createRange().text;
	}
	showResPopUp(aThread.objName + "_spm" ,evt);
	return false;
}

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[��x���[���ŕ���
 */
SPM.hide = function(aThread)
{
	document.getElementById(aThread.objName + "_spm").style.visibility = "hidden";
	return false;
}

/**
 * �N���[�W������O���[�o���ϐ� spmBlockID ���擾���邽�߂̊֐�
 */
SPM.getBlockID = function()
{
	return spmBlockID;
}

/**
 * �N���b�N���Ɏ��s�����֐� (�|�b�v�A�b�v�E�C���h�E���J��) ��ݒ肷��
 */
SPM.setOnClick = function(obj, aThread, inUrl)
{
	var option = (arguments.length > 3) ? arguments[3] : '';
	obj.onclick = function(evt)
	{
		evt = (evt) ? evt : ((window.event) ? window.event : null);
		if (evt) {
			return SPM.openSubWin(aThread, inUrl, option);
		}
		return false;
	}
}

/**
 * �}�E�X�I�[�o�[/�A�E�g���Ɏ��s�����֐� (���j���[�̕\��/��\��) ��ݒ肷��
 */
SPM.setOnPopUp = function(obj, targetId, isSubMenu)
{
	// ���[���I�[�o�[
	obj.onmouseover = function(evt)
	{
		evt = (evt) ? evt : ((window.event) ? window.event : null);
		if (evt) {
			showResPopUp(targetId, evt);
		}
	}
	// ���[���A�E�g
	obj.onmouseout = function(evt)
	{
		evt = (evt) ? evt : ((window.event) ? window.event : null);
		if (evt) {
			hideResPopUp(targetId);
		}
	}
}

/**
 * �A���J�[�𐶐�����
 */
SPM.createMenuItem = function(txt)
{
	var anchor = document.createElement('a');
	anchor.href = 'javascript:void(null)';
	anchor.onclick = function() { return false; }
	anchor.appendChild(document.createTextNode(txt));

	// �N���b�N���ꂽ�Ƃ��̃C�x���g�n���h����ݒ�
	if (arguments.length > 1 && arguments[1] != null) {
		if (typeof arguments[1] === 'function') {
			anchor.onclick = arguments[1];
		} else {
			var aThread = arguments[1][0];
			var inUrl = arguments[1][1];
			var option = (arguments[1].length > 2) ? arguments[1][2] : '';
			SPM.setOnClick(anchor, aThread, inUrl, option);
		}
	}

	// �T�u���j���[���|�b�v�A�b�v����C�x���g�n���h����ݒ�
	if (arguments.length > 2 && arguments[2] != null) {
		SPM.setOnPopUp(anchor, arguments[2], true);
	}

	return anchor;
}

/**
 * ���ځ[��/NG�T�u���j���[�𐶐�����
 */
SPM.createNgAbornSubMenu = function(menuId, aThread, mode)
{
	var amenu = document.createElement('div');
	amenu.id = menuId;
	amenu.className = 'spm';
	SPM.setOnPopUp(amenu, amenu.id, true);

	amenu.appendChild(SPM.createMenuItem('���O', [aThread, 'info_sp.php', 'mode=' + mode + '_name']));
	amenu.appendChild(SPM.createMenuItem('���[��', [aThread, 'info_sp.php', 'mode=' + mode + '_mail']));
	amenu.appendChild(SPM.createMenuItem('�{��', [aThread, 'info_sp.php', 'mode=' + mode + '_msg']));
	amenu.appendChild(SPM.createMenuItem('ID', [aThread, 'info_sp.php', 'mode=' + mode + '_id']));

	return amenu;
}

/**
 * �t�B���^�����O�T�u���j���[�𐶐�����
 */
SPM.createFilterSubMenu = function(menuId, aThread)
{
	this.getOnClick = function(field, match)
	{
		return (function(evt){
			evt = (evt) ? evt : ((window.event) ? window.event : null);
			if (evt) { SPM.openFilter(aThread, field, match); }
		});
	}

	var fmenu = document.createElement('div');
	fmenu.id = menuId;
	fmenu.className = 'spm';
	SPM.setOnPopUp(fmenu, fmenu.id, true);

	fmenu.appendChild(SPM.createMenuItem('�������O', this.getOnClick('name', 'on')));
	fmenu.appendChild(SPM.createMenuItem('�������[��', this.getOnClick('mail', 'on')));
	fmenu.appendChild(SPM.createMenuItem('�������t', this.getOnClick('date', 'on')));
	fmenu.appendChild(SPM.createMenuItem('����ID', this.getOnClick('id', 'on')));
	fmenu.appendChild(SPM.createMenuItem('�قȂ閼�O', this.getOnClick('name', 'off')));
	fmenu.appendChild(SPM.createMenuItem('�قȂ郁�[��', this.getOnClick('mail', 'off')));
	fmenu.appendChild(SPM.createMenuItem('�قȂ���t', this.getOnClick('date', 'off')));
	fmenu.appendChild(SPM.createMenuItem('�قȂ�ID', this.getOnClick('id', 'off')));

	return fmenu;
}

/* ==================== �o������ ====================
 * <a href="javascript:void(0);" onclick="foo()">��
 * <a href="javascript:void(foo());">�Ɠ����ɓ����B
 * JavaScript��URI�𐶐�����Ƃ��A&��&amp;�Ƃ��Ă͂����Ȃ��B
 * ================================================== */

/**
 * URI�̏��������A�|�b�v�A�b�v�E�C���h�E���J��
 */
SPM.openSubWin = function(aThread, inUrl, option)
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
		if (aThread.spmOption[2] == 1) {
			popup = 2; // ���ځ[��/NG���[�h�o�^�̊m�F�����Ȃ��Ƃ�
		}
		if (option.indexOf("_msg") != -1 && spmSelected != '') {
			option += "&selected_string=" + encodeURIComponent(spmSelected);
		}
	} else if (inUrl == "post_form.php") {
		if (aThread.spmOption[1] == 2) {
			// inHeight = 450;
		}
		if (location.href.indexOf("/read_new.php?") != -1) {
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
	} else if (inUrl == "aas.php") {
		inWidth  = (aas_popup_width) ? aas_popup_width : 250;
		inHeight = (aas_popup_height) ? aas_popup_height : 330;
	}
	inUrl += "?host=" + aThread.host + "&bbs=" + aThread.bbs + "&key=" + aThread.key;
	inUrl += "&rescount=" + aThread.rc + "&ttitle_en=" + aThread.ttitle_en;
	inUrl += "&resnum=" + spmResNum + "&popup=" + popup;
	if (option != "") {
		inUrl += "&" + option;
	}
	OpenSubWin(inUrl, inWidth, inHeight, boolS, boolR);
	return true;
}

/**
 * URI�̏��������A�t�B���^�����O���ʂ�\������
 */
SPM.openFilter = function(aThread, field, match)
{
	var inUrl = "read_filter.php?bbs=" + aThread.bbs + "&key=" + aThread.key + "&host=" + aThread.host;
	inUrl += "&rescount=" + aThread.rc + "&ttitle_en=" + aThread.ttitle_en + "&resnum=" + spmResNum;
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

/**
 * �R�s�y�p�ɃX�������|�b�v�A�b�v���� (for SPM)
 */
SPM.invite = function(aThread)
{
	Invite(aThread.title, aThread.url, aThread.host, aThread.bbs, aThread.key, spmResNum);
}

// ����݊��̂��߁A�ꉞ
makeSPM = SPM.init;
showSPM = SPM.show;
closeSPM = SPM.hide;
