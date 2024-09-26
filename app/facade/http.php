<?php
/**
 * 人人站CMS
 * ============================================================================
 * 版权所有 2015-2030 山东康程信息科技有限公司，并保留所有权利。
 * 网站地址: http://www.rrzcms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */

namespace app\facade;

use think\Facade;

/**
 * http 请求
 * @see \app\http
 * @package app\facade
 * @mixin \app\http
 * @method static mixed send($url = '', $options = []) 发送http请求
 */
class http extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass() {
        return 'app\http';
    }
}