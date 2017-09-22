<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace app\common\validate;

use think\Db;
use think\validate;

/**
 * 前台用户验证器
 * Class Member
 * @package app\common\validate
 */
class Member extends validate {

	protected $rule = [
		'id' => 'require',
		'username' => 'require|unique:Member',
		'phone' => 'regex:/^1[3-9][0-9]{9}$/|unique:Member',
		'email' => 'email|unique:Member',
		'old_password' => 'require|_checkOldPwd',
		'password' => 'require|regex:/^[\S]{6,16}$/',
		're_password' => 'confirm:password',
	];

	protected $message = [
		'id.require' => '{%uid require}',
		'username.require' => '{%username require}',
		'username.unique' => '{%username unique}',
		'phone.regex' => '{%phone regex}',
		'phone.unique' => '{%phone unique}',
		'email.email' => '{%email email}',
		'email.unique' => '{%phone unique}',
		'old_password.require' => '{%old_password require}',
		'old_password._checkOldPwd' => '{%old_password checkOldPwd}',
		'password.require' => '{%password require}',
		'password.regex' => '{%password regex}',
		're_password.confirm' => '{%re_password confirm}',
	];

	protected $scene = [
		'add' => ['username', 'phone', 'email', 'password', 're_password'],
		'edit' => ['id', 'phone', 'email'],
		'pass' => ['id', 'old_password', 'password', 're_password'],
		'auth' => ['id'],
		'reg' => ['phone'],
	];

	protected function _checkOldPwd($value, $rule, $data) {
		$member = Db::name('Member')->where('id', $data['id'])->find();
		return $member['password'] == passwordEncode($value, $member['random_code']) ? true : false;
	}
}