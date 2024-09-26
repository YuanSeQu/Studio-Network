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


use think\facade\Db;

class User extends Account
{

    /**
     * 不检验登陆验证的Action
     * @var array
     */
    protected $ignoreAction = ['verify', 'login', 'reg', 'reg_verify'];

    /**
     * 会员中心
     * @return string
     * @throws \Exception
     */
    function index() {
        $this->userMenus();
        $user = M('users')->where('id', $this->account['id'])->find();
        $this->assign('rrz', [
            'user' => $user,
            'count_articles' => M('articles')->where('user_id', $this->account['id'])->where('deltime',0)->count(),
            'isContribute' => sysConfig('users.is_contribute'),
        ]);
        return $this->page('user_index', '会员中心');
    }

    /**
     * 投稿列表
     * @return string
     * @throws \Exception
     */
    function articles() {
        $this->userMenus();
        $page = I('get.p', 1);
        $limit = 10;
        $where = [
            ['a.user_id', '=', $this->account['id'],],
            ['a.deltime', '=', 0,],
        ];
        $model = M('articles')->alias('a')
            ->field('a.*,b.name as type_name,b.id as type_id')
            ->join('article_nodes b', 'a.node_id=b.id', 'left');
        $model->where($where);

        $count_model = clone $model;
        $count = $count_model->count();

        $list = $model->order('sort asc,a.id desc')->page($page, $limit)->select()->toArray();
        $lib = new \app\home\lib\Articles;
        foreach ($list as $key => $item) {
            $item['url'] = $lib->getUrl($item['id'], 'article', $item['type_id']);
            if ($item['wap_content'] && $this->env['isMobile'] && strlen(trim($item['wap_content'])) > 20) {
                $item['content'] = $item['wap_content'];
            }
            $item['brief'] = $item['seo_description'];
            $item['type_url'] = $item['type_id'] ? $lib->getUrl($item['type_id'], 'node', $item['type_id']) : '';
            $item['img'] = $item['img'] ?: $this->env['defImg'];
            $list[$key] = $item;
        }

        $this->assign('__DATA__', ['list' => $list, 'count' => $count, 'cur' => $page, 'limit' => $limit, 'max' => $count ? ceil($count / $limit) : 0,]);

        return $this->page('user_articles', '会员投稿');
    }

    /**
     * 发布投稿
     * @return string
     * @throws \Exception
     */
    function addArticle() {
        $this->is_contribute();

        $id = I('get.id');
        is_numeric($id) and $row = \app\admin\model\Articles::getInfo($id);

        $this->userMenus();

        $nodeIds = sysConfig('users.contribute_nodeids');
        $nodeIds = explode(',', $nodeIds);

        $pathIds = M('article_nodes')->where('id', 'in', $nodeIds)->column('id_path');
        $pathIds = explode(',', implode(',', $pathIds));

        $nodes = M('article_nodes')->field('id,name,depth,id_path')
            ->order('path asc,id asc')->where('deltime', 0)->select()->toArray();
        $nodes = tierMenusList($nodes);
        $nodeList = [];
        foreach ($nodes as $item) {
            if (!in_array($item['id'], $pathIds)) continue;
            $name = str_repeat('&nbsp;', ($item['depth'] - 1) * 4) . $item['name'];
            $nodeList[] = [
                'id' => $item['id'],
                'title' => $name,
                'disabled' => in_array($item['id'], $nodeIds) == false,
            ];
        }

        $this->assign('rrz', [
            'nodeList' => $nodeList,
            'data' => $row ?? [],
        ]);

        return $this->page('article_add', '发布投稿');
    }

    private function is_contribute() {
        $isContribute = sysConfig('users.is_contribute');
        if (!$isContribute) {
            $this->error('投稿功能未开启！');
        }
    }

    /**
     * 保存文章信息
     * @throws \Exception
     */
    function saveArticle() {
        $this->is_contribute();
        $id = I('get.id');
        $data = I('post.', null, 'trim');
        unset($data['tags']);

        $data['title'] or $this->error('请填写文章标题！');
        $data['node_id'] or $this->error('请填选择文章栏目！');

        $data['uptime'] = time();
        $data['img'] or $data['img'] = get_html_first_imgurl($data['content']);
        if ($data['content']) {
            $data['seo_description'] = @msubstr(checkStrHtml($data['content']), 0, 125, false);
        }

        $nodeIds = sysConfig('users.contribute_nodeids');
        $nodeIds = explode(',', $nodeIds);

        if (!in_array($data['node_id'], $nodeIds)) {
            $this->error('选择的栏目没有开启投稿！');
        }

        //处理文章状态是否需要审核
        $auto = sysConfig('users.contribute_auto');
        if (!$auto) {
            $data['ifpub'] = 'false';
        }

        if (is_numeric($id)) {
            $rs = M('articles')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');
            \app\admin\model\Articles::onAfterSave($id, $data);
            $this->success('保存成功！', U('/user/articles'));
        }
        $data['user_id'] = $this->account['id'];
        $data['author'] = $this->account['nickname'];

        $data['pubtime'] = time();
        $data['add_time'] = time();
        $rId = M('articles')->insert($data, true);
        $rId or $this->error('保存失败！');
        \app\admin\model\Articles::onAfterSave($rId, $data);

        $this->success('保存成功！', U('/user/articles'));
    }

