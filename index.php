<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace think;

// SESSION会话名称
//session_name('s' . substr(md5(__FILE__), 0, 8));

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');

// 定义Runtime运行目录
//define('RUNTIME_PATH', __DIR__ . '/runtime/');

// 加载框架基础引导文件
require __DIR__ . '/thinkphp/base.php';

// think文件检查，防止TP目录计算异常
file_exists('think') || touch('think');

// 执行应用并响应
Container::get('app')->path(APP_PATH)->run()->send();
