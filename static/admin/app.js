// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------
//console.log();
// 当前资源URL目录
var baseUrl = (function () {
	var scripts = document.scripts, src = scripts[scripts.length - 1].src;
	return src.substring(0, src.lastIndexOf("/") + 1);
})();

// RequireJs 配置参数
require.config({
	baseUrl: baseUrl,
	waitSeconds: 0,
	map: {'*': {css: baseUrl + '../plugs/require/require.css.js'}},
	paths: {
		// 自定义插件
		'admin.plugs': ['plugs'],
		'admin.listen': ['listen'],
		// 开源插件
		'layui': ['../plugs/layui/layui'],
		'ueditor': ['../plugs/ueditor/ueditor'],
		//'template': ['../plugs/template/template'],
		'pcasunzips': ['../plugs/jquery/pcasunzips'],
		//'laydate': ['../plugs/layui/laydate/laydate'],
		// 未修改源码
		'pace': ['../plugs/jquery/pace.min'],
		'json': ['../plugs/jquery/json2.min'],
		'print': ['../plugs/jquery/jquery.PrintArea'],
		'base64': ['../plugs/jquery/base64.min'],
		'jquery': ['../plugs/jquery/jquery.min'],
		//'websocket': ['../plugs/socket/websocket'],
		'bootstrap': ['../plugs/bootstrap/js/bootstrap.min'],
		'jquery.ztree': ['../plugs/ztree/jquery.ztree.all.min'],
		'bootstrap.typeahead': ['../plugs/bootstrap/js/bootstrap3-typeahead.min'],
		'zeroclipboard': ['../plugs/ueditor/third-party/zeroclipboard/ZeroClipboard.min'],
		'jquery.cookies': ['../plugs/jquery/jquery.cookie'],
		'jquery.masonry': ['../plugs/jquery/masonry.min'],

	},
	shim: {
		'layui': {deps: ['jquery']},
		//'laydate': {deps: ['jquery']},
		'bootstrap': {deps: ['jquery']},
		'pcasunzips': {deps: ['jquery']},
		'jquery.cookies': {deps: ['jquery']},
		'jquery.masonry': {deps: ['jquery']},
		'admin.plugs': {deps: ['jquery', 'layui']},
		'bootstrap.typeahead': {deps: ['jquery', 'bootstrap']},
		//'websocket': {deps: [baseUrl + '../plugs/socket/swfobject.min.js']},
		'admin.listen': {deps: ['jquery', 'jquery.cookies', 'admin.plugs']},
		'jquery.ztree': {deps: ['jquery', 'css!' + baseUrl + '../plugs/ztree/zTreeStyle/zTreeStyle.css']},
	},
	deps: ['css!' + baseUrl + '../plugs/awesome/css/font-awesome.min.css'],
	// 开启debug模式，不缓存资源
	urlArgs: "ver=" + (new Date()).getTime()
});

window.WEB_SOCKET_SWF_LOCATION = baseUrl + "../plugs/socket/WebSocketMain.swf";
window.UEDITOR_HOME_URL = (window.ROOT_URL ? window.ROOT_URL + '/static/' : baseUrl) + 'plugs/ueditor/';
//window.LAYDATE_PATH = baseUrl + '../plugs/layui/laydate/';

// UI框架初始化
require(['pace', 'jquery', 'layui', 'bootstrap', 'jquery.cookies'], function () {
	layui.config({dir: baseUrl + '../plugs/layui/'});
	layui.use(['layer', 'form', 'laydate'], function () {
		window.layer = layui.layer;
		window.form = layui.form();
		window.laydate = layui.laydate;
		require(['admin.listen']);
	});
});