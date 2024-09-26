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


class Seo extends Base
{
    /*
     * url配置
     */
    function index(){
        if (!$this->request->isPost()) {
            $admin = sysConfig('admin');
            $this->assign('admin', $admin);
            $this->assign('seo', sysConfig('seo'));
            return $this->fetch();
        }
        $data = I('post.');
        sysConfig('seo', $data);
        $this->success('操作成功！');
    }

    function sitemap() {
        if (!$this->request->isPost()) {
            $this->assign('sitemap', sysConfig('sitemap'));
            return $this->fetch();
        }
        $data = I('post.');
        sysConfig('sitemap', $data);
        sitemap_all();
        $this->success('操作成功！');
    }
    /*
     * 更新sitemap
     */
    function update_sitemap() {
        ignore_user_abort(true);//忽略断开
        sitemap_all(true);
        $this->success('操作成功！');
    }

    function push_zzbaidu($url = '', $type = 'add'){
        ignore_user_abort(true);//忽略断开
        if (!$this->request->post()) {
            $this->error('百度推送URL失败！');
        }
        // 获取token的值：http://ziyuan.baidu.com/linksubmit/index?site=http://www.rrzcms.com/
        $zzbaidutoken = sysConfig('sitemap.zzbaidutoken');
        if (empty($zzbaidutoken)) {
            $this->error('尚未配置实时推送Url的token！');
        }
        $urlsArr[] = $url;
        $type = ('edit' == $type) ? 'update' : 'urls';

        if (is_http_url($zzbaidutoken)) {
            $searchs = ["/urls?","/update?"];
            $replaces = ["/{$type}?", "/{$type}?"];
            $api = str_replace($searchs, $replaces, $zzbaidutoken);
        } else {
            $api = 'http://data.zz.baidu.com/'.$type.'?site='.$this->request->host(true).'&token='.trim($zzbaidutoken);
        }

        $result = curl([
            'url' => $api,
            'type' => 'post',
            'data' => implode("\n", $urlsArr),
            'headers' => ['Content-Type: text/plain'],
            'dataType' => 'json',
        ]);
        if (!empty($result['success'])) {
            $this->success('百度推送URL成功！');
        } else {
            $msg = !empty($result['message']) ? $result['message'] : '百度推送URL失败！';
            $this->error($msg);
        }
    }


    function links() {
        $this->pagedata['tabs'] = [
            ['name' => 'SEO配置', 'url' => U('Seo/index'),],
            ['name' => 'Sitemap', 'url' => U('Seo/sitemap'),],
            ['name' => '友情链接', 'class' => 'current',],
        ];
        $this->pagedata['actions'] = [
            ['label' => '添加友情链接', 'target' => 'page', 'href' => U('Seo/addLink'), 'options' => '{title:"添加友情链接",area:["400px"]}',],
        ];
        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'logo', 'title' => '', 'width' => '50', 'type' => 'img',],
            ['field' => 'title', 'title' => '网站名称', 'width' => '150', 'align' => 'left',],
            ['field' => 'url', 'title' => '链接地址', 'width' => '200', 'type' => 'url'],
            ['field' => 'status', 'title' => '显示', 'width' => '80', 'callback' => function ($item) {
                return '<div class="layui-form"><input type="checkbox" name="status" lay-filter="links-status" lay-skin="switch" lay-text="显示|隐藏" value="1" ' . ($item['status'] ? 'checked' : '') . ' unvalue="0"/></div>';
            }],
            ['field' => 'update_time', 'title' => '更新时间', 'width' => '150', 'type' => 'time',],
            ['field' => 'sort', 'title' => '排序', 'width' => '70', 'callback' => function ($item) {
                return '<input class="layui-input layui-input-sm js-sort" data-val="' . $item['sort'] . '" value="' . $item['sort'] . '"  maxlength="3" type="text" />';
            }],
            ['field' => 'cz', 'title' => '操作', 'width' => '150', 'callback' => function ($item) {
                $html = '<a href="' . U('Seo/addLink', ['id' => $item['id'],]) . '" target="page" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Seo/delLink', ['id' => $item['id'],]) . '" msg="确定要删除吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
        ];
        $this->pagedata['model'] = M('site_links')->order('id desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['grid_class'] = 'js-view-links';
        return $this->grid_fetch();
    }

    /**
     * 删除链接
     */
    function delLink() {
        $id = I('get.id');
        is_numeric($id) or $this->error('参数不合法！');
        $rs = M('site_links')->where('id', $id)->delete();
        $rs or $this->error('删除失败！');
        $this->success('删除成功！', true);
    }

    /**
     * 设置链接排序值
     */
    function sortLink() {
        $id = I('get.id');
        $sort = I('post.sort', 0);
        if (!is_numeric($id) || !is_numeric($sort)) $this->error();
        M('site_links')->where('id', $id)->save(['sort' => $sort,]);
        $this->success();
    }

    /**
     * 设置链接状态
     */
    function setLinkStatus() {
        $id = I('get.id');
        $status = I('post.status', 0);
        if (!is_numeric($id) || !is_numeric($status)) $this->error();
        M('site_links')->where('id', $id)->save(['status' => $status,]);
        $this->success();
    }

    /**
     * 添加保存 友情链接
     */
    function addLink() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            is_numeric($id) and $row = M('site_links')->where('id', $id)->find();
            $this->assign('row', $row ?? []);
            return $this->fetch();
        }
        $data = I('post.');
        $data['title'] = trim($data['title']) or $this->error('请填写网站名称！');
        $data['url'] = trim($data['url']) or $this->error('请填写网址URL！');
        $data['update_time'] = time();
        if (is_numeric($id)) {
            $rs = M('site_links')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');
            $this->success('保存成功！', 'Seo/links');
        }
        $data['add_time'] = time();
        M('site_links')->insert($data) or $this->error('保存失败！');

        $this->success('保存成功！', 'Seo/links');
    }
}