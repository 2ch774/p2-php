/* vim: set fileencoding=cp932 ai noet ts=4 sw=4 sts=4: */
/* mi: charset=Shift_JIS */

// �ҏW���̓��e���e�𓮓I�v���r���[���邽�߂̊֐��Q

// ������
function DPInit()
{
	if (!dpreview_ok || !document.getElementById || !document.getElementById('dpreview').innerHTML) {
		dpreview_ok = 0;
		return;
	}
	DPSetName(document.getElementById('FROM').value);
	DPSetMail(document.getElementById('mail').value);
	DPSetMsg(document.getElementById('MESSAGE').value);
	DPSetDate();
}


// ���e�̃e�L�X�g��u������
function DPReplaceInnerText(elem, cont)
{
	if (typeof elem == 'string') {
		elem = document.getElementById(elem);
	}
	if (!elem.firstChild) {
		elem.appendChild(document.createTextNode(cont));
	} else {
		elem.replaceChild(document.createTextNode(cont), elem.firstChild);
	}
}


// ���O�����X�V����
function DPSetName(_value)
{
	if (!dpreview_ok) { return; }
	var dpname = '';
	if (_value.length == 0) {
		dpname = '���������񁗂��������ς�';
	} else {
		tp = _value.indexOf('#');
		if (tp != -1) {
			dpname = _value.substr(0, tp);
			DBSetTrip(_value.substr(tp + 1, 8));
		} else {
			dpname = _value;
			DPReplaceInnerText('dp_trip', '');
		}
	}
	DPReplaceInnerText('dp_name', dpname);
	DPSetDate();
}


// ���[�������X�V����
function DPSetMail(_value)
{
	if (!dpreview_ok) { return; }
	DPReplaceInnerText('dp_mail', _value);
	DPSetDate();
}


// �{�����X�V����
function DPSetMsg(_value)
{
	if (!dpreview_ok) { return; }
	DPReplaceInnerText('dp_msg', _value);
	DPSetDate();
}


// ���t���X�V����
function DPSetDate()
{
	if (!dpreview_ok) { return; }
	var now  = new Date();
	var year = now.getFullYear();
	var mon  = now.getMonth() + 1;
	var date = now.getDate();
	var hour = now.getHours();
	var min  = now.getMinutes();
	var sec  = now.getSeconds();
	var timestamp = year
		+ '/' + ((mon < 10) ? '0' + mon : mon)
		+ '/' + ((date < 10) ? '0' + date : date)
		+ ' ' + ((hour < 10) ? '0' + hour : hour)
		+ ':' + ((min < 10) ? '0' + min : min)
		+ ':' + ((sec < 10) ? '0' + sec : sec)
	DPReplaceInnerText('dp_date', timestamp);
}


// XMLHttpRequest��p���ăg���b�v��ݒ肷��
function DBSetTrip(tk)
{
	var objHTTP = getXmlHttp();
	if (!objHTTP) {
		DPReplaceInnerText('dp_trip', '��XMLHTTP Disabled.');
		return;
	}
	objHTTP.onreadystatechange = function() {
		if (objHTTP.readyState == 4) {
			DPReplaceInnerText('dp_trip', '��' + objHTTP.responseText);
		}
	}
	var uri = 'tripper.php?tk=' + encodeURIComponent(tk);
	objHTTP.open('GET', uri, true);
	objHTTP.send(null);
}


// XMLHttpRequest��p���ăg���b�v���擾����
function DBGetTrip(tk)
{
	var objHTTP = getXmlHttp();
	if (!objHTTP) {
		return '��XMLHTTP Disabled.';
	}
	var uri = 'tripper.php?tk=' + encodeURIComponent(tk);
	objHTTP.open('GET', uri, false);
	objHTTP.send(null);
	if ((objHTTP.status != 200 || objHTTP.readyState != 4) && !objHTTP.responseText) {
		return '��XMLHTTP Failed.';
	}
	return '��' + objHTTP.responseText;
}
