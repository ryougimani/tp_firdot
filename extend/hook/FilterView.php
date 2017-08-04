<?php
// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

namespace hook;

use think\Request;

/**
 * 视图输出过滤
 * Class FilterView
 * @package hook
 */
class FilterView {

	protected $request; // 当前请求对象

    /**
     * 行为入口
     * @access public
     * @param $params
     */
    public function run(&$params) {
        $this->request = Request::instance();
        $app = $this->request->root(true);
        $replace = [
            '__APP__'    => $app,
            '__SELF__'   => $this->request->url(true),
            '__PUBLIC__' => strpos($app, EXT) ? ltrim(dirname($app), DS) : $app,
        ];
        $params = str_replace(array_keys($replace), array_values($replace), $params);
    }
}
