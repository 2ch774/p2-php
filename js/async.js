/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */
/*
	expack - ���X�̔񓯊��ǂݍ���JavaScript�t�@�C��
	�C�x���g���X�i�̂Ƃ���̓I���C���[��JavaScript & DHTML�N�b�N�u�b�N���Q�l�ɂ����B
*/

/**
 * XMLHttpRequest�œ���̃��X��ǂݍ��ނ��߂̃��N�G�X�g�𑗐M����
 *
 * ���[�U�̎g�����ɂ���Ă͕����̃��N�G�X�g�������ɑ��M���ꂤ��̂�
 * ���N�G�X�g���Ƃ�XMLHttpRequest�I�u�W�F�N�g���쐬����B
 * �i�ЂƂ̃I�u�W�F�N�g�ł�낤�Ƃ���ƃG���[�j
 */
function asyncLoad(host, bbs, key, resnum, q, targetId)
{
	var uri = 'read_async.php?host='+host+'&bbs='+bbs+'&key='+key+'&ls='+resnum+'n&q='+q+'&offline=1';

	var req = getXmlHttp();
	if (!req) {
		alert('XMLHttp not available.');
		return;
	}

	var receiver;
	if (typeof targetId == 'string') {
		receiver = document.getElementById(targetId);
	} else {
		receiver = targetId;
	}
	if (!receiver) {
		alert('asyncLoad() Error: A target element not exists.');
		return;
	}
	receiver.innerHTML = 'Now Loading...';

	// ���X�|���X���Ԃ��Ă���O�ɂ��̊֐��𔲂��邽�߂ɔ񓯊����[�h�ɂ���B
	// ���N�G�X�g���Ƃ�XMLHttpRequest�I�u�W�F�N�g���쐬����A���ʂ�\��������I�u�W�F�N�g���ς��̂ŁA
	// �n���h���ɂ͐�p�̖����֐����쐬����B
	req.onreadystatechange = function() {
		try {
			// �񓯊����N�G�X�g�̂Ƃ��Areq.readyState�̃`�F�b�N���O��req.status�𒲂ׂ悤�Ƃ����
			// �G���[�iNS_ERROR_NOT_AVAILABLE�j���N����B
			if (req.readyState == 4) {
				if (req.status == 200) {
					// Safari(KHTML?)�ł�encoding��������XML�錾�������ƕ�����������̂�
					// �����������邽�߂ɓ��ɕt����XML�錾���폜���Ă���HTML�R�[�h�Ƃ��đ���B
					receiver.innerHTML = req.responseText.replace(/^<\?xml .+?\?>\n?/, '');
				} else {
					receiver.innerHTML = '<em>HTTP Error:<br />' + req.status + ' ' + req.statusText + '</em>';
				}
			}
		} catch (e) {
			var msg = (typeof e == 'string') ? e : ((e.message) ? e.message : 'Unknown Error');
			alert("XMLHttpRequest Error:\n" + msg);
		}
	};

	req.open('get', uri, true);
	req.send(null);
}

/**
 * ���X���e��ǂݍ���
 */
function loadRes(asyncObj, resnum)
{
	alert("function 'loadRes' is not available.");
}

/**
 * ���X���e��ǂݍ��݁A�\�ߗp�ӂ��Ă������v�f�ƒu��������
 */
function loadResBody(asyncObj, resnum)
{
	var resBodyId = 'rb' + resnum + 'of' + asyncObj.key;
	var resButtonId = 'rbr' + resnum + 'of' + asyncObj.key;

	if (document.getElementById(resBodyId)) {
		return;
	}

	var btn = document.getElementById(resButtonId);
	if (!btn) {
		alert("loadResBody Error: A target element '" + resButtonId + "' not exists.");
		return;
	}

	var resBody = document.createElement('div');
	resBody.id = resBodyId;

	btn.parentNode.replaceChild(resBody, btn);

	asyncLoad(asyncObj.host, asyncObj.bbs, asyncObj.key, resnum, 0, resBodyId);
}

/**
 * ���X�|�b�v�A�b�v��ǂݍ���
 */
function loadResPopUp(asyncObj, resnum)
{
	var qResId = 'q' + resnum + 'of' + asyncObj.key;
	if (document.getElementById(qResId)) {
		return;
	}
	var container = document.getElementById('popUpContainer');
	if (!container) {
		alert("Element 'popUpContainer' not exists.");
		return;
	}

	var qResPopUp = document.createElement('div');
	// id��class��DOM�̃v���p�e�B����`����Ă���̂ŁAsetAttribute()�����Ƀv���p�e�B������������B
	// ��IE�ł�setAttribute()��class��ݒ肵�Ă�CSS�����f����Ȃ���className�Őݒ肷��Δ��f�����B
	qResPopUp.id = qResId;
	qResPopUp.className = 'respopup';

	// �C�x���g���X�i��ݒ�
	// DOM2
	if (qResPopUp.addEventListener) {
		qResPopUp.addEventListener('mouseover', showResPopUpListener, false);
		qResPopUp.addEventListener('mouseout', hideResPopUpListener, false);
	// old
	} else {
		qResPopUp.onmouseover = showResPopUpListener;
		qResPopUp.onmouseout = hideResPopUpListener;
	}

	container.appendChild(qResPopUp);

	asyncLoad(asyncObj.host, asyncObj.bbs, asyncObj.key, resnum, 1, qResId);
}

