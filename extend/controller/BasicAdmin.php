<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace controller;

use think\Controller;
use think\Db;
use service\DataService;
use service\ToolsService;

/**
 * 后台权限基础控制器
 * Class BasicAdmin
 * @package controller
 */
class BasicAdmin extends Controller {

	protected $title; // 页面标题
	protected $table; // 默认操作数据表
	protected $lang; // 语言包

	/**
	 * 当前对象回调成员方法
	 * @access protected
	 * @param string $method 方法名称
	 * @param array $data 需要处理的数据
	 * @return bool
	 */
	protected function _callback($method, &$data) {
		foreach ([$method, "_" . $this->request->action() . "{$method}"] as $_method) {
			if (method_exists($this, $_method) && false === $this->$_method($data))
				return false;
		}
		return true;
	}

	/**
	 * 列表集成处理方法
	 * @access protected
	 * @param \think\db\Query|string $dbQuery 数据库查询对象
	 * @param bool $isPage 是启用分页
	 * @param bool $isDisplay 是否直接输出显示
	 * @param bool $total 总记录数
	 * @return mixed
	 */
	protected function _list($dbQuery = null, $isPage = true, $isDisplay = true, $total = false) {
		$db = is_null($dbQuery) ? Db::name($this->table) : (is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery);
		// 列表排序默认处理
		if ($this->request->isPost() && $this->request->post('action') === 'resort') {
			$data = $this->request->post();
			unset($data['action']);
			foreach ($data as $key => &$value) {
				if (false === $db->where('id', intval(ltrim($key, '_')))->setField('sort', $value)) {
					$this->error(lang('sort error'));
				}
			}
			$this->success(lang('sort success'), '');
		}
		// 排序
		if (null === $db->getOptions('order')) {
			$fields = $db->getTableFields($db->getTable());
			$order = ['id' => 'asc'];
			in_array('sort', $fields) && $order = array_merge(['sort' => 'asc'], $order);
			$db->order($order);
		}
		// 是否分页
		if ($isPage) {
			// 获取行数
			$listRows = intval($this->request->get('rows', cookie('rows')));
			// cookie保存行数
			cookie('rows', $listRows >= 10 ? $listRows : 20);
			// 查询分页数据
			$page = $db->paginate($listRows, $total, ['query' => $this->request->get()]);
			$result['list'] = $page->all();
			$result['count'] = $page->count();
			$pattern = ['|href="(.*?)"|', '|pagination|'];
			$replacement = ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'];
			$result['page'] = preg_replace($pattern, $replacement, $page->render());
		} else {
			$result['list'] = $db->select();
		}
		// 处理列表数据
		if (false !== $this->_callback('_data_filter', $result['list']) && $isDisplay) {
			!empty($this->title) && $this->assign('title', $this->title);
			return $this->fetch('', $result);
		}
		return $result;
	}

	/**
	 * 表单默认操作
	 * @access protected
	 * @param \think\db\Query|string $dbQuery 数据库查询对象
	 * @param string $template 模板
	 * @param string $pkField 主键
	 * @param array $where 查询规则
	 * @param array $extendData 扩展数据
	 * @return mixed
	 */
	protected function _form($dbQuery = null, $template = 'form', $pkField = '', $where = [], $extendData = []) {
		$db = is_null($dbQuery) ? Db::name($this->table) : (is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery);
		// 获取主键名称与值
		$pk = empty($pkField) ? ($db->getPk() ? $db->getPk() : 'id') : $pkField;
		$pkValue = $this->request->request($pk, isset($where[$pk]) ? $where[$pk] : (isset($extendData[$pk]) ? $extendData[$pk] : null));
		// 非POST请求, 获取数据并显示表单页面
		if (!$this->request->isPost()) {
			$data = ($pkValue !== null) ? array_merge((array)$db->where($pk, $pkValue)->where($where)->find(), $extendData) : $extendData;
			if (false !== $this->_callback('_form_filter', $data)) {
				empty($this->title) || $this->assign('title', $this->title);
				return $this->fetch($template, ['data' => $data]);
			}
			return $data;
		}
		// POST请求, 数据自动存库
		$data = array_merge($this->request->post(), $extendData);
		if (false !== $this->_callback('_form_filter', $data)) {
			$result = DataService::save($db, $data, $pk, $where);
			if (false !== $this->_callback('_form_result', $result)) {
				if ($result !== false) {
					$this->success(lang('form success'), '');
				}
				$this->error(lang('form error'));
			}
		}
	}

	/**
	 * 导入默认操作
	 * @param \think\db\Query|string $dbQuery
	 * @param array $field
	 * @param string $inputName
	 */
	protected function _import($dbQuery = null, $field = [], $inputName = 'file') {
		if ($this->request->isPost()) {
			$db = is_null($dbQuery) ? Db::name($this->table) : (is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery);
			$pk = $db->getPk() ? $db->getPk() : 'id';
			// 判断文件是否存在
			$file = $this->request->post($inputName);
			empty($file) && $this->error("请选择导入文件");
			$file_name = substr($file, stripos($file, '/static') + 1);
			!file_exists($file_name) && $this->error('文件不存在');

			// 文件读取
			vendor("PHPExcel.PHPExcel");
			$objReader = \PHPExcel_IOFactory::createReader('Excel2007');
			$objPHPExcel = $objReader->load($file_name, $encode = 'utf-8');
			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow(); // 取得总行数
			if ($highestRow < 2) {
				$this->error("导入文件没有内容！");
			}

			$data = [];
			for ($i = 2; $i <= $highestRow; $i++) {
				$item = [];
				foreach ($field as $k => $v) {
					$item[$v] = $objPHPExcel->getActiveSheet()->getCell("{$k}" . $i)->getValue();
				}
				$data[] = $item;
			}

			if (false === $this->_callback('_import_filter', $data)) {

			}

			$db->startTrans();
			foreach ($data as $val) {
				$result = DataService::save($db, ['data' => $val, 'upKey' => $pk, 'where' => []]);
				if (!$result) {
					$db->rollback();
					$this->error('导入失败');
				}
			}
			$db->commit();
			$this->success('导入成功', '');
		}
	}

	/**
	 * 表单树形选择
	 * @access protected
	 * @param \think\db\Query|string $dbQuery 数据库查询对象
	 * @param bool $tree 是否树形
	 * @param string $firstValue 首行值
	 * @param string $key 键
	 * @param string $pk 主键
	 * @param string $ppk 父主键
	 * @return array|false|\PDOStatement|string|\think\Collection
	 */
	protected function _form_select($dbQuery = null, $tree = false, $firstValue = '请选择分类', $key = 'name', $pk = 'id', $ppk = 'pid') {
		$db = is_null($dbQuery) ? Db::name($this->table) : (is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery);
		// 排序
		if (null === $db->getOptions('order')) {
			$fields = $db->getTableFields($db->getTable());
			in_array('sort', $fields) && $db->order(['sort' => 'asc']);
		}
		$data = $db->where('status', '1')->select();
		if ($tree) {
			$data[] = [$key => $firstValue, $pk => '0', $ppk => '-1'];
			$data = ToolsService::listToTable($data, $pk, $ppk);
		} else {
			array_unshift($data, [$key => $firstValue, $pk => '0']);
		}
		return $data;
	}
}
