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
 * 文章分类管理
 * Class ArticleClass
 * @package app\admin\controller
 */
class ArticleClass extends BasicAdmin {

	protected $table = 'ArticleClass';

	/**
	 * 文章列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->title = lang('article class') . lang('index title');
		$db = Db::name($this->table);
		// 实例化并显示
		return parent::_list($db, false);
	}

	/**
	 * 列表数据处理
	 * @access protected
	 * @param array $data
	 */
	protected function _index_data_filter(&$data) {
		foreach ($data as &$vel) {
			$vel['ids'] = join(',', ToolsService::getListSubId($data, $vel['id']));
		}
		$data = ToolsService::listToTable($data);
	}

	/**
	 * 文章分类添加
	 * @access public
	 * @return \think\response\View
	 */
	public function add() {
		return parent::_form($this->table, 'form');
	}

	/**
	 * 文章分类编辑
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
		}
		if ($this->request->isGet()) {
			// 上级分类内容处理
			$classes = $this->_form_select($this->table, true, lang('class top'));
			$this->assign('classes', $classes);
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
