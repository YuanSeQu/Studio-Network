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
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;

class Login extends BaseController
{

    function index() {
        return $this->fetch();
    }

    function submit(){
        $data = I('post.', null, 'trim');
        if (C('captcha.enabled') && !captcha_check($data['vertify'] ?? '')) {
            return $this->error('验证码输入错误！');
        }
        $where = [
            'user_name' => $data['user_name'],
            'password' => md5($data['password']),
        ];
        $row = M('admin')->where($where)->find();
        if (!$row) {
            service('adminLoginError', $msg);
            return $this->error($msg ?: '用户名或密码错误！');
        }

        if ($row['status'] != 1) return $this->error('账号已禁用，请联系管理员！');

        $row['last_ip'] = getClientIP();
        $row['last_login'] = time();
        $row['login_cnt'] = $row['login_cnt'] + 1;
        M('admin')->where('id', $row['id'])->save([
            'last_ip' => $row['last_ip'],
            'last_login' => $row['last_login'],
            'login_cnt' => Db::raw('login_cnt+1'),
        ]);

        $row['password'] = $data['password'];

        $role = M('admin_role')->where('role_id', $row['role_id'])->find();
        $row['role_name'] = $role['role_name'] ?? '';
        $row['act_list'] = $role['act_list'] ?? '';


        //设置登陆信息
        $this->setAccount($row);

        adminLog('后台登录', $row['id']);

        return $this->success('登陆成功！','Index/index');
    }

    function logout(){
        $this->setAccount(null);
        $this->redirect(U('Login/index'));
    }

    function verify() {
        ob_clean();
        return captcha();
    }

    function setInlet() {
        ignore_user_abort(true);//忽略断开
        $inlet = I('inlet', 1);
        $inlet = is_numeric($inlet) ? $inlet : 1;
        sysConfig('admin.inlet', $inlet);
        exit('passing');
    }
}