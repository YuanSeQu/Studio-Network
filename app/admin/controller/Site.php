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


class Site extends Base
{
    /**
     * 导航菜单
     * @throws \Exception
     */
    function menus() {

        $this->pagedata['tabs'] = [
            ['name' => '导航菜单'],
        ];
        $this->pagedata['actions'] = [
            ['label' => '添加一级菜单', 'target' => 'dialog', 'href' => U('Site/addMenus'), 'options' => '{title:"添加菜单",area:["450px"]}'],
        ];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'title', 'class' => 'js-lanmu', 'title' => '＋ 菜单名称', 'width' => '550', 'align' => 'left', 'callback' => function ($item) {
                $w = 20 * $item['depth'];
                return '<i class="layui-icon layui-icon-subtraction mr5"></i><span class="w40x" style="width:' . $w . 'px"></span><span>' . $item['title'] . '</span>';
            }],
            ['field' => 'sort', 'title' => '排序', 'width' => '70', 'callback' => function ($item) {
                return '<input  href="' . U('Site/sortMenus') . '" class="layui-input layui-input-sm js-sort" data-val="' . $item['sort'] . '" value="' . $item['sort'] . '"  maxlength="3" type="text" />';
            }],
            ['field' => 'cz', 'title' => '操作', 'width' => '300', 'align' => 'left', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Site/addMenus', ['pid' => $item['id'],]) . '" options="{title:\'添加菜单\',area:[\'450px\']}" target="dialog" class="layui-btn layui-btn-primary layui-btn-xs"">添加下级菜单</a>';
                $html .= '<a href="' . U('Site/addMenus', ['id' => $item['id'], 'pid' => $item['parent_id'],]) . '" options="{title:\'编辑菜单\',area:[\'450px\']}" target="dialog" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Site/delMenus', ['id' => $item['id'],]) . '" msg="确定要删除吗？<p class=\'f12 cl-f44\'>下级菜单也将会被删除！</p>" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
        ];
        $data = M('site_menus')->field('id,title,depth,sort,parent_id,id_path')
            ->order('path asc,id asc')
            ->select()->toArray();

        $this->pagedata['data'] = tierMenusList($data);

        $this->pagedata['trAttr'] = [
            'pid' => 'parent_id',
        ];//表格行属性
        $this->pagedata['pk_field'] = 'id';
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['isPage'] = false;
        $this->pagedata['grid_class'] = 'js-view-menus';

