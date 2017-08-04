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
use service\NodeService;
use service\ToolsService;

/**
 * 节点管理
 * Class Node
 * @package app\admin\controller
 */
class Node extends BasicAdmin {

	public $table = 'SystemNode';

	/**
	 * 节点列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->assign('alert', [
			'type' => 'danger',
			'title' => '安全警告',
			'content' => '结构为系统自动生成，状态数据请勿随意修改！'
		]);
		$this->assign('title', '系统节点管理');
		// 获取节点数据
		$nodes = NodeService::get();
		// 树形表格处理
		foreach ($nodes as $type => &$val) {
			$val = ToolsService::listToTable($val, 'node', 'pnode');
		}
		$this->assign('nodes', $nodes);
		return view();
	}

	/**
	 * 保存节点变更
	 * @access public
	 */
	public function save() {
		if ($this->request->isPost()) {
			$post = $this->request->post();
			if (isset($post['name']) && isset($post['value'])) {
				// 分割名称
				$arr = explode('.', $post['name']);
				$field = array_shift($arr);
				$type = array_shift($arr) == 'home' ? 1 : 0;
				$data = ['node' => join(',', $arr), $field => $post['value'], 'type' => $type];
				DataService::save($this->table, $data, 'node');
				$this->success('参数保存成功！', '');
			}
		} else {
			$this->error('访问异常，请重新进入...');
		}
	}
}
