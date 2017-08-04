<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace service;

/**
 * 系统工具服务
 * Class ToolsService
 * @package service
 */
class ToolsService {

	/**
	 * 列表转树形
	 * @access public
	 * @param array $list 列表数据
	 * @param string $pk 主键
	 * @param string $ppk 父级主键
	 * @param string $child 子集键名
	 * @return array
	 */
	public static function listToTree($list, $pk = 'id', $ppk = 'pid', $child = 'sub') {
		$tree = $refer = [];
		if (is_array($list)) {
			// 创建基于主键的数组引用
			foreach ($list as $key => $val) {
				$refer[$val[$pk]] = &$list[$key];
			}
			foreach ($list as $key => $val) {
				if (isset($val[$ppk]) && isset($refer[$val[$ppk]])) {
					$refer[$val[$ppk]][$child][] = &$list[$key];
				} else {
					$tree[] = &$list[$key];
				}
			}
		}
		unset($refer);
		return $tree;
	}

	/**
	 * 列表转表格
	 * @access public
	 * @param array $list 列表数据
	 * @param string $pk 主键
	 * @param string $ppk 父级主键
	 * @param string $path
	 * @param string $ppath
	 * @param bool $is_first
	 * @return array
	 */
	public static function listToTable($list, $pk = 'id', $ppk = 'pid', $path = 'path', $ppath = '', $is_first = true) {
		$table = [];
		// 列表树形处理
		if ($is_first)
			$tree = self::listToTree($list, $pk, $ppk);
		else
			$tree = $list;
		foreach ($tree as $val) {
			$val[$path] = $ppath . '-' . $val[$pk];
			$val['spl'] = str_repeat("&nbsp;&nbsp;&nbsp;├&nbsp;&nbsp;", substr_count($ppath, '-'));
			if (isset($val['sub'])) {
				$sub = $val['sub'];
				unset($val['sub']);
				$table[] = $val;
				if ($sub) {
					$sub_array = self::ListToTable($sub, $pk, $ppk, $path, $val[$path], false);
					$table = array_merge($table, (Array)$sub_array);
				}
			} else {
				$table[] = $val;
			}
		}
		return $table;
	}

	/**
	 * 获取列表子id
	 * @access public
	 * @param array $list 数据列表
	 * @param int $id 起始
	 * @param string $pk 主键
	 * @param string $ppk 父级主键
	 * @return array
	 */
	public static function getListSubId($list, $id = 0, $pk = 'id', $ppk = 'pid') {
		$ids = [intval($id)];
		foreach ($list as $v) {
			if (intval($v[$ppk]) > 0 && intval($v[$ppk]) == intval($id)) {
				$ids = array_merge($ids, self::getListSubId($list, intval($v[$pk]), $pk, $ppk));
			}
		}
		return $ids;
	}

	/**
	 * 产生随机字串
	 * @access public
	 * @param int $length 长度
	 * @param int $type 字串类型 0字母加数字 1数字 2字母 3大写 4小写
	 * @param string $addChars 额外字符
	 * @return null|string
	 */
	public static function getRandString($length = 12, $type = 0, $addChars = '') {
		$str = null;
		switch ($type) {
			case 1:
				$chars = '0123456789';
				break;
			case 2:
				$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
				break;
			case 3:
				$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
				break;
			case 4:
				$chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
				break;
			default:
				$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
				break;
		}
		// 打乱字符串
		$chars = str_shuffle(str_repeat($chars, $length));
		// 获取字符串长度
		$chars_len = strlen($chars) - 1;
		// 循环长度获得随机字符串
		while (strlen($str) < $length) {
			$str .= mb_substr($chars, mt_rand(0, $chars_len), 1, 'UTF-8');
		};
		return $str;
	}

	/**
	 * 生成一定数量的随机数，并且不重复
	 * @access public
	 * @param int $num 数量
	 * @param int $length 长度
	 * @param int $type 字串类型 0字母加数字 1数字 2字母 3大写 4小写
	 * @return array|bool
	 */
	public static function getCountRand($num, $length = 12, $type = 0) {
		//不足以生成一定数量的不重复数字
		if ($type == 1 && $length < strlen($num))
			return false;
		$rand = [];
		while (count($rand) < $num) {
			$rand[] = self::getRandString($length, $type);
			$rand = array_unique($rand);
		}
		return $rand;
	}
}
