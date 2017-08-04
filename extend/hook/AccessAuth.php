<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace hook;

use service\NodeService;
use think\Request;
use think\Loader;
use think\exception\HttpResponseException;
use think\Config;
use think\View;

/**
 * 访问权限管理
 * Class AccessAuth
 * @package hook
 */
class AccessAuth {

	protected $request; // 当前请求对象

	/**
	 * 行为入口
	 * @access public
	 * @param $params
	 */
	public function run(&$params) {
		$this->request = Request::instance();
		// 获取模块、控制器、方法名称
		list($module, $controller, $action) = [Loader::parseName($this->request->module()), Loader::parseName($this->request->controller()), Loader::parseName($this->request->action())];
		// 获取当前控制器中的变量
		$vars = get_class_vars(config('app_namespace') . "\\{$module}\\controller\\" . Loader::parseName($controller, 1));
		// 判断是否后台模块
		if (in_array($module, Config::get('admin_module'))) {
			// 用户登录状态检查
			if ((!empty($vars['checkAuth']) || !empty($vars['checkLogin'])) && !session('user')) {
				if ($this->request->isAjax()) {
					$result = ['code' => 0, 'msg' => '抱歉, 您还没有登录获取访问权限!', 'data' => '', 'url' => '@admin/login', 'wait' => 3];
					throw new HttpResponseException(json($result));
				}
				throw new HttpResponseException(redirect('@admin/login'));
			}
			// 访问权限节点检查
			if (!empty($vars['checkLogin']) && !NodeService::checkAuthNode("{$module}/{$controller}/{$action}")) {
				$result = ['code' => 0, 'msg' => '抱歉, 您没有访问该模块的权限!', 'data' => '', 'url' => '', 'wait' => 3];
				throw new HttpResponseException(json($result));
			}
		} else {
			// 用户登录状态检查
			if ((!empty($vars['checkAuth']) || !empty($vars['checkLogin'])) && !session('member')) {
				if ($this->request->isAjax()) {
					$result = ['code' => 0, 'msg' => '抱歉, 您还没有登录!', 'data' => '', 'url' => '@user/login', 'wait' => 3];
					throw new HttpResponseException(json($result));
				}
				throw new HttpResponseException(redirect('@user/login'));
			}
			// 访问权限节点检查
			if (!empty($vars['checkLogin']) && !NodeService::checkAuthNode("{$module}/{$controller}/{$action}")) {
				$result = ['code' => 0, 'msg' => '抱歉, 您没有访问该模块的权限!', 'data' => '', 'url' => '', 'wait' => 3];
				throw new HttpResponseException(json($result));
			}
		}
		// 权限正常, 默认赋值
		$view = View::instance(Config::get('template'), Config::get('view_replace_str'));
		$view->assign('classuri', strtolower("{$module}/{$controller}"));
	}
}
