/* vim: set fileencoding=cp932 ai noet ts=4 sw=4 sts=4: */
/* mi: charset=Shift_JIS */

/* expack - ���[�U�ݒ�Ǘ��Ń^�u��ǉ����邽�߂�JavaScript */
/* �K��tabber.js�̌�ɓǂݍ��� */

var oldonload = window.onload;
window.onload = function() {
	// �E�C���h�E�̃^�C�g����ݒ�
	setWinTitle();
	if (!document.getElementsByTagName) {
		return;
	}

	// �Â� onload �C�x���g�n���h�� (=�^�u����) �����s
	if (typeof oldonload == 'function') {
		oldonload();
	}

	// �P�ڂ� 'tabbernav' �ɑ��M�E���Z�b�g�p�̃^�u��ǉ�����
	var tabs = document.getElementsByTagName('ul');
	for (var i = 0; i < tabs.length; i++) {
		if (tabs[i].className != 'tabbernav') {
			continue;
		}
		var targetForm = document.getElementById('edit_conf_user_form');

		// �u�ύX��ۑ�����v�^�u
		var saveTab = document.createElement('a');
		saveTab.appendChild(document.createTextNode('[�ύX��ۑ�����]'));
		saveTab.href = 'javascript:void(null);';
		saveTab.style.fontSize = '80%';
		saveTab.onclick = function() {
			if (window.confirm('�ݒ��ύX���Ă���낵���ł����H')) {
				var saveElem = document.createElement('input');
				saveElem.type = 'hidden';
				saveElem.name = 'submit_save';
				saveElem.value = 'true';
				targetForm.appendChild(saveElem);
				targetForm.submit();
			}
		}

		// �u�ύX���������v�^�u
		var resetTab = document.createElement('a');
		resetTab.appendChild(document.createTextNode('[�ύX��������]'));
		resetTab.href = 'javascript:void(null);';
		resetTab.style.fontSize = '80%';
		resetTab.onclick = function() {
			if (window.confirm('�ύX���������Ă���낵���ł����H�i�S�Ẵ^�u�̕ύX�����Z�b�g����܂��j')) {
				targetForm.reset();
			}
		}

		// �u�f�t�H���g�ɖ߂��v�^�u
		var defaultTab = document.createElement('a');
		defaultTab.appendChild(document.createTextNode('[�f�t�H���g�ɖ߂�]'));
		defaultTab.href = 'javascript:void(null);';
		defaultTab.style.fontSize = '80%';
		defaultTab.onclick = function() {
			if (window.confirm('���[�U�ݒ���f�t�H���g�ɖ߂��Ă���낵���ł����H�i��蒼���͂ł��܂���j')) {
				var defaultElem = document.createElement('input');
				defaultElem.type = 'hidden';
				defaultElem.name = 'submit_default';
				defaultElem.value = 'true';
				targetForm.appendChild(defaultElem);
				targetForm.submit();
			}
		}

		// �^�u��ǉ�
		tabs[i].appendChild(document.createElement('li')).appendChild(saveTab);
		tabs[i].appendChild(document.createElement('li')).appendChild(resetTab);
		tabs[i].appendChild(document.createElement('li')).appendChild(defaultTab);
		return;
	}
}
