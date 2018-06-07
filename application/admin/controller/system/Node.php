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

/**
 * 节点管理
 * Class Node
 * @package app\admin\controller\system
 */
class Node extends BasicAdmin {

	protected $table = 'SystemNode';

	/**
	 * 节点列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->assign('title', lang('system_node_list'));
		$this->assign('alert', [
			'type' => 'danger',
			'title' => lang('danger_title'),
			'content' => lang('node_danger')
		]);
		// 获取节点数据
		$nodes = NodeService::get();
		// 树形表格处理
		foreach ($nodes as $type => &$val) {
			$val = ToolsService::listToTable($val, 'node', 'pnode');
		}
		$this->assign('nodes', $nodes);
		return $this->fetch();
	}

	/**
	 * 保存节点变更
	 * @access public
	 */
	public function save() {
		if ($this->request->isPost()) {
			$post = $this->request->post();
			if (isset($post['list'])) {
				$data = [];
				foreach ($post['list'] as $vo) {
					$data['node'] = $vo['node'];
					$data[$vo['name']] = $vo['value'];
					$data['type'] = $vo['type'] == 'home' ? 1 : 0;
				}
				!empty($data) && DataService::save($this->table, $data, 'node');
				$this->success(lang('save_success'), '');
			}
		} else {
			$this->error(lang('save_error'));
		}
	}
}
