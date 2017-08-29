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
		return DataService::save('SystemConfig', $data, 'name');
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
 * @param string $delimiter 分割符
 * @return mixed|Pinyin|string
 */
function transformPinyin($chinese, $type = 0, $delimiter = ' ') {
	$pinyin = new \Pinyin();
	switch ($type) {
		case 1:
			$pinyin = $pinyin->transformUcwords($chinese, $delimiter);
			break;
		case 2:
			$pinyin = $pinyin->transformWithTone($chinese, $delimiter);
			break;
		default:
			$pinyin = $pinyin->transformWithoutTone($chinese, $delimiter);
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

//将XML转为array
function xmlToArray($xml)
{
	//禁止引用外部xml实体
	libxml_disable_entity_loader(true);
	$values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	return $values;
}