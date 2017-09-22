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
		if ($params['type'] == 'module') {
			// 获取模块名称
			list($module) = $params[$params['type']];
			// 模板根路径
			if ($view_base = Config::get('template.view_base')) {
				// 判断是否后台模块
				if (in_array($module, Config::get('admin_module'))) {
					$template_path = $view_base . 'admin' . DS;
				} else {
					// 是否手机浏览
					$type = $this->request->isMobile() ? 'mobile' . DS : 'pc' . DS;
					// 模板名称
					$theme = 'default' . DS;
					$template_path = $view_base . $type . $theme;
				}
				// 赋值模板路径
				Config::set('template.view_base', $template_path);
			}
			//$view_base && define('TEMPLATE_PATH', $view_base);
		}
	}
}