        return $this->grid_fetch();
    }

    /*
     * 菜单排序
     */
    function sortMenus() {
        $id = I('post.id');
        $sort = I('post.sort', 0);
        if (!is_numeric($id) || !is_numeric($sort)) $this->error();

        $row = M('site_menus')->where('id', $id)->field('path,id_path')->find();
        $path = $row['path'];
        $path = substr($path, 0, -4) . (1000 + ($sort > 1000 ? 999 : $sort));

        $rs = M('site_menus')->where('id', $id)->save(['sort' => $sort, 'path' => $path,]);
        $rs or $this->error();

        $len = strlen($path) + 1;
        M('site_menus')->where('id_path', 'like', $row['id_path'] . ',%')
            ->exp('path', "concat('{$path}',substring(path,{$len}))")->update();

        $this->updateCache();

        $this->success('', true);
    }

    /**
     * 更新数据缓存
     */
    function updateCache(){
        $lib = new \app\home\model\menus();
        $lib->updateCache();//更新数据
    }

    /**
     * 删除菜单
     * @throws \Exception
     */
    function delMenus() {
        $id = I('get.id');
        is_numeric($id) or $this->error('参数不合法！');

        $path = M('site_menus')->where('id', $id)->value('id_path');
        $where = ' id=' . $id;
        $path and $where .= " or(id_path like '{$path},%') ";

        $rs = M('site_menus')->whereRaw($where)->delete();
        $rs or $this->error('删除失败！');

        $this->updateCache();

        $this->success('删除成功！', true);
    }

    /**
     * 添加菜单
     * @throws \Exception
     */
    function addMenus() {
        if (!$this->request->isPost()) {
            $pid = I('get.pid');
            if (is_numeric($pid) && $pid > 0) {
                $title = M('site_menus')->where('id', $pid)->value('title');
                if ($title) {
                    $this->assign('ptitle', $title);
                    $this->assign('pid', $pid);
                }
            }
            $id = I('get.id');
            $id and $row = M('site_menus')->where('id', $id)->find();
            if ($id && !empty($row)) {
                $menus = M('site_menus')->field('id,title,depth,id_path')
                    ->order('path asc,id asc')
                    ->select()->toArray();
                $menus = tierMenusList($menus);
                foreach ($menus as &$item) {
                    $item['title'] = str_repeat('&nbsp;', ($item['depth'] - 1) * 4) . $item['title'];
                }
                $this->assign('menus', $menus);
            }

            $this->assign('row', $row ?? []);
            return $this->fetch();
        }

        $id = I('get.id');
        $data = I('post.', null, 'trim');

        $data['title'] = trim($data['title']) or $this->error('请填写菜单名称！');
        if ($data['dir_name'] && preg_match('/[^a-zA-Z0-9_\/]/', $data['dir_name'])) {
            $this->error('目录名称错误，仅支持字母、数字、下划线、斜杠！');
        }
        $pinyin = new \app\admin\lib\Piyin('site_menus');

        $data['dir_name'] = preg_replace('/\s+/', '', $data['dir_name']);//替换空格
        if ($data['dir_name'] && $pinyin->dirnameIsHas($data['dir_name'], $id)) {
            $this->error('目录名称已存在，请更改！');
        }
        $data['dir_name'] = $pinyin->get_dirname($data['title'], $data['dir_name'], $id);
        if ($id && is_numeric($id)) {
            /*自己的上级不能是自己*/
            if (intval($id) == intval($data['parent_id'])) {
                $this->error('自己不能成为自己的上级分类');
            }
            /*--end*/

            $oldInfo = M('site_menus')->where('id', $id)->find();
            if ($oldInfo['parent_id'] != $data['parent_id']) {
                $pInfo = M('site_menus')->where('id', $data['parent_id'])->field('depth,path,id_path')->find();
                $sort = M('site_menus')->where('parent_id', $data['parent_id'])->max('sort');
                $sort = $sort ? $sort + 1 : 1;

                $data['depth'] = ($pInfo['depth'] ?? 0) + 1;
                $data['path'] = ($pInfo['path'] ?? '') . (1000 + ($sort > 1000 ? 999 : $sort));
                $data['sort'] = $sort;
                $data['id_path'] = empty($pInfo['id_path']) ? $id : $pInfo['id_path'] . ',' . $id;
            }

            $rs = M('site_menus')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');

            if ($oldInfo['parent_id'] != $data['parent_id']) {
                $list = M('site_menus')->where('id_path', 'like', $oldInfo['id_path'] . ',%')->field('id,depth,path,id_path')
                    ->order('path asc,id asc')->select()->toArray();
                foreach ($list as $item) {
                    $path = $data['path'] . substr($item['path'], strlen($oldInfo['path']));
                    $id_path = $data['id_path'] . substr($item['id_path'], strlen($oldInfo['id_path']));
                    $depth = substr_count($id_path, ',') + 1;
                    M('site_menus')->where('id', $item['id'])->save([
                        'depth' => $depth,
                        'path' => $path,
                        'id_path' => $id_path,
                    ]);
                }
            }

            $this->success('保存成功！', true);
        }
        $pid = isset($data['parent_id']) && is_numeric($data['parent_id']) ? $data['parent_id'] : 0;

        $pInfo = M('site_menus')->where('id', $pid)->field('depth,path,id_path')->find();
        $sort = M('site_menus')->where('parent_id', $pid)->max('sort');
        $sort = $sort ? $sort + 1 : 1;

        $data['depth'] = ($pInfo['depth'] ?? 0) + 1;
        $data['path'] = ($pInfo['path'] ?? '') . (1000 + ($sort > 1000 ? 999 : $sort));
        $data['sort'] = $sort;

        $rId = M('site_menus')->insert($data, true);
        $rId or $this->error('保存失败！');

        M('site_menus')->where('id', $rId)->save([
            'id_path' => (isset($pInfo['id_path']) && $pInfo['id_path'] ? $pInfo['id_path'] . ',' : '') . $rId
        ]);

        $this->success('保存成功！', true);
    }

    /**
     * 更新路由
     * @throws \Exception
     */
    function updateRoute() {
        \app\facade\route::schema(false);//重新生成路由
        $this->success('操作成功！');
    }

    /**
     * 站点缓存设置
     * @throws \Exception
     */
    function cache() {
        if (!$this->request->isPost()) {
            $webcache = sysConfig('webcache');
            $webcache or $webcache = ['switch' => 1, 'browser' => 1, 'html' => 1, 'data' => 1,];
            $this->assign('webcache', $webcache);
            return $this->fetch();
        }
        $data = I('post.', null, 'trim');
        $switch = array_filter($data);
        $data['switch'] = $switch ? 1 : 0;
        sysConfig('webcache', $data);
        $this->success('保存成功！', ['switch' => $switch ? 1 : 0]);
    }


    /**
     * 站点配置
     * @throws \Exception
     */
    function setting() {
        $this->assign('website', sysConfig('website'));

        //====admin
        $admin = sysConfig('admin');
        $file = $this->app->getRootPath() . '.env.php';
        $strConfig = file_get_contents($file);
        $app_debug = strtolower(strMatch('/app_debug(?:\s+)?=(?:\s+)?(\w+)\s/iU', $strConfig));
        $admin['app_debug'] = ($app_debug === 'true' || $app_debug === '1') ? true : false;
        $admin['app_map'] = strMatch('/admin_map(?:\s+)?=(?:\s+)?(?:")?(\w+)(?:")?\s/iU', $strConfig);

        $this->assign('admin', $admin);
        //====

        //==== webinfo、custom
        $webinfo = sysConfig('webinfo');
        $this->assign('webinfo', $webinfo);

        $cusList = @json_decode($webinfo['custom'], true) ?: [];
        $cusInfo = sysConfig('custom');

        foreach ($cusList as &$item) {
            $item['value'] = $cusInfo[$item['name']] ?? '';
        }
        $this->assign('cusList', json_encode($cusList));
        //====


        $this->assign('webfilter', sysConfig('webfilter'));

        $filesize = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : '未开启';
        $this->assign('filesize', $filesize);

        $upload = sysConfig('upload');
        if (!$upload) {
            $upConfig = C('imgspace.config');
            foreach ($upConfig as $key => $item) {
                $upload[$key . '_ext'] = $item['fileExt'] ?? '';
                $upload[$key . '_mime'] = $item['fileMime'] ?? '';
            }
        }

        $this->assign('upload', $upload);

        return $this->fetch();
    }

    /**
     * 保存配置
     * @throws \Exception
     */
    function saveConfig() {
        $data = I('post.');
        $url = '';
        foreach ($data as $key => $item) {
            if ($key == 'admin') {
                if (preg_match('/[^a-zA-Z0-9_]/', $item['app_map'])) {
                    $this->error('后台路径目录名只支持字母数字组合！');
                }
                $old = sysConfig('admin.app_map') ?: 'admin';
                if ($item['app_map'] != $old) {
                    $map = trim($item['app_map']);
                    $this->setAdminMap($map);
                    $url = U('Index/index');
                    $url = str_replace('/' . $old, '/' . $map, $url);
                    $item['app_map'] = $map;
                }
                $this->setAppDebug($item['app_debug']);
            } elseif ($key == 'webinfo') {
                $custom = @json_decode($item['custom'], true) ?: [];
                $where = [
                    ['type', '=', 'custom'],
                ];
                if ($ks = array_column($custom, 'name')) {
                    $where[] = ['name', 'not in', $ks];
                }
                M('config')->where($where)->delete();//删除多余的参数
            }
            sysConfig($key, $item);
        }
        $data = '';
        $url = $data = ['jump' => $url];
        $this->success('保存成功！', $data);
    }

    //设置调试模式
    private function setAppDebug($debug) {
        $file = $this->app->getRootPath() . '.env.php';
        $strConfig = file_get_contents($file);
        $strConfig = preg_replace('/app_debug(\s+)?=(\s+)?(\w+)/i', 'APP_DEBUG = ' . ($debug ? 'true' : 'false'), $strConfig);
        file_put_contents($file, $strConfig);
    }

    //设置后台目录
    private function setAdminMap($map) {
        $file = $this->app->getRootPath() . '.env.php';
        $strConfig = file_get_contents($file);
        $strConfig = preg_replace('/ADMIN_MAP(\s+)?=(\s+)?(")?([\w\_\-\@]*)(")?/i', "ADMIN_MAP = \"{$map}\"", $strConfig);
        file_put_contents($file, $strConfig);
        return true;
    }

}