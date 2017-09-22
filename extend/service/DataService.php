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
	 * @param string|array $upKey 条件主键限制
	 * @param array $where 其它的where条件
	 * @return bool
	 */
	public static function save($dbQuery, $data, $upKey = 'id', $where = []) {
		// 实例化查询类
		$db = is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery;
		// 获取表字段
		$fields = $db->getTableFields($db->getTable());
		// 默认字段
		$defData = [
			'create_time' => time(), // 创建时间
			'update_time' => time(), // 更新时间
		];
		// 更具表字段获取保存和更新的数据
		$_data = [];
		foreach (array_merge($data, $defData) as $k => $v) {
			in_array($k, $fields) && ($_data[$k] = $v);
		}
		// 更新数据
		if (self::where($db, $data, $upKey, $where)->count()) {
			unset($_data['create_time'], $_data['create_by']);
			return self::where($db, $data, $upKey, $where)->update($_data) !== false;
		}
		// 新增数据
		return self::where($db, $data, $upKey, $where)->insert($_data) !== false;
	}

	/**
	 * 应用 where 条件
	 * @access protected
	 * @param \think\db\Query $db 数据查询对象
	 * @param array $data 需要保存或更新的数据
	 * @param string|array $upKey 条件主键限制
	 * @param array $where 其它的where条件
	 * @return \think\db\Query
	 */
	protected static function where(&$db, $data, $upKey, $where = []) {
		foreach ((is_string($upKey) ? explode(',', $upKey) : $upKey) as $v) {
			// 数据中是否有条件字段
			if (is_string($v) && array_key_exists($v, $data)) {
				$db->where($v, $data[$v]);
			} elseif (is_string($v)) {
				$db->where($v, null);
			}
		}
		return $db->where($where);
	}

	/**
	 * 数据内容更新
	 * @access public
	 * @param \think\db\Query|string $dbQuery 数据查询对象
	 * @param array $where where条件
	 * @return bool
	 */
	public static function update($dbQuery, $where = []) {
		// 实例化查询类
		$db = is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery;
		// 获取ID
		$ids = explode(',', input("post.id", ''));
		// 获取修改字段
		$field = input('post.field', '');
		// 获取修改值
		$value = input('post.value', '');
		// 获取主键
		$pk = $db->getPk(['table' => $db->getTable()]);
		// 设置条件
		$db->where(empty($pk) ? 'id' : $pk, 'in', $ids);
		!empty($where) && $db->where($where);
		// 删除模式
		if ($field === 'delete') {
			$fields = $db->getTableFields($db->getTable());
			if (in_array('is_deleted', $fields)) {
				// 删除到回收站
				return false !== $db->update(['is_deleted' => 1]);
			}
			// 直接删除
			return false !== $db->delete();
		}
		// 还原模式
		if ($field === 'restore') {
			$fields = $db->getTableFields(['table' => $db->getTable()]);
			if (in_array('is_deleted', $fields)) {
				return false !== $db->update(['is_deleted' => 0]);
			}
		}
		// 更新模式
		return false !== $db->update([$field => $value]);
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
	 * @param string $sequence
	 * @param string $type
	 * @return bool
	 */
	public static function deleteSequence($sequence, $type = 'SYSTEM') {
		$data = ['sequence' => $sequence, 'type' => strtoupper($type)];
		return Db::name('SystemSequence')->where($data)->delete();
	}
}
