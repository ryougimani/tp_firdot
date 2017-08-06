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
use think\Db;

/**
 * 权限管理
 * Class Auth
 * @package app\admin\controller
 */
class Auth extends BasicAdmin {

	protected $modelName = '权限';
	public $table = 'SystemAuth';

	/**
	 * 权限列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->title = '系统权限管理';
		return parent::_list($this->table);
	}

	/**
	 * 权限授权
	 * @access public
	 * @return \think\response\View
	 */
	public function apply() {
		$auth_id = $this->request->get('id', '0');
		switch (strtolower($this->request->get('action', '0'))) {
			case 'getnode':
				$nodes = NodeService::get('admin');
				$checked = Db::name('SystemAuthNode')->where('auth', $auth_id)->column('node');
				foreach ($nodes as $key => &$node) {
					$node['checked'] = in_array($node['node'], $checked);
					if (empty($node['is_auth']) && substr_count($node['node'], '/') > 1) {
						unset($nodes[$key]);
					}
				}
				$this->success('获取节点成功！', '', $this->_filterNodes($this->_filterNodes(ToolsService::listToTree($nodes, 'node', 'pnode', '_sub_'))));
				break;
			case 'save':
				$data = [];
				$post = $this->request->post();
				foreach (isset($post['nodes']) ? $post['nodes'] : [] as $node) {
					$data[] = ['auth' => $auth_id, 'node' => $node];
				}
				Db::name('SystemAuthNode')->where('auth', $auth_id)->delete();
				Db::name('SystemAuthNode')->insertAll($data);
				$this->success('节点授权更新成功！', '');
				break;
			default :
				$this->assign('title', '节点授权');
				return parent::_form($this->table, 'apply');
		}
	}

	/**
	 * 节点数据拼装
	 * @access protected
	 * @param array $nodes 节点数据
	 * @param int $level 级别
	 * @return array
	 */
	protected function _filterNodes($nodes, $level = 1) {
		foreach ($nodes as $key => &$node) {
			if (!empty($node['_sub_']) && is_array($node['_sub_'])) {
				$node['_sub_'] = $this->_filterNodes($node['_sub_'], $level + 1);
			} elseif ($level < 3) {
				unset($nodes[$key]);
			}
		}
		return $nodes;
	}

	/**
	 * 权限添加
	 * @access public
	 * @return \think\response\View
	 */
	public function add() {
		return parent::_form($this->table, 'form');
	}

	/**
	 * 权限编辑
	 * @access public
	 * @return \think\response\View
	 */
	public function edit() {
		return parent::_form($this->table, 'form');
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
		if (DataService::update($this->table)) {
			$id = $this->request->post('id');
			Db::name('SystemAuthNode')->where('auth', $id)->delete();
			$this->success($this->modelName . '删除成功！', '');
		}
		$this->error($this->modelName . '删除失败，请稍候再试！');
	}
}