    /**
     * 删除投稿
     * @return string
     * @throws \Exception
     */
    function delArticle() {
        $id = I('get.id');
        $id = $this->checkIds($id);
        $where = [
            ['id', 'in', $id],
            ['user_id', '=', $this->account['id']]
        ];
        $rs = M('articles')->where($where)->delete();
        $rs or $this->error('删除失败！');
        $this->success('删除成功！', true);
    }

    /**
     * 个人信息
     * @return string
     * @throws \Exception
     */
    function info() {

        if (!$this->request->isPost()) {
            $where = [
                ['is_hidden', '=', 0],
            ];
            $fields = M('users_attribute')->field('title,name,dtype,is_required,dfvalue')
                ->where($where)->order('sort asc,id asc')->select()->toArray();

            $user = M('users')->where('id', $this->account['id'])->find();
            $attr_info = M('users_attr')->where('user_id', '=', $this->account['id'])->find();

            foreach ($fields as $item) {
                $user[$item['name']] = $attr_info[$item['name']] ?? '';
            }

            $rrz = [
                'user' => $user,
                'attr_config' => $fields,
            ];

            $this->assign('rrz', $rrz);
            $this->userMenus();

            return $this->page('user_info', '个人信息');
        }

        $data = I('post.', null, 'trim');
        $attr = $data['attr'];
        $data['nickname'] or $this->error('请填写昵称！');

        $model = new \app\home\model\Users();
        if (!$model->isAttrRequired($attr, 'info', $msg)) {
            $this->error($msg);
        }

        $save = [
            'nickname' => $data['nickname'],
            'update_time' => time(),
        ];
        if (isset($attr['mobile'])) {
            $save['mobile'] = $attr['mobile'];
        }
        if (isset($attr['email'])) {
            $save['email'] = $attr['email'];
        }

        if ($data['password']) {
            $save['password'] = md5(base64_encode($data['password'] . '|RRZCMS'));
        }
        $rs = M('users')->where('id', $this->account['id'])->save($save);
        $rs === false and $this->error('保存失败！');

        M('users_attr')->where('user_id', $this->account['id'])->save($attr);

        unset($save['password']);
        $account = array_merge($this->account, $save);
        $this->setAccount($account);//更新登陆信息

        $this->success('保存成功！');
    }

    /**
     * 设置头像
     * @return string
     * @throws \Exception
     */
    function upAvatar() {
        $url = I('post.url');
        if ($url) {
            M('users')->where('id', $this->account['id'])->save(['head_pic' => $url,]);
            $this->success('设置成功！');
        }
        $this->success('设置成功！');
    }

    /**
     * 会员注册
     * @return string
     * @throws \Exception
     */
    function reg() {

        sysConfig('users.open_reg') or $this->error('注册功能尚未开放！');

        if (!$this->request->isPost()) {

            $where = [
                ['is_hidden', '=', 0],
                ['is_reg', '=', 1],
            ];

            $fields = M('users_attribute')->field('title,name,dtype,is_required,dfvalue')
                ->where($where)->order('sort asc,id asc')->select()->toArray();

            $rrz = [
                'attr_config' => $fields,
                'is_verify' => C('captcha.enabled'),
            ];
            $this->assign('rrz', $rrz);

            return $this->page('user_reg', '账号注册');
        }

        $data = I('post.', null, 'trim');
        $attr = $data['attr'] ?? [];
        unset($data['attr']);

        if (C('captcha.enabled')) {
            if (empty($data['vertify'])) {
                $this->error('图片验证码不能为空！');
            }
            if (!captcha_check($data['vertify'] ?? '')) {
                $this->error('验证码输入错误！');
            }
        }

        empty($data['user_name']) and $this->error('用户名不能为空！');
        if (!preg_match("/^[\x{4e00}-\x{9fa5}\w\-\_\@\#]{2,30}$/u", $data['user_name'])) {
            $this->error('请输入2-30位的汉字、英文、数字、下划线等组合');
        }
        $notallow = sysConfig('users.reg_notallow');
        $notallow = array_filter(explode('|', $notallow));
        if ($notallow && in_array($data['user_name'], $notallow)) {
            $this->error('用户名为系统禁止注册！');
        }
        //用户名验证
        $count = M('users')->where('name', $data['user_name'])->count();
        if ($count) {
            $this->error('用户名已存在！');
        }

        empty($data['password']) and $this->error('密码不能为空！');
        empty($data['password2']) and $this->error('重复密码不能为空！');
        $data['password'] == $data['password2'] or $this->error('重复密码不一致！');

        $model = new \app\home\model\Users();
        if (!$model->isAttrRequired($attr, 'reg', $msg)) {
            $this->error($msg);
        }

        $data = [
            'name' => $data['user_name'],
            'password' => md5(base64_encode($data['password'] . '|RRZCMS')),
            'head_pic' => '/static/images/dfboy.png',//设置默认头像
            'nickname' => $data['user_name'],//昵称和用户名一致
            'mobile' => $attr['mobile'] ?? '',
            'email' => $attr['email'] ?? '',
            'reg_time' => time(),
            'register_place' => 2,//1为后台注册，2为前台注册。默认为2。
        ];
        $reg_verify = sysConfig('users.reg_verify');
        if ($reg_verify == 1) {//注册验证 1 后台激活
            $data['is_activation'] = 0;
        }

        $userId = M('users')->insert($data, true) or $this->error('注册失败！');

        $count = M('users_attr')->where('user_id', $userId)->count();
        if ($count) {
            M('users_attr')->where('user_id', $userId)->save($attr);
        } else {
            $attr['user_id'] = $userId;
            M('users_attr')->insert($attr);
        }

        $this->success('注册成功，请登录！', U('/user/login'));
    }


