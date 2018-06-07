<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace service;

use think\Db;

/**
 * 基础数据服务
 * Class DataService
 * @package service
 */
class DataService {

	/**
	 * 数据增量或修改
	 * @access public
	 * @param \think\db\Query|string $dbQuery 数据查询对象
	 * @param array $data 需要保存或更新的数据
	 * @param string|array $keys 条件主键限制
	 * @param array $where 其它的where条件
	 * @return bool|int
	 * @throws \think\Exception
	 * @throws \think\exception\PDOException
	 */
	public static function save($dbQuery, $data, $keys = 'id', $where = []) {
		// 实例化查询类
		$db = is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery;
		// 获取表名和表字段
		list($table, $fields) = [$db->getTable(), $db->getTableFields()];
		// 根据条件主键获取查询条件
		$map = [];
		if (!empty($keys) && (is_string($keys) || is_array($keys))) {
			foreach ((is_string($keys) ? explode(',', $keys) : $keys) as $v) {
				if (is_string($v) && array_key_exists($v, $data)) {
					$map[$v] = $data[$v];
				} elseif (is_string($v)) {
					$map[$v] = null;
				}
			}
		}
		// 默认字段
		$defData = [
			'create_by' => ((!empty(session('admin.id'))) ? session('admin.id') : 0), // 创建人
			'create_time' => time(), // 创建时间
			'update_time' => time(), // 更新时间
		];
		// 根据表字段获取保存或更新的数据
		$saveData = [];
		foreach (array_merge($data, $defData) as $k => $v) {
			in_array($k, $fields) && ($saveData[$k] = $v);
		}
		// 更新数据
		if ($db->where($where)->where($map)->count()) {
			unset($saveData['create_time'], $saveData['create_by']);
			return Db::table($table)->where($where)->where($map)->update($saveData) !== false;
		}
		// 新增数据
		return Db::table($table)->insertGetId($saveData);
	}

	/**
	 * 数据内容更新
	 * @access public
	 * @param \think\db\Query|string $dbQuery 数据查询对象
	 * @param array $where where条件
	 * @return bool
	 * @throws \think\Exception
	 * @throws \think\exception\PDOException
	 */
	public static function update(&$dbQuery, $where = []) {
		$request = app('request');
		// 实例化查询类
		$db = is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery;
		// 获取表名和主键
		list($table, $fields, $pk) = [$db->getTable(), $db->getTableFields(), $db->getPk()];
		// 获取修改字段和值
		list($field, $value) = [$request->post('field', ''), $request->post('value', '')];
		$map[] = empty($pk) ? ['id', 'in', explode(',', $request->post('id', ''))] : [$pk, 'in', explode(',', $request->post($pk, ''))];
		// 删除模式，如果存在 is_deleted 字段使用软删除
		if ($field === 'delete') {
			if (in_array('is_deleted', $fields)) {
				return Db::table($table)->where($where)->where($map)->update(['is_deleted' => '1']) !== false;
			}
			return Db::table($table)->where($where)->where($map)->delete() !== false;
		}
		// 还原模式
		if ($field === 'restore') {
			if (in_array('is_deleted', $fields)) {
				return Db::table($table)->where($where)->where($map)->update(['is_deleted' => '0']) !== false;
			}
		}
		// 更新模式，更新指定字段内容
		return Db::table($table)->where($where)->where($map)->update([$field => $value]) !== false;
	}

	/**
	 * 生成唯一序号 (失败返回 NULL )
	 * @access public
	 * @param int $length 序号长度
	 * @param string $type 序号顾类型
	 * @return string
	 */
	public static function createSequence($length = 10, $type = 'SYSTEM') {
		$times = 0;
		while ($times++ < 10) {
			$sequence = ToolsService::getRandString($length, 1);
			$data = ['sequence' => $sequence, 'type' => strtoupper($type), 'create_time' => time()];
			if (Db::name('SystemSequence')->where($data)->count() < 1 && Db::name('SystemSequence')->insert($data)) {
				return $sequence;
			}
		}
		return null;
	}

	/**
	 * 删除指定序号
	 * @access public
	 * @param $sequence
	 * @param string $type
	 * @return int
	 * @throws \think\Exception
	 * @throws \think\exception\PDOException
	 */
	public static function deleteSequence($sequence, $type = 'SYSTEM') {
		$data = ['sequence' => $sequence, 'type' => strtoupper($type)];
		return Db::name('SystemSequence')->where($data)->delete();
	}
}
