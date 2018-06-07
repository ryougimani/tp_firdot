<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace hook;

use think\Request;
use think\Db;
use think\exception\HttpResponseException;

/**
 * 访问权限管理
 * Class AccessAuth
 * @package hook
 */
class AccessAuth {

	/**
	 * 行为入口
	 * @access public
	 * @param Request $request
	 * @param $params
	 */
	public function run(Request $request, $params) {
		// 获取模块、控制器、方法名称
		list($module, $controller, $action) = [parse_name($request->module()), parse_name($request->controller()), $request->action()];
		$node = parse_name("{$module}/{$controller}") . strtolower("/{$action}");
		$info = Db::name('SystemNode')->cache(true, 30)->where('node', $node)->find();
		$access = [
			//'is_menu' => intval(!empty($info['is_menu'])),
			'is_auth' => intval(!empty($info['is_auth'])),
			'is_login' => empty($info['is_auth']) ? intval(!empty($info['is_login'])) : 1
		];
		// 判断是否后台模块
		if (in_array($module, app('config')->get('admin_module'))) {
			// 登录状态检查
			if (!empty($access['is_login']) && !session('admin')) {
				$msg = ['code' => 0, 'msg' => '抱歉，您还没有登录获取访问权限！', 'url' => url('@admin/login')];
				throw new HttpResponseException($request->isAjax() ? json($msg) : redirect($msg['url']));
			}
		} else {
			// 登录状态检查
			if (!empty($access['is_login']) && !session('member')) {
				$msg = ['code' => 0, 'msg' => '抱歉，您还没有登录获取访问权限！', 'url' => url('@login')];
				throw new HttpResponseException($request->isAjax() ? json($msg) : redirect($msg['url']));
			}
		}
		// 访问权限检查
		if (!empty($access['is_auth']) && !auth($node)) {
			throw new HttpResponseException(json(['code' => 0, 'msg' => '抱歉，您没有访问该模块的权限！']));
		}
		// 模板常量声明
		app('view')->init(config('template.'))->assign(['controlUrl' => parse_name("{$module}/{$controller}")]);
	}
}