/**
 * >>1-10�̂悤�ȃ��X�͈͎w����ʂɃ|�b�v�A�b�v����R���e�i�𐶐�����
 */
function makeRangeResPopUp(asyncObj, fromNum, toNum)
{
	var rangeResPopId = 'rp' + fromNum + 'to' + toNum + 'of' + asyncObj.key;
	if (document.getElementById(rangeResPopId)) {
		return;
	}
	var container = document.getElementById('popUpContainer');
	if (!container) {
		alert("Element 'popUpContainer' not exists.");
		return;
	}

	var rangeResPopUp = document.createElement('div');
	rangeResPopUp.id = rangeResPopId;
	rangeResPopUp.className = 'respopup';
	rangeResPopUp.style.lineHeight = '150%';

	// �C�x���g���X�i��ݒ�
	// DOM2
	if (rangeResPopUp.addEventListener) {
		rangeResPopUp.addEventListener('mouseover', showResPopUpListener, false);
		rangeResPopUp.addEventListener('mouseout', hideResPopUpListener, false);
	// old
	} else {
		rangeResPopUp.onmouseover = showResPopUpListener;
		rangeResPopUp.onmouseout = hideResPopUpListener;
	}

	// �e���X���|�b�v�A�b�v�����郊���N�i+���s�j��}��
	for (var i = fromNum; i <= toNum; i++) {
		rangeResPopUp.appendChild(makeResPopUpElement(asyncObj, i));
		if (i < toNum) {
			rangeResPopUp.appendChild(document.createElement('br'));
		}
	}

	container.appendChild(rangeResPopUp);
}

/**
 * �w�背�X�ԍ���񓯊��|�b�v�A�b�v������a�v�f�����
 */
function makeResPopUpElement(asyncObj, resnum, inString)
{
	var qResPopId = 'q' + resnum + 'of' + asyncObj.key;
	var url = asyncObj.readPhp + '?host=' + asyncObj.host + '&bbs=' + asyncObj.bbs + '&key=' + asyncObj.key + '&ls=' + resnum + 'n' + '&offline=1';

	var elem = document.createElement('a');
	if (inString) {
		elem.innerHTML = inString;
	} else {
		elem.innerHTML = '&gt;&gt;' + resnum;
	}

	elem.setAttribute('href', url);
	if (asyncObj.readTarget) {
		elem.setAttribute('target', asyncObj.readTarget);
	}

	// �|�b�v�A�b�v�\��/��\������v�f���������g�ł͂Ȃ��̂�
	// �Ώۗv�f��ID�𖄂ߍ��񂾖����֐��Ƃ��ăC�x���g���X�i���쐬
	var mouseOverListener = function(evt) {
		var evt = (evt) ? evt : ((window.event) ? event : null);
		loadResPopUp(asyncObj, resnum);
		showResPopUp(qResPopId, evt);
	};
	var mouseOutListener = function() {
		hideResPopUp(qResPopId);
	};

	// �C�x���g���X�i��ݒ�
	// DOM2
	if (elem.addEventListener) {
		elem.addEventListener('mouseover', mouseOverListener, false);
		elem.addEventListener('mouseout', mouseOutListener, false);
	// old
	} else {
		elem.onmouseover = mouseOverListener;
		elem.onmouseout = mouseOutListener;
	}

	return elem;
}

/**
 * ���X�|�b�v�A�b�v�\���i�ێ��j�p�C�x���g���X�i
 */
function showResPopUpListener(evt)
{
	var evt = (evt) ? evt : ((window.event) ? event : null);
	if (evt) {
		var tgt = (evt.currentTarget) ? evt.currentTarget : ((evt.srcElement) ? evt.srcElement : null);
		var tgtId = getResPopUpId(tgt, 0);
		if (tgtId) {
			showResPopUp(tgtId, evt);
		}
	}
}

/**
 * ���X�|�b�v�A�b�v��\���p�C�x���g���X�i
 */
function hideResPopUpListener(evt)
{
	var evt = (evt) ? evt : ((window.event) ? event : null);
	if (evt) {
		var tgt = (evt.currentTarget) ? evt.currentTarget : ((evt.srcElement) ? evt.srcElement : null);
		if (tgt && tgt.id && tgt.className == 'respopup') {
			hideResPopUp(tgt.id);
		}
	}
}

/**
 * ���X�|�b�v�A�b�vID���擾����
 *
 * IE�Łi�񓯊��́j���X�|�b�v�A�b�v�̎q�v�f�ɃJ�[�\�����d�˂��Ƃ��ɂ�
 * ���X�|�b�v�A�b�v���ێ������悤�ɂ��邽�߂ɕK�v
 * �^�O��onmouseover������DOM��onmouserover�v���p�e�B�ň����ɈႢ������̂��ȁH
 */
function getResPopUpId(tgt, repeat)
{
	var repeat = (typeof repeat == 'number') ? repeat : 0;
	if (repeat > 10) {
		return false;
	}
	if (tgt && tgt.id && tgt.className == 'respopup') {
		return tgt.id;
	} else if (tgt.parentNode) {
		return getResPopUpId(tgt.parentNode, repeat + 1);
	} else {
		return false;
	}
}
