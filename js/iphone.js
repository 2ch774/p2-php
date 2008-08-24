/*
 * rep2expack - DOM�𑀍삵��iPhone�ɍœK������
 */

// {{{ GLOBALS

var _IPHONE_JS_OLD_ONLOAD = window.onload;

// }}}
// {{{ window.onload()

/*
 * iPhone�p�ɗv�f�𒲐�����
 *
 * @return void
 */
window.onload = (function(){
	if (_IPHONE_JS_OLD_ONLOAD) {
		_IPHONE_JS_OLD_ONLOAD();
	}

	// accesskey�����ƃL�[�ԍ��\�����폜
	var anchors = document.evaluate('.//a[@accesskey]',
	                                document.body,
	                                null,
	                                XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
	                                null
	                                );
	var re = new RegExp('^[0-9#*]\\.');

	for (var i = 0; i < anchors.snapshotLength; i++) {
		var node = anchors.snapshotItem(i);
		var txt = node.firstChild;

		if (txt && txt.nodeType == 3 && re.test(txt.nodeValue)) {
			// TOP�ւ̃����N���{�^����
			if (txt.nodeValue == '0.TOP') {
				node.className = 'button';
				if (node.parentNode.childNodes.length == 1) {
					node.parentNode.style.textAlign = 'center';
				} else if (node.parentNode == document.body) {
					var container = document.createElement('div');
					container.style.textAlign = 'center';
					document.body.insertBefore(container, node);
					document.body.removeChild(node);
					container.appendChild(node);
				}
			}

			// �L�[�ԍ��\�����폜
			txt.nodeValue = txt.nodeValue.replace(re, '');
		}

		// accceskey�������폜
		node.removeAttribute('accesskey');
	}

	// �O�������N������������
	rewrite_external_link(document.body);

	// textarea�̕��𒲐�
	adjust_textarea_size();

	// ��]���̃C�x���g�n���h����ݒ�
	document.body.onorientationchange = (function(){
		adjust_textarea_size();
	});

	// ���P�[�V�����o�[���B��
	if (!location.hash) {
		scrollTo(0, 0);
	}
});

// }}}
// {{{ rewrite_external_link()

/*
 * �O�������N���m�F���Ă���V�����^�u�ŊJ���悤�ɕύX����
 *
 * @param Element contextNode
 * @return void
 */
function rewrite_external_link(contextNode)
{
	var anchors = document.evaluate('.//a[@href and starts-with(@href, "http")]',
	                                contextNode,
	                                null,
	                                XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
	                                null
	                                );
	var re = new RegExp('^https?://(.+?@)?([^:/]+)');

	for (var i = 0; i < anchors.snapshotLength; i++) {
		var node = anchors.snapshotItem(i);
		var url = node.getAttribute('href');
		var m = re.exec(url);

		if (m && m[2] != location.host) {
			if (!node.onclick) {
				node.onclick = confirm_open_external_link;
			}

			if (!node.hasAttribute('target')) {
				node.setAttribute('target', '_blank');
			}
		}
	}
}

// }}}
// {{{ confirm_open_external_link()

/*
 * �O���T�C�g���J�����ǂ������m�F����
 *
 * @return Boolean
 */
function confirm_open_external_link()
{
	return confirm('�O���T�C�g���J���܂���?\n' + this.href);
}

// }}}
// {{{ check_prev()

/*
 * �O��checkbox�v�f���g�O������B�^��label����
 *
 * @param Element elem
 * @return void
 */
function check_prev(elem)
{
	elem.previousSibling.checked = !elem.previousSibling.checked;
}

// }}}
// {{{ check_next()

/*
 * ����checkbox�v�f���g�O������B�^��label����
 *
 * @return void
 */
function check_next(elem)
{
	elem.nextSibling.checked = !elem.nextSibling.checked;
}

// }}}
// {{{ adjust_textarea_size()

/*
 * textarea�̕����ő剻����
 *
 * @return void
 */
function adjust_textarea_size()
{
	var areas = document.body.getElementsByTagName('textarea');

	for (var i = 0; i < areas.length; i++) {
		var width = areas[i].parentNode.clientWidth;
		if (width > 100) {
			width -= 12; // (borderWidth + padding) * 2
			if (width > 480) {
				width = 480; // maxWidth
			}
			areas[i].style.width = width.toString() + 'px';
		}
	}
}

// }}}
// {{{ change_link_target()

/*
 * �����N�^�[�Q�b�g��؂�ւ���
 *
 * @param String expr
 * @param Boolean toggle
 * @return void
 */
function change_link_target(expr, toggle)
{
	var anchors = document.evaluate(expr,
	                                document.body,
	                                null,
	                                XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
	                                null
	                                );

	if (toggle) {
		for (var i = 0; i < anchors.snapshotLength; i++) {
			anchors.snapshotItem(i).setAttribute('target', '_blank');
		}
	} else {
		for (var i = 0; i < anchors.snapshotLength; i++) {
			anchors.snapshotItem(i).removeAttribute('target');
		}
	}
}

// }}}
// {{{ override event object

/*
 * Event�I�u�W�F�N�g��X���W�𓾂郁�\�b�h��Y���W�𓾂郁�\�b�h��ǉ�����
 * iPhone/iPod Touch��Safari�ȊO�ł͕K�v�ɉ����Ă����̃��\�b�h���㏑������
 */
Event.prototype.getOffsetX = (function(){ return this.pageX; });
Event.prototype.getOffsetY = (function(){ return this.pageY; });

// }}}

/*
 * Local Variables:
 * mode: javascript
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: t
 * End:
 */
/* vim: set syn=css fenc=cp932 ai noet ts=4 sw=4 sts=4 fdm=marker: */
