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


class Users extends Base
{
    /**
     * 会员列表
     * @return false|string
     * @throws \Exception
     */
    function index() {
        $checkType = I('get.checkType');
        $this->pagedata['tabs'] = [
            ['name' => '会员列表', 'class' => 'current',],
            ['name' => '会员属性', 'url' => U('Users/attribute'),],
            ['name' => '功能配置', 'url' => U('Users/config'),],
        ];
        $this->pagedata['search'] = [
            ['tag' => 'input', 'name' => 'name|has|trim', 'placeholder' => '用户名',],
        ];

        $this->pagedata['actions'] = [
            ['label' => '新增会员', 'target' => 'page', 'href' => U('Users/addUser'),],
            ['label' => '批量操作', 'group' => [
                ['label' => '批量新增', 'target' => 'dialog', 'href' => U('Users/addUsers'), 'options' => '{title:"批量新增会员",area:["550px","450px"]}',],
                ['label' => '批量删除', 'target' => 'confirm', 'msg' => '确定要删除已选数据吗？', 'argpk' => 1, 'href' => U('Users/delUser'),],
            ],]
        ];
        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '50',],
            ['field' => 'head_pic', 'title' => '', 'width' => '50', 'callback' => function ($item) {
                return '<img width="40" height="40" src="' . ($item['head_pic'] ?: '/static/images/dfboy.png') . '"/>';
            },],
            ['field' => 'name,nickname', 'title' => '昵称/用户名', 'width' => '150', 'align' => 'left', 'callback' => function ($item) {
                $html = '';
                $html .= '<p>' . $item['nickname'] . '</p>';
                $html .= '<p class="f12 cl-999">' . $item['name'] . '</p>';
                return $html;
            },],
            ['field' => 'is_activation', 'title' => '激活', 'width' => '150', 'callback' => function ($item) {
                return '<div class="layui-form"><input type="checkbox" name="is_activation" lay-filter="user-isActivation" lay-skin="switch" lay-text="是|否" value="1" ' . ($item['is_activation'] ? 'checked' : '') . ' unvalue="0"/></div>';
            }],
            ['field' => 'reg_time', 'title' => '注册日期', 'width' => '150', 'type' => 'time',],
            ['field' => 'cz', 'title' => '操作', 'width' => '150', 'callback' => function ($item) {
                $html = '<a href="' . U('Users/addUser', ['id' => $item['id'],]) . '" target="page" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Users/delUser', ['id' => $item['id'],]) . '" msg="确定要删除吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return '<div>' . $html . '</div>';
            }],
        ];
        $this->pagedata['model'] = M('users')->where('is_del', 0)->order('id desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['grid_class'] = 'js-grid-users';
        $this->pagedata['checkType'] = $checkType ?: true;
        return $this->grid_fetch();
    }

    /**
     * 批量新增会员
     * @throws \Exception
     */
    function addUsers() {
        if (!$this->request->isPost()) {
            return $this->fetch();
        }
        $name = I('post.name', null, 'trim');
        $pwd = I('post.password', null, 'trim');

        $this->validate(['用户名' => $name, '密码' => $pwd,],
            ['用户名' => 'require', '密码' => 'require']
        );//验证数据

        $nameArr = explode("\r\n", $name);
        $nameArr = array_filter($nameArr, 'trim');//去除前后空格
        $nameArr = array_filter($nameArr);//去除空值
        $nameArr = array_unique($nameArr); //去重

        $nameList = M('users')->where('name', 'in', $nameArr)->column('name');

        $list = [];
        foreach ($nameArr as $val) {
            if (trim($val) == '' || empty($val) || in_array($val, $nameList) || !preg_match("/^[\x{4e00}-\x{9fa5}\w\-\_\@\#]{2,30}$/u", $val)) {
                continue;
            }
            $list[] = [
                'name' => trim($val),
                'nickname' => trim($val),
                'password' => md5(base64_encode($pwd . '|RRZCMS')),
                'head_pic' => '/static/images/dfboy.png',
                'reg_time' => time(),
                'register_place' => 1,
            ];
        }
        M('users')->insertAll($list) or $this->error('操作失败！');

        $this->success('操作成功！', 'Users/index');
    }

    /**
     * 删除会员
     * @throws \Exception
     */
    function delUser() {
        $id = I('get.id');
        $id = $this->checkIds($id);
        $rs = M('users')->where('id', 'in', $id)->save(['is_del' => 1]);
        $rs or $this->error('删除失败！');
        $this->success('删除成功！', true);
    }

    /**
     * 设置是否注册表单
     * @throws \Exception
     */
    function setUserIsActivation() {
        $id = I('get.id');
        $status = I('post.status', 0);
        if (!is_numeric($id) || !is_numeric($status)) $this->error();
        M('users')->where('id', $id)->save(['is_activation' => $status,]);
        $this->success();
    }

    /**
     * 添加会员
     * @throws \Exception
     */
    function addUser() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            is_numeric($id) and $row = M('users')->where('id', $id)->find();
            $this->assign('row', $row ?? []);

            //取出自定义字段
            $fields = M('users_attribute')->where('is_hidden', 0)->order('sort asc,id asc')->select()->toArray();
            $this->assign('fields', $fields ?: []);
            if (is_numeric($id) && $id) {
                $field = array_column($fields, 'name');
                $data = M('users_attr')->where('user_id', $id)->field($field)->find();
            }
            $this->assign('data', $data ?? []);

            return $this->fetch();
        }
        $data = I('post.', null, 'trim');
        unset($data['attr']);

        $attr = I('post.attr', null, 'trim');
        $user = new \app\admin\model\Users();
        $attr = $user->checkAttrFieldValue($attr);

        $this->validate($attr,
            ['mobile' => 'mobile', 'email' => 'email'],
            ['mobile' => '手机号码格式不正确！', 'email' => '邮箱地址格式不正确！']
        );//验证数据

        $data['mobile'] = $attr['mobile'];
        $data['email'] = $attr['email'];


        $data['head_pic'] = $data['head_pic'] ?: '/static/images/dfboy.png';
        if (is_numeric($id)) {
            if ($data['password']) {
                $data['password'] = md5(base64_encode($data['password'] . '|RRZCMS'));
            } else {
                unset($data['password']);
            }
            unset($data['name']);
            $data['update_time'] = time();
            $rs = M('users')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');

            $attr['user_id'] = $id;
            $count = M('users_attr')->where('user_id', $id)->count();
            M('users_attr')->where('user_id', $id)->save($attr, $count ? false : true);


            $this->success('保存成功！', 'Users/index');
        }

        $data['name'] or $this->error('请设置账户名！');
        M('users')->where('name', '=', $data['name'])->count() and $this->error('用户名已存在！');

        $data['password'] or $this->error('请设置账户密码！');
        if ($data['password']) {
            $data['password'] = md5(base64_encode($data['password'] . '|RRZCMS'));
        }
        $data['nickname'] = $data['nickname'] ?: $data['name'];
        $data['reg_time'] = time();
        $data['register_place'] = 1;

        $rId = M('users')->insert($data, true);
        $rId or $this->error('保存失败！');
        $attr['user_id'] = $rId;
        M('users_attr')->insert($attr);

        $this->success('保存成功！', 'Users/index');
    }


    /**
     * 会员属性列表
     * @throws \Exception
     */
    function attribute() {
        $this->pagedata['tabs'] = [
            ['name' => '会员列表', 'url' => U('Users/index'),],
            ['name' => '会员属性', 'class' => 'current',],
            ['name' => '功能配置', 'url' => U('Users/config'),],
        ];
        $this->pagedata['actions'] = [
            ['label' => '新增属性', 'target' => 'page', 'href' => U('Users/addAttribute'),],
        ];
        $fieldType = C('field.type');
        $fieldType['mobile'] = ['title' => '手机号码'];
        $fieldType['email'] = ['title' => '邮箱地址'];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'title', 'title' => '标题', 'width' => '150'],
            ['field' => 'name', 'title' => '名称', 'width' => '100',],
            ['field' => 'dtype', 'title' => '字段类型', 'width' => '150', 'callback' => function ($item) use ($fieldType) {
                return isset($fieldType[$item['dtype']]) ? $fieldType[$item['dtype']]['title'] : $item['dtype'];
            }],
            ['field' => 'is_hidden', 'title' => '禁用', 'width' => '50', 'type' => 'enum', 'enum' => ['否', '是'],],
            ['field' => 'is_reg', 'title' => '注册表单', 'width' => '150', 'callback' => function ($item) {
                return '<div class="layui-form"><input type="checkbox" name="is_reg" lay-filter="userAttribute-isReg" lay-skin="switch" lay-text="是|否" value="1" ' . ($item['is_reg'] ? 'checked' : '') . ' unvalue="0"/></div>';
            }],
            ['field' => 'sort', 'title' => '排序', 'width' => '70', 'callback' => function ($item) {
                return '<input class="layui-input layui-input-sm js-sort" data-val="' . $item['sort'] . '" value="' . $item['sort'] . '"  maxlength="3" type="text" />';
            }],
            ['field' => 'is_system', 'title' => '操作', 'width' => '100', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Users/addAttribute', ['id' => $item['id'],]) . '" target="page" class="layui-btn layui-btn-xs">编辑</a>';
                if ($item['is_system']) {
                    $html .= '<a href="javascript:;" class="layui-btn layui-btn-danger layui-btn-xs layui-btn-disabled">删除</a>';
                } else {
                    $html .= '<a href="' . U('Users/delAttribute', ['id' => $item['id'],]) . '" msg="<span class=cl-r>该属性的数据将一起清空</span>，确认彻底删除？" 
                        target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                }
                return $html;
            }],
        ];
        $this->pagedata['model'] = M('users_attribute')->order('sort asc,id asc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['grid_class'] = 'js-grid-user-attribute';
        return $this->grid_fetch();
    }

    /**
     * 属性排序
     * @throws \Exception
     */
    function sortAttribute() {
        $id = I('get.id');
        $sort = I('post.sort', 0);
        if (!is_numeric($id) || !is_numeric($sort)) $this->error();
        M('users_attribute')->where('id', $id)->save(['sort' => $sort,]);
        $this->success('', true);
    }

    /**
     * 设置是否注册表单
     * @throws \Exception
     */
    function setAttributeIsReg() {
        $id = I('get.id');
        $status = I('post.status', 0);
        if (!is_numeric($id) || !is_numeric($status)) $this->error();
        M('users_attribute')->where('id', $id)->save(['is_reg' => $status,]);
        $this->success();
    }

    /**
     * 添加属性
     * @throws \Exception
     */
    function addAttribute() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            is_numeric($id) and $row = M('users_attribute')->where('id', $id)->find();
            $this->assign('row', $row??[]);
            $fields = C('field.type');
            $fields = array_filter($fields, function ($item) {
                return in_array($item['name'], ['text', 'checkbox', 'multitext', 'radio', 'select', 'img', 'file']);
            });
            $this->assign('fieldType', array_values($fields));
            return $this->fetch();
        }

        $post = I('post.', null, 'trim');
        $user = new \app\admin\model\Users();
        $rs = $user->saveAttrField($id, $post, $msg);
        $rs or $this->error($msg);
        $this->success($msg ?: '操作成功！', 'Users/attribute');
    }

    /**
     * 删除会员属性
     * @throws \Exception
     */
    function delAttribute() {
        $id = I('get.id');
        is_numeric($id) or $this->error('参数不合法！');
        $user = new \app\admin\model\Users();
        $user->delAttrField($id, $msg) or $this->error($msg);
        $this->success($msg ?: '操作成功！', true);
    }


    /**
     * 功能配置
     */
    function config() {
        if (!$this->request->isPost()) {

            $data = sysConfig('users');
            $data['contribute_nodeids'] = empty($data['contribute_nodeids']) ? [] : explode(',', $data['contribute_nodeids']);
            $this->assign('data', $data);

            $nodes = M('article_nodes')->field('id,name,depth,id_path')->where('deltime', 0)->order('path asc,id asc')->select()->toArray();
            $nodes = tierMenusList($nodes);
            foreach ($nodes as &$item) {
                $item['name'] = str_repeat('&nbsp;', ($item['depth'] - 1) * 4) . $item['name'];
            }
            $this->assign('nodes', $nodes);
            return $this->fetch();
        }
        $users = I('post.users', null, 'trim');
        $users['contribute_nodeids'] = implode(',', $users['contribute_nodeids'] ?? []);

        sysConfig('users', $users);

        $this->success('操作成功！');
    }

}