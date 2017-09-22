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
 * 文章管理
 * Class Article
 * @package app\admin\controller
 */
class Article extends BasicAdmin {

	protected $table = 'Article';

	/**
	 * 文章列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->title = lang('article') . lang('index title');
		$get = $this->request->get();
		$db = Db::name($this->table)->where('is_deleted', 0);
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
	 * 文章回收站
	 * @access public
	 * @return \think\response\View
	 */
	public function recycle() {
		$this->title = lang('article') . lang('recycle title');
		$db = Db::name($this->table)->where('is_deleted', 1);
		// 实例化并显示
		return parent::_list($db);
	}

	/**
	 * 列表数据处理
	 * @access protected
	 * @param array $data
	 */
	protected function _data_filter(&$data) {
		$classes = Db::name($this->table . 'Class')->where('status', 1)->column('name', 'id');
		foreach ($data as &$val) {
			$val['class_name'] = isset($classes[$val['class_id']]) ? $classes[$val['class_id']] : null;
			$val['update_time'] = date('Y-m-d H:i:s', $val['update_time']);
		}
	}

	/**
	 * 文章添加
	 * @access public
	 * @return \think\response\View
	 */
	public function add() {
		$this->title = lang('add article');
		return parent::_form($this->table, 'form');
	}

	/**
	 * 文章编辑
	 * @access public
	 * @return \think\response\View
	 */
	public function edit() {
		$this->title = lang('edit article');
		return parent::_form($this->table, 'form');
	}

	/**
	 * 表单数据默认处理
	 * @access protected
	 * @param array $data
	 */
	protected function _form_filter(&$data) {
		if ($this->request->isPost()) {
			if (isset($data['tag']) && is_array($data['tag'])) {
				$data['tag'] = join(',', $data['tag']);
			}
			$data['description'] = empty($data['description']) ? htmlspecialchars(mb_substr(strip_tags(str_replace(["\s", '　'], '', $data['content'])), 0, 120)) : htmlspecialchars($data['description']);
			$data['content'] = htmlspecialchars($data['content']);
		}
		if ($this->request->isGet()) {
			// 上级分类内容处理
			$classes = $this->_form_select($this->table . 'Class', true, lang('class top'));
			$this->assign('classes', $classes);
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
