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
 * 系统日志管理
 * Class Log
 * @package app\admin\controller
 */
class Log extends BasicAdmin {

	protected $modelName = '系统日志';
	public $table = 'SystemLog';

	/**
	 * 日志列表
	 * @access public
	 * @return \think\response\View
	 */
	public function index() {
		$this->title = '系统操作日志';
		$this->assign('actions', Db::name($this->table)->group('action')->column('action'));
		$db = Db::name($this->table)->order('id desc');
		$get = $this->request->get();
		foreach (['action', 'content', 'username'] as $key) {
			if (isset($get[$key]) && $get[$key] !== '') {
				$db->where($key, 'like', "%{$get[$key]}%");
			}
		}
		return parent::_list($db);
	}

	/**
	 * 列表数据处理
	 * @access public
	 * @param $data
	 */
	protected function _index_data_filter(&$data) {
		$ip = new \Ip2Region();
		foreach ($data as &$vo) {
			$result = $ip->btreeSearch($vo['ip']);
			$vo['isp'] = isset($result['region']) ? $result['region'] : '';
			$vo['isp'] = str_replace(['|0|0|0|0', '|'], ['', ' '], $vo['isp']);
		}
	}

	/**
	 * 删除操作
	 * @access public
	 */
	public function del() {
		if (DataService::update($this->table)) {
			$this->success($this->modelName . '删除成功！', '');
		}
		$this->error($this->modelName . '删除失败，请稍候再试！');
	}
}
