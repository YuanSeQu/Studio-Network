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
namespace app\install\controller;

use app\BaseController;

class Base extends BaseController
{


    protected function initialize() {

    }

    protected function page(string $template = '') {
        $this->assign('body', $this->fetch($template));
        return $this->fetch('base/page');
    }
}