    /**
     * 退出登录
     */
    function logout() {
        //清除登陆信息
        $this->setAccount(null);
        $url = getRrzUrl('/');
        $this->redirect($url);
    }

    /**
     * 注册验证码
     * @return \think\Response
     */
    function reg_verify() {
        ob_clean();
        return captcha();
    }

    /**
     * 验证码
     * @return \think\Response
     */
    function verify() {
        ob_clean();
        return captcha();
    }

    /**
     * 账号登陆
     * @return string
     * @throws \Exception
     */
    function login() {
        if (!$this->request->isPost()) {
            $rrz = [
                'is_verify' => C('captcha.enabled'),
                'is_reg' => sysConfig('users.open_reg'),
            ];
            $this->assign('rrz', $rrz);
            return $this->page('user_login', '账号登陆');
        }

        $data = I('post.', null, 'trim');

        if (C('captcha.enabled')) {
            if (empty($data['vertify'])) {
                $this->error('图片验证码不能为空！');
            }
            if (!captcha_check($data['vertify'] ?? '')) {
                $this->error('验证码输入错误！');
            }
        }

        if (empty($data['user_name'])) {
            $this->error('用户名不能为空！');
        } else if (!preg_match("/^[\x{4e00}-\x{9fa5}\w\-\_\@\#]{2,30}$/u", $data['user_name'])) {
            $this->error('用户名不正确！');
        }

        if (empty($data['password'])) {
            $this->error('密码不能为空！');
        }

        $where = [
            'name' => $data['user_name'],
            'is_del' => 0,
            //'password' => md5($data['password']),
        ];
        $row = M('users')->where($where)->find();
        if (!$row) $this->error('该用户不存在，请先注册！');

        //账号激活判断
        if (empty($row['is_activation'])) {
            $this->error('该会员尚未激活，请联系管理员！');
        }

        //账号锁定冻结
        if (!empty($row['is_lock'])) {
            $this->error('该账号冻结锁定，请联系管理员！');
        }

        if (md5(base64_encode($data['password'] . '|RRZCMS')) !== $row['password']) {
            $this->error('密码不正确！');
        }

        $save = [
            'last_login' => time(),
            'last_ip' => $this->request->ip(),
            'login_count' => Db::raw('login_count+1'),
        ];
        M('users')->where('id', $row['id'])->save($save);

        $row = array_merge($row, $save);
        $row['password'] = $data['password'];
        $this->setAccount($row);

        event('user.login.after', $row);//用户登录后的操作

        $this->success('登陆成功！', U('/user/index'));
    }


    /**
     * 获取会员中心菜单
     * @return mixed
     */
    protected function userMenus() {
        $menus = C('menus');
        $list = [];
        $action = strtolower($this->request->controller() . '/' . $this->request->action());
        foreach ($menus as $item) {
            if ($item['visible'] && $item['list']) {
                foreach ($item['list'] as $key => &$val) {
                    if (!$val['visible']) {
                        unset($item['list'][$key]);
                        continue;
                    }
                    $val['class'] = $action == strtolower($val['action']) ? 'on' : '';
                    $val['url'] = $val['action'] ? U('/' . $val['action']) : '';
                }
                $item['list'] = array_values($item['list']);
                $item['list'] and $list[] = $item;
            }
        }
        $this->assign('user_menus', $list);
        return $list;
    }


}