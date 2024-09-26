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

use app\admin\model\Goods as Good;

class Goods extends Base
{

    /**
     * 产品列表
     * @return false|string
     * @throws \Exception
     */
    function index() {
        $checkType = I('get.checkType');
        $catId = I('get.cat_id');
        $this->pagedata['tabs'] = [
            ['name' => '产品列表', 'class' => 'current',],
            $checkType ? false : ['name' => '产品分类', 'url' => U('Goods/cat'),],
            $checkType ? false : ['name' => '产品品牌', 'url' => U('Goods/brand'),],
            $checkType ? false : ['name' => '产品模型', 'url' => U('Channeltype/index', ['type' => 'goods',]),],
        ];
        $search = $_POST['search'] ?? [];
        if ($this->request->isPost() && $catId && (!isset($search['cat_id']) || !$search['cat_id'])) {
            $catId = null;
        }
        $cat = [];
        $catList = [
            '' => '',
        ];
        if (!$this->request->isPost()) {

            if ($catId && is_numeric($catId)) {
                $cat = M('goods_cat')->where('id', $catId)->field('id,name')->find();
                $cat and $search['cat_id'] = $catId;
            }

            $cats = M('goods_cat')->field('id,name,depth,id_path')
                ->order('path asc,id asc')->where('deltime', 0)->select()->toArray();
            $cats = tierMenusList($cats);
            foreach ($cats as $item) {
                $name = str_repeat('&nbsp;', ($item['depth'] - 1) * 4) . $item['name'];
                $catList[$item['id']] = $name;
            }
        }

        $this->pagedata['search'] = [
            ['tag' => 'input', 'name' => 'a.name|has|trim', 'placeholder' => '产品名称',],
            ['tag' => 'select', 'name' => 'cat_id', 'placeholder' => '产品分类', 'value' => $cat['id'] ?? '', 'options' => $catList,],
            ['tag' => 'select', 'name' => 'a.ifpub', 'placeholder' => '上架', 'options' => ['' => '全部', 'true' => '是', 'false' => '否',],],
        ];

        if (isset($search['cat_id']) && $search['cat_id']) {
            $catId = $search['cat_id'];
            $idPath = M('goods_cat')->where('id', $catId)->value('id_path');
            $ids = M('goods_cat')->where('id_path', 'like', $idPath . ',%')->column('id');
            $search['a.cat_id|in'] = array_merge([$search['cat_id']], $ids);
            unset($search['cat_id']);
            $_POST['search'] = $search;
        }
        session('cat_id', $catId);

        $checkType ? false : $this->pagedata['actions'] = [
            ['label' => '添加产品', 'target' => 'page', 'href' => U('Goods/addGoods'),],
            ['label' => '批量操作', 'group' => [
                ['label' => '批量删除', 'class' => 'vjs-batch-del',],
                ['label' => '批量上架', 'target' => 'confirm', 'msg' => '确定要上架已选产品吗？', 'argpk' => 1, 'href' => U('Goods/pubGoods', ['if' => 1]),],
                ['label' => '批量下架', 'target' => 'confirm', 'msg' => '确定要下架已选产品吗？', 'argpk' => 1, 'href' => U('Goods/pubGoods', ['if' => 0]),],
                ['label' => '批量更换分类', 'target' => 'dialog', 'options' => '{title:"更换分类","argpk":1}', 'href' => U('Goods/changeCat'),],
                ['label' => '批量复制产品', 'target' => 'dialog', 'options' => '{title:"复制产品","argpk":1}', 'href' => U('Goods/copyGoods'),],
            ],],
            ['label' => '<i class="layui-icon layui-icon-delete"></i>回收站', 'target' => 'page', 'href' => U('Recycle/goods'),],
        ];
        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '40',],
            $checkType ? false : ['field' => 'cz', 'title' => '操作', 'width' => '110', 'callback' => function ($item) {
                $html = '';
                $html .= '<a target="page" href="' . U('Goods/addGoods', ['id' => $item['id'],]) . '" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a class="layui-btn layui-btn-danger layui-btn-xs js-del">删除</a>';
                return $html;
            }],
            ['field' => 'def_img', 'title' => '', 'width' => '60', 'align' => 'right', 'type' => 'img',],
            ['field' => 'name', 'title' => '产品名称', 'width' => '250', 'align' => 'left', 'callback' => function ($item) {
                $url = U('/item/' . $item['id']);
                $html = '<a class="cl-38f" href="' . $url . '" target="_blank">' . $item['name'] . '</a>';
                $item['is_head'] and $html .= ' <span class="cl-f44">[头条]</span>';
                $item['is_special'] and $html .= ' <span class="cl-f44">[特荐]</span>';
                $item['is_recom'] and $html .= ' <span class="cl-f44">[推荐]</span>';
                $item['is_news'] and $html .= ' <span class="cl-f44">[新品]</span>';
                $html .= '&nbsp;&nbsp;<a href="' . $url . '" class="layui-icon cl-green unl" target="_blank" title="浏览">&#xe7ae;</a>';
                return $html;
            }],
            ['field' => 'cat_name', 'title' => '产品分类', 'width' => '100', 'callback' => function ($item) {
                $url = U('/cat/' . $item['cat_id']);
                return '<a href="' . $url . '" target="_blank" class="unl">' . $item['cat_name'] . '</a>';
            }],
            ['field' => 'price', 'title' => '价格', 'width' => '70',],
            ['field' => 'store', 'title' => '库存', 'width' => '60',],
            ['field' => 'view_count', 'title' => '浏览量', 'width' => '60',],
            ['field' => 'sales', 'title' => '销量', 'width' => '60',],
            ['field' => 'ifpub', 'title' => '上架', 'width' => '50', 'type' => 'enum', 'enum' => ['true' => '是', 'false' => '否',],],
            ['field' => 'pubtime', 'title' => '发布时间', 'width' => '100', 'type' => 'time', 'format' => 'Y-m-d',],
            ['field' => 'addtime', 'title' => '创建时间', 'width' => '150', 'type' => 'time',],
            $checkType ? false : ['field' => 'sort', 'title' => '排序', 'width' => '60', 'callback' => function ($item) {
                return '<input class="layui-input layui-input-sm js-sort" data-val="' . $item['sort'] . '" value="' . $item['sort'] . '"  maxlength="3" type="text" />';
            }],
        ];
        $this->pagedata['model'] = M('goods')->alias('a')->where('a.deltime', '=', 0)
            ->field('a.id,a.name,a.price,a.store,a.addtime,a.def_img,a.is_head,a.is_special,a.is_recom,a.is_news,a.sort,a.view_count,a.sales,a.ifpub,a.cat_id,a.pubtime,b.name as cat_name')
            ->join('goods_cat b', 'b.id=a.cat_id', 'left')
            ->order('a.id desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['grid_class'] = 'js-grid-goods';
        $this->pagedata['checkType'] = $checkType ?: true;

        return $this->grid_fetch();
    }

    /**
     * 复制产品
     * @throws \Exception
     */
    function copyGoods() {
        if (!$this->request->isPost()) {
            $ids = I('get.id');
            $ids = $this->checkIds($ids);
            $this->assign('ids', implode(',', is_array($ids) ? $ids : [$ids]));

            $cats = M('goods_cat')->field('id,name as title,id_path,depth,"group" as type')
                ->where('deltime', 0)->order('path asc,id asc')->select()->toArray();
            $cats = tierMenusList($cats, 'child', false);
            $this->assign('cats', json_encode($cats));

            return $this->fetch();
        }
        $ids = I('post.ids');
        $catId = I('post.cat_id');
        if (!is_numeric($catId) || empty($catId)) {
            $this->error('请先选择分类！');
        }
        $num = I('post.num');
        if (!is_numeric($num) || $num < 1) {
            $this->error('复制数量至少一篇！');
        }
        Good::batchCopy($ids, $catId, $num);
        $this->success('操作成功！', true);
    }

    /**
     * 更换分类
     * @throws \Exception
     */
    function changeCat() {
        if (!$this->request->isPost()) {
            $ids = I('get.id');
            $ids = $this->checkIds($ids);
            $this->assign('ids', implode(',', is_array($ids) ? $ids : [$ids]));

            $cats = M('goods_cat')->field('id,name as title,id_path,depth,"group" as type')
                ->where('deltime', 0)->order('path asc,id asc')->select()->toArray();
            $cats = tierMenusList($cats, 'child', false);
            $this->assign('cats', json_encode($cats));

            return $this->fetch();
        }
        $ids = I('post.ids');
        $catId = I('post.cat_id');
        if (!is_numeric($catId) || empty($catId)) {
            $this->error('请先选择分类！');
        }
        $rs = M('goods')->where('id', 'in', $ids)->save(['cat_id' => $catId,]);
        $rs === false and $this->error('操作失败！');
        $this->success('操作成功！', true);
    }

    /**
     * 批量上下架产品
     * @throws \Exception
     */
    function pubGoods() {
        $isPub = I('get.if', '');
        $isPub = ['0' => 'false', '1' => 'true'][$isPub] ?? '';
        $isPub or $this->error('参数错误！');
        $id = $this->checkIds(I('get.id'));
        M('goods')->where('id', 'in', $id)->save(['ifpub' => $isPub,]);
        $this->success('操作成功！', true);
    }

    /**
     * 产品排序
     * @throws \Exception
     */
    function sortGoods() {
        $id = I('get.id');
        $sort = I('post.sort', 0);
        if (!is_numeric($id) || !is_numeric($sort)) $this->error();
        M('goods')->where('id', $id)->save(['sort' => $sort,]);
        $this->success();
    }

    /**
     * 删除商品
     * @throws \Exception
     */
    function delGoods() {
        $id = I('get.id');
        $id = $this->checkIds($id);
        $rs = M('goods')->where('id', 'in', $id)->save(['deltime' => time(),]);
        $rs === false and $this->error('操作失败！');
        $this->success('操作成功！', true);
    }

    /**
     * 添加保存产品信息
     * @throws \Exception
     */
    function addGoods() {
        $id = I('get.id');
        $catId = session('cat_id');
        if (!$this->request->isPost()) {

            is_numeric($id) and $row = Good::getInfo($id);
            if (isset($row)) {
                if ($row['sku_desc'] && ($skus = unserialize($row['sku_desc']))) {
                    $row['skus'] = $skus;
                    $row['sku_desc'] = M('goods_skus')->where('goods_id', $row['id'])->column('sku_name,sku_id,price', 'sku_name');
                    $row['sku_desc'] = json_encode($row['sku_desc']);
                }
                $row['imgs'] = $row['imgs'] ? explode(',', $row['imgs'] ?: '') : [];
                $row['pubtime'] and $row['pubtime'] = date('Y-m-d H:i:s', $row['pubtime']);
            }
            $this->assign('row', $row ?? []);


            $cats = M('goods_cat')->field('id,name as title,id_path,depth,"group" as type')
                ->where('deltime', 0)->order('path asc,id asc')->select()->toArray();
            $cats = tierMenusList($cats, 'child', false);
            $this->assign('cats', json_encode($cats));

            $this->assign('catId', isset($row['cat_id']) ? $row['cat_id'] : $catId);

            $brands = M('goods_brand')->field('id,title')->order('sort asc,id desc')->select()->toArray();
            $this->assign('brands', $brands);


            $specification = $this->getSku(true);
            $this->assign('specification', $specification);

            $temp = new \app\admin\lib\Template('/pc');
            $templist = $temp->getTmplPath('item');
            $this->assign('templist', $templist);

            return $this->fetch();
        }
        $data = I('post.', null, 'trim');
        unset($data['tags']);

        //处理自定义字段值
        $libField = new \app\admin\model\Channel;
        $data = $libField->checkFieldValue($data['cat_id'], 'goods', $data);


        $skus = $data['skus'] ?? [];
        $data['imgs'] = $data['imgs'] ?? [];
        $data['def_img'] = $data['imgs'] ? $data['imgs'][0] : '';
        $data['imgs'] = $data['imgs'] ? implode(',', $data['imgs'] ?: []) : '';

        if (!$data['brief'] && $data['content']) {
            $data['brief'] = @msubstr(checkStrHtml($data['content']), 0, C('config.seo_description_length'), false);
        }
        if (!$data['seo_description']) {
            $data['seo_description'] = $data['brief'];
        }

        if (empty($data['is_jump'])) {
            $data['jump_url'] = '';
        }

        if ($skus) {
            $data['sku_desc'] and $data['sku_desc'] = json_decode($data['sku_desc'], true);
            $data['sku_desc'] and $data['sku_desc'] = serialize($data['sku_desc']);
        } else {
            $data['sku_desc'] = $data['sku_name'] = '';
        }
        is_numeric($data['price']) or $data['price'] = 0;
        unset($data['skus'], $data['attrs']);
        $attrs = I('post.attrs', null, 'trim');

        $data['pubtime'] = $data['pubtime'] ? strtotime($data['pubtime']) : time();

        $lib = new \app\home\lib\Goods();
        if (is_numeric($id)) {
            $rs = M('goods')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');
            $res = [
                'url' => $lib->getUrl($id, 'item', $data['cat_id'] ?: 0, true),
                'type' => 'edit',
            ];
        } else {
            $data['admin_id'] = $this->account['id'];
            $data['addtime'] = time();
            $id = M('goods')->insert($data, true);
            $id or $this->error('保存失败！');
            $res = [
                'url' => $lib->getUrl($id, 'item', $data['cat_id'] ?: 0, true),
                'type' => 'add',
            ];
        }
        $this->saveSkus($id, $skus);

        M('goods_attr')->where('goods_id', $id)->delete();
        if ($attrs) {
            $list = [];
            foreach ($attrs as $attrId => $value) {
                $list[] = [
                    'goods_id' => $id,
                    'attr_id' => $attrId,
                    'attr_value' => $value,
                ];
            }
            unset($attrs);
            $list and M('goods_attr')->insertAll($list);
        }

        Good::onAfterSave($id, $data);

        $jumpUrl = U('Goods/index', ['cat_id' => $catId]);
        $this->success('保存成功', $jumpUrl, $res);
    }

    /**
     * 保存产品规格数据
     */
    function saveSkus($id, $skus = []) {
        $skuIds = [];
        foreach ($skus as $item) {
            $skuId = $item['sku_id'];
            $item = [
                'goods_id' => $id,
                'price' => (float)$item['price'],
                'sku_name' => $item['sku_name'],
                'sku_desc' => serialize(json_decode($item['sku_desc'], true)),
            ];
            if ($skuId == 'newId') {//新添加
                $skuId = M('goods_skus')->insert($item, true);
            } elseif (is_numeric($skuId)) {//更新
                M('goods_skus')->where('sku_id=' . $skuId)->save($item);
            }
            is_numeric($skuId) and $skuIds[] = $skuId;
        }
        $filter = [
            ['goods_id', '=', $id,]
        ];
        $skuIds and $filter[] = ['sku_id', 'not in', $skuIds];
        M('goods_skus')->where($filter)->delete();
    }

    /**
     * 设置用户自定义的规格
     */
    function setSku() {
        $data = I('post.');
        if (isset($data['pid'])) {
            is_numeric($data['pid']) or $this->error();
            $_d = ['spec_id' => $data['pid'], 'spec_value' => $data['name'],];
            $rowId = M('goods_spec_values')->where($_d)->value('spec_value_id');
            if (!$rowId) {
                $rowId = M('goods_spec_values')->insert($_d, true) or $this->error();
            }
            $this->success(['id' => $rowId, 'name' => $data['name'], 'pid' => $data['pid'],]);
        }
        $_d = ['spec_name' => $data['name'],];
        $rowId = M('goods_specification')->where($_d)->value('spec_id');
        if (!$rowId) {
            $rowId = M('goods_specification')->insert($_d, true) or $this->error();
        }
        $this->success(['id' => $rowId, 'name' => $data['name'],]);
    }

    /**
     * 获取规格
     */
    function getSku($isRet = false) {
        $pid = I('post.pid', false);
        if ($pid) {
            is_numeric($pid) or $this->error();
            $sku = M('goods_spec_values')->where(['spec_id' => $pid,])
                ->field('spec_value_id as id,spec_value as name')
                ->order('spec_value_id desc')->select()->toArray();
            $this->success(['pid' => $pid, 'values' => $sku,]);
        }
        $sku = M('goods_specification')->alias('a')
            ->field('a.spec_id,a.spec_name,b.spec_value_id,b.spec_value')
            ->join('goods_spec_values b ', 'a.spec_id=b.spec_id', 'left')
            ->order('a.sort asc,a.spec_id desc,b.spec_value_id desc')
            ->select()->toArray();
        $skus = [];
        foreach ($sku as $item) {
            $key = '_' . $item['spec_id'];
            isset($skus[$key]) or $skus[$key] = ['id' => $item['spec_id'], 'name' => $item['spec_name'],];
            $item['spec_value_id'] and $skus[$key]['values'][] = ['id' => $item['spec_value_id'], 'name' => $item['spec_value'],];
        }
        $skus = array_values($skus);
        if ($isRet) return $skus;
        $this->success($skus);
    }


    /**
     * 产品分类
     * @return false|string
     * @throws \Exception
     */
    function cat() {
        $checkType = I('checkType');
        $this->pagedata['tabs'] = [
            $checkType ? false : ['name' => '产品列表', 'url' => U('Goods/index'),],
            ['name' => '产品分类', 'class' => 'current',],
            $checkType ? false : ['name' => '产品品牌', 'url' => U('Goods/brand'),],
            $checkType ? false : ['name' => '产品模型', 'url' => U('Channeltype/index', ['type' => 'goods',]),],
        ];
        $checkType ? false : $this->pagedata['actions'] = [
            ['label' => '添加分类', 'target' => 'page', 'href' => U('Goods/addCat'),],
            ['label' => '<i class="layui-icon layui-icon-delete"></i>回收站', 'target' => 'page', 'href' => U('Recycle/cats'),],
        ];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'name', 'class' => 'js-lanmu', 'title' => '＋ 分类名称', 'width' => '550', 'align' => 'left', 'callback' => function ($item) use ($checkType) {
                $w = 20 * $item['depth'];
                $html = '<i class="layui-icon layui-icon-subtraction mr5"></i><span class="w40x" style="width:' . $w . 'px"></span>';
                $html .= $item['ifpub'] == 'false' ? '<font color="red">[隐]</font> ' : '';
                $checkType ? $html .= $item['name'] :
                    $html .= '<a class="hover" target="page" href="' . U('Goods/index', ['cat_id' => $item['id'],]) . '">' . $item['name'] . '</a>';
                if (!$checkType) {
                    $_where = ' id=' . $item['id'];
                    $item['id_path'] and $_where .= " or(id_path like '{$item['id_path']},%') ";
                    $ids = M('goods_cat')->whereRaw($_where)->where('deltime', 0)->column('id');
                    $count = M('goods')->where('deltime',0)->where('cat_id', 'in', $ids)->count();
                    $html .= '<i class="f12 cl-999">（产品：' . $count . '条）</i>';
                }
                return $html;
            }],
            $checkType ? false : ['field' => 'sort', 'title' => '排序', 'width' => '70', 'callback' => function ($item) {
                return '<input href="' . U('Goods/sortCat') . '" class="layui-input layui-input-sm js-sort" data-val="' . $item['sort'] . '" value="' . $item['sort'] . '" type="text" maxlength="3" />';
            }],
            $checkType ? false : ['field' => 'cz', 'title' => '操作', 'width' => '300', 'align' => 'left', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Goods/addCat', ['pid' => $item['id'],]) . '" target="page" class="layui-btn layui-btn-primary layui-btn-xs"">添加下级分类</a>';
                $html .= '<a href="' . U('Goods/addCat', ['id' => $item['id'], 'pid' => $item['parent_id'],]) . '" target="page" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Goods/delCat', ['id' => $item['id'],]) . '" msg="<span class=\'cl-f44\'>如有子分类及产品将一起清空</span>，确认删除到回收站？" 
                target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
        ];

        $data = M('goods_cat')->field('id,name,depth,sort,parent_id,id_path,ifpub')
            ->order('path asc,id asc')->where('deltime', 0)->select()->toArray();

        $this->pagedata['data'] = tierMenusList($data);

        $this->pagedata['trAttr'] = [
            'pid' => 'parent_id',
        ];//表格行属性
        $this->pagedata['pk_field'] = 'id';//手动指定住建
        $this->pagedata['fixedColumn'] = true;//固定列宽
        $this->pagedata['isPage'] = false;//不显示分页
        $this->pagedata['grid_class'] = 'js-view-goods-cat';

        return $this->grid_fetch('site/menus');
    }

    /**
     * 栏目排序
     * @throws \Exception
     */
    function sortCat() {
        $id = I('post.id');
        $sort = I('post.sort', 0);
        if (!is_numeric($id) || !is_numeric($sort)) $this->error('');

        $row = M('goods_cat')->where('id', $id)->field('path,id_path')->find();
        $path = $row['path'];
        $path = substr($path, 0, -4) . (1000 + ($sort > 1000 ? 999 : $sort));

        $rs = M('goods_cat')->where('id', $id)->save(['sort' => $sort, 'path' => $path, 'uptime' => time(),]);
        $rs or $this->error();

        $len = strlen($path) + 1;
        M('goods_cat')->where('id_path', 'like', $row['id_path'] . ',%')
            ->exp('path', "concat('{$path}',substring(path,{$len}))")->update();

        $this->updateCache();

        $this->success('', true);
    }

    /**
     * 更新数据缓存
     */
    function updateCache() {
        $lib = new \app\home\model\cats();
        $lib->updateCache();//更新数据
    }

    /**
     * 删除栏目
     * @throws \Exception
     */
    function delCat() {
        $id = I('get.id');
        is_numeric($id) or $this->error('参数不合法！');

        $path = M('goods_cat')->where('id', $id)->value('id_path');
        $where = ' id=' . $id;
        $path and $where .= " or(id_path like '{$path},%') ";
        $ids = M('goods_cat')->whereRaw($where)->column('id');

        $rs = M('goods_cat')->whereRaw($where)->save(['deltime' => time(),]);
        $rs === false and $this->error('操作失败！');

        M('goods')->where('cat_id', 'in', $ids)->save(['deltime' => time(),]);

        $this->updateCache();

        $this->success('操作成功！', true);
    }

    /**
     * 添加栏目
     */
    function addCat() {
        if (!$this->request->isPost()) {
            $pid = I('get.pid');
            if (is_numeric($pid) && $pid > 0) {
                $title = M('goods_cat')->where('id', $pid)->value('name');
                if ($title) {
                    $this->assign('ptitle', $title);
                    $this->assign('pid', $pid);
                }
            }
            $id = I('get.id');
            $id and $row = M('goods_cat')->where('id', $id)->find();
            if (isset($row) && $row['attrs']) {
                $attrIds = array_values(array_filter(explode(',', $row['attrs'])));
                if ($attrIds) {
                    $cols = M('goods_attribute')->where('id', 'in', $attrIds)->column('id,name', 'id');
                    $attrs = [];
                    foreach ($attrIds as $attrId) {
                        if (!isset($cols[$attrId])) continue;
                        $attrs[] = $cols[$attrId];
                    }
                    unset($cols);
                }
            }
            if ($id && !empty($row)) {
                $cats = M('goods_cat')->field('id,name,depth,id_path')
                    ->order('path asc,id asc')->where('deltime', 0)->select()->toArray();
                $cats = tierMenusList($cats);
                foreach ($cats as &$item) {
                    $item['name'] = str_repeat('&nbsp;', ($item['depth'] - 1) * 4) . $item['name'];
                }
                $this->assign('cats', $cats);
            }

            $this->assign('view_route', '{typedir}/{aid}.html');
            $this->assign('list_route', '{typedir}/list_{tid}_{page}.html');

            $this->assign('attrs', $attrs ?? []);
            $this->assign('row', $row ?? []);

            $temp = new \app\admin\lib\Template('/pc');
            $templist = $temp->getTmplPath('cat');
            $this->assign('templist', $templist);

            $templist_view = $temp->getTmplPath('item');
            $this->assign('templist_view', $templist_view);

            $channelList = M('channeltype')->where('type', 'goods')->field('id,title')->select()->toArray();
            $this->assign('channelList', $channelList);

            return $this->fetch();
        }

        $id = I('get.id');
        $data = I('post.', null, 'trim');

        $data['name'] = trim($data['name']) or $this->error('请填写栏目名称！');
        $data['attrs'] = isset($data['attrs']) && $data['attrs'] ? implode(',', $data['attrs']) : '';

        if (strpos($data['view_route'], '{aid}') === false) {
            $this->error('产品命名规则 必须存在 {aid} ！');
        }
        if (strpos($data['list_route'], '{page}') === false) {
            $this->error('列表命名规则 必须存在 {page} ！');
        }

        /*=== 处理目录名==开始===*/
        if ($data['dir_name'] && preg_match('/[^a-zA-Z0-9_\/]/', $data['dir_name'])) {
            $this->error('目录名称错误，仅支持字母、数字、下划线、斜杠！');
        }
        $pinyin = new \app\admin\lib\Piyin('goods_cat');

        $data['dir_name'] = preg_replace('/\s+/', '', $data['dir_name']);//替换空格
        if ($data['dir_name'] && $pinyin->dirnameIsHas($data['dir_name'], $id)) {
            $this->error('目录名称已存在，请更改！');
        }
        $data['dir_name'] = $pinyin->get_dirname($data['name'], $data['dir_name'], $id);
        /*===处理目录名==结束===*/

        $upNext = $data['upnext'];
        unset($data['upnext']);

        if ($id && is_numeric($id)) {
            $data['uptime'] = time();

            /*自己的上级不能是自己*/
            if (intval($id) == intval($data['parent_id'])) {
                $this->error('自己不能成为自己的上级分类');
            }
            /*--end*/

            $oldInfo = M('goods_cat')->where('id', $id)->find();
            if ($oldInfo['parent_id'] != $data['parent_id']) {
                $pInfo = M('goods_cat')->where('id', $data['parent_id'])->field('depth,path,id_path')->find();
                $sort = M('goods_cat')->where('parent_id', $data['parent_id'])->max('sort');
                $sort = $sort ? $sort + 1 : 1;

                $data['depth'] = ($pInfo['depth'] ?? 0) + 1;
                $data['path'] = ($pInfo['path'] ?? '') . (1000 + ($sort > 1000 ? 999 : $sort));
                $data['sort'] = $sort;
                $data['id_path'] = empty($pInfo['id_path']) ? $id : $pInfo['id_path'] . ',' . $id;
            }

            $rs = M('goods_cat')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');

            if ($oldInfo['parent_id'] != $data['parent_id']) {
                $list = M('goods_cat')->where('id_path', 'like', $oldInfo['id_path'] . ',%')->field('id,depth,path,id_path')
                    ->order('path asc,id asc')->select()->toArray();
                foreach ($list as $item) {
                    $path = $data['path'] . substr($item['path'], strlen($oldInfo['path']));
                    $id_path = $data['id_path'] . substr($item['id_path'], strlen($oldInfo['id_path']));
                    $depth = substr_count($id_path, ',') + 1;
                    M('goods_cat')->where('id', $item['id'])->save([
                        'depth' => $depth,
                        'path' => $path,
                        'id_path' => $id_path,
                    ]);
                }
                $oldInfo['id_path'] = $data['id_path'];//赋值最新数据
            }

            //更新下级分类的属性
            if ($upNext) {
                $next = array_columns([$data], 'channel_id,tmpl_path,tmpl_view,view_route,list_route');
                M('goods_cat')->where('id_path', 'like', $oldInfo['id_path'] . ',%')->save($next[0]);
            }
            $this->success('保存成功！', 'Goods/cat');
        }
        $pid = isset($data['parent_id']) && is_numeric($data['parent_id']) ? $data['parent_id'] : 0;

        $pInfo = M('goods_cat')->where('id', $pid)->field('depth,path,id_path')->find();
        $sort = M('goods_cat')->where('parent_id', $pid)->max('sort');
        $sort = $sort ? $sort + 1 : 1;

        $data['depth'] = ($pInfo['depth'] ?? 0) + 1;
        $data['path'] = ($pInfo['path'] ?? '') . (1000 + ($sort > 1000 ? 999 : $sort));
        $data['sort'] = $sort;
        $data['uptime'] = time();

        $rId = M('goods_cat')->insert($data, true);
        $rId or $this->error('保存失败！');

        M('goods_cat')->where('id', $rId)->save([
            'id_path' => (isset($pInfo['id_path']) && $pInfo['id_path'] ? $pInfo['id_path'] . ',' : '') . $rId
        ]);

        $this->success('保存成功！', 'Goods/cat');
    }


    /**
     * 产品属性列表
     */
    function attrList() {
        $this->pagedata['tabs'] = [
            ['name' => '参数列表', 'class' => 'current',]
        ];

        $this->pagedata['search'] = [
            ['tag' => 'input', 'name' => 'name|has|trim', 'placeholder' => '参数名称',],
        ];

        $this->pagedata['actions'] = [
            ['label' => '添加参数', 'target' => 'dialog', 'href' => U('Goods/addAttr'), 'options' => '{title:"添加参数",area:["530px","460px"]}',],
        ];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => 80,],
            ['field' => 'name', 'title' => '参数名称', 'width' => 120,],
            ['field' => 'type', 'title' => '参数类型', 'width' => 150, 'type' => 'enum', 'enum' => ['单行文本框', '下拉式列表', '多行文本框',],],
            ['field' => 'cz', 'title' => '操作', 'width' => 200, 'align' => 'left', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Goods/addAttr', ['id' => $item['id'],]) . '" options="{title:\'编辑参数\',area:[\'530px\',\'460px\']}" target="dialog" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Goods/delAttr', ['id' => $item['id'],]) . '" msg="确定要删除吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
        ];

        $ids = I('get.notids');
        if ($ids) {
            $this->pagedata['where'] = [
                ['id', 'not in', $ids],
            ];
        }

        $this->pagedata['model'] = M('goods_attribute');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['checkType'] = true;
        return $this->grid_fetch();
    }

    /**
     *  添加参数
     */
    function addAttr() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            if (is_numeric($id)) {
                $row = M('goods_attribute')->where('id=' . $id)->find();
            }
            $this->assign('row', $row ?? []);
            return $this->fetch();
        }
        $data = I('post.', null, 'trim');
        $data['name'] or $this->error('请填写参数名称！');
        if ($data['type'] == '1' && !$data['values']) {
            $this->error('请填写可选值列表！');
        }
        if ($data['type'] != '1') {
            $data['is_filter'] = 0;
        }
        if (is_numeric($id)) {
            $rs = M('goods_attribute')->where('id=' . $id)->save($data);
            $rs === false and $this->error('保存失败！');
        } else {
            M('goods_attribute')->insert($data) or $this->error('保存失败！');
        }
        $this->success('保存成功！', true);
    }

    function delAttr() {
        $ids = I('id');
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $ids = array_values(array_filter($ids, 'is_numeric'));
        $ids or $this->error('参数不合法！');

        $rs = M('goods_attribute')->where('id', 'in', $ids)->delete();
        $rs or $this->error('删除失败！');
        $this->success('删除成功！', true);
    }

    function getAttrHtml() {
        $catId = I('post.catId', 0);
        $goodsId = I('post.goodsId');

        if (!$catId || !is_numeric($catId)) return '';

        $info = M('goods_cat')->where('id', $catId)->field('attrs,parent_id')->find();
        $attrIds = empty($info['attrs']) ? '' : $info['attrs'];
        if (!$attrIds && !empty($info['parent_id'])) {
            $attrIds = M('goods_cat')->where('id', $info['parent_id'])->value('attrs');
        }

        $attrIds = $attrIds ? array_values(array_filter(explode(',', $attrIds))) : '';
        if ($attrIds) {
            $cols = M('goods_attribute')->where('id', 'in', $attrIds)->column('id,name,type,values', 'id');
            $attrs = [];
            foreach ($attrIds as $attrId) {
                if (!isset($cols[$attrId])) continue;
                $attrs[] = $cols[$attrId];
            }
            unset($cols);
        }

        $this->assign('attrs', $attrs ?? []);
        if (is_numeric($goodsId) && $goodsId) {
            $attrValue = M('goods_attr')->where('goods_id', $goodsId)->column('attr_value', 'attr_id');
        }
        $this->assign('attrValue', $attrValue ?? []);

        return $this->fetch('goods/attrs');
    }


    function brand() {
        $this->pagedata['tabs'] = [
            ['name' => '产品列表', 'url' => U('Goods/index'),],
            ['name' => '产品分类', 'url' => U('Goods/cat'),],
            ['name' => '产品品牌', 'class' => 'current',],
            ['name' => '产品模型', 'url' => U('Channeltype/index', ['type' => 'goods',]),],
        ];

        $this->pagedata['search'] = [
            ['tag' => 'input', 'name' => 'title|has|trim', 'placeholder' => '品牌名称',],
        ];

        $this->pagedata['actions'] = [
            ['label' => '添加品牌', 'target' => 'dialog', 'href' => U('Goods/addBrand'), 'options' => '{title:"添加品牌",area:["450px"]}',],
            ['label' => '批量删除', 'target' => 'confirm', 'msg' => '确定要删除已选数据吗？', 'argpk' => 1, 'href' => U('Goods/delBrand'),],
        ];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '40',],
            ['field' => 'title', 'title' => '品牌名称', 'width' => '150',],
            ['field' => 'logo', 'title' => '品牌Logo', 'width' => '130', 'type' => 'img',],
            ['field' => 'url', 'title' => '品牌地址', 'width' => '200', 'type' => 'url',],
            ['field' => 'sort', 'title' => '排序', 'width' => '70', 'callback' => function ($item) {
                return '<input class="layui-input layui-input-sm js-sort" data-val="' . $item['sort'] . '" value="' . $item['sort'] . '"  maxlength="3" type="text" />';
            }],
            ['field' => 'cz', 'title' => '操作', 'width' => '300', 'align' => 'left', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Goods/addBrand', ['id' => $item['id'],]) . '" options="{title:\'编辑品牌\',area:[\'450px\']}" target="dialog" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Goods/delBrand', ['id' => $item['id'],]) . '" msg="确定要删除吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
        ];

        $this->pagedata['model'] = M('goods_brand')->order('sort asc,id desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['grid_class'] = 'js-grid-brands';
        $this->pagedata['checkType'] = true;

        return $this->grid_fetch();
    }

    /**
     * 添加品牌
     */
    function addBrand() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            is_numeric($id) and $row = M('goods_brand')->where('id', $id)->find();
            $this->assign('row', $row ?? []);
            return $this->fetch();
        }
        $data = I('post.', null, 'trim');
        if (is_numeric($id)) {
            $rs = M('goods_brand')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');
            $this->success('保存成功！', true);
        }

        M('goods_brand')->insert($data) or $this->error('保存失败！');
        $this->success('保存成功！', true);
    }

    /**
     * 删除
     * @throws \think\db\exception\DbException
     */
    function delBrand() {
        $id = I('get.id');
        $id = $this->checkIds($id);
        $rs = M('goods_brand')->where('id', 'in', $id)->delete();
        $rs or $this->error('删除失败！');

        M('goods')->where('brand_id', 'in', $id)->save(['brand_id' => 0,]);

        $this->success('删除成功！', true);
    }

    /**
     * 排序
     */
    function sortBrand() {
        $id = I('get.id');
        $sort = I('post.sort', 0);
        if (!is_numeric($id) || !is_numeric($sort)) $this->error();
        M('goods_brand')->where('id', $id)->save(['sort' => $sort,]);
        $this->success();
    }

}