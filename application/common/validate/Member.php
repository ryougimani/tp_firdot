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
		'id.require' => '请选择用户！',

		'username.require' => '请输入用户账号！',
		'username.unique' => '用户账号已经存在，请使用其它账号！',

		'phone.regex' => '手机号不合法，请输入正确的手机号！',
		'phone.unique' => '手机号已经存在，请使用其它手机号！',

		'email.email' => '电子邮箱不合法，请输入正确的电子邮箱！',
		'email.unique' => '电子邮箱已经存在，请使用其它电子邮箱！',

		'old_password.require' => '请输入旧密码！',
		'old_password._checkOldPwd' => '旧密码不匹配，请重新输入!',

		'password.require' => '请输入用户密码！',
		'password.regex' => '密码必须为6-16位字母数字符号组合！',

		're_password.confirm' => '两次输入的密码不一致！',
	];

	protected $scene = [
		'add' => ['username', 'phone', 'email', 'password', 're_password'],
		'edit' => ['id', 'phone', 'email'],
		'pass' => ['id', 'old_password', 'password', 're_password'],
		'auth' => ['id'],
	];

	protected function _checkOldPwd($value, $rule, $data) {
		$member = Db::name('Member')->where('id', $data['id'])->find();
		return $member['password'] == passwordEncode($value, $member['random_code']) ? true : false;
	}
}