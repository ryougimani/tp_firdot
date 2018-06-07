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
 * 系统用户管理控制器
 * Class User
 * @package app\admin\controller\system
 */
class User extends BasicAdmin {

	protected $table = 'SystemUser';

	/**
	 * 用户列表
	 * @access public
	 * @return \think\response\View
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function index() {
		// 设置页面标题
		$this->title = lang('system_user_list');
		// 获取到所有GET参数
		$get = $this->request->get();
		// 实例Query对象
		$db = Db::name($this->table)->order(['id' => 'desc'])->where('is_deleted', '0');
		// 应用搜索条件
		foreach (['username', 'phone'] as $key) {
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
		$this->assign('authorizes', Db::name('SystemAuth')->column('name','id'));
		foreach ($data as &$val) {
			isset($val['login_time']) && $val['login_time'] = date('Y-m-d H:i:s', $val['login_time']);
			isset($val['authorize']) && $val['authorize'] = explode(',', $val['authorize']);
		}
	}

	/**
	 * 用户添加
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
	 * 用户编辑
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
	 * 授权管理
	 * @access public
	 * @return \think\response\View
	 * @throws \think\Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 * @throws \think\exception\PDOException
	 */
	public function auth() {
		return parent::_form($this->table, 'auth');
	}

	/**
	 * 用户密码修改
	 * @access public
	 * @return \think\response\View
	 * @throws \think\Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 * @throws \think\exception\PDOException
	 */
	public function pass() {
		if (in_array('10000', explode(',', $this->request->post('id')))) {
			$this->error(lang('not_edit_admin'));
		}
		$this->assign('verify', false);
		return parent::_form($this->table, 'pass');
	}

	/**
	 * 表单数据默认处理
	 * @access public
	 * @param array $data
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function _form_filter(&$data) {
		if ($this->request->isPost()) {
			if (isset($data['authorize']) && is_array($data['authorize'])) {
				$data['authorize'] = join(',', $data['authorize']);
			}
			// 规则验证
			$result = $this->validate($data, 'User.' . $this->request->action());
			(true !== $result) && $this->error($result);
		}
		if ($this->request->isGet()) {
			isset($data['authorize']) && $data['authorize'] = explode(',', $data['authorize']);
			$this->assign('authorizes', Db::name('SystemAuth')->select());
		}
	}

	/**
	 * 添加表单数据处理
	 * @access protected
	 * @param array $data
	 */
	protected function _add_form_filter(&$data) {
		if ($this->request->isPost()) {
			$data['random_code'] = ToolsService::getRandString(8);
		}
	}
	/**
	 * 密码表单数据处理
	 * @access protected
	 * @param array $data
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	protected function _pass_form_filter(&$data) {
		if ($this->request->isPost()) {
			$user = Db::name($this->table)->where('id', $data['id'])->find();
			$data['password'] = passwordEncode($data['password'], $user['random_code']);
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
		if (in_array('10000', explode(',', $this->request->post('id')))) {
			$this->error(lang('not edit admin'));
		}
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
		if (in_array('10000', explode(',', $this->request->post('id')))) {
			$this->error(lang('not_edit_admin'));
		}
		if (DataService::update($this->table)) {
			$this->success(lang('del_success'), '');
		}
		$this->error(lang('del_error'));
	}
}
