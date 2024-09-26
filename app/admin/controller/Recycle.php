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


class Recycle extends Base
{
    /**
     * 回收站 - 文章管理
     * @throws \Exception
     */
    function articles() {
        $this->pagedata['tabs'] = [
            ['name' => '回收站 - 文章管理', 'class' => 'current',],
        ];
        $this->pagedata['search'] = [
            ['tag' => 'input', 'name' => 'a.title|has|trim', 'placeholder' => '文章标题',],
        ];
        $this->pagedata['actions'] = [
            ['label' => '批量操作', 'group' => [
                ['label' => '批量还原', 'target' => 'confirm', 'msg' => '<span class="cl-f44">选定文档与关联栏目一起还原</span>，确认批量还原？', 'argpk' => 1, 'href' => U('Recycle/restoreArticle'),],
                ['label' => '批量彻底删除', 'target' => 'confirm', 'msg' => '此操作不可恢复，确认批量彻底删除？', 'argpk' => 1, 'href' => U('Recycle/delArticle'),],
            ],],
        ];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '40',],
            ['field' => 'cz', 'title' => '操作', 'width' => '120', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Recycle/restoreArticle', ['id' => $item['id'],]) . '" class="layui-btn layui-btn-xs"  
                    msg="<span class=\'cl-f44\'>文档与关联栏目一起还原</span>，确认还原？" target="confirm">还原</a>';
                $html .= '<a href="' . U('Recycle/delArticle', ['id' => $item['id'],]) . '"  msg="此操作不可恢复，确认彻底删除？"
                     target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">彻底删除</a>';
                return $html;
            }],
            ['field' => 'img', 'title' => '', 'width' => '60', 'align' => 'right', 'type' => 'img',],
            ['field' => 'title', 'title' => '文章标题', 'width' => '250', 'align' => 'left'],
            ['field' => 'node_name', 'title' => '文章栏目', 'width' => '120',],
            ['field' => 'deltime', 'title' => '删除时间', 'width' => '150', 'type' => 'time',],
        ];
        $this->pagedata['model'] = M('articles')->alias('a')->where('a.deltime', '>', 0)
            ->field('a.id,a.title,a.img,a.deltime,a.node_id,b.name as node_name')
            ->join('article_nodes b', 'b.id=a.node_id', 'left')
            ->order('a.deltime desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['checkType'] = true;

        return $this->grid_fetch();
    }

    /**
     * 回收站 - 文章管理 - 还原
     * @throws \Exception
     */
    function restoreArticle() {
        $ids = I('get.id');
        $ids = $this->checkIds($ids);
        $rs = M('articles')->where('id', 'in', $ids)->save(['deltime' => 0,]);
        $rs === false and $this->error('操作失败！');

        $nodeIds = M('articles')->where('id', 'in', $ids)->column('node_id');

        $idPath = M('article_nodes')->where('id', 'in', $nodeIds)->column('id_path');
        $nodeIds = implode(',', $idPath);
        M('article_nodes')->where('id', 'in', $nodeIds)->where('deltime', '>', 0)->save(['deltime' => 0]);

        $this->success('操作成功！', true);
    }

    /**
     * 回收站 - 文章管理 - 彻底删除
     * @throws \Exception
     */
    function delArticle() {
        $ids = I('get.id');
        $ids = $this->checkIds($ids);
        $rs = M('articles')->where('id', 'in', $ids)->delete();
        $rs or $this->error('操作失败！');
        M('tag_rel')->where('rel_id', 'in', $ids)->where('tag_type', 1)->delete();
        $this->success('操作成功！', true);
    }

    /**
     * 回收站 - 文章栏目
     * @throws \Exception
     */
    function nodes() {
        $this->pagedata['tabs'] = [
            ['name' => '回收站 - 文章栏目', 'class' => 'current',],
        ];
        $this->pagedata['actions'] = [
            ['label' => '批量操作', 'group' => [
                ['label' => '批量还原', 'target' => 'confirm', 'msg' => '确定要还原所选数据？', 'argpk' => 1, 'href' => U('Recycle/restoreNode'),],
                ['label' => '批量彻底删除', 'target' => 'confirm', 'msg' => '此操作不可恢复，确认批量彻底删除？', 'argpk' => 1, 'href' => U('Recycle/delNode'),],
            ],],
        ];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '40',],
            ['field' => 'name', 'title' => '文章栏目', 'width' => '200', 'align' => 'left'],
            ['field' => 'deltime', 'title' => '删除时间', 'width' => '150', 'type' => 'time',],
            ['field' => 'cz', 'title' => '操作', 'width' => '150', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Recycle/restoreNode', ['id' => $item['id'],]) . '"  msg="<span class=\'cl-f44\'>如有父级栏目及文档将一起还原</span>，确认还原？" target="confirm"
                class="layui-btn layui-btn-xs">还原</a>';
                $html .= '<a href="' . U('Recycle/delNode', ['id' => $item['id'],]) . '"  msg="此操作不可恢复，确认彻底删除？"
                     target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">彻底删除</a>';
                return $html;
            }],
        ];
        $this->pagedata['model'] = M('article_nodes')->where('deltime', '>', 0)->order('deltime desc');

        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['checkType'] = true;

        return $this->grid_fetch();
    }

    /**
     * 回收站 - 文章栏目 - 还原
     * @throws \Exception
     */
    function restoreNode() {
        $ids = I('get.id');
        $ids = $this->checkIds($ids);
        $ids = is_array($ids) ? $ids : [$ids];
        $idPath = M('article_nodes')->where('id', 'in', $ids)->column('id_path');
        $ids = implode(',', $idPath);
        $ids = M('article_nodes')->where('id', 'in', $ids)->where('deltime', '>', 0)->column('id');

        $rs = M('article_nodes')->where('id', 'in', $ids)->save(['deltime' => 0]);
        $rs === false and $this->error('操作失败！');

        M('articles')->where('node_id', 'in', $ids)->where('deltime', '>', 0)->save(['deltime' => 0]);
        $this->success('操作成功！', true);
    }

    /**
     * 回收站 - 文章栏目 - 彻底删除
     * @throws \Exception
     */
    function delNode() {
        $ids = I('get.id');
        $ids = $this->checkIds($ids);
        $ids = is_array($ids) ? $ids : [$ids];
        foreach ($ids as $id) {
            $path = M('article_nodes')->where('id', $id)->value('id_path');
            $where = ' id=' . $id;
            $path and $where .= " or(id_path like '{$path},%') ";
            $where = '(' . $where . ') and deltime>0';
            $nIds = M('article_nodes')->whereRaw($where)->column('id');
            M('article_nodes')->whereRaw($where)->delete();
            M('articles')->where('node_id', 'in', $nIds)->delete();
            M('tag_rel')->where('type_id', 'in', $nIds)->where('tag_type', 1)->delete();
        }
        $this->success('操作成功！', true);
    }

    /**
     * 回收站 - 产品管理
     * @throws \Exception
     */
    function goods() {
        $this->pagedata['tabs'] = [
            ['name' => '回收站 - 产品管理', 'class' => 'current',],
        ];
        $this->pagedata['search'] = [
            ['tag' => 'input', 'name' => 'a.name|has|trim', 'placeholder' => '产品名称',],
        ];
        $this->pagedata['actions'] = [
            ['label' => '批量操作', 'group' => [
                ['label' => '批量还原', 'target' => 'confirm', 'msg' => '<span class="cl-f44">选定产品与关联分类一起还原</span>，确认批量还原？', 'argpk' => 1, 'href' => U('Recycle/restoreGoods'),],
                ['label' => '批量彻底删除', 'target' => 'confirm', 'msg' => '此操作不可恢复，确认批量彻底删除？', 'argpk' => 1, 'href' => U('Recycle/delGoods'),],
            ],],
        ];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '40',],
            ['field' => 'cz', 'title' => '操作', 'width' => '120', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Recycle/restoreGoods', ['id' => $item['id'],]) . '" class="layui-btn layui-btn-xs"  
                    msg="<span class=\'cl-f44\'>产品与关联分类一起还原</span>，确认还原？" target="confirm">还原</a>';
                $html .= '<a href="' . U('Recycle/delGoods', ['id' => $item['id'],]) . '"  msg="此操作不可恢复，确认彻底删除？"
                     target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">彻底删除</a>';
                return $html;
            }],
            ['field' => 'def_img', 'title' => '', 'width' => '60', 'align' => 'right', 'type' => 'img',],
            ['field' => 'name', 'title' => '产品名称', 'width' => '250', 'align' => 'left'],
            ['field' => 'cat_name', 'title' => '产品分类', 'width' => '120',],
            ['field' => 'deltime', 'title' => '删除时间', 'width' => '150', 'type' => 'time',],
        ];
        $this->pagedata['model'] = M('goods')->alias('a')->where('a.deltime', '>', 0)
            ->field('a.id,a.name,a.def_img,a.cat_id,a.deltime,b.name as cat_name')
            ->join('goods_cat b', 'b.id=a.cat_id', 'left')
            ->order('a.deltime desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['checkType'] = true;

        return $this->grid_fetch();
    }

    /**
     * 回收站 - 产品管理 - 还原
     * @throws \Exception
     */
    function restoreGoods() {
        $ids = I('get.id');
        $ids = $this->checkIds($ids);
        $rs = M('goods')->where('id', 'in', $ids)->save(['deltime' => 0,]);
        $rs === false and $this->error('操作失败！');

        $catIds = M('goods')->where('id', 'in', $ids)->column('cat_id');

        $idPath = M('goods_cat')->where('id', 'in', $catIds)->column('id_path');
        $catIds = implode(',', $idPath);
        M('goods_cat')->where('id', 'in', $catIds)->where('deltime', '>', 0)->save(['deltime' => 0]);

        $this->success('操作成功！', true);
    }

    /**
     * 回收站 - 产品管理 - 彻底删除
     * @throws \Exception
     */
    function delGoods() {
        $id = I('get.id');
        $id = $this->checkIds($id);
        M('goods')->where('id', 'in', $id)->delete() or $this->error('删除失败！');
        M('goods_skus')->where('goods_id', 'in', $id)->delete();
        M('goods_attr')->where('goods_id','in', $id)->delete();
        M('tag_rel')->where('rel_id', 'in', $id)->where('tag_type', 2)->delete();
        $this->success('删除成功！', true);
    }

    /**
     * 回收站 - 产品分类
     * @throws \Exception
     */
    function cats(){
        $this->pagedata['tabs'] = [
            ['name' => '回收站 - 产品分类', 'class' => 'current',],
        ];
        $this->pagedata['actions'] = [
            ['label' => '批量操作', 'group' => [
                ['label' => '批量还原', 'target' => 'confirm', 'msg' => '确定要还原所选数据？', 'argpk' => 1, 'href' => U('Recycle/restoreCat'),],
                ['label' => '批量彻底删除', 'target' => 'confirm', 'msg' => '此操作不可恢复，确认批量彻底删除？', 'argpk' => 1, 'href' => U('Recycle/delCat'),],
            ],],
        ];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '40',],
            ['field' => 'name', 'title' => '产品分类', 'width' => '200', 'align' => 'left'],
            ['field' => 'deltime', 'title' => '删除时间', 'width' => '150', 'type' => 'time',],
            ['field' => 'cz', 'title' => '操作', 'width' => '150', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Recycle/restoreCat', ['id' => $item['id'],]) . '"  msg="<span class=\'cl-f44\'>如有父级分类及产品将一起还原</span>，确认还原？" target="confirm"
                class="layui-btn layui-btn-xs">还原</a>';
                $html .= '<a href="' . U('Recycle/delCat', ['id' => $item['id'],]) . '"  msg="此操作不可恢复，确认彻底删除？"
                     target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">彻底删除</a>';
                return $html;
            }],
        ];
        $this->pagedata['model'] = M('goods_cat')->where('deltime', '>', 0)->order('deltime desc');

        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['checkType'] = true;

        return $this->grid_fetch();
    }

    /**
     * 回收站 - 产品分类 - 还原
     * @throws \Exception
     */
    function restoreCat(){
        $ids = I('get.id');
        $ids = $this->checkIds($ids);
        $ids = is_array($ids) ? $ids : [$ids];
        $idPath = M('goods_cat')->where('id', 'in', $ids)->column('id_path');
        $ids = implode(',', $idPath);
        $ids = M('goods_cat')->where('id', 'in', $ids)->where('deltime', '>', 0)->column('id');

        $rs = M('goods_cat')->where('id', 'in', $ids)->save(['deltime' => 0]);
        $rs === false and $this->error('操作失败！');

        M('goods')->where('cat_id', 'in', $ids)->where('deltime', '>', 0)->save(['deltime' => 0]);
        $this->success('操作成功！', true);
    }

    /**
     * 回收站 - 产品分类 - 彻底删除
     * @throws \Exception
     */
    function delCat(){
        $ids = I('get.id');
        $ids = $this->checkIds($ids);
        $ids = is_array($ids) ? $ids : [$ids];
        foreach ($ids as $id) {
            $path = M('goods_cat')->where('id', $id)->value('id_path');
            $where = ' id=' . $id;
            $path and $where .= " or(id_path like '{$path},%') ";
            $where = '(' . $where . ') and deltime>0';
            $catIds = M('goods_cat')->whereRaw($where)->column('id');
            M('goods_cat')->whereRaw($where)->delete();

            $gIds = M('goods')->where('cat_id', 'in', $catIds)->column('id');
            M('goods')->where('cat_id', 'in', $catIds)->delete();

            M('goods_skus')->where('goods_id', 'in', $gIds)->delete();
            M('goods_attr')->where('goods_id','in', $gIds)->delete();
            M('tag_rel')->where('type_id', 'in', $catIds)->where('tag_type', 2)->delete();

        }
        $this->success('操作成功！', true);
    }


}