/*
	rep2expack - DOM�𑀍삵��iPhone�ɍœK������
*/

var _IPHONE_JS_OLD_ONLOAD = window.onload;

// {{{ window.onload()

/*
 * iPhone�p�ɗv�f�𒲐�����
 */
window.onload = function(){
	if (_IPHONE_JS_OLD_ONLOAD) {
		_IPHONE_JS_OLD_ONLOAD();
	}

	// accesskey�����ƃL�[�ԍ��\�����폜
	var anchors = document.evaluate('.//a[@accesskey]', document.body, null, 7, null);
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

	// ���P�[�V�����o�[���B��
	if (!location.hash) {
		scrollTo(0, 0);
	}
};

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
	                                contextNode, null, 7, null);
	var re = new RegExp('^https?://(.+?@)?([^:/]+)');

	for (var i = 0; i < anchors.snapshotLength; i++) {
		var node = anchors.snapshotItem(i);
		var url = node.getAttribute('href');
		var m = re.exec(url);

		if (m && m[2] != location.host) {
			if (!node.onclick) {
				node.onclick = (function(url){
					return (function(){ return confirm('�O���T�C�g���J���܂�\n' + url); });
				})(url);
			}

			if (!node.hasAttribute('target')) {
				node.setAttribute('target', '_blank');
			}
		}
	}
}

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
