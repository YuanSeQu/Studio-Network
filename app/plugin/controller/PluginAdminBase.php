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


class PluginAdminBase extends PluginBase
{
    //初始化
    protected function initialize() {
        $this->getAccount();

        $this->setTabs();//设置默认页签
    }

    /*
     * 显示带有tab标签的页面
     * @param $tabcode 当前tab标签编码
     * @param $temp 要渲染的模板
     */
    function tab_fetch($tabcode, $temp) {
        return $this->page($temp);
    }

    /**
     * 页面输出
     * @param string $template
     * @return string
     * @throws \Exception
     */
    function page(string $template = '') {

        $body = $this->fetch($template);
        $this->assign('body', $body);

        $tabs = $this->fetch('admin@base/grid/tabs', ['tabs' => $this->pagedata['tabs'],]);
        $this->assign('tabs', $tabs);

        return $this->fetch('plugin@:page');
    }

    /**
     * 设置页签
     * @param array $tabs
     */
    function setTabs($tabs = []) {
        if (!$tabs) {
            $plugin = $this->getPlugin();
            $config = $plugin->info['config'];
            if (empty($config['tabs'])) {
                $config['tabs'] = $this->pagedata['tabs'] ?: [
                    ['code' => 'index', 'name' => $plugin->info['name'], 'url' => 'Admin/index', 'target' => 'page', 'urltype' => 1]
                ];
            }
            $tabs = $config['tabs'];

            $url = $this->request->url();

            foreach ($tabs as &$item) {
                if ($item['urltype'] == 1) {
                    $item['url'] = $this->getUrl($item['url']);
                }
                if ($url == $item['url']) {
                    $item['class'] = 'current';
                }
            }

            array_unshift($tabs, ['name' => '我的插件', 'url' => U('Plugin/index'),]);
            $name = '<a href="http://www.rrzcms.com/Admin/Plugins/plugininfo/code/' . $plugin->info['code'] . '.html?pluginlx=help" target="_blank">使用指南</a>';
            $tabs[] = ['name' => $name, 'target' => '_blank',];
        }
        $this->pagedata['tabs'] = $tabs;
    }

}