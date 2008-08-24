/*
	rep2expack - iPhone�p���X�|�b�v�A�b�v
*/

// {{{ globals

var _RESPOPUP_IPHONE_JS_HASH = new Object();
var _RESPOPUP_IPHONE_JS_INDEX = 0;

// }}}
// {{{ _irespopup_get_z_index()

/*
 * z-index�ɐݒ肷��l��Ԃ�
 *
 * css/ic2_iphone.css �� div#ic2-info �� z-index �� 999 ��
 * �Œ肳��Ă���̂Ń|�b�v�A�b�v���J��Ԃ��ƕs�������B
 * �|�b�v�A�b�v�I�u�W�F�N�g�� z-index ���W���Ǘ�����K�v����B
 *
 * @param Element obj
 * @return String
 */
function _irespopup_get_z_index(obj)
{
	return (10 + _RESPOPUP_IPHONE_JS_INDEX).toString();
}

// }}}
// {{{ _irespopup_make_activate()

/*
 * �I�u�W�F�N�g���őO�ʂɈړ�����֐���Ԃ�
 *
 * @param Element obj
 * @return void
 */
function _irespopup_make_activate(obj)
{
	return (function(){
		_RESPOPUP_IPHONE_JS_INDEX++;
		obj.style.zIndex = _irespopup_get_z_index();
	});
}

// }}}
// {{{ _irespopup_make_deactivate()

/*
 * DOM�c���[����I�u�W�F�N�g����菜���֐���Ԃ�
 *
 * @param Element obj
 * @param Strin key 
 * @return void
 */
function _irespopup_make_deactivate(obj, key)
{
	return (function(){
		delete _RESPOPUP_IPHONE_JS_HASH[key];
		obj.parentNode.removeChild(obj);
		delete obj;
	});
}

// }}}
// {{{ iResPopUp()

/*
 * iPhone�p���X�|�b�v�A�b�v
 *
 * @param String url
 * @param Event evt
 * @return Boolean
 * @todo use asynchronous request
 */
function iResPopUp(url, evt)
{
	var yOffset = Math.max(10, evt.getOffsetY() - 20).toString() + 'px';

	if (_RESPOPUP_IPHONE_JS_HASH[url]) {
		_RESPOPUP_IPHONE_JS_INDEX++;
		_RESPOPUP_IPHONE_JS_HASH[url].style.top = yOffset;
		_RESPOPUP_IPHONE_JS_HASH[url].style.zIndex = _irespopup_get_z_index();
		return false;
	}

	var req = new XMLHttpRequest();
	req.open('GET', url + '&ajax=true', false);
	req.send(null);

	if (req.readyState == 4) {
		if (req.status == 200) {
			_RESPOPUP_IPHONE_JS_INDEX++;

			var container = document.createElement('div');
			var closer = document.createElement('img');
			var popid = '_respop' + _RESPOPUP_IPHONE_JS_INDEX.toString();

			container.id = popid;
			container.className = 'respop';
			container.innerHTML = req.responseText.replace(/<[^<>]+? id="/, '$0' + popid + '_'); //"
			container.style.top = yOffset;
			container.style.zIndex = _irespopup_get_z_index();
			//container.onclick = _irespopup_make_activate(container);

			closer.className = 'close-button';
			closer.setAttribute('src', 'img/iphone/close.png');
			closer.onclick = _irespopup_make_deactivate(container, url);

			container.appendChild(closer);
			document.body.appendChild(container);

			rewrite_external_link(container);

			_RESPOPUP_IPHONE_JS_HASH[url] = container;

			return false;
		}
	}

	return true;
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
