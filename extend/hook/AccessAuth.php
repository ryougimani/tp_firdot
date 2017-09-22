<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace hook;

use think\Db;
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
		$node = strtolower("{$module}/{$controller}/{$action}");
		$info = Db::name('SystemNode')->where('node', $node)->find();
		$access = [
			'is_auth'  => intval(!empty($info['is_auth'])),
			'is_login' => empty($info['is_auth']) ? intval(!empty($info['is_login'])) : 1
		];
		// 判断是否后台模块
		if (in_array($module, Config::get('admin_module'))) {
			// 用户登录状态检查
			if (!empty($access['is_login']) && !session('user')) {
				if ($this->request->isAjax()) {
					$this->response('抱歉，您还没有登录获取访问权限！', 0, url('@admin/login'));
				}
				throw new HttpResponseException(redirect('@admin/login'));
			}
		} else {
			// 用户登录状态检查
			if (!empty($access['is_login']) && !session('member')) {
				if ($this->request->isAjax() && !$this->request->isMobile()) {
					$this->response('抱歉，您还没有登录获取访问权限！', 0, url('@login'));
				}
				throw new HttpResponseException(redirect('@login'));
			}
		}
		// 访问权限节点检查
		if (!empty($access['is_auth']) && !auth($node)) {
			$this->response('抱歉，您没有访问该模块的权限！', 0);
		}
		// 权限正常, 默认赋值
		$view = View::instance(Config::get('template'), Config::get('view_replace_str'));
		$view->assign('controlUrl', strtolower("{$module}/{$controller}"));
	}

	/**
	 * 返回消息对象
	 * @access public
	 * @param string $msg 消息内容
	 * @param int $code 返回状态码
	 * @param string $url 跳转URL地址
	 * @param array $data 数据内容
	 * @param int $wait
	 */
	protected function response($msg, $code = 0, $url = '', $data = [], $wait = 3) {
		$result = ['code' => $code, 'msg' => $msg, 'data' => $data, 'url' => $url, 'wait' => $wait];
		throw new HttpResponseException(json($result));
	}
}
