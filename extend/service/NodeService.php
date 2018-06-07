<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | author: ryougimani <ryougimani@qq.com>
// +----------------------------------------------------------------------

namespace service;

use think\Db;

/**
 * 权限节服务
 * Class NodeService
 * @package service
 */
class NodeService {

	/**
	 * 应用用户权限节点
	 * @access public
	 * @param bool $isAdmin
	 * @return bool|mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public static function applyAuthNode($isAdmin = false) {
		if ($isAdmin) {
			if ($adminId = session('admin.id')) {
				session('admin', Db::name('SystemUser')->where(['id' => $adminId])->find());
			}
			if ($authorize = session('admin.authorize')) {
				$authorizes = Db::name('SystemAuth')->where('id', 'in', $authorize)->where('status',1)->column('id');
				if (empty($authorizes)) {
					return session('admin.nodes', []);
				}
				$nodes = Db::name('SystemAuthNode')->where('auth','in', $authorizes)->column('node');
				return session('admin.nodes', $nodes);
			}
		} else {
			if ($memberId = session('member.id')) {
				session('member', Db::name('Member')->where(['id' => $memberId])->find());
			}
			if ($authorize = session('member.authorize')) {
				$authorizes = Db::name('MemberAuth')->where('id', 'in', $authorize)->where('status',1)->column('id');
				if (empty($authorizes)) {
					return session('member.nodes', []);
				}
				$nodes = Db::name('MemberAuthNode')->where('auth','in', $authorizes)->column('node');
				return session('member.nodes', $nodes);
			}
		}
		return false;
	}

	/**
	 * 获取授权节点
	 * @access protected
	 * @return array|mixed
	 */
	protected static function getAuthNode() {
		$nodes = cache('need_auth_node');
		if (empty($nodes)) {
			$nodes = Db::name('SystemNode')->where(['is_auth' => '1'])->column('node');
			cache('need_auth_node', $nodes);
		}
		return $nodes;
	}

	/**
	 * 检查用户节点权限
	 * @access public
	 * @param $node
	 * @return bool
	 */
	public static function checkAuthNode($node) {
		list($module, $controller, $action) = explode('/', str_replace(['?', '=', '&'], '/', $node . '///'));
		$currentNode = parse_name("{$module}/{$controller}") . strtolower("/{$action}");
		// 判断是否后台模块
		if (in_array($module, app('config')->get('admin_module'))) {
			// 超级管理员或后台主页
			if (session('admin.username') === 'admin' || stripos($node, 'admin/index') === 0) {
				return true;
			}
			// 不在权限节点里
			if (!in_array($currentNode, self::getAuthNode())) {
				return true;
			}
			// 有节点权限
			return in_array($currentNode, (array)session('admin.nodes'));
		} else {
			// 不在权限节点里
			if (!in_array($currentNode, self::getAuthNode())) {
				return true;
			}
			// 有节点权限
			return in_array($currentNode, (array)session('member.nodes'));
		}
	}

	/**
	 * 获取代码节点
	 * @access protected
	 * @param string $key
	 * @param array $nodes
	 * @return array
	 */
	public static function get($key = null, $nodes = []) {
		// 获取数据库节点
		$alias = Db::name('SystemNode')->column('node,is_auth,is_login,title');
		// 忽视列表
		//$ignore = ['admin/index', 'admin/login', 'admin/plugs','wechat/api', 'wechat/notify', 'wechat/review',];
		$ignore = ['admin/login', 'admin/plugs',];
		// 后台模块
		$admin_module = app('config')->get('admin_module');
		// 树形结构树立
		foreach (self::getNodeTree(env('app_path')) as $c) {
			// 判断是否在忽视列表中
			foreach ($ignore as $v) {
				if (stripos($c, $v) === 0) continue 2;
			}
			$temp = explode('/', $c);
			$type = in_array($temp[0], $admin_module) ? 'admin' : 'home';
			list($a, $b) = ["{$temp[0]}", "{$temp[0]}/{$temp[1]}"];
			$nodes[$type][$a] = array_merge(isset($alias[$a]) ? $alias[$a] : ['node' => $a, 'title' => '', 'is_login' => 0, 'is_auth' => 0], ['pnode' => '']);
			$nodes[$type][$b] = array_merge(isset($alias[$b]) ? $alias[$b] : ['node' => $b, 'title' => '', 'is_login' => 0, 'is_auth' => 0], ['pnode' => $a]);
			$nodes[$type][$c] = array_merge(isset($alias[$c]) ? $alias[$c] : ['node' => $c, 'title' => '', 'is_login' => 0, 'is_auth' => 0], ['pnode' => $b]);
		}
		if ($key)
			return $nodes[$key];
		else
			return $nodes;
	}

	/**
	 * 获取节点列表
	 * @access protected
	 * @param string $path 路径
	 * @param array $nodes 额外数据
	 * @return array
	 */
	protected static function getNodeTree($path, $nodes = []) {
		foreach (self::getPathFiles($path) as $file) {
			// 判断是否为控制器
			if (!preg_match('|/([\w]+)/controller/([\w/]+)|', str_replace(DIRECTORY_SEPARATOR, '/', $file), $matches) || count($matches) !== 3) {
				continue;
			}
			// 检查类是否已定义
			$className = env('app_namespace') . str_replace('/', '\\', $matches[0]);
			if (!class_exists($className)) {
				continue;
			}
			// 循环方法去除内部方法加入节点数组
			foreach (get_class_methods($className) as $actionName) {
				if (strpos($actionName, '_') !== 0) {
					// 判断多层控制器
					if (substr_count($matches[2], '/')) {
						$temps = explode('/', $matches[2]);
						foreach ($temps as &$temp) {
							$temp = parse_name($temp);
						}
						$matches[2] = implode('.', $temps);
					} else {
						$matches[2] = parse_name($matches[2]);
					}
					$nodes[] = parse_name("{$matches[1]}/{$matches[2]}") . strtolower("/{$actionName}");
				}
			}
		}
		return $nodes;
	}

	/**
	 * 获取所有PHP文件
	 * @access protected
	 * @param string $path 目录
	 * @param array $data 额外数据
	 * @param string $ext 文件后缀
	 * @return array
	 */
	protected static function getPathFiles($path, $data = [], $ext = 'php') {
		// 遍历当前目录下的所有文件和目录
		foreach (scandir($path) as $dir) {
			// 过滤返回上级目录
			if (strpos($dir, '.') === 0) {
				continue;
			}
			// 当前是否为路径或PHP文件
			if (($tmp = realpath($path . DIRECTORY_SEPARATOR . $dir)) && (is_dir($tmp) || pathinfo($tmp, PATHINFO_EXTENSION) === $ext)) {
				is_dir($tmp) ? $data = array_merge($data, self::getPathFiles($tmp)) : $data[] = $tmp;
			}
		}
		return $data;
	}
}
