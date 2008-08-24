/*
 * IC2::Viewer - DOM�𑀍삵��iPhone�ɍœK������
 */

// {{{ GLOBALS

var _IV2_IPHONE_JS_OLD_ONLOAD = window.onload;

// }}}
// {{{ window.onload()

/*
 * iPhone�p�ɗv�f�𒲐�����
 *
 * @return void
 */
window.onload = (function(){
	if (_IV2_IPHONE_JS_OLD_ONLOAD) {
		_IV2_IPHONE_JS_OLD_ONLOAD();
	}

	// �T���l�C�����^�b�v�����Ƃ��A�V�����^�u�ŊJ���悤�ɂ���
	var anchors = document.evaluate('.//div[@class="iv2-images"]/div/a[@href]',
	                                document.body,
	                                null,
	                                XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
	                                null
	                                );

	for (var i = 0; i < anchors.snapshotLength; i++) {
		anchors.snapshotItem(i).setAttribute('target', '_blank');
	}
});

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
