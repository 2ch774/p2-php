/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */
/*
	expack - �c���[�\���֘AJavaScript�t�@�C��
*/

var nodeIdRegExp = new RegExp('^([a-z]+)(\\d+)of(\\d+)$');

/**
 * ���X���e��\��������B�����肷��
 */
function showHideNode(asyncObj, nodeId, loadBody, evt)
{
	var resId = nodeIdRegExp.exec(nodeId);
	if (resId == null || resId[1] != 'content') {
		return;
	}
	var nodeContent = document.getElementById(nodeId);
	if (!nodeContent) {
		return;
	}

	var evt = (evt) ? evt : ((window.event) ? event : null);

	if (nodeContent.style.display == 'none') {
		if (evt && evt.altKey) {
			showNodeRecursive(asyncObj, nodeId, loadBody);
		} else {
			showNode(asyncObj, nodeId, loadBody);
		}
	} else {
		if (evt && evt.altKey) {
			hideNodeRecursive(asyncObj, nodeId);
		} else {
			hideNode(asyncObj, nodeId);
		}
	}
}

/**
 * ���X���e��\������
 */
function showNode(asyncObj, nodeId, loadBody)
{
	var resId = nodeIdRegExp.exec(nodeId);
	if (resId == null || resId[1] != 'content') {
		return;
	}
	var nodeContent = document.getElementById(nodeId);
	if (!nodeContent) {
		return;
	}

	var nodeOpener = document.getElementById('opener'+resId[2]+'of'+resId[3]);
	var resnum = resId[2];

	nodeContent.style.display = 'block';
	nodeOpener.innerHTML = nodeOpener.innerHTML.replace(/^./, '-');
	if (loadBody) {
		loadResBody(asyncObj, resnum);
		/*if (document.getElementById('childrenOf'+nodeId)) {
			loadResPopUp(asyncObj, resnum);
		}*/
	}
}

/**
 * ���X���e���B��
 */
function hideNode(asyncObj, nodeId)
{
	var resId = nodeIdRegExp.exec(nodeId);
	if (resId == null || resId[1] != 'content') {
		return;
	}
	var nodeContent = document.getElementById(nodeId);
	if (!nodeContent) {
		return;
	}

	var nodeOpener = document.getElementById('opener'+resId[2]+'of'+resId[3]);

	nodeContent.style.display = 'none';
	nodeOpener.innerHTML = nodeOpener.innerHTML.replace(/^./, '+');
}

/**
 * ���X���e��\�����A�q���X�̓��e���ċA�I�ɕ\������
 */
function showNodeRecursive(asyncObj, nodeId, loadBody)
{
	showNode(asyncObj, nodeId, loadBody);

	var resId = nodeIdRegExp.exec(nodeId);
	if (resId == null || resId[1] != 'content') {
		return;
	}

	var children_container = document.getElementById('children'+resId[2]+'of'+resId[3]);
	if (children_container && children_container.hasChildNodes()) {
		var re = new RegExp('^content(\\d+)$');
		for (var i = 0; i < children_container.childNodes.length; i++) {
			// �^�O�ƃ^�O�̊Ԃ̕�����i�z���C�g�X�y�[�X�܂ށj���Ɨ������q�m�[�h�Ƃ��Ĉ�����̂Œ���
			var c = children_container.childNodes[i];
			if (!c.id) {
				continue;
			}
			var cid = nodeIdRegExp.exec(c.id);
			if (cid != null && cid[1] == 'content') {
				showNodeRecursive(asyncObj, c.id, loadBody);
			}
		}
	}
}

/**
 * ���X���e���B���A�q���X�̓��e���ċA�I�ɉB��
 */
function hideNodeRecursive(asyncObj, nodeId)
{
	hideNode(asyncObj, nodeId);

	var resId = nodeIdRegExp.exec(nodeId);
	if (resId == null || resId[1] != 'content') {
		return;
	}

	var children_container = document.getElementById('children'+resId[2]+'of'+resId[3]);
	if (children_container && children_container.hasChildNodes()) {
		var re = new RegExp('^content(\\d+)$');
		for (var i = 0; i < children_container.childNodes.length; i++) {
			// �^�O�ƃ^�O�̊Ԃ̕�����i�z���C�g�X�y�[�X�܂ށj���Ɨ������q�m�[�h�Ƃ��Ĉ�����̂Œ���
			var c = children_container.childNodes[i];
			if (!c.id) {
				continue;
			}
			var cid = nodeIdRegExp.exec(c.id);
			if (cid != null && cid[1] == 'content') {
				hideNodeRecursive(asyncObj, c.id);
			}
		}
	}
}

/**
 * �e���X�i�̖{���ȊO�j���ċA�I�ɕ\������
 */
function showAncestors(asyncObj, ancestors)
{
	for (var i = 0; i <= ancestors.length; i++) {
		var nodeId = 'content' + ancestors[i];
		var obj = document.getElementById(nodeId)
		if (obj && obj.style.display == 'none') {
			showNode(asyncObj, nodeId, 0);
		}
	}
}
