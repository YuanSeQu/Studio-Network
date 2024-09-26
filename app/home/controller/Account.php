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


class Account extends Base
{

    /**
     * 用户信息
     * @var
     */
    protected $account = [];

    /**
     * 不检验登陆验证的Action
     * @var array
     */
    protected $ignoreAction = [];

    protected function initialize() {
        parent::initialize();

        //关闭会员中心跳转到首页
        if (!sysConfig('users.open') || !sysConfig('admin.web_user')) {
            //清除登陆信息
            $this->setAccount(null);

            $url = getRrzUrl('/');
            if (!$this->request->isAjax()) {
                $this->redirect($url);
            }
            $this->request->isJson() or exit('error| |' . $url);
            exit(json_encode(['code' => 'no_login', 'msg' => ' ', 'url' => $url,]));
        }

        //登陆验证
        if (!in_array($this->request->action(), $this->ignoreAction)) {
            $this->account = $this->getAccount();
            if (!$this->account) {
                if ($this->request->isAjax()) {
                    $this->request->isJson() or exit('no_login');
                    exit(json_encode(['code' => 'no_login',]));
                } else {
                    $this->redirect(U('/user/login'));
                }
            }
        }
        $this->assign('account', $this->account);
    }

    /**
     * 输出页面
     * @param string $template
     * @param string $title
     * @return string
     * @throws \Exception
     */
    protected function page(string $template = '', string $title = '') {
        $template = 'user/' . $template;
        $body = $this->fetch($template);

        $connector = sysConfig('seo.connector_title') ?: '-';
        $connector == '-' and $connector = ' - ';

        $vars = [
            'title' => __($title) . $connector . sysConfig('website.name'),
            'body' => $body,
        ];

        //自定义基础模版
        if ($this->checkTemplateFile('user/base_page')) {
            $template = 'user/base_page';
        } else {
            $template = $this->getTemplateFile('base/page');
        }
        return $this->fetch($template, ['rrz' => $vars,]);
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
        if (!$this->checkTemplateFile($template)) {
            $conf = [
                'view_path' => $this->app->getAppPath() . 'view' . DIRECTORY_SEPARATOR,
            ];//重置模版文件路径
            $this->viewDriver->config($conf);
        }
        return parent::fetch($template, $vars);
    }

    /**
     * 获取模版路径
     * @param string $template
     * @return mixed|string
     */
    protected function getTemplateFile(string $template = '') {
        $path = $this->app->getAppPath();
        $template = str_replace('/', DIRECTORY_SEPARATOR, $template);
        $template = $path . 'view' . DIRECTORY_SEPARATOR . $template . '.html';
        return $template;
    }

    /**
     * 检测id是否合法参数
     * @param string $id
     * @return array|string
     * @throws \Exception
     */
    protected function checkIds($id = '') {
        if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
            $id = array_values(array_filter($id, 'is_numeric'));
            $id or $this->error('参数不合法！');
        } else {
            is_numeric($id) or $this->error('参数不合法！');
        }
        return $id;
    }

}