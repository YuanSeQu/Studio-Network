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

namespace app;

use think\App;
use think\facade\View;
use traits\Jump;
use app\admin\lib\Controller;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    use Jump;
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 视图输出类
     * @var \think\View
     */
    protected $view;

    /**
     * 视图输出类
     * @var \app\admin\lib\Controller
     */
    private $Controller;

    /**
     * 构造方法
     * @access public
     * @param  App $app 应用对象
     */
    public function __construct(App $app) {
        $this->app = $app;
        $this->request = $this->app->request;
        $this->view = View::class;

        $this->initController();
        // 控制器初始化
        $this->initialize();
        event('ActionBegin');
    }

    /**
     * 初始化 Controller
     */
    protected function initController() {
        //非调试模式关闭 E_NOTICE 类型的错误提示
        if (!$this->app->isDebug()) {
            @error_reporting(E_ALL ^ E_NOTICE);//显示除去 E_NOTICE 之外的所有错误信息
            @ini_set('error_reporting', E_ALL ^ E_NOTICE);
        }

        $this->Controller = $this->app->invokeClass(Controller::class, [$this->app, $this->request, $this->view]);
    }

    // 初始化
    protected function initialize() {

    }

    /**
     * 获取用户session信息
     * @param string $appName 应用名称
     * @return mixed arry
     */
    protected function getAccount($appName = '') {
        return getAccount($appName);
    }

    /**
     * 设置用户session信息
     * @param array $account 用户信息
     * @param string $appName 应用名称
     */
    protected function setAccount($account = [], $appName = '') {
        $appName = $appName ?: $this->app->http->getName();
        $key = 'account.' . $appName;
        session($key, $account);
    }

    /**
     * 验证数据
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return bool
     * @throws \Exception
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false) {
        $v = validate($validate, $message, $batch, false);
        if (!$v->check($data)) {
            $error = $v->getError();
            $this->error($batch ? implode('，', $error) : $error);
        }
        return true;
    }

    /**
     * 模板变量赋值
     * @access public
     * @param string|array $name 模板变量
     * @param mixed $value 变量值
     * @return $View
     */
    protected function assign($name, $value = null) {
        return $this->Controller->assign($name, $value);
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access public
     * @param string $template 模板文件名或者内容
     * @param array $vars 模板变量
     * @return string
     * @throws \Exception
     */
    protected function fetch(string $template = '', array $vars = []) {
        return $this->Controller->fetch($template, $vars);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array $vars 模板变量
     * @return string
     */
    protected function display(string $content, array $vars = []) {
        return $this->Controller->display($content, $vars);
    }

    /**
     * 替换输出内容
     * @param string $content
     * @return string
     */
    protected function replace_string(string $content): string {
        return viewReplacePath($content);
    }

    /**
     * 拼接为字符串并去编码
     * @param array $arr 数组
     * @return string
     */
    protected function arrJoinStr($arr) {
        return $this->Controller->arrJoinStr($arr);
    }
}
