<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace app\common\validate;

use think\Validate;
use think\Db;

/**
 * 系统用户验证器
 * Class User
 * @package app\common\validate
 */
class User extends Validate {

	protected $rule = [
		'id' => 'require',
		'username' => 'require|unique:SystemUser',
		'phone' => 'regex:/^1[3-9][0-9]{9}$/',
		'email' => 'email',
		'old_password' => 'require|_checkOldPwd',
		'password' => 'require|regex:/^[\S]{6,16}$/',
		're_password' => 'confirm:password',
	];

	protected $message = [
		'id.require' => '{%uid_require}',
		'username.require' => '{%username_require}',
		'username.unique' => '{%username_unique}',
		'phone.regex' => '{%phone_regex}',
		'email.email' => '{%email_email}',
		'old_password.require' => '{%old_password_require}',
		'old_password._checkOldPwd' => '{%old_password_checkOldPwd}',
		'password.require' => '{%password_require}',
		'password.regex' => '{%password_regex}',
		're_password.confirm' => '{%re_password_confirm}',
	];

	protected $scene = [
		'add' => ['username', 'phone', 'email'],
		'edit' => ['id', 'phone', 'email'],
		'pass' => ['id', 'password', 're_password'],
		'auth' => ['id'],
	];

	protected function _checkOldPwd($value, $rule, $data) {
		$user = Db::name('SystemUser')->where('id', $data['id'])->find();
		return $user['password'] == passwordEncode($value, $user['random_code']) ? true : false;
	}
}