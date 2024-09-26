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

namespace app\plugin\listener;

use think\facade\Event;
use think\facade\Route;

class InitHookListener
{
    // 行为扩展的执行入口必须是run
    public function handle($param) {
        Route::any('plugin/:_plugin/[:_controller]/[:_action]', "\\app\\plugin\\controller\\Plugin@index");

        //CMS尚未安装，退出执行
        if (!is_file(root_path('public') . 'setup/install.lock')) {
            return;
        }

        $plugins = M('plugin')->field('code')->where('status', 1)->select();
        foreach ($plugins as $pinfo) {
            $file = root_path("public") . 'addons/' . parse_name($pinfo['code']) . '/event/config.php';
            if (file_exists($file)) {
                $config = include $file;
                foreach ($config as $k => $v) {
                    Event::listen($k, $v);
                }
            }
        }

    }
}