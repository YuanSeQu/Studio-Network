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

class Channeltype extends Base
{

    function index() {
        $type = I('type', 'articles');
        $this->pagedata['tabs'] = $type == 'articles' ? [
            ['name' => '文章列表', 'url' => U('Article/index'),],
            ['name' => '文章栏目', 'url' => U('Article/nodes'),],
            ['name' => '文章模型', 'class' => 'current',],
        ] : [
            ['name' => '产品列表', 'url' => U('Goods/index'),],
            ['name' => '产品分类', 'url' => U('Goods/cat'),],
            ['name' => '产品品牌', 'url' => U('Goods/brand'),],
            ['name' => '产品模型', 'class' => 'current',],
        ];
        $this->pagedata['actions'] = [
            ['label' => '新增模型', 'target' => 'dialog', 'options' => '{title:"新增模型",area:["450px"]}', 'href' => U('Channeltype/addType', ['type' => $type,]),],
        ];
        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'title', 'title' => '模型名称', 'width' => '250', 'align' => 'left',],
            ['field' => 'cz', 'title' => '操作', 'width' => '200', 'callback' => function ($item) use ($type) {
                $html = '';
                $html .= '<a target="dialog" options="{title:\'编辑模型\',area:[\'450px\']}" href="' . U('Channeltype/addType', ['id' => $item['id'],]) . '" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Channeltype/delType', ['id' => $item['id'],]) . '" msg="<font color=&quot;#ff0000&quot;>此操作将会删除与该模型所有相关的数据且不可恢复，请谨慎操作！</font>是否确认删除？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                $html .= '<a class="layui-btn layui-btn-xs layui-btn-normal" href="' . U('Channeltype/fields', ['type_id' => $item['id'],]) . '" target="dialog" options="{area:[\'1020px\',\'550px\']}" >内容字段</a>';
                return $html;
            }],
        ];
        $this->pagedata['model'] = M('channeltype')->where('type', '=', $type)->order('id asc');
        $this->pagedata['fixedColumn'] = true;
        return $this->grid_fetch();
    }

    //删除模型
    function delType() {
        $id = I('get.id');
        is_numeric($id) or $this->error('参数错误！');
        $channel = M('channeltype')->where('id', $id)->find();
        //删除模型
        M('channeltype')->where('id', $id)->delete() or $this->error('删除失败！');
        //删除模型字段
        $ids = M('channelfield')->where('channel_id', $id)->column('id');
        $lib = new \app\admin\model\Channel;
        foreach ($ids as $field_id) {
            $lib->delField($field_id);
        }
        //清除分类表模型信息
        $table = $channel['type'] == 'articles' ? 'article_nodes' : 'goods_cat';
        M($table)->where('channel_id', $id)->save(['channel_id' => 0]);

        $this->success('删除成功！',true);
    }
    //添加模型
    function addType() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            $type = I('get.type', 'articles');
            $row = [];
            if (is_numeric($id)) {
                $row = M('channeltype')->where('id', $id)->find();
                $type = $row['type'];
            }
            $this->assign('type', $type);
            $this->assign('row', $row);
            return $this->fetch();
        }
        $data = I('post.', null, 'trim');
        $title = $data['title'] or $this->error('请设置模型名称！');
        $type = $data['type'] or $type = 'articles';
        if (is_numeric($id) && $id) {
            $rs = M('channeltype')->where('id', $id)->save(['title' => $title, 'update_time' => time(),]);
        } else {
            $rs = M('channeltype')->insert([
                'title' => $title,
                'type' => $type,
                'add_time' => time(),
            ]);
        }
        $rs or $this->error('保存失败！');
        $this->success('保存成功！', true);
    }

    //模型字段列表
    function fields(){
        $typeId = I('get.type_id');
        if (!is_numeric($typeId) || !$typeId) exit('参数错误！');
        $this->pagedata['tabs'] = [
            ['name' => '内容字段',],
        ];
        $this->pagedata['actions'] = [
            ['label' => '新增字段', 'target' => 'dialog', 'options' => '{title:"新增字段",area:["750px","550px"]}', 'href' => U('Channeltype/addField', ['type_id' => $typeId,]),],
        ];
        $fieldType = C('field.type');

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'title', 'title' => '字段标题', 'width' => '150',],
            ['field' => 'name', 'title' => '字段名称', 'width' => '150',],
            ['field' => 'dtype', 'title' => '字段类型', 'width' => '150', 'callback' => function ($item) use ($fieldType) {
                return isset($fieldType[$item['dtype']]) ? $fieldType[$item['dtype']]['title'] : $item['dtype'];
            }],
            ['field' => 'update_time', 'title' => '更新时间', 'type' => 'time', 'width' => '150',],
            ['field' => 'cz', 'title' => '操作', 'width' => '200', 'callback' => function ($item) {
                $html = '';
                $html .= '<a target="dialog" options="{title:\'编辑字段\',area:[\'750px\',\'550px\']}" href="' . U('Channeltype/addField', ['id' => $item['id'],]) . '" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Channeltype/delField', ['id' => $item['id'],]) . '" msg="<font color=&quot;#ff0000&quot;>此操作将会删除与该模型所有相关的数据且不可恢复，请谨慎操作！</font>是否确认删除？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
        ];
        $this->pagedata['model'] = M('channelfield')->where('channel_id',$typeId)->order('id asc');
        $this->pagedata['fixedColumn'] = true;
        return $this->grid_fetch();
    }

    //删除字段
    function delField(){
        $id = I('get.id');
        is_numeric($id) or $this->error('参数不合法！');
        $lib = new \app\admin\model\Channel;
        $lib->delField($id, $msg) or $this->error($msg);
        $this->success($msg ?: '操作成功！', true);
    }

    //添加、编辑、保存字段
    function addField() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            $typeId = I('get.type_id');
            if (is_numeric($id) && $id) {
                $row = M('channelfield')->where('id', $id)->find();
                $typeId = $row['channel_id'];
            }
            $this->assign('typeId', is_numeric($typeId) ? $typeId : 0);
            $this->assign('fieldType', array_values(C('field.type')));
            $this->assign('row', $row ?? []);
            return $this->fetch();
        }
        $post = I('post.', null, 'trim');
        $lib = new \app\admin\model\Channel;
        $rs = $lib->saveField($id, $post, $msg);
        $rs or $this->error($msg);
        $this->success($msg ?: '操作成功！', true);
    }

    /*
     * 获取自定义字段的html展示
     */
    function getCustomFieldHtml() {
        $type = I('post.type');
        $typeId = I('post.typeId');
        $dataId = I('post.dataId');
        $thWidth = I('post.thWidth', 100);
        //非法参数
        if (!is_numeric($typeId) || !$typeId || !in_array($type, ['articles', 'goods'])) {
            return '';
        }
        $table = $type == 'articles' ? 'article_nodes' : 'goods_cat';

        $info = M($table)->where('id', $typeId)->field('channel_id,parent_id')->find();
        $channelId = $info ? $info['channel_id'] : 0;
        //查找模型，向上查找一级
        if (!$channelId && $info['parent_id']) {
            $channelId = M($table)->where('id', $info['parent_id'])->value('channel_id');
        }
        //没有模型
        if (!$channelId){
            return '';
        }
        //取出自定义字段
        $fields = M('channelfield')->where('channel_id', $channelId)->order('sort asc,id asc')->select()->toArray();
        //没有字段
        if (!$fields){
            return '';
        }
        $this->assign('fields',$fields);

        if (is_numeric($dataId) && $dataId) {
            $field = array_column($fields, 'name');
            $data = M($type)->where('id', $dataId)->field($field)->find();
        }
        $this->assign('data', $data ?? []);
        $this->assign('thWidth', $thWidth ?: '100');

        return $this->fetch('channeltype/custom_fields');
    }


}