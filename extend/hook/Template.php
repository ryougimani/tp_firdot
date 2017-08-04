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
use think\Config;

/**
 * 模板路径处理
 * Class Template
 * @package hook
 */
class Template {

	protected $request; // 当前请求对象

	/**
	 * 行为入口
	 * @access public
	 * @param $params
	 */
	public function run(&$params) {
		$this->request = Request::instance();
		// 判断类型
		if ($params['type'] == 'module') {
			// 获取模块名称
			list($module) = $params[$params['type']];
			// 是否手机浏览
			$theme = $this->request->isMobile() ? 'mobile' . DS : 'default' . DS;
			// 模板根路径
			$view_base = Config::get('template.view_base');
			$view_base && define('TEMPLATE_PATH', $view_base);
			// 判断是否后台模块
			if (!in_array($module, Config::get('admin_module'))) {
				$template_path = empty($view_base) ? '' : $view_base . $theme;
			} else {
				$template_path = empty($view_base) ? '' : $view_base;
			}
			// 赋值模板路径
			Config::set('template.view_base', $template_path);
		}
	}
}
