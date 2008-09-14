/*
 * rep2expack - DOM�𑀍삵��iPhone�ɍœK������
 */

// {{{ window.onload()

window.addEventListener('load', function(evt){
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
	document.body.addEventListener('orientationchange', function(evt){
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
	var anchors = document.evaluate('.//a[starts-with(@href, "http://") or starts-with(@href, "https://")]',
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
	if (elem.previousSibling.onclick) {
		elem.previousSibling.onclick();
	}
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
	if (elem.nextSibling.onclick) {
		elem.nextSibling.onclick();
	}
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
// {{{ make_textarea_smaller()

/*
 * textarea�̍���������������
 *
 * @param String id
 * @return void
 */
function make_textarea_smaller(id)
{
	var area = document.getElementById(id);
	if (!area) {
		return;
	}

	//var y = area.clientHeight;
	var rows = area.hasAttribute('rows') ? parseInt(area.getAttribute('rows')) : 3;
	rows = Math.max(rows - 1, 3);
	area.setAttribute('rows', rows.toString());
	//window.scrollBy(0, area.clientHeight - y);
}

// }}}
// {{{ make_textarea_larger()

/*
 * textarea�̍�����傫������
 *
 * @param String id
 * @return void
 */
function make_textarea_larger(id)
{
	var area = document.getElementById(id);
	if (!area) {
		return;
	}

	//var y = area.clientHeight;
	var rows = area.hasAttribute('rows') ? parseInt(area.getAttribute('rows')) : 3;
	rows = Math.max(rows + 1, 3);
	area.setAttribute('rows', rows.toString());
	//window.scrollBy(0, area.clientHeight - y);
}

// }}}
// {{{ enable_input_autocorrect()

/*
 * �t�H�[����autocorrect�̗L���E������؂�ւ���
 *
 * @param String id
 * @param Boolean id
 * @return void
 */
function enable_input_autocorrect(id, onoff)
{
	var elem = document.getElementById(id);
	if (!elem) {
		return;
	}

	elem.setAttribute('autocorrect', (onoff ? 'on' : 'off'));
}

// }}}
// {{{ change_link_target()

/*
 * �����N�^�[�Q�b�g��؂�ւ���
 *
 * @param String|Array expr
 * @param Boolean toggle
 * @param Element contextNode
 * @param String target
 * @return void
 */
function change_link_target(expr, toggle)
{
	var contextNode = (arguments.length > 2 && arguments[2]) ? arguments[2] : document.body;

	if (typeof expr != 'string') {
		var args = [toggle, contextNode];
		if (arguments.length > 3) {
			args.push(arguments[3]);
		}
		for (var i = 0; i < expr.length; i++) {
			args.unshift(expr[i]);
			change_link_target.apply(this, args);
			args.shift();
		}
		return;
	}

	var anchors = document.evaluate(expr,
	                                contextNode,
	                                null,
	                                XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
	                                null
	                                );

	if (toggle) {
		for (var i = 0; i < anchors.snapshotLength; i++) {
			anchors.snapshotItem(i).setAttribute('target', '_blank');
		}
	} else if (arguments.length > 3) {
		for (var i = 0; i < anchors.snapshotLength; i++) {
			anchors.snapshotItem(i).setAttribute('target', arguments[3]);
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
/* vim: set syn=javascript fenc=cp932 ai noet ts=4 sw=4 sts=4 fdm=marker: */
