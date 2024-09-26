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

namespace app\home\lib;


class User extends Base
{


    function getUserInfo($type = 'info') {
        $user = getAccount();
        $info = [
            'isLogin' => $user ? true : false,
            'info' => $user ?? [],
            'url' => [
                'login' => U('/user/login'),
                'reg' => U('/user/reg'),
                'logout' => U('/user/logout'),
                'index' => U('/user/index'),
            ],
            'hidden' => '',
            'htmlId' => $this->newDomId(),
        ];
        $isOpen = sysConfig('users.open');
        if (!$isOpen) return false;

        if ($type == 'info') {
            return $user ? $info : false;
        }
        if ($user) return false;

        if ($type == 'login') {
            $info = [
                'url' => U('/user/login'),
                'hidden' => '',
                'htmlId' => $this->newDomId(),
            ];
        } elseif ($type == 'reg') {
            $isOpenReg = sysConfig('users.open_reg');
            if (!$isOpenReg) return false;
            $info = [
                'url' => U('/user/reg'),
                'hidden' => '',
                'htmlId' => $this->newDomId(),
            ];
        }
        return $info;
    }

    /**
     * 生成domid
     * @return string
     */
    private function newDomId() {
        return 'dom_el_' . substr(md5(microtime()), 0, 6) . get_rand_str(6, 0, 4);
    }

}