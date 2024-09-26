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

namespace app\plugin\controller;
use app\BaseController;
use think\facade\App;

class Plugin extends BaseController
{
    public function index($_plugin, $_controller, $_action)
    {
        $_controller =parse_name($_controller, 1);
        if (!preg_match('/^[A-Za-z](\w|\.)*$/', $_controller)) {
            abort(404, 'controller not exists:' . $_controller);
        }
        if (!preg_match('/^[A-Za-z](\w|\.)*$/', $_plugin)) {
            abort(404, 'plugin not exists:' . $_plugin);
        }
        $pluginControllerClass = "addons\\{$_plugin}\\controller\\{$_controller}";;
        $vars = [];
        return App::invokeMethod([$pluginControllerClass, $_action], $vars);
    }
}