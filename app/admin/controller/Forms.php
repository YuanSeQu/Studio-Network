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


class Forms extends Base
{
    function index() {
        $this->pagedata['tabs'] = [
            ['name' => '表单数据', 'class' => 'current',],
            ['name' => '表单配置', 'url' => U('Forms/config'),],
        ];

        if (!$this->request->isPost()) {
            $formList = M('form_data')->field('distinct form_name')->select()->column('form_name', 'form_name');
            if (!$formList){
                $formList = M('forms')->field('title')->select()->column('title', 'title');
            }
            $this->pagedata['search'] = [
                ['tag' => 'input', 'name' => 'title|has|trim', 'placeholder' => '内容',],
                ['tag' => 'select', 'name' => 'form_name', 'placeholder' => '表单', 'value' => '', 'options' => ['' => ''] + $formList,],
            ];
            $this->pagedata['actions'] = [
                ['label' => '批量删除', 'target' => 'confirm', 'msg' => '确定要删除已选数据吗？', 'argpk' => 1, 'href' => U('Forms/delFormData'),],
            ];
        }

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'form_name', 'title' => '表单名称', 'width' => '150',],
            ['field' => 'content', 'title' => '提交内容', 'width' => '350', 'callback' => function ($item) {
                $content = json_decode($item['content'], true) ?: [];
                $content = http_build_query($content, '', '<br/>');
                $content = urldecode(str_replace('=', '：', $content));
                $str = text_msubstr(str_replace('<br/>', '，', $content), 50);
                $options = [
                    'area' => ['400px', '300px'],
                    'title' => false,
                    'content' => '<div class="p20">' . $content . '</div>',
                ];
                return '<a class="cl-38f" title="点击查看" target="dialog" data-options="' . htmlspecialchars(json_encode($options)) . '">' . $str . '</a>';
            }],
            ['field' => 'add_time', 'title' => '提交时间', 'width' => '150', 'type' => 'time',],
        ];
        $this->pagedata['model'] = M('form_data')->order('id desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['checkType'] = true;
        return $this->grid_fetch();
    }

    function delFormData(){
        $id = I('get.id');
        $id = $this->checkIds($id);
        $rs = M('form_data')->where('id', 'in', $id)->delete();
        $rs or $this->error('删除失败！');
        $this->success('删除成功！', true);
    }

    function config() {
        $this->pagedata['tabs'] = [
            ['name' => '表单数据', 'url' => U('Forms/index'),],
            ['name' => '表单配置', 'class' => 'current'],
        ];
        $this->pagedata['actions'] = [
            ['label' => '添加表单', 'target' => 'page', 'href' => U('Forms/addForm'),],
        ];
        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'title', 'title' => '表单名称', 'width' => '150',],
            ['field' => 'config', 'title' => '表单字段', 'width' => '350', 'callback' => function ($item) {
                $list = unserialize($item['config']) ?: [];
                $nams= array_column($list,'name');
                return '<div class="">' . implode('，', $nams) . '</div>';
            }],
            ['field' => 'cz', 'title' => '操作', 'width' => '120', 'callback' => function ($item) {
                $html = '<a href="' . U('Forms/addForm', ['id' => $item['id'],]) . '" target="page" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Forms/delForm', ['id' => $item['id'],]) . '" msg="确定要删除该表单吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
        ];
        $this->pagedata['model'] = M('forms')->where('is_del', 0)->order('id desc');
        $this->pagedata['fixedColumn'] = true;
        return $this->grid_fetch();
    }

    function delForm(){
        $id = I('get.id');
        is_numeric($id) or $this->error('参数不合法！');
        M('forms')->where('id', $id)->delete() or $this->error('删除失败！');
        $this->success('删除成功！', true);
    }

    function addForm() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            is_numeric($id) and $row = M('forms')->where('id', $id)->find();
            if (isset($row)){
                $row['config'] = unserialize($row['config']) ?: [];
            }
            $this->assign('row', $row ?? []);
            return $this->fetch();
        }
        $data = I('post.');
        $data['title'] = trim($data['title']) or $this->error('请填写表单名称！');
        $list = array_values(array_filter($data['list'] ?: [], function (&$item) {
            $item['name'] = trim($item['name']);
            if (!$item['name']) return false;
            return true;
        }));
        $list or $this->error('请填设置表单字段！');
        $data['config'] = serialize($list);

        unset($data['list'], $list);

        if (is_numeric($id)) {
            $rs = M('forms')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');
            $this->success('保存成功！', 'Forms/config');
        }
        $data['add_time'] = time();
        $rs = M('forms')->insert($data);
        $rs or $this->error('保存失败！');
        $this->success('保存成功！', 'Forms/config');
    }
}