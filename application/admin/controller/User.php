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
use service\ToolsService;

/**
 * 系统用户管理控制器
 * Class User
 * @package app\admin\controller
 */
class User extends BasicAdmin {

	protected $modelName = '系统用户';
	public $table = 'SystemUser';

	/**
	 * 用户列表
	 * @access public
	 * @return \think\response\View
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
	 * 用户添加
	 * @access public
	 * @return \think\response\View
	 */
	public function add() {
		return $this->_form($this->table, 'form');
	}

	/**
	 * 用户编辑
	 * @access public
	 * @return \think\response\View
	 */
	public function edit() {
		return $this->_form($this->table, 'form');
	}

	/**
	 * 授权管理
	 * @access public
	 * @return \think\response\View
	 */
	public function auth() {
		return $this->_form($this->table, 'auth');
	}

	/**
	 * 用户密码修改
	 * @access public
	 * @return \think\response\View
	 */
	public function pass() {
		if (in_array('10000', explode(',', $this->request->post('id')))) {
			$this->error('系统超级账号禁止操作！');
		}
		$this->assign('verify', false);
		return $this->_form($this->table, 'pass');
	}

	/**
	 * 表单数据默认处理
	 * @access public
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
			if (isset($data['password'])) {
				if ($data['password'] !== $data['re_password']) {
					$this->error('两次输入的密码不一致！');
				}
				if (isset($data['id'])) {
					$user = Db::name($this->table)->where('id', $data['id'])->find();
					if (isset($data['old_password'])) {
						($user['password'] !== passwordEncode($data['old_password'], $user['random_code'])) && $this->error('旧密码不匹配，请重新输入!');
					}
					$data['password'] = passwordEncode($data['password'], $user['random_code']);
				} else {
					$data['random_code'] = ToolsService::getRandString(8);
					$data['password'] = passwordEncode($data['password'], $data['random_code']);
				}
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
