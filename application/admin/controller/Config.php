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
use service\LogService;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 */
class Config extends BasicAdmin {

	public $table = 'SystemConfig';
	public $title = '网站参数配置';

	/**
	 * 系统常规配置
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		if ($this->request->isPost()) {
			$post = $this->request->post();
			foreach ($post as $key => $vel) {
				systemConfig($key, $vel);
			}
			LogService::write('系统管理', '修改系统配置参数成功');
			$this->success('数据修改成功！', '');
		}
		$this->assign('title', $this->title);
		return view();
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
