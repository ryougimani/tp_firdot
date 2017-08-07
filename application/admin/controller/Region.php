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
use service\ToolsService;
use think\Db;

/**
 * 地域管理
 * Class Region
 * @package app\admin\controller
 */
class Region extends BasicAdmin {

	protected $modelName = '地域';
	public $table = 'Region';

	/**
	 * 课程列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		function_exists('set_time_limit') && set_time_limit(0);
		$this->title = '地域管理';
		$db = Db::name($this->table)->order(['sort','id']);
		// 实例化并显示
		return parent::_list($db, false);
	}

	/**
	 * 列表数据处理
	 * @access protected
	 * @param array $data
	 */
	protected function _index_data_filter(&$data) {
		if ($this->request->isPost()) {
			// 编辑清楚缓存
			cache('region', null);
		}
		$_data = cache('region');
		if (empty($_data)) {
			foreach ($data as &$vel) {
				$vel['ids'] = join(',', ToolsService::getListSubId($data, $vel['id']));
			}
			$data = ToolsService::listToTable($data);
			cache('region', $data);
		} else {
			$data = $_data;
		}
	}

	/**
	 * 地域添加
	 * @access public
	 * @return \think\response\View
	 */
	public function add() {
		return parent::_form($this->table, 'form');
	}

	/**
	 * 地域编辑
	 * @access public
	 * @return \think\response\View
	 */
	public function edit() {
		return parent::_form($this->table, 'form');
	}

	/**
	 * 表单数据默认处理
	 * @access protected
	 * @param array $data
	 */
	protected function _form_filter(&$data) {
		if ($this->request->isPost()) {
			$data['pinyin'] = transformPinyin($data['name']);
			// 编辑清楚缓存
			cache('region', null);
		}
		if ($this->request->isGet()) {
			// 上级分类内容处理
			$classes = $this->_form_tree_select($this->table, '顶级分类');
			$this->assign('classes', $classes);
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
