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


use \app\facade\route;

route::regRoute();//注册路由

//注册详情页点击量路由
think\facade\Route::rule('view/count', 'Api/incInfoView')->ext('asp')->completeMatch();
//增加 m 地址首页
think\facade\Route::rule('m/', 'Index/index')->completeMatch();
think\facade\Route::rule('m', 'Index/index')->completeMatch();

//获取百度地图数据
think\facade\Route::rule('baidu/map', 'Api/baiduMap')->ext('asp')->completeMatch();

//站点状态
think\facade\Route::rule('rrz/state', 'Api/state')->ext('asp')->completeMatch();