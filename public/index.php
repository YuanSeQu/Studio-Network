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


if ('7.1.0' > phpversion()) {
    header("Content-type:text/html;charset=utf-8");
    die('本系统要求PHP版本 >= 7.1.0，当前PHP版本为：' . phpversion() . '，请到虚拟主机控制面板里切换PHP版本，或联系空间商协助切换。<a href="http://www.rrzcms.com/Admin/News/info/id/1.html" target="_blank">点击查看人人站安装教程</a>');
}

require __DIR__ . '/../vendor/autoload.php';

defined('PUBLIC_PATH') or define('PUBLIC_PATH', '');

eval('$app = new \think\repair\App();');//兼容php5.2 的语法检测

// 执行HTTP应用并响应
$http = $app->http;

$request = $app->request;
$url = $request->url();


//检测是否已安装RRZCMS系统
if (strpos($url, '/install') === false && file_exists(__DIR__ . '/setup/') && !file_exists(__DIR__ . '/setup/install.lock')) {
    $response = $http->name('install')->run();
    $response->send();
    $http->end($response);
    exit;
}

$response = $http->run();

$response->send();

$http->end($response);
