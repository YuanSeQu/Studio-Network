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

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------
return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 开启应用快速访问
    'app_express'    =>    true,
    // 默认应用
    'default_app'      => 'home',
    // 默认时区
    'default_timezone' => env('app.default_timezone', 'Asia/Shanghai'),

    // 应用映射（自动多应用模式有效）
    'app_map' => [
        env('app.admin_map', 'admin') => 'admin',
    ],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],

    // 默认输出类型
    'default_return_type'   => 'html',
    // 默认AJAX 数据返回格式,可选json xml
    'default_ajax_return'   => 'json',

    // 异常页面的模板文件
    'exception_tmpl' => app()->getRootPath() . 'extend/think_exception.tpl',
    // 默认跳转页面对应的模板文件
    'dispatch_error_tmpl' => app()->getRootPath() . '/public/jump.html',
    // 默认成功跳转对应的模板文件
    'dispatch_success_tmpl' => app()->getRootPath() . '/public/jump.html',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
];
