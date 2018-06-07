<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace app\admin\controller;

use controller\BasicAdmin;
use service\NodeService;
use service\ToolsService;
use think\Db;
use think\App;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 */
class Index extends BasicAdmin {

	/**
	 * 后台框架布局
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		NodeService::applyAuthNode(true);
		$this->assign('title', '系统管理');
		$this->assign('menus', $this->_getMenu());
		return view();
	}

	/**
	 * 系统信息页
	 * @access public
	 * @return \think\response\View
	 */
	public function main() {
		$_version = Db::query('select version() as ver');
		return $this->fetch('', [
			'title'     => '后台首页',
			'think_ver' => App::VERSION,
			'mysql_ver' => array_pop($_version)['ver'],
		]);
	}

	/**
	 * 获取后台主菜单
	 * @access protected
	 * @return array
	 */
	protected function _getMenu() {
		// 获取系统菜单数据
		$list = Db::name('SystemMenu')->where('status', '1')->order('sort asc,id asc')->select();
		// 转换为树形菜单
		$menus = ToolsService::listToTree($list);
		// 过滤权限
		$menus = $this->_filterMenu($menus);
		//dump($menus); exit;
		return $menus;
	}

	/**
	 * 过滤后台主菜单
	 * @access protected
	 * @param array $menus
	 * @return array
	 */
	protected function _filterMenu($menus) {
		foreach ($menus as $key => &$menu) {
			// 循环子集
			if (!empty($menu['sub'])) {
				$menu['sub'] = $this->_filterMenu($menu['sub']);
			}
			// 处理连接
			if (!empty($menu['sub'])) {
				$menu['url'] = '#';
			} elseif (stripos($menu['url'], 'http') === 0) {
				continue;
			} elseif ($menu['url'] !== '#' && NodeService::checkAuthNode(join('/', array_slice(explode('/', $menu['url']), 0, 3)))) {
				$menu['url'] = url($menu['url']);
			} else {
				unset($menus[$key]);
			}
		}
		return $menus;
	}
}
