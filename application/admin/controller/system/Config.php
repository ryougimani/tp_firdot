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
use service\LogService;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller\system
 */
class Config extends BasicAdmin {

	protected $table = 'SystemConfig';
	private $types = [1 => 'text', 2 => 'textarea'];


	public function index() {
		$db = Db::name($this->table)->order('group');
		return parent::_list($db);
//		$groups = Db::name($this->table)->distinct(true)->column('group');
//		$this->assign('groups', $groups);
//
//		$lists = Db::name($this->table)->order('group')->select();
//		$this->assign('list', $lists);
//
//		//dump($groups); exit;
//		return view();
//		if ($this->request->isPost()) {
//			$post = $this->request->post();
//			foreach ($post as $key => $vel) {
//				system_config($key, $vel);
//			}
//			LogService::write('系统管理', '修改系统配置参数成功');
//			$this->success('数据修改成功！', '');
//		}
//		$this->assign('title', $this->title);
//		return view();
	}

	protected function _index_data_filter(&$data) {
		$temp = [];
		foreach ($data as $val) {
			$temp[$val['group']][] = $val;
		}
		$data = empty($temp) ? $data : $temp;
	}

	public function set() {
		$this->title = '参数设置';
		$db = Db::name($this->table);
		return parent::_list($db, false);
	}

	public function add() {
		$this->title = $this->lang['add_title'];
		return parent::_form($this->table, 'form');
	}

	public function edit() {
		$this->title = $this->lang['add_title'];
		return parent::_form($this->table, 'form');
	}

	protected function _form_filter(&$data) {
		if ($this->request->isGet()) {
			$types = [];
			$typesLang = lang('config_types');
			foreach ($this->types as $key => $val) {
				$types[$key] = isset($typesLang[$val]) ? $typesLang[$val] : $val;
			}
			$this->assign('types', $types);
		}
	}

	public function enables() {
		if (DataService::update($this->table)) {
			$this->success(lang('enables_success'), '');
		}
		$this->error(lang('enables_error'));
	}

	public function disables() {
		if (DataService::update($this->table)) {
			$this->success(lang('disables_success'), '');
		}
		$this->error(lang('disables_error'));
	}


	/**
	 * 文件存储配置
	 * @access public
	 * @return \think\response\View
	 */
	public function file() {
		$this->assign('alert', [
			'type' => 'success',
			'title' => '操作提示',
			'content' => '文件引擎参数影响全局文件上传功能，请勿随意修改！'
		]);
		$this->title = '文件存储配置';
		return $this->index();
	}
}
