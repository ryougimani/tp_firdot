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
	protected $lang; // 语言

	/**
	 * 列表集成处理方法
	 * @access protected
	 * @param \think\db\Query|string $dbQuery 数据库查询对象
	 * @param bool $isPage 是启用分页
	 * @param bool $isDisplay 是否直接输出显示
	 * @param bool $total 总记录数
	 * @param array $result 返回内容
	 * @return array|string
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	protected function _list($dbQuery = null, $isPage = true, $isDisplay = true, $total = false, $result = []) {
		$db = is_null($dbQuery) ? Db::name($this->table) : (is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery);
		// 获取列表的排序条件
		if (null === $db->getOptions('order')) {
			$fields = $db->getTableFields();
			in_array('id', $fields) && $order = ['id' => 'asc'];
			in_array('sort', $fields) && isset($order) ? $order = array_merge(['sort' => 'asc'], $order) : $order = ['sort' => 'asc'];
			$db->order($order);
		}
		// 是否分页显示
		if ($isPage) {
			$rows = intval($this->request->get('rows', cookie('admin-rows')));
			cookie('admin-rows', $rows >= 10 ? $rows : 20);
			// 使用paginate查询分页
			$query = $this->request->get();
			$page = $db->paginate($rows, $total, ['query' => $query]);
			// 判断是否有数据
			if (($totalNum = $page->total()) > 0) {
				// 获取 最大页数
				list($rowsHTML, $pageHTML, $maxNum) = [[], [], $page->lastPage()];
				list($maxPage, $rowsOption, $pageOption) = [$page->lastPage(), [], []];
				foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150, 160, 170, 180, 190, 200] as $num) {
					list($query['rows'], $query['page']) = [$num, '1'];
					$url = url('@admin') . '#' . $this->request->baseUrl() . '?' . http_build_query($query);
					$rowsOption[] = lang('page rows option', ['url' => $url, 'select' => ($rows === $num ? 'selected' : ''), 'num' => $num]);
				}

//				for ($i = 1; $i <= $maxPage; $i++) {
//					list($query['rows'], $query['page']) = [$rows, $i];
//					$url = url('@admin') . '#' . $this->request->baseUrl() . '?' . http_build_query($query);
//					$selected = $i === intval($page->currentPage()) ? 'selected' : '';
//					$pageHTML[] = "<option data-url='{$url}' {$selected} value='{$i}'>{$i}</option>";
//				}
				list($pattern, $replacement) = [['|href="(.*?)"|', '|pagination|'], ['data-open="$1"', 'pagination']];
				//$html = "<span class='pagination-trigger nowrap'>共 {$totalNum} 条记录，每页显示 <select data-auto-none>" . join('', $rowsHTML) . "</select> 条，共 " . ceil($totalNum / $rows) . " 页当前显示第 <select>" . join('', $pageHTML) . "</select> 页。</span>";
				$pageHtml = lang('page html', ['pageHtml' => preg_replace($pattern, $replacement, $page->render()), 'totalNum' => $totalNum, 'rowsOption' => join('', $rowsOption)]);
				list($result['total'], $result['list'], $result['page']) = [$totalNum, $page->all(), $pageHtml];
			} else {
				list($result['total'], $result['list'], $result['page']) = [$totalNum, $page->all(), $page->render()];
			}
//			list($pattern, $replacement) = [['|href="(.*?)"|', '|pagination|'], ['data-open="$1"', 'pagination pull-right']];
//			list($result['list'], $result['page']) = [$page->all(), preg_replace($pattern, $replacement, $page->render())];
		} else {
			$result['list'] = $db->select();
		}
		// 列表数据处理
		if (false !== $this->_callback('_data_filter', $result['list'], []) && $isDisplay) {
			!empty($this->title) && $this->assign('title', $this->title);
			return view('', $result);
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
	 * @return array|mixed
	 * @throws \think\Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 * @throws \think\exception\PDOException
	 */
	protected function _form($dbQuery = null, $template = 'form', $pkField = '', $where = [], $extendData = []) {
		$db = is_null($dbQuery) ? Db::name($this->table) : (is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery);
		// 获取主键名称与值

		$pk = empty($pkField) ? ($db->getPk() ? $db->getPk() : 'id') : $pkField;
		$pkValue = $this->request->request($pk, isset($where[$pk]) ? $where[$pk] : (isset($extendData[$pk]) ? $extendData[$pk] : null));
		// 非POST请求, 获取数据并显示表单页面
		if (!$this->request->isPost()) {
			$data = ($pkValue !== null) ? array_merge((array)$db->where($pk, $pkValue)->where($where)->find(), $extendData) : $extendData;
			if (false !== $this->_callback('_form_filter', $data, [])) {
				empty($this->title) || $this->assign('title', $this->title);
				return view($template, ['data' => $data]);
			}
			return $data;
		}
		// POST请求, 数据自动存库
		$data = array_merge($this->request->post(), $extendData);
		if (false !== $this->_callback('_form_filter', $data, [])) {
			$result = DataService::save($dbQuery, $data, $pk, $where);
			if (false !== $this->_callback('_form_result', $result, [])) {
				if ($result !== false) {
					$this->success(lang('form_success'), '');
				}
				$this->error(lang('form_error'));
			}
		}
	}

	/**
	 * 当前对象回调方法
	 * @access protected
	 * @param string $method 方法名称
	 * @param array $data 需要处理的数据
	 * @param array $otherData 其他数据
	 * @return bool
	 */
	protected function _callback($method, &$data, $otherData) {
		foreach ([$method, "_" . $this->request->action() . "{$method}"] as $_method) {
			if (method_exists($this, $_method) && false === $this->$_method($data, $otherData))
				return false;
		}
		return true;
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
			in_array('sort', $db->getTableFields()) && $db->order(['sort' => 'asc']);
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
