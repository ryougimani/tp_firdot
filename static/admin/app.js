// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

//console.log();
// 当前资源URL目录
var _root = (function () {
	var scripts = document.scripts, src = scripts[scripts.length - 1].src;
	return src.substring(0, src.lastIndexOf("/") + 1);
})();

// RequireJs 配置参数
require.config({
	waitSeconds: 60,
	baseUrl: _root,
	paths: {
		'layui': ['../plugs/layui/layui'],
		'ckeditor': ['../plugs/ckeditor/ckeditor'],
		'bootstrap': ['../plugs/bootstrap/js/bootstrap.min'],
		'ztree': ['../plugs/ztree/jquery.ztree.all.min'],
		'jquery.cookies': ['../plugs/jquery/jquery.cookie'],
	},
	shim: {

	},
	deps: ['bootstrap'],
	// 开启debug模式，不缓存资源
	urlArgs: "ver=" + (new Date()).getTime()
});

// 注册jquery到require模块
define('jquery', function () {
	return layui.$;
});

// UI框架初始化
PageLayout.call(this);

// UI框架布局函数
function PageLayout(callback, custom) {
	require(custom || [], callback || false);
}
