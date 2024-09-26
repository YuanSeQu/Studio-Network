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

use think\App;
use app\admin\controller\Base;
use app\plugin\lib\Common;

class PluginBase extends Base
{
    private $plugin;
    public $pluginCode;

    /**
     * 构造函数
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app) {
        $this->app = $app;
        $this->request = $this->app->request;
        $this->getPlugin();
        $this->view = $this->plugin->getView();

        $this->initController();
        // 控制器初始化
        $this->initialize();
    }

    /**
     * @return \app\plugin\lib\Plugin
     * @throws
     */
    public function getPlugin() {
        if (is_null($this->plugin)) {
            $this->pluginCode = $this->request->param('_plugin');
            $this->pluginCode = parse_name($this->pluginCode, 1);
            $class = Common::plugin_get_class($this->pluginCode);
            //检测是否安装
            $findPlugin = M('plugin')->field('id,status')->where('code', $this->pluginCode)->find();
            if (empty($findPlugin) || $findPlugin['status'] == 0) {
                if ($this->request->isAjax() && $this->request->isJson())
                    $this->error('插件未安装!');
                else
                    exit("<script>$.showMsg(false,'插件未安装!')</script>");
            }
            $this->plugin = new $class;
        }
        return $this->plugin;
    }

    /*
     * 获取URL
     */
    public function getUrl($con, $fun = '', $vars = [], $domain = false) {
        $url = $this->pluginCode . '://' . $con;
        if ($fun)
            $url .= '/' . $fun;
        return Common::plugin_get_url($url, $vars, $domain);
    }

    // 初始化
    protected function initialize() {
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access public
     * @param string $template 模板文件名或者内容
     * @param array $vars 模板变量
     * @return string
     * @throws \Exception
     */
    protected function fetch(string $template = '', array $vars = []): string {
        $driver = $this->view->engine();
        if (!$template) {
            $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            if (!empty($dbt[1]['function'])) {
                $template = $dbt[1]['function'];
            }
        }
        // 模板不存在 抛出异常
        if (!$driver->exists($template)) {
            return "插件模板文件不存在" . $template;
        }
        return parent::fetch($template, $vars);
    }

}