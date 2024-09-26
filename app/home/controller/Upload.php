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


class Upload extends \app\admin\controller\Upload
{


    protected function initialize() {

        $ref = $this->request->server('HTTP_REFERER');
        $host = $this->request->host();

        //不是注册页上传需要验证登陆状况
        if (strpos($ref, $host) === false || strpos($ref, '/user/reg') === false) {
            parent::initialize();
        } else {
            $this->setConfig();
        }
    }

    /**
     * 获取文件保存路径
     * @param $type
     * @return string
     */
    protected function getSavePath($type = '') {
        $accountId = $this->account['id'] ?? 0;
        return 'user/' . $accountId . '/' . $type;
    }

}