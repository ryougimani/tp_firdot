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
use think\Db;
use service\DataService;
use service\ToolsService;

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
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function index() {
		$this->title = lang('system_menu_list');
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
	 * @throws \think\Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 * @throws \think\exception\PDOException
	 */
	public function add() {
		return parent::_form($this->table, 'form');
	}

	/**
	 * 编辑菜单
	 * @access public
	 * @return \think\response\View
	 * @throws \think\Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 * @throws \think\exception\PDOException
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
			$menus = $this->_form_select($this->table, true, lang('top_menu'), 'title');
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
			// 设置上级菜单
			if (!isset($data['pid']) && $this->request->get('pid', '0')) {
				$data['pid'] = $this->request->get('pid', '0');
			}
			$this->assign('menus', $menus);
		}
	}

	/**
	 * 启用操作
	 * @access public
	 */
	public function enables() {
		if (DataService::update($this->table)) {
			$this->success(lang('enables_success'), '');
		}
		$this->error(lang('enables_error'));
	}

	/**
	 * 禁用操作
	 * @access public
	 */
	public function disables() {
		if (DataService::update($this->table)) {
			$this->success(lang('disables_success'), '');
		}
		$this->error(lang('disables_error'));
	}

	/**
	 * 删除操作
	 * @access public
	 */
	public function del() {
		if (DataService::update($this->table)) {
			$this->success(lang('del_success'), '');
		}
		$this->error(lang('del_error'));
	}
}
