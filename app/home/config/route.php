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
// | 路由设置
// +----------------------------------------------------------------------

$suffix = sysConfig('seo.url_suffix');

return [
    // URL伪静态后缀
    'url_html_suffix' => $suffix ?: 'html',
    // 路由是否完全匹配
    'route_complete_match' => true,
    // 请求缓存规则 true为自动规则
    'request_cache_key' => env('app_debug', false) ? false : '__URL__',
    // 请求缓存有效期
    'request_cache_expire' => 1800,
    // 全局请求缓存排除规则
    'request_cache_except' => [
        '/search',
        '/user',
    ],
    // 请求缓存的Tag
    'request_cache_tag' => 'home',
];