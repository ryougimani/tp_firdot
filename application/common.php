<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

use service\NodeService;
use service\DataService;
use think\Db;

$mobileRegex = '^1[3-9][0-9]{9}$';

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */
function auth($node) {
	return NodeService::checkAuthNode($node);
}

/**
 * 获取或设置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是false为获取值，否则为更新
 * @return bool|mixed|string
 */
function systemConfig($name, $value = false) {
	static $config = [];
	if ($value !== false) {
		$config = [];
		$data = ['name'=>$name, 'value'=>$value];
		return DataService::save('SystemConfig', $data,'name');
	}
	if (empty($config)) {
		foreach (Db::name('SystemConfig')->select() as $item) {
			$config[$item['name']] = $item['value'];
		}
	}
	return isset($config[$name]) ? $config[$name] : '';
}

/**
 * 汉字转换拼音
 * @param string $chinese 中文字符
 * @param int $type 转换类型 默认为全拼音 1为首字母 2为带音调
 * @return mixed|Pinyin|string
 */
function transformPinyin($chinese, $type = 0) {
	vendor("pinyin.pinyin");
	$pinyin = new \Pinyin();
	switch ($type) {
		case 1:
			$pinyin = $pinyin->transformUcwords($chinese);
			break;
		case 2:
			$pinyin = $pinyin->transformWithTone($chinese);
			break;
		default:
			$pinyin = $pinyin->transformWithoutTone($chinese);
	}
	return $pinyin;
}

/**
 * 用户密码加密
 * @param string $password 输入密码
 * @param string $code 加密编号
 * @return string
 */
function passwordEncode($password, $code){
	return md5(md5($password).$code);
}

/**
 * 产生随机字串
 * @param int $length 长度
 * @param int $type 字串类型 0字母加数字 1数字 2字母 3大写 4小写
 * @param string $addChars 额外字符
 * @return null|string
 */
function rand_string($length = 12, $type = 0, $addChars = '') {
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
 * @param int $num 数量
 * @param int $length 长度
 * @param int $type 字串类型 0字母加数字 1数字 2字母 3大写 4小写
 * @return array|bool
 */
function count_rand($num, $length = 12, $type = 0) {
	//不足以生成一定数量的不重复数字
	if ($type == 1 && $length < strlen($num))
		return false;

	$rand = [];
	while (count($rand) < $num) {
		$rand[] = rand_string($length, $type);
		$rand = array_unique($rand);
	}
	return $rand;
}

/**
 * 打印输出数据到文件
 * @param mixed $data
 * @param bool $replace
 * @param string|null $pathname
 */
function p($data, $replace = false, $pathname = NULL) {
	is_null($pathname) && $pathname = RUNTIME_PATH . date('Ymd') . '.txt';
	$str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . "\n";
	$replace ? file_put_contents($pathname, $str) : file_put_contents($pathname, $str, FILE_APPEND);
}

/**
 * 获取微信操作对象
 * @param string $type
 * @return \Wechat\WechatReceive|\Wechat\WechatUser|\Wechat\WechatPay|\Wechat\WechatScript|\Wechat\WechatOauth|\Wechat\WechatMenu
 */
function & load_wechat($type = '') {
	static $wechat = array();
	$index = md5(strtolower($type));
	if (!isset($wechat[$index])) {
		$config = [
			'token'          => systemConfig('wechat_token'),
			'appid'          => systemConfig('wechat_appid'),
			'appsecret'      => systemConfig('wechat_appsecret'),
			'encodingaeskey' => systemConfig('wechat_encodingaeskey'),
			'mch_id'         => systemConfig('wechat_mch_id'),
			'partnerkey'     => systemConfig('wechat_partnerkey'),
			'ssl_cer'        => systemConfig('wechat_cert_cert'),
			'ssl_key'        => systemConfig('wechat_cert_key'),
			'cachepath'      => CACHE_PATH . 'wxpay' . DS,
		];
		$wechat[$index] = Loader::get($type, $config);
	}
	return $wechat[$index];
}

/**
 * 安全URL编码
 * @param array|string $data
 * @return string
 */
function encode($data) {
	return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(serialize($data)));
}

/**
 * 安全URL解码
 * @param string $string
 * @return string
 */
function decode($string) {
	$data = str_replace(['-', '_'], ['+', '/'], $string);
	$mod4 = strlen($data) % 4;
	!!$mod4 && $data .= substr('====', $mod4);
	return unserialize(base64_decode($data));
}

/**
 * array_column 函数兼容
 */
if (!function_exists("array_column")) {

	function array_column(array &$rows, $column_key, $index_key = null) {
		$data = [];
		foreach ($rows as $row) {
			if (empty($index_key)) {
				$data[] = $row[$column_key];
			} else {
				$data[$row[$index_key]] = $row[$column_key];
			}
		}
		return $data;
	}
}

