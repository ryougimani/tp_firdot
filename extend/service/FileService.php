<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | author: ryougimani <ryougimani@qq.com>
// +----------------------------------------------------------------------

namespace service;

use think\Config;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use OSS\OssClient;
use OSS\Core\OssException;
use think\Log;
use Exception;

/**
 * 文件存储服务
 * Class FileService
 * @package service
 */
class FileService {

	/**
	 * 根据文件后缀获取文件MINE
	 * @access public
	 * @param array|string $ext 文件后缀
	 * @param array $mine 文件后缀MINE信息
	 * @return string
	 */
	public static function getFileMine($ext, $mine = []) {
		$mines = Config::get('mines');
		foreach (is_string($ext) ? explode(',', $ext) : $ext as $_ext) {
			if (isset($mines[strtolower($_ext)])) {
				$_extInfo = $mines[strtolower($_ext)];
				$mine[] = is_array($_extInfo) ? join(',', $_extInfo) : $_extInfo;
			}
		}
		return join(',', $mine);
	}

	/**
	 * 获取文件当前URL地址
	 * @access public
	 * @param string $filename 文件名称
	 * @param string|null $storage 存储类型
	 * @return bool|string
	 */
	public static function getFileUrl($filename, $storage = null) {
		if (self::hasFile($filename, $storage) === false) {
			return false;
		}
		switch (empty($storage) ? systemConfig('storage_type') : $storage) {
			case 'local':
				return self::getBaseUrlLocal() . $filename;
			case 'qiniu':
				return self::getBaseUrlQiniu() . $filename;
			case 'oss':
				return self::getBaseUrlOss() . $filename;
		}
		return false;
	}

	/**
	 * 检查文件是否已经存在
	 * @access public
	 * @param string $filename 文件名称
	 * @param string|null $storage 存储类型
	 * @return bool
	 */
	public static function hasFile($filename, $storage = null) {
		switch (empty($storage) ? systemConfig('storage_type') : $storage) {
			case 'local':
				return file_exists(ROOT_PATH . 'static/upload/' . $filename);
			case 'qiniu':
				$auth = new Auth(systemConfig('storage_qiniu_access_key'), systemConfig('storage_qiniu_secret_key'));
				$bucketMgr = new BucketManager($auth);
				list($ret, $err) = $bucketMgr->stat(systemConfig('storage_qiniu_bucket'), $filename);
				return $err === null;
			case 'oss':
				$ossClient = new OssClient(systemConfig('storage_oss_keyid'), systemConfig('storage_oss_secret'), self::getBaseUrlOss(), true);
				return $ossClient->doesObjectExist(systemConfig('storage_oss_bucket'), $filename);
		}
		return false;
	}

	/**
	 * 获取服务器URL前缀
	 * @access public
	 * @return string
	 */
	public static function getBaseUrlLocal() {
		$request = request();
		$base = $request->root();
		$root = strpos($base, '.') ? ltrim(dirname($base), DS) : $base;
		if ('' != $root) {
			$root = '/' . ltrim($root, '/');
		}
		return ($request->isSsl() ? 'https' : 'http') . '://' . $request->host() . "{$root}/static/upload/";
	}

	/**
	 * 获取七牛云URL前缀
	 * @access public
	 * @return string
	 */
	public static function getBaseUrlQiniu() {
		return (systemConfig('storage_qiniu_is_https') ? 'https' : 'http') . '://' . systemConfig('storage_qiniu_domain') . '/';
	}

	/**
	 * 获取AliOss URL前缀
	 * @access public
	 * @return string
	 */
	public static function getBaseUrlOss() {
		return (systemConfig('storage_oss_is_https') ? 'https' : 'http') . '://' . systemConfig('storage_oss_domain') . '/';
	}

	/**
	 * 根据配置获取到本地文件上传目标地址
	 * @access public
	 * @return string
	 */
	public static function getUploadLocalUrl() {
		return url('@admin/plugs/upload');
	}

	/**
	 * 根据配置获取到七牛云文件上传目标地址
	 * @access public
	 * @param bool $isClient
	 * @return string
	 */
	public static function getUploadQiniuUrl($isClient = true) {
		$region = systemConfig('storage_qiniu_region');
		$isHttps = !!systemConfig('storage_qiniu_is_https');
		switch ($region) {
			case '华东':
				if ($isHttps)
					return $isClient ? 'https://upload.qbox.me' : 'https://up.qbox.me';
				return $isClient ? 'http://upload.qiniu.com' : 'http://up.qiniu.com';
			case '华北':
				if ($isHttps)
					return $isClient ? 'https://upload-z1.qbox.me' : 'https://up-z1.qbox.me';
				return $isClient ? 'http://upload-z1.qiniu.com' : 'http://up-z1.qiniu.com';
			case '北美':
				if ($isHttps)
					return $isClient ? 'https://upload-na0.qbox.me' : 'https://up-na0.qbox.me';
				return $isClient ? 'http://upload-na0.qiniu.com' : 'http://up-na0.qiniu.com';
			case '华南':
			default:
				if ($isHttps)
					return $isClient ? 'https://upload-z2.qbox.me' : 'https://up-z2.qbox.me';
				return $isClient ? 'http://upload-z2.qiniu.com' : 'http://up-z2.qiniu.com';
		}
	}

