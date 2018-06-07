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
use think\Db;
use service\DataService;

/**
 * 系统日志管理
 * Class Log
 * @package app\admin\controller\system
 */
class Log extends BasicAdmin {

	public $table = 'SystemLog';

	/**
	 * 日志列表
	 * @access public
	 * @return \think\response\View
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function index() {
		$this->title = lang('system_log_list');
		$this->assign('actions', Db::name($this->table)->group('action')->column('action'));
		$db = Db::name($this->table)->order(['id' => 'desc']);
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
	 * @throws \Exception
	 */
	protected function _index_data_filter(&$data) {
		$ip = new \Ip2Region();
		foreach ($data as &$val) {
			$result = $ip->btreeSearch($val['ip']);
			$val['isp'] = isset($result['region']) ? $result['region'] : '';
			$val['isp'] = str_replace(['内网IP', '0', '|'], '', $val['isp']);
			$val['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
		}
	}

	/**
	 * 删除操作
	 * @access public
	 */
	public function del() {
		if (DataService::update($this->table)) {
			$this->success(lang('del_success'), '');
		}
		$this->error(lang('del_error'));
	}
}
