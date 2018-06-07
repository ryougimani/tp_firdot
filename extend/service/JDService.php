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
 * JD数据服务
 * Class JDService
 * @package service
 */
class JDService {

	private static $url = 'https://bizapi.jd.com/oauth2/accessToken';
	private static $client_id; // 即对接账号
	private static $client_secret; // 即对接账号的密码
	private static $username; // 京东的用户名
	private static $password; // 京东的用户密码

	public static function setParameter() {
		self::$client_id = 'ZlVRiTysNKVRKJQ71jJO';
		self::$client_secret = 'wpmirzc67MFvNiyPtkdI';
		self::$username = '东方团购中心vop';
		self::$password = 'jd.com';
	}

	/**
	 * 获取Access Token
	 */
	public static function getAccessToken() {
		self::setParameter();
		$param = [
			'grant_type' => 'access_token',
			'client_id' => self::$client_id,
			'client_secret' => self::$client_secret,
			'timestamp' => date('Y-m-d H:i:s'),
			'username' => self::$username,
			'password' => md5(self::$password),
			'scope' => '',
			'sign' => '',
		];
		self::getSign($param);
		$url = self::$url . '?' . self::getUrl($param);
		$result = self::getHttpResponsePOST($url);
		dump($result); exit;
	}


	public function RefreshToken() {
		$param = [
			'refresh_token' => '',
			'client_id' => self::$client_id,
			'client_secret' => self::$client_secret,
		];
	}

	private static function getSign(&$param) {
		$sign = $param['client_secret'] . $param['timestamp'] . $param['client_id'] . $param['username'] . $param['password'] . $param['grant_type'] . $param['scope'] . $param['client_secret'];
		$param['sign'] = strtoupper(md5($sign));
	}

	private static function getUrl($param) {
		$param = array_filter($param);
		$arg = '';
		while (list($key, $val) = each($param)) {
			$arg .= $key . '=' . urlencode($val) . '&';
		}
		$arg = substr($arg,0,count($arg)-2);
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		return $arg;
	}


	private static function getHttpResponsePOST($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //SSL证书认证
		curl_setopt($curl, CURLOPT_POST, 1); // post数据
		//curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // post的变量
		$responseText = curl_exec($curl);
		curl_close($curl);
		return $responseText;
	}
}