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
use think\Db;
use service\LogService;
use service\NodeService;

/**
 * 后台登陆
 * Class Login
 * @package app\admin\controller
 */
class Login extends BasicAdmin {

	public $checkLogin = false; // 默认检查用户登录状态
	public $checkAuth = false; // 默认检查节点访问权限

	/**
	 * 模型初始化
	 * @access protected
	 */
	public function _initialize() {
		if (session('user') && $this->request->action() !== 'out') {
			$this->redirect('@admin');
		}
	}

	/**
	 * 后台登录
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		if ($this->request->isPost()) {
			// 获取POST数据并验证
			$post = $this->request->post();
			(empty($post['username']) || strlen($post['username']) < 4) && $this->error('登录账号长度不能少于4位有效字符!');
			(empty($post['password']) || strlen($post['password']) < 4) && $this->error('登录密码长度不能少于4位有效字符!');
			// 获取用户信息并验证
			$user = Db::name('SystemUser')->where('username', $post['username'])->find();
			empty($user) && $this->error('登录账号不存在，请重新输入!');
			($user['password'] !== passwordEncode($post['password'], $user['random_code'])) && $this->error('登录密码与账号不匹配，请重新输入!');
			// 保存SESSION
			session('user', $user);
			// 修改用户最后登录时间和登录次数
			Db::name('SystemUser')->where('id', $user['id'])->update(['login_num' => ['exp', 'login_num+1'], 'login_time' => time(), 'login_ip' => $this->request->ip()]);
			// 获取用户权限节点
			!empty($user['authorize']) && NodeService::applyAuthNode(true);
			LogService::write('系统管理', '用户登录系统成功');
			$this->success('登录成功，正在进入系统...', '@admin');
		}
		$this->assign('title', '用户登录');
		return view();
	}

	/**
	 * 退出登录
	 * @access public
	 */
	public function out() {
		LogService::write('系统管理', '用户退出系统成功');
		session('user', null);
		session_destroy();
		$this->success('退出登录成功！', '@admin/login');
	}
}
