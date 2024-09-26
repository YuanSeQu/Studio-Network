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

namespace app\home\controller;

use app\BaseController;

class Base extends BaseController
{

    /**
     * 模版引擎
     * @var \think\Template
     */
    protected $viewDriver;

    protected $env = [
        'lang' => '',//语言
        'page' => 'index',//当前页面类型
        'menu' => [],//当前菜单信息
        'isMobile' => false,//是否手机端
        'defImg' => '',//站点默认图
    ];//将作为系统变量输入到模版中

    protected function initialize() {
        parent::initialize();
        $lang = I('param.lang', $this->app->lang->getLangSet());
        $this->env['lang'] = $lang;
        $this->app->lang->setLangSet($lang);
        $this->env['isMobile'] = $this->app->request->isMobile();
        $this->env['defImg'] = sysConfig('website.def_img',null,'');

        $status = sysConfig('website.status');
        $status or exit('<div style="text-align:center; font-size:20px; font-weight:bold; margin:50px 0px;">' . __('网站暂时关闭，维护中……') . '</div>');

        $wap_domain = sysConfig('admin.wap_domain');
        if ($this->request->isMobile() && $wap_domain) {//手机站独立域名
            $wap_domain = trim($wap_domain);
            if (preg_match('/^http(s)?\:\/\//i', $wap_domain)) {
                $info = parse_url($wap_domain);
                if (strpos($this->request->domain(), $info['host']) === false) {
                    header('Location: ' . $wap_domain);
                    exit;
                }
            }
        }

        /**初始化模板位置**/
        $driver = $this->view::engine();
        $path = $templatePath = $driver->getConfig('view_path');
        $tpl_replace_string = $driver->getConfig('tpl_replace_string');

        $type = 'pc';
        if ($this->request->isMobile() && is_dir($path . 'mobile')) {
            $type = 'mobile';
        }

        $path = $path . $type . DIRECTORY_SEPARATOR;
        is_dir($path) or $this->error('不存在模版！');

        $tpl_replace_string['__TEMPLATE__'] = getRootUrl() . PUBLIC_PATH . ltrim($tpl_replace_string['__TEMPLATE__'], '/');
        $tpl_replace_string['__SKIN__'] = $tpl_replace_string['__TEMPLATE__'] . $type . '/skin/';
        $tpl_replace_string['%IMAGES%'] = $tpl_replace_string['__TEMPLATE__'] . $type . '/images';

        $conf = [
            'view_path' => $path,
            'tpl_replace_string' => $tpl_replace_string,
        ];

        $driver->config($conf);
        $this->viewDriver = $driver;

        //动态参数
        $confPath = $templatePath . 'config.json';
        if (is_file($confPath)) {
            $conf = @json_decode(file_get_contents($confPath), true);

            if (isset($conf['jsCss_version'])) {
                C(['version' => $conf['jsCss_version'],], 'config');
            }
        }
    }

    /*
     * 设置seo标题
     * @param string $page
     * @param array $rrz
     */
    protected function setSeoTitle($page = '', $rrz = []) {
        $seo = sysConfig('seo');
        $title = [$rrz['seo_title']];
        if (in_array($page, ['article', 'item']) && isset($seo['view_title']) && $seo['view_title'] > 1) {
            if ($seo['view_title'] == '3') {
                $lm = $rrz[($page == 'article' ? 'node' : 'cat') . '_name'] ?? '';
                $lm and $title[] = $lm;
            }
            $title[] = sysConfig('website.name');
        } elseif (in_array($page, ['node', 'cat', 'tag', 'search']) && isset($seo['list_title']) && $seo['list_title'] > 1) {
            if ($seo['list_title'] == '3') {
                $p = I('p', 1);
                $p and $title[] = __('第%s页', [$p,]);
            }
            $title[] = sysConfig('website.name');
        }
        $connector = $seo['connector_title'] ?? '_';
        $connector == '-' and $connector = ' - ';
        $rrz['seo_title'] = implode($connector, $title);
        return $rrz;
    }

    protected function checkTemplateFile(string $template) {
        $driver = $this->viewDriver;
        return $driver->exists($template);
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
        $lang = $this->env['lang'];
        if ($lang && $template && !is_file($template)) {
            $tpl = $template . '_' . $lang;
            if ($this->checkTemplateFile($tpl)) {
                $template = $tpl;
            }
        }
        $this->checkTemplateFile($template) or $this->error('模板【' . $template . '】不存在！');

        $this->assign('env', $this->env);
        $html = parent::fetch($template, $vars);
        $html = preg_replace('/\<\!\-\-(.*)\-\-\>/iU', '', $html);
        $webfilter = sysConfig('webfilter');
        if ($webfilter['status']) {
            $replace = array_filter(explode('|', $webfilter['replace']));
            $replace and $html = str_replace($replace, '***', $html);
        }
        return $html;
    }

    /**
     * 操作错误跳转的快捷方法.
     * @param mixed $msg 提示信息
     * @param string $url 跳转的 URL 地址
     * @param mixed $data 返回的数据
     * @param int $wait 跳转等待时间
     * @param array $header 发送的 Header 信息
     * @throws \Exception
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = []) {
        $msg = __($msg);
        parent::error($msg, $url, $data, $wait, $header);
    }

    /**
     * 操作成功跳转的快捷方法.
     * @param mixed $msg 提示信息
     * @param string $url 跳转的 URL 地址
     * @param mixed $data 返回的数据
     * @param int $wait 跳转等待时间
     * @param array $header 发送的 Header 信息
     * @throws \Exception
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = []) {
        $msg = __($msg);
        parent::success($msg, $url, $data, $wait, $header);
    }
}
