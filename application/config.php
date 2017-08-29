<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

return [
	/* 应用设置 */
	'app_namespace' => 'app', // 应用命名空间
	'app_debug' => true, // 应用调试模式
	'app_trace' => false, // 应用Trace
	'app_status' => '', // 应用模式状态
	'app_multi_module' => true, // 是否支持多模块
	'auto_bind_module' => false, // 入口自动绑定模块
	'root_namespace' => [], // 注册的根命名空间
	'extra_file_list' => [THINK_PATH . 'helper' . EXT], // 扩展函数文件
	'default_return_type' => 'html', // 默认输出类型
	'default_ajax_return' => 'json', // 默认AJAX 数据返回格式,可选json xml ...
	'default_jsonp_handler' => 'jsonpReturn', // 默认JSONP格式返回的处理方法
	'var_jsonp_handler' => 'callback', // 默认JSONP处理方法
	'default_timezone' => 'PRC', // 默认时区
	'lang_switch_on' => true, // 是否开启多语言
	'default_filter' => '', // 默认全局过滤方法 用逗号分隔多个
	'default_lang' => 'zh-cn', // 默认语言
	'class_suffix' => false, // 应用类库后缀
	'controller_suffix' => false, // 控制器类后缀

	/* 模块设置 */
	'default_module' => 'index', // 默认模块名
	'deny_module_list' => ['common'], // 禁止访问模块
	'default_controller' => 'Index', // 默认控制器名
	'default_action' => 'index', // 默认操作名
	'default_validate' => '', // 默认验证器
	'empty_controller' => 'Error', // 默认的空控制器名
	'action_suffix' => '', // 操作方法后缀
	'controller_auto_search' => false, // 自动搜索控制器
	'admin_module' => ['admin', 'system', 'wechat'], // 后台模块

	/* URL设置 */
	'var_pathinfo' => 's', // PATHINFO变量名 用于兼容模式
	'pathinfo_fetch' => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'], // 兼容PATH_INFO获取
	'pathinfo_depr' => '/', // pathinfo分隔符
	'url_html_suffix' => 'html', // URL伪静态后缀
	'url_common_param' => false, // URL普通方式参数 用于自动生成
	'url_param_type' => 0, // URL参数方式 0 按名称成对解析 1 按顺序解析
	'url_route_on' => true, // 是否开启路由
	'route_complete_match' => false, // 路由使用完整匹配
	'route_config_file' => ['route', 'admin/route'], // 路由配置文件（支持配置多个）
	'url_route_must' => false, // 是否强制使用路由
	'url_domain_deploy' => false, // 域名部署
	'url_domain_root' => '', // 域名根，如thinkphp.cn
	'url_convert' => true, // 是否自动转换URL中的控制器和操作名
	'url_controller_layer' => 'controller', // 默认的访问控制器层
	'var_method' => '_method', // 表单请求类型伪装变量
	'var_ajax' => '_ajax', // 表单ajax伪装变量
	'var_pjax' => '_pjax', // 表单pjax伪装变量
	'request_cache' => false, // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
	'request_cache_expire' => null, // 请求缓存有效期

	/* 模板设置 */
	'template' => [
		'type' => 'Think', // 模板引擎类型 支持 php think 支持扩展
		'view_base' => ROOT_PATH . 'template' . DS, // 模板根路径
		'view_path' => '', // 模板路径
		'view_suffix' => 'html', // 模板后缀
		'view_depr' => '_', // 模板文件名分隔符
		'tpl_begin' => '{', // 模板引擎普通标签开始标记
		'tpl_end' => '}', // 模板引擎普通标签结束标记
		'taglib_begin' => '{', // 标签库标签开始标记
		'taglib_end' => '}', // 标签库标签结束标记
	],
	'view_replace_str' => [], // 视图输出字符串内容替换
	'dispatch_success_tmpl' => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl', // 默认跳转页面对应的模板文件
	'dispatch_error_tmpl' => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

	/* 异常及错误设置 */
	'exception_tmpl' => THINK_PATH . 'tpl' . DS . 'think_exception.tpl', // 异常页面的模板文件
	'error_message' => '页面错误！请稍后再试～', // 错误显示信息,非调试模式有效
	'show_error_msg' => false, // 显示错误信息
	'exception_handle' => '', // 异常处理handle类 留空使用 \think\exception\Handle

	/* 日志设置 */
	'log' => [
		'type' => 'File', // 日志记录方式，内置 file socket 支持扩展
		'path' => LOG_PATH, // 日志保存目录
		'level' => ['error', 'log', 'info', 'sql', 'notice', 'alert', 'debug'], // 日志记录级别
		'apart_level' => ['error', 'sql'], // error和sql日志单独记录
	],

	/* Trace设置 开启 app_trace 后 有效 */
	'trace' => [
		'type' => 'Html', // 内置Html Console 支持扩展
	],

	/* 缓存设置 */
	'cache' => [
		'type' => 'File', // 驱动方式
		'path' => CACHE_PATH, // 缓存保存目录
		'prefix' => '', // 缓存前缀
		'expire' => 0, // 缓存有效期 0表示永久缓存
	],

	/* 会话设置 */
	'session' => [
		'id' => '',
		'var_session_id' => '', // SESSION_ID的提交变量,解决flash上传跨域
		'prefix' => 'think', // SESSION 前缀
		'type' => '', // 驱动方式 支持redis memcache memcached
		'auto_start' => true,// 是否自动开启 SESSION
	],

	/* Cookie设置 */
	'cookie' => [
		'prefix' => '', // cookie 名称前缀
		'expire' => 0, // cookie 保存时间
		'path' => '/', // cookie 保存路径
		'domain' => '', // cookie 有效域名
		'secure' => false, //  cookie 启用安全传输
		'httponly' => '', // httponly设置
		'setcookie' => true, // 是否使用 setcookie
	],

	/* 分页配置 */
	'paginate' => [
		'type' => 'bootstrap',
		'var_page' => 'page',
		'list_rows' => 15,
	],

];
