/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

// PHP�̓����֐���͂���JavaScript�֐�

function nl2br(str) {
	return str.replace(/\r\n|\r|\n/g, "<br />");
}

function htmlspecialchars(str) {
	return str.replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
