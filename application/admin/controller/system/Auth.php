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

/**
 * 权限管理
 * Class Auth
 * @package app\admin\controller\system
 */
class Auth extends BasicAdmin {

	protected $table = 'SystemAuth';

	/**
	 * 权限列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->title = lang('system auth') . lang('index title');
		return parent::_list($this->table);
	}

	/**
	 * 权限授权
	 * @access public
	 * @return string
	 */
	public function apply() {
		$auth_id = $this->request->get('id', '0');
		$method = '_apply_' . strtolower($this->request->get('action', '0'));
		if (method_exists($this, $method)) {
			return $this->$method($auth_id);
		}
		$this->title = lang('auth apply');
		return parent::_form($this->table, 'apply');
	}

	/**
	 * 读取授权节点
	 * @access protected
	 * @param $auth_id
	 */
	protected function _apply_getnode($auth_id) {
		$nodes = NodeService::get('admin');
		$checked = Db::name('SystemAuthNode')->where(['auth' => $auth_id])->column('node');
		foreach ($nodes as $key => &$node) {
			$node['checked'] = in_array($node['node'], $checked);
			if (empty($node['is_auth']) && substr_count($node['node'], '/') > 1) {
				unset($nodes[$key]);
			}
		}
		$all = $this->_apply_filter(ToolsService::listToTree($nodes, 'node', 'pnode', '_sub_'));
		$this->success(lang('get auth success'), '', $all);
	}

	/**
	 * 保存授权节点
	 * @access protected
	 * @param $auth_id
	 */
	protected function _apply_save($auth_id) {
		list($data, $post) = [[], $this->request->post()];
		foreach (isset($post['nodes']) ? $post['nodes'] : [] as $node) {
			$data[] = ['auth' => $auth_id, 'node' => $node];
		}
		Db::name('SystemAuthNode')->where(['auth' => $auth_id])->delete();
		Db::name('SystemAuthNode')->insertAll($data);
		$this->success(lang('save auth success'), '');
	}

	/**
	 * 节点数据拼装
	 * @access protected
	 * @param array $nodes
	 * @param int $level
	 * @return array
	 */
	protected function _apply_filter($nodes, $level = 1) {
		foreach ($nodes as $key => &$node) {
			if (!empty($node['_sub_']) && is_array($node['_sub_'])) {
				$node['_sub_'] = $this->_apply_filter($node['_sub_'], $level + 1);
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
			$this->success(lang('enables success'), '');
		}
		$this->error(lang('enables error'));
	}

	/**
	 * 禁用操作
	 * @access public
	 */
	public function disables() {
		if (DataService::update($this->table)) {
			$this->success(lang('disables success'), '');
		}
		$this->error(lang('disables error'));
	}

	/**
	 * 删除操作
	 * @access public
	 */
	public function del() {
		if (DataService::update($this->table)) {
			$id = $this->request->post('id');
			Db::name('SystemAuthNode')->where('auth', $id)->delete();
			$this->success(lang('del success'), '');
		}
		$this->error(lang('del error'));
	}
}
