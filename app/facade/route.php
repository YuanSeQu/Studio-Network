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
 * 路由设置
 * @see \app\home\lib\route
 * @package app\facade
 * @mixin \app\home\lib\route
 * @method static array schema($cache) 获取路由缓存
 * @method static array getRoute($type = '', $typeId = 0) 获取路由
 * @method static bool regRoute() 注册路由
 * @method static string getRouteUrl($type = '', $data = [], $domain = false) 获取路由URL
 * @method static array getTemplateLang() 获取模版多语言配置
 * @method static mixed get(string $name) 获取属性
 */
class route extends Facade
{

    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass() {
        return 'app\home\lib\route';
    }
}