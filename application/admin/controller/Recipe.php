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
use think\Db;

/**
 * 食谱管理
 * Class Recipe
 * @package app\admin\controller
 */
class Recipe extends BasicAdmin {

	protected $table = 'Recipe';
	protected $week = ['1' => '星期一', '2' => '星期二', '3' => '星期三', '4' => '星期四', '5' => '星期五', '6' => '星期六', '7' => '星期日'];
	protected $recipeConfig = [['name' => '早点', 'class' => [1,6]], ['name' => '午餐', 'class' => [3,4,5,2]], ['name' => '午点', 'class' => [7,8]],];

	/**
	 * 食谱列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->title = lang('recipe') . lang('index title');
		$get = $this->request->get();
		$db = Db::name($this->table)->order('date desc');
		// 应用搜索条件
		foreach (['title'] as $key) {
			if (isset($get[$key]) && $get[$key] !== '') {
				$db->where($key, 'like', "%{$get[$key]}%");
			}
		}
		// 实例化并显示
		return parent::_list($db);
	}

	/**
	 * 列表数据处理
	 * @access protected
	 * @param array $data
	 */
	protected function _data_filter(&$data) {
		foreach ($data as &$val) {
			$val['week'] = $this->week[$val['week']];
			$val['update_time'] = date('Y-m-d H:i:s', $val['update_time']);
		}
	}

	/**
	 * 食谱添加
	 * @access public
	 * @return \think\response\View
	 */
	public function add() {
		$this->title = lang('add recipe');
		return parent::_form($this->table, 'form');
	}

	/**
	 * 食谱编辑
	 * @access public
	 * @return \think\response\View
	 */
	public function edit() {
		$this->title = lang('edit recipe');
		return parent::_form($this->table, 'form');
	}

	/**
	 * 表单数据默认处理
	 * @access protected
	 * @param array $data
	 */
	protected function _form_filter(&$data) {
		if ($this->request->isPost()) {
			// 规则验证
			$result = $this->validate($data, 'Recipe');
			(true !== $result) && $this->error($result);
			// 计算星期
			if (isset($data['date'])) {
				$data['week'] = date('N', strtotime($data['date']));
			}
			// 处理食谱
			if (isset($data['recipe']) && is_array($data['recipe'])) {
				$cooks = DB::name('Cook')->where('status', 1)->column('name', 'id');
				$classes = DB::name('CookClass')->where('status', 1)->column('name', 'id');
				$cook_ids = [];
				foreach ($data['recipe'] as $key => &$val) {
					$item = [];
					foreach ($val as $k => $v) {
						!empty($v) && $item[$classes[$k]] = [$v => $cooks[$v]];
						!empty($v) && $cook_ids[] = $v;
					}
					$val = $item;
				}
				$data['recipe'] = serialize($data['recipe']);
				$data['cook_ids'] = join(',', $cook_ids);
			}
		}
		if ($this->request->isGet()) {
			isset($data['recipe']) && $data['recipe'] = unserialize($data['recipe']);
			isset($data['cook_ids']) && $data['cook_ids'] = explode(',', $data['cook_ids']);
			$this->assign('recipeConfig', $this->recipeConfig);
			// 分类
			$classes = DB::name('CookClass')->where('status', 1)->column('name', 'id');
			$this->assign('classes', $classes);
			// 食谱
			$cooks = $this->_form_select('Cook', false, lang('select cook'));
			$this->assign('cooks', $cooks);
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

	/**
	 * 还原操作
	 * @access public
	 */
	public function restore() {
		if (DataService::update($this->table)) {
			$this->success(lang('restore success'), '');
		}
		$this->error(lang('restore error'));
	}

	/**
	 * 完全删除操作
	 * @access public
	 */
	public function completeDel() {
		if (DataService::update($this->table)) {
			$this->success(lang('del success'), '');
		}
		$this->error(lang('del error'));
	}
}
