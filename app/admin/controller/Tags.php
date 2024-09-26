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

use app\admin\model\Tag;

class Tags extends Base
{

    /**
     * 标签列表
     * @return false|string
     * @throws \Exception
     */
    function index() {

        $this->pagedata['tabs'] = [
            ['name' => 'TAG标签列表',],
        ];
        $this->pagedata['search'] = [
            ['tag' => 'input', 'title' => 'title|has|trim', 'placeholder' => '标签',],
            ['tag' => 'select', 'name' => 'type', 'placeholder' => '类型', 'options' => ['' => '全部', '1' => '文章标签', '2' => '产品标签',],],
        ];

        $this->pagedata['actions'] = [
            ['label' => '批量新增', 'target' => 'dialog', 'href' => U('Tags/addAll'), 'options' => '{title:"批量新增",area:["450px","530px"]}',],
            ['label' => '批量操作', 'group' =>
                [
                    ['label' => '批量删除', 'target' => 'confirm', 'msg' => '确定要删除已选数据吗？', 'argpk' => 1, 'href' => U('Tags/delTag'),],
                    ['label' => '一键清空', 'target' => 'confirm', 'msg' => '确定要清空Tag数据吗？', 'href' => U('Tags/clearTag'),],
                ],
            ]
        ];
        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'title', 'title' => '标签', 'width' => '130', 'align' => 'left', 'callback' => function ($item) {
                return '<a href="' . U('/tags/' . $item['id'], [], true) . '" target="_blank">' . $item['title'] . '</a>';
            }],
            ['field' => 'type', 'title' => '类型', 'width' => '100', 'type' => 'enum', 'enum' => ['1' => '文章标签', '2' => '产品标签',],],
            ['field' => 'view_count', 'title' => '点击', 'width' => '70', 'callback' => function ($item) {
                return '<input class="layui-input layui-input-sm js-view_count tc p0" data-val="' . $item['view_count'] . '" value="' . $item['view_count'] . '"  maxlength="8" type="text" />';
            }],
            ['field' => 'total', 'title' => '文档数', 'width' => '100'],
            ['field' => 'is_common', 'title' => '设为常用', 'width' => '100', 'callback' => function ($item) {
                return '<div class="layui-form"><input type="checkbox" name="is_common" lay-filter="tag-iscommon" lay-skin="switch" lay-text="是|否" value="1" ' . ($item['is_common'] ? 'checked' : '') . ' unvalue="0"/></div>';
            }],
            ['field' => 'add_time', 'title' => '新增时间', 'width' => '100', 'type' => 'time', 'format' => 'Y-m-d',],
            ['field' => 'cz', 'title' => '操作', 'width' => '150', 'callback' => function ($item) {
                $html = '<a href="' . U('Tags/relation', ['id' => $item['id'], 'type' => $item['type'],]) . '" class="layui-btn layui-btn-primary layui-btn-xs js-tag-relation">关联</a>';
                $html .= '<a href="' . U('Tags/editTag', ['id' => $item['id'],]) . '" target="page" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Tags/delTag', ['id' => $item['id'],]) . '" msg="确定要删除吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
        ];
        $this->pagedata['model'] = M('tag')->order('id desc');
        $this->pagedata['grid_class'] = 'js-grid-Tags';
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['checkType'] = true;
        return $this->grid_fetch();
    }

    function relation() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            $relIds = M('tag_rel')->where('tag_id', $id)->column('rel_id');
            $this->success(['relIds' => $relIds, 'type' => I('get.type'), 'id' => $id,]);
        }
        if (!is_numeric($id) || !$id) {
            $this->error('参数错误！');
        }
        $row = M('tag')->where('id', $id)->find() or $this->error('标签数据不存在！');

        $relIds = I('post.relIds', null, 'trim');
        if (strpos($relIds, ',') !== false) {
            $relIds = explode(',', $relIds);
            $relIds = array_values(array_filter($relIds, 'is_numeric'));
        } elseif (is_numeric($relIds)) {
            $relIds = [$relIds];
        } else {
            $relIds = [];
        }

        $where = [
            ['tag_id', '=', $id],
            ['tag_type', '=', $row['type']],
        ];
        $oldIds = M('tag_rel')->where($where)->column('rel_id');

        $list = [];
        foreach ($relIds as $relId) {
            if (!in_array($relId, $oldIds)) {
                $typeId = 0;
                if ($row['type'] == 1) {
                    $typeId = M('articles')->where('id', $relId)->value('node_id');
                } elseif ($row['type'] == 2) {
                    $typeId = M('goods')->where('id', $relId)->value('cat_id');
                }
                $list[] = [
                    'tag_id' => $id,
                    'rel_id' => $relId,
                    'type_id' => $typeId,
                    'tag_type' => $row['type'],
                    'tag_title' => $row['title'],
                    'last_modify' => time(),
                ];
            }
        }
        //新增的
        if ($list) {
            M('tag_rel')->insertAll($list);
        }
        //删除的
        $delIds = array_diff($oldIds, $relIds);
        if ($delIds) {
            M('tag_rel')->where('tag_id', $id)->whereIn('rel_id', $delIds)->delete();
        }
        //更新文章数量
        M('tag')->where('id', $id)->save(['total' => count($relIds)]);

        $this->success('操作成功！', true);
    }

    function clearTag() {
        $sql = [
            'TRUNCATE TABLE ' . M('tag')->getTable(),
            'TRUNCATE TABLE ' . M('tag_rel')->getTable(),
        ];
        M()->batchQuery($sql) or $this->error('操作失败！');
        $this->success('操作成功！', true);
    }

    /**
     * 删除标签
     * @throws \think\db\exception\DbException
     * @throws \Exception
     */
    function delTag() {
        $id = I('get.id');
        $id = $this->checkIds($id);
        $rs = M('tag')->where('id', 'in', $id)->delete();
        $rs or $this->error('删除失败！');
        M('tag_rel')->where('tag_id', 'in', $id)->delete();
        $this->success('删除成功！', true);
    }

    /**
     * 编辑
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \Exception
     */
    function editTag() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            $row = M('tag')->where('id', $id)->find();
            $this->assign('row', $row);

            $temp = new \app\admin\lib\Template('/pc');
            $templist = $temp->getTmplPath('tag');
            $this->assign('templist', $templist);

            return $this->fetch();
        }
        if (!is_numeric($id) || !$id) $this->error('参数错误！');
        $data = I('post.', null, 'trim');

        M('tag')->where('id', $id)->save($data) or $this->error('操作失败！');
        $this->success('操作成功！', 'Tags/index');
    }

    /**
     * 批量添加
     * @return string
     * @throws \Exception
     */
    function addAll() {
        if (!$this->request->isPost()) {
            return $this->fetch();
        }

        $title = I('post.title', null, 'trim') or $this->error('请填写标签！');
        $type = I('post.type', 1, 'trim');

        $arry = explode("\r\n", $title);

        Tag::addAll($arry, $type) or $this->error('添加失败！');
        $this->success('操作成功！', true);
    }


    /**
     * 设置常用标签
     * @throws \Exception
     */
    function setTagIsCommon() {
        $id = I('get.id');
        $status = I('post.status', 0);
        if (!is_numeric($id) || !is_numeric($status)) $this->error();
        M('tag')->where('id', $id)->save(['is_common' => $status,]);
        $this->success();
    }

    /**
     * 设置浏览量
     * @throws \Exception
     */
    function setTagViewCount() {
        $id = I('get.id');
        $count = I('post.count', 0);
        if (!is_numeric($id) || !is_numeric($count)) $this->error();
        M('tag')->where('id', $id)->save(['view_count' => $count,]);
        $this->success();
    }

    /**
     * 获取常用标签
     * @throws \Exception
     */
    function getCommonList() {
        $type = I('get.type', 1);
        $key = I('get.key', '');

        $list = [];

        if (empty($key)) {
            $where = [
                ['tag_type', '=', $type],
            ];

            $list = M('tag_rel')->field('DISTINCT tag_id as id,tag_title as title')
                ->where($where)->order('last_modify desc,rel_id desc')->limit(3)->select()->toArray();
        }


        $where = [
            ['type', '=', $type],
            ['is_common', '=', 1],
        ];
        if ($list) {
            $ids = array_column($list, 'id');
            $where[] = ['id', 'not in', $ids];
        }
        if ($key) {
            $where[] = ['title', 'like', '%' . $key . '%'];
        }

        $num = 20 - count($list);
        $row = M('tag')->where($where)->field('id,title')->order('total desc, id desc')->limit($num)->select()->toArray();
        if (is_array($list) && is_array($row)) {
            $list = array_merge($list, $row);
        }

        //不够数量进行补充
        $surplusNum = $num - count($list);
        if (0 < $surplusNum) {
            $ids = array_column($list, 'id');
            $where = [
                ['type', '=', $type],
            ];
            $ids and $where[] = ['id', 'not in', $ids];
            if ($key) {
                $where[] = ['title', 'like', '%' . $key . '%'];
            }
            $row = M('tag')->where($where)->field('id,title')->order('total desc, id desc')->limit($surplusNum)->select()->toArray();
            if (is_array($list) && is_array($row)) {
                $list = array_merge($list, $row);
            }
        }

        $this->success($list);
    }

}