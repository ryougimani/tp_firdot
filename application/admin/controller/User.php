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
 * 系统用户管理控制器
 * Class User
 * @package app\admin\controller
 */
class User extends BasicAdmin {

	public $table = 'SystemUser';

	/**
	 * 用户列表
	 */
	public function index() {
		// 设置页面标题
		$this->title = '系统用户管理';
		// 获取到所有GET参数
		$get = $this->request->get();
		// 实例Query对象
		$db = Db::name($this->table)->where('is_deleted', '0');
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
	 * 授权管理
	 * @return array|string
	 */
	public function auth() {
		return $this->_form($this->table, 'auth');
	}

	/**
	 * 用户添加
	 */
	public function add() {
		return $this->_form($this->table, 'form');
	}

	/**
	 * 用户编辑
	 */
	public function edit() {
		return $this->_form($this->table, 'form');
	}

	/**
	 * 用户密码修改
	 */
	public function pass() {
		if (in_array('10000', explode(',', $this->request->post('id')))) {
			$this->error('系统超级账号禁止操作！');
		}
		if ($this->request->isGet()) {
			$this->assign('verify', false);
			return $this->_form($this->table, 'pass');
		}
		$data = $this->request->post();
		if ($data['password'] !== $data['repassword']) {
			$this->error('两次输入的密码不一致！');
		}
		if (DataService::save($this->table, ['data' => ['id' => $data['id'], 'password' => md5($data['password'])], 'upKey' => 'id'])) {
			$this->success('密码修改成功，下次请使用新密码登录！', '');
		}
		$this->error('密码修改失败，请稍候再试！');
	}

	/**
	 * 表单数据默认处理
	 * @param array $data
	 */
	public function _form_filter(&$data) {
		if ($this->request->isPost()) {
			if (isset($data['authorize']) && is_array($data['authorize'])) {
				$data['authorize'] = join(',', $data['authorize']);
			}
			if (isset($data['id'])) {
				unset($data['username']);
			} elseif (Db::name($this->table)->where('username', $data['username'])->find()) {
				$this->error('用户账号已经存在，请使用其它账号！');
			}
		} else {
			$data['authorize'] = explode(',', isset($data['authorize']) ? $data['authorize'] : '');
			$this->assign('authorizes', Db::name('SystemAuth')->select());
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
		if (in_array('10000', explode(',', $this->request->post('id')))) {
			$this->error('系统超级账号禁止操作！');
		}
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
		if (in_array('10000', explode(',', $this->request->post('id')))) {
			$this->error('系统超级账号禁止删除！');
		}
		if (DataService::update($this->table)) {
			$this->success($this->modelName . '删除成功！', '');
		}
		$this->error($this->modelName . '删除失败，请稍候再试！');
	}
}
