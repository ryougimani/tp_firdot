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
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\Db;

/**
 * 系统菜单后台管理管理
 * Class Menu
 * @package app\admin\controller
 */
class Menu extends BasicAdmin {

	protected $modelName = '系统菜单';
	public $table = 'SystemMenu';

	/**
	 * 菜单列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->title = '系统菜单管理';
		$db = Db::name($this->table)->order('sort asc,id asc');
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
		return $this->_form($this->table, 'form');
	}

	/**
	 * 编辑菜单
	 * @access public
	 * @return \think\response\View
	 */
	public function edit() {
		return $this->_form($this->table, 'form');
	}

	/**
	 * 表单数据前缀方法
	 * @access protected
	 * @param array $data 数据
	 */
	protected function _form_filter(&$data) {
		if ($this->request->isGet()) {
			// 上级菜单内容处理
			$menus = $this->_form_tree_select($this->table, '顶级菜单', 'title');
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
			$this->success($this->modelName . '启用成功！', '');
		}
		$this->error($this->modelName . '启用失败，请稍候再试！');
	}

	/**
	 * 禁用操作
	 * @access public
	 */
	public function disables() {
		if (DataService::update($this->table)) {
			$this->success($this->modelName . '禁用成功！', '');
		}
		$this->error($this->modelName . '禁用失败，请稍候再试！');
	}

	/**
	 * 删除操作
	 * @access public
	 */
	public function del() {
		if (DataService::update($this->table)) {
			$this->success($this->modelName . '删除成功！', '');
		}
		$this->error($this->modelName . '删除失败，请稍候再试！');
	}
}
