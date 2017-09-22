<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace app\admin\controller\system;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\Db;

/**
 * 系统菜单后台管理管理
 * Class Menu
 * @package app\admin\controller\system
 */
class Menu extends BasicAdmin {

	protected $table = 'SystemMenu';

	/**
	 * 菜单列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->title = lang('system menu') . lang('index title');
		$db = Db::name($this->table);
		return parent::_list($db, false);
	}

	/**
	 * 列表数据处理
	 * @access protected
	 * @param array $data
	 */
	protected function _index_data_filter(&$data) {
		foreach ($data as &$vel) {
			($vel['url'] !== '#') && ($vel['url'] = url($vel['url']));
			$vel['ids'] = join(',', ToolsService::getListSubId($data, $vel['id']));
		}
		$data = ToolsService::listToTable($data);
	}

	/**
	 * 添加菜单
	 * @access public
	 * @return \think\response\View
	 */
	public function add() {
		return parent::_form($this->table, 'form');
	}

	/**
	 * 编辑菜单
	 * @access public
	 * @return \think\response\View
	 */
	public function edit() {
		return parent::_form($this->table, 'form');
	}

	/**
	 * 表单数据前缀方法
	 * @access protected
	 * @param array $data 数据
	 */
	protected function _form_filter(&$data) {
		if ($this->request->isGet()) {
			// 上级菜单内容处理
			$menus = $this->_form_select($this->table, true, lang('menu top'), 'title');
			foreach ($menus as $key => &$menu) {
				// 删除3级以上菜单
				if (substr_count($menu['path'], '-') > 3) {
					unset($menus[$key]);
					continue;
				}
				if (isset($data['pid'])) {
					$current_path = "-{$data['pid']}-{$data['id']}";
					if ($data['pid'] !== '' && (stripos("{$menu['path']}-", "{$current_path}-") !== false || $menu['path'] === $current_path)) {
						unset($menus[$key]);
					}
				}
			}
			// 读取系统功能节点
			$nodes = NodeService::get();
			foreach ($nodes as $key => $node) {
				if (empty($node['is_menu'])) {
					unset($nodes[$key]);
				}
			}
			$this->assign('nodes', array_column($nodes, 'node'));
			$this->assign('menus', $menus);
		}
	}

	/**
	 * 启用操作
	 * @access public
	 */
	public function enables() {
		if (DataService::update($this->table)) {
			$this->success(lang('enables success'), '');
		}
		$this->error(lang('enables error'));
	}

	/**
	 * 禁用操作
	 * @access public
	 */
	public function disables() {
		if (DataService::update($this->table)) {
			$this->success(lang('disables success'), '');
		}
		$this->error(lang('disables error'));
	}

	/**
	 * 删除操作
	 * @access public
	 */
	public function del() {
		if (DataService::update($this->table)) {
			$this->success(lang('del success'), '');
		}
		$this->error(lang('del error'));
	}
}
