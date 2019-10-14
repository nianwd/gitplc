
//rem
(function() {
	var t;
	function initHtmlFont() {
		var maxWidth = 720;
		var html = document.documentElement;
		var windowWidth = html.clientWidth;
		windowWidth = html.clientWidth > maxWidth ? maxWidth : html.clientWidth;
		html.style.fontSize = (windowWidth / 375) * 100 + 'px';
	}
	window.onresize = function() {
		clearTimeout(t);
		t = setTimeout(initHtmlFont, 250);
	}
	initHtmlFont();
})();

var apiUrl = 'http://plc.yunzupu.online/api';//请求后台接口地址

//获取url参数
function getQueryString(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if(r != null) return unescape(r[2]);
	return null;
}