	/**
	 * 获取AliOSS上传地址
	 * @access public
	 * @return string
	 */
	public static function getUploadOssUrl() {
		return (request()->isSsl() ? 'https' : 'http') . '://' . systemConfig('storage_oss_domain');
	}

	/**
	 * 获取文件相对名称
	 * @access public
	 * @param string $source 文件名称
	 * @param string $ext 文件后缀
	 * @param string $pre 文件前缀
	 * @return string
	 */
	public static function getFileName($source, $ext = '', $pre = '') {
		return $pre . DS . $source . '.' . $ext;
	}

	/**
	 * 根据Key读取文件内容
	 * @access public
	 * @param string $filename 文件名称
	 * @param string|null $storage 存储类型
	 * @return string|null
	 */
	public static function readFile($filename, $storage = null) {
		switch (empty($storage) ? systemConfig('storage_type') : $storage) {
			case 'local':
				$filepath = ROOT_PATH . 'static/upload/' . $filename;
				if (file_exists($filepath)) {
					return file_get_contents($filepath);
				}
			case 'qiniu':
				$auth = new Auth(systemConfig('storage_qiniu_access_key'), systemConfig('storage_qiniu_secret_key'));
				return file_get_contents($auth->privateDownloadUrl(self::getBaseUrlQiniu() . $filename));
			case 'oss':
				$ossClient = new OssClient(systemConfig('storage_oss_keyid'), systemConfig('storage_oss_secret'), self::getBaseUrlOss(), true);
				return $ossClient->getObject(systemConfig('storage_oss_bucket'), $filename);
		}
		Log::error("通过{$storage}读取文件{$filename}的不存在！");
		return null;
	}

	/**
	 * 根据当前配置存储文件
	 * @access public
	 * @param string $filename 文件名称
	 * @param string $content 文件内容
	 * @param string|null $storage 存储类型
	 * @return array|false
	 */
	public static function save($filename, $content, $storage = null) {
		$type = empty($storage) ? systemConfig('storage_type') : $storage;
		if (!method_exists(__CLASS__, $type)) {
			Log::error("保存存储失败，调用{$type}存储引擎不存在！");
			return false;
		}
		return self::$type($filename, $content);
	}

	/**
	 * 文件储存在本地
	 * @access public
	 * @param string $filename 文件名称
	 * @param string $content 文件内容
	 * @return array|null
	 */
	public static function local($filename, $content) {
		try {
			$filepath = ROOT_PATH . 'static/upload/' . $filename;
			!file_exists(dirname($filepath)) && mkdir(dirname($filepath), '0755', true);
			if (file_put_contents($filepath, $content)) {
				$url = pathinfo(request()->baseFile(true), PATHINFO_DIRNAME) . '/static/upload/' . $filename;
				return ['file' => $filepath, 'hash' => md5_file($filepath), 'key' => "static/upload/{$filename}", 'url' => $url];
			}
		} catch (Exception $err) {
			Log::error('本地文件存储失败, ' . var_export($err, true));
		}
		return null;
	}

	/**
	 * 七牛云存储
	 * @access public
	 * @param string $filename 文件名称
	 * @param string $content 文件内容
	 * @return array|null
	 */
	public static function qiniu($filename, $content) {
		$auth = new Auth(systemConfig('storage_qiniu_access_key'), systemConfig('storage_qiniu_secret_key'));
		$token = $auth->uploadToken(systemConfig('storage_qiniu_bucket'));
		$uploadMgr = new UploadManager();
		list($result, $err) = $uploadMgr->put($token, $filename, $content);
		if ($err !== null) {
			Log::error('七牛云文件上传失败, ' . var_export($err, true));
			return null;
		}
		$result['file'] = $filename;
		$result['url'] = self::getBaseUrlQiniu() . $filename;
		return $result;
	}

	/**
	 * 阿里云OSS
	 * @access public
	 * @param string $filename 文件名称
	 * @param string $content 文件内容
	 * @return array|null
	 */
	public static function oss($filename, $content) {
		try {
			$ossClient = new OssClient(systemConfig('storage_oss_keyid'), systemConfig('storage_oss_secret'), self::getBaseUrlOss(), true);
			$result = $ossClient->putObject(systemConfig('storage_oss_bucket'), $filename, $content);
			return ['file' => $filename, 'hash' => $result['content-md5'], 'key' => $filename, 'url' => $result['oss-request-url']];
		} catch (OssException $err) {
			Log::error('阿里云OSS文件上传失败, ' . var_export($err, true));
			return null;
		}
	}

	/**
	 * 下载文件到本地
	 * @access public
	 * @param string $url 文件URL地址
	 * @param bool $isForce 是否强制重新下载文件
	 * @return array|null;
	 */
	public static function download($url, $isForce = false) {
		try {
			$filename = self::getFileName($url, strtolower(pathinfo($url, 4)), 'download/');
			if (false === $isForce && ($siteUrl = self::getFileUrl($filename, 'local'))) {
				$realfile = ROOT_PATH . 'static/upload/' . $filename;
				return ['file' => $realfile, 'hash' => md5_file($realfile), 'key' => "static/upload/{$filename}", 'url' => $siteUrl];
			}
			return self::local($filename, file_get_contents($url));
		} catch (\Exception $e) {
			Log::error("FileService 文件下载失败 [ {$url} ] . {$e->getMessage()}");
			return false;
		}
	}

}
