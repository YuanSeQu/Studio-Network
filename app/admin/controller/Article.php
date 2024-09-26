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

use app\admin\model\Articles;

class Article extends Base
{

    /**
     * 文章列表
     * @return false|string
     * @throws \Exception
     */
    function index() {
        $checkType = I('get.checkType');
        $nodeId = I('get.node_id');
        $this->pagedata['tabs'] = [
            ['name' => '文章列表', 'class' => 'current',],
            $checkType ? false : ['name' => '文章栏目', 'url' => U('Article/nodes'),],
            $checkType ? false : ['name' => '文章模型', 'url' => U('Channeltype/index', ['type' => 'articles',]),],
        ];
        $search = $_POST['search'] ?? [];
        if ($this->request->isPost() && $nodeId && (!isset($search['node_id']) || !$search['node_id'])) {
            $nodeId = null;
        }
        $node = [];
        $nodeList = [
            '' => '',
        ];
        if (!$this->request->isPost()) {

            if ($nodeId && is_numeric($nodeId)) {
                $node = M('article_nodes')->where('id', $nodeId)->field('id,name')->find();
                $node and $search['node_id'] = $nodeId;
            }

            $nodes = M('article_nodes')->field('id,name,depth,id_path')
                ->order('path asc,id asc')->where('deltime', 0)->select()->toArray();
            $nodes = tierMenusList($nodes);
            foreach ($nodes as $item) {
                $name = str_repeat('&nbsp;', ($item['depth'] - 1) * 4) . $item['name'];
                $nodeList[$item['id']] = $name;
            }
        }
        $this->pagedata['search'] = [
            ['tag' => 'input', 'name' => 'a.title|has|trim', 'placeholder' => '文章标题',],
            ['tag' => 'select', 'name' => 'node_id', 'placeholder' => '文章栏目', 'value' => $node['id'] ?? '', 'options' => $nodeList,],
            ['tag' => 'select', 'name' => 'a.ifpub', 'placeholder' => '发布', 'options' => ['' => '全部', 'true' => '是', 'false' => '否',],],
        ];

        if (isset($search['node_id']) && $search['node_id']) {
            $nodeId = $search['node_id'];
            $idPath = M('article_nodes')->where('id', $nodeId)->value('id_path');
            $ids = M('article_nodes')->where('id_path', 'like', $idPath . ',%')->column('id');
            $search['a.node_id|in'] = array_merge([$search['node_id']], $ids);
            unset($search['node_id']);
            $_POST['search'] = $search;
        }
        session('node_id', $nodeId);

        $checkType ? false : $this->pagedata['actions'] = [
            ['label' => '添加文章', 'target' => 'page', 'href' => U('Article/addArticle'),],
            ['label' => '批量操作', 'group' => [
                ['label' => '批量删除', 'class' => 'vjs-batch-del',],
                ['label' => '批量发布', 'target' => 'confirm', 'msg' => '确定要发布已选数据吗？', 'argpk' => 1, 'href' => U('Article/pubArticle', ['if' => 1]),],
                ['label' => '批量取消发布', 'target' => 'confirm', 'msg' => '确定要取消发布吗？', 'argpk' => 1, 'href' => U('Article/pubArticle', ['if' => 0]),],
                ['label' => '批量更换栏目', 'target' => 'dialog', 'options' => '{title:"更换栏目","argpk":1}', 'href' => U('Article/changeNode'),],
                ['label' => '批量复制文档', 'target' => 'dialog', 'options' => '{title:"复制文档","argpk":1}', 'href' => U('Article/copyArticle'),],
            ],],
            ['label' => '<i class="layui-icon layui-icon-delete"></i>回收站', 'target' => 'page', 'href' => U('Recycle/articles'),],
        ];
        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '40',],
            $checkType ? false : ['field' => 'cz', 'title' => '操作', 'width' => '120', 'callback' => function ($item) {
                $html = '';
                $html .= '<a target="page" href="' . U('Article/addArticle', ['id' => $item['id'],]) . '" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a class="layui-btn layui-btn-danger layui-btn-xs js-del">删除</a>';
                return $html;
            }],
            ['field' => 'img', 'title' => '', 'width' => '60', 'align' => 'right', 'type' => 'img',],
            ['field' => 'title', 'title' => '文章标题', 'width' => '250', 'align' => 'left', 'callback' => function ($item) {
                $url = U('/article/' . $item['id']);
                $html = '<a class="cl-38f" href="' . $url . '" target="_blank">' . subtext($item['title'], 40) . '</a>';
                $item['is_head'] and $html .= ' <span class="cl-f44">[头条]</span>';
                $item['is_special'] and $html .= ' <span class="cl-f44">[特荐]</span>';
                $item['is_recom'] and $html .= ' <span class="cl-f44">[推荐]</span>';
                $html .= '&nbsp;&nbsp;<a href="' . $url . '" class="layui-icon cl-green unl" target="_blank" title="浏览">&#xe7ae;</a>';
                return $html;
            }],
            ['field' => 'node_name', 'title' => '文章栏目', 'width' => '120', 'callback' => function ($item) {
                $url = U('/node/' . $item['node_id']);
                return '<a href="' . $url . '" target="_blank" class="unl">' . $item['node_name'] . '</a>';
            }],
            ['field' => 'ifpub', 'title' => '发布', 'width' => '80', 'callback' => function ($item) {
                return '<div class="layui-form"><input type="checkbox" name="status" 
                    lay-filter="article-ifpub" lay-skin="switch" lay-text="是|否" value="1" ' . ($item['ifpub'] == 'true' ? 'checked' : '') . ' unvalue="0"/></div>';
            }],
            ['field' => 'view_count', 'title' => '浏览量', 'width' => '80',],
            ['field' => 'pubtime', 'title' => '发布时间', 'width' => '120', 'type' => 'time', 'format' => 'Y-m-d',],
            ['field' => 'uptime', 'title' => '更新时间', 'width' => '150', 'type' => 'time',],
            $checkType ? false : ['field' => 'sort', 'title' => '排序', 'width' => '70', 'callback' => function ($item) {
                return '<input class="layui-input layui-input-sm js-sort" data-val="' . $item['sort'] . '" value="' . $item['sort'] . '"  maxlength="3" type="text" />';
            }],
        ];
        $this->pagedata['model'] = M('articles')->alias('a')->where('a.deltime','=',0)
            ->field('a.id,a.title,a.img,a.ifpub,a.pubtime,a.uptime,a.view_count,a.is_head,a.is_special,a.is_recom,a.sort,a.node_id,b.name as node_name')
            ->join('article_nodes b', 'b.id=a.node_id', 'left')
            ->order('a.id desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['grid_class'] = 'js-grid-articles';
        $this->pagedata['checkType'] = $checkType ?: true;

        return $this->grid_fetch();
    }

    /**
     * 复制文档
     * @throws \Exception
     */
    function copyArticle(){
        if (!$this->request->isPost()) {
            $ids = I('get.id');
            $ids = $this->checkIds($ids);
            $this->assign('ids', implode(',', is_array($ids) ? $ids : [$ids]));

            $nodes = M('article_nodes')->field('id,name as title	,id_path,depth,"group" as type')
                ->where('deltime',0)->order('path asc,id asc')->select()->toArray();
            $nodes = tierMenusList($nodes, 'child', false);
            $this->assign('nodes', json_encode($nodes));

            return $this->fetch();
        }
        $ids = I('post.ids');
        $nodeId = I('post.node_id');
        if (!is_numeric($nodeId) || empty($nodeId)) {
            $this->error('请先选择栏目！');
        }
        $num = I('post.num');
        if (!is_numeric($num) || $num < 1) {
            $this->error('复制数量至少一篇！');
        }
        Articles::batchCopy($ids, $nodeId, $num);
        $this->success('操作成功！', true);
    }

    /**
     * 更换栏目
     * @throws \Exception
     */
    function changeNode() {
        if (!$this->request->isPost()) {
            $ids = I('get.id');
            $ids = $this->checkIds($ids);
            $this->assign('ids', implode(',', is_array($ids) ? $ids : [$ids]));

            $nodes = M('article_nodes')->field('id,name as title	,id_path,depth,"group" as type')
                ->where('deltime',0)->order('path asc,id asc')->select()->toArray();
            $nodes = tierMenusList($nodes, 'child', false);
            $this->assign('nodes', json_encode($nodes));

            return $this->fetch();
        }
        $ids = I('post.ids');
        $nodeId = I('post.node_id');
        if (!is_numeric($nodeId) || empty($nodeId)) {
            $this->error('请先选择栏目！');
        }
        $rs = M('articles')->where('id', 'in', $ids)->save(['node_id' => $nodeId,]);
        $rs === false and $this->error('操作失败！');
        $this->success('操作成功！', true);
    }

    /**
     * 投稿管理
     */
    function contribute() {
        $this->pagedata['tabs'] = [
            ['name' => '投稿列表', 'class' => 'current',],
        ];
        $this->pagedata['search'] = [
            ['tag' => 'input', 'name' => 'a.title|has|trim', 'placeholder' => '标题搜索',],
        ];

        $this->pagedata['actions'] = [
            ['label' => '批量操作', 'group' => [
                ['label' => '批量删除', 'target' => 'confirm', 'msg' => '确定要删除已选数据吗？', 'argpk' => 1, 'href' => U('Article/delArticle'),],
                ['label' => '批量发布', 'target' => 'confirm', 'msg' => '确定要发布已选数据吗？', 'argpk' => 1, 'href' => U('Article/pubArticle', ['if' => 1]),],
            ],]
        ];

        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '40',],
            ['field' => 'cz', 'title' => '操作', 'width' => '120', 'callback' => function ($item) {
                $html = '';
                $html .= '<a target="page" href="' . U('Article/addArticle', ['id' => $item['id'], 'tg' => 1,]) . '" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Article/delArticle', ['id' => $item['id'],]) . '" msg="确定要删除吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
            ['field' => 'img', 'title' => '', 'width' => '60', 'align' => 'right', 'type' => 'img',],
            ['field' => 'title', 'title' => '文章标题', 'width' => '250', 'align' => 'left', 'callback' => function ($item) {
                return subtext($item['title'], 40);
            }],
            ['field' => 'user_name', 'title' => '发布人', 'width' => '100', 'callback' => function ($item) {
                if ($item['user_id']) {
                    return $item['user_name'];
                }
                return $item['admin_name'];
            }],
            ['field' => 'node_name', 'title' => '文章栏目', 'width' => '120',],
            ['field' => 'ifpub', 'title' => '发布', 'width' => '80', 'callback' => function ($item) {
                return '<div class="layui-form"><input type="checkbox" name="status" 
                    lay-filter="article-ifpub" lay-skin="switch" lay-text="是|否" value="1" ' . ($item['ifpub'] == 'true' ? 'checked' : '') . ' unvalue="0"/></div>';
            }],
            ['field' => 'view_count', 'title' => '浏览量', 'width' => '80',],
            ['field' => 'pubtime', 'title' => '发布时间', 'width' => '120', 'type' => 'time', 'format' => 'Y-m-d',],
            ['field' => 'sort', 'title' => '排序', 'width' => '70', 'callback' => function ($item) {
                return '<input class="layui-input layui-input-sm js-sort" data-val="' . $item['sort'] . '" value="' . $item['sort'] . '"  maxlength="3" type="text" />';
            }],
        ];
        $this->pagedata['model'] = M('articles')->alias('a')
            ->field('a.id,a.title,a.img,a.ifpub,a.pubtime,a.view_count,a.is_head,a.is_special,a.is_recom,a.sort,b.name as node_name')
            ->field('c.name as user_name,c.id as user_id,d.user_name as admin_name,d.id as admin_id')
            ->join('article_nodes b', 'b.id=a.node_id', 'left')
            ->join('users c', 'c.id=a.user_id', 'left')
            ->join('admin d', 'd.id=a.admin_id', 'left')
            ->where('a.ifpub', '=', 'false')->order('a.id desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['grid_class'] = 'js-grid-contribute';
        $this->pagedata['checkType'] = true;

        return $this->grid_fetch('index');
    }


    /**
     * 发布或取消发布
     */
    function pubArticle() {
        $isPub = I('get.if', '');
        $isPub = ['0' => 'false', '1' => 'true'][$isPub] ?? '';
        $isPub or $this->error('参数错误！');
        $id = $this->checkIds(I('get.id'));
        M('articles')->where('id', 'in', $id)->save(['ifpub' => $isPub,]);
        $this->success('操作成功！', true);
    }


    /**
     * 文章排序
     */
    function sortArticle() {
        $id = I('get.id');
        $sort = I('post.sort', 0);
        if (!is_numeric($id) || !is_numeric($sort)) $this->error();
        M('articles')->where('id', $id)->save(['sort' => $sort,]);
        $this->success();
    }

    /**
     * 删除文章
     */
    function delArticle() {
        $id = I('get.id');
        $id = $this->checkIds($id);
        $rs = M('articles')->where('id', 'in', $id)->save(['deltime' => time(),]);
        $rs === false and $this->error('操作失败！');
        $this->success('操作成功！', true);
    }

    /**
     * 添加保存文章
     */
    function addArticle() {
        $id = I('get.id');
        $nodeId = session('node_id');
        if (!$this->request->isPost()) {
            is_numeric($id) and $row = Articles::getInfo($id);
            if (isset($row)) {
                $row['pubtime'] and $row['pubtime'] = date('Y-m-d H:i:s', $row['pubtime']);
            }
            $this->assign('row', $row ?? []);

            $nodes = M('article_nodes')->field('id,name as title	,id_path,depth,"group" as type')
                ->where('deltime',0)->order('path asc,id asc')->select();
            $nodes = tierMenusList($nodes, 'child', false);
            $this->assign('nodes', json_encode($nodes));

            $this->assign('nodeId', isset($row['node_id']) ? $row['node_id'] : $nodeId);

            $temp = new \app\admin\lib\Template('/pc');
            $templist = $temp->getTmplPath('article');
            $this->assign('templist', $templist);

            return $this->fetch();
        }
        $data = I('post.', null, 'trim');
        //$tags = $data['tags'];
        unset($data['tags']);


        //处理自定义字段值
        $libField = new \app\admin\model\Channel;
        $data = $libField->checkFieldValue($data['node_id'], 'articles', $data);

        $data['uptime'] = time();
        $data['pubtime'] = $data['pubtime'] ? strtotime($data['pubtime']) : time();
        $data['img'] or $data['img'] = get_html_first_imgurl($data['content']);
        if (!$data['seo_description'] && $data['content']) {
            $data['seo_description'] = @msubstr(checkStrHtml($data['content']), 0, C('config.seo_description_length'), false);
        }
        if (empty($data['is_jump'])) {
            $data['jump_url'] = '';
        }
        $tg = I('get.tg', 0);
        $jumpUrl = $tg ? U('Article/contribute') : U('Article/index', ['node_id' => $nodeId]);
        $lib = new \app\home\lib\Articles();
        if (is_numeric($id)) {

            $rs = M('articles')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');
            $url = $lib->getUrl($id, 'article', $data['node_id'] ?: 0, true);

            Articles::onAfterSave($id, $data);

            $this->success('保存成功！', $jumpUrl, ['url' => $url, 'type' => 'edit',]);
        }
        $data['admin_id'] = $this->account['id'];
        $data['add_time'] = time();
        $rId = M('articles')->insert($data, true);
        $rId or $this->error('保存失败！');
        $url = $lib->getUrl($rId, 'article', $data['node_id'] ?: 0, true);

        Articles::onAfterSave($rId, $data);

        $this->success('保存成功！', $jumpUrl, ['url' => $url, 'type' => 'add',]);
    }

    /**
     * 文章栏目
     */
    function nodes() {
        $checkType = I('checkType');
        $this->pagedata['tabs'] = [
            $checkType ? false : ['name' => '文章列表', 'url' => U('Article/index'),],
            ['name' => '文章栏目', 'class' => 'current',],
            $checkType ? false : ['name' => '文章模型', 'url' => U('Channeltype/index', ['type' => 'articles',]),],
        ];
        $checkType ? false : $this->pagedata['actions'] = [
            ['label' => '添加栏目', 'target' => 'page', 'href' => U('Article/addNode'),],
            ['label' => '<i class="layui-icon layui-icon-delete"></i>回收站', 'target' => 'page', 'href' => U('Recycle/nodes'),],
        ];
        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'name', 'class' => 'js-lanmu', 'title' => '＋ 栏目名称', 'width' => '550', 'align' => 'left', 'callback' => function ($item) use ($checkType) {
                $w = 20 * $item['depth'];
                $html = '<i class="layui-icon layui-icon-subtraction mr5"></i><span class="w40x" style="width:' . $w . 'px"></span>';
                $html .= $item['ifpub'] == 'false' ? '<font color="red">[隐]</font> ' : '';
                $checkType ? $html .= $item['name'] :
                    $html .= '<a class="hover" target="page" href="' . U('Article/index', ['node_id' => $item['id'],]) . '">' . $item['name'] . '</a>';
                if (!$checkType) {
                    $_where = ' id=' . $item['id'];
                    $item['id_path'] and $_where .= " or(id_path like '{$item['id_path']},%') ";
                    $ids = M('article_nodes')->whereRaw($_where)->where('deltime',0)->column('id');
                    $count = M('articles')->where('deltime',0)->where('node_id', 'in', $ids)->count();
                    $html .= '<i class="f12 cl-999">（文档：' . $count . '条）</i>';
                }
                return $html;
            }],
            $checkType ? false : ['field' => 'sort', 'title' => '排序', 'width' => '70', 'callback' => function ($item) {
                return '<input href="' . U('Article/sortMenus') . '" class="layui-input layui-input-sm js-sort" data-val="' . $item['sort'] . '" value="' . $item['sort'] . '" type="text" maxlength="3" />';
            }],
            $checkType ? false : ['field' => 'cz', 'title' => '操作', 'width' => '300', 'align' => 'left', 'callback' => function ($item) {
                $html = '';
                $html .= '<a href="' . U('Article/addNode', ['pid' => $item['id'],]) . '" target="page" class="layui-btn layui-btn-primary layui-btn-xs"">添加下级栏目</a>';
                $html .= '<a href="' . U('Article/addNode', ['id' => $item['id'], 'pid' => $item['parent_id'],]) . '" target="page" class="layui-btn layui-btn-xs">编辑</a>';
                $html .= '<a href="' . U('Article/delNode', ['id' => $item['id'],]) . '" msg="<span class=\'cl-f44\'>如有子栏目及文档将一起清空</span>，确认删除到回收站？" 
                target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                return $html;
            }],
        ];

        $data = M('article_nodes')->field('id,name,depth,sort,parent_id,id_path,ifpub')
            ->where('deltime', '=', 0)->order('path asc,id asc')->select()->toArray();

        $this->pagedata['data'] = tierMenusList($data);

        $this->pagedata['trAttr'] = [
            'pid' => 'parent_id',
        ];//表格行属性
        $this->pagedata['pk_field'] = 'id';//手动指定住建
        $this->pagedata['fixedColumn'] = true;//固定列宽
        $this->pagedata['isPage'] = false;//不显示分页
        $this->pagedata['grid_class'] = 'js-view-nodes';

        return $this->grid_fetch('site/menus');
    }

    /**
     * 栏目排序
     * @throws \Exception
     */
    function sortMenus() {
        $id = I('post.id');
        $sort = I('post.sort', 0);
        if (!is_numeric($id) || !is_numeric($sort)) $this->error('');

        $row = M('article_nodes')->where('id', $id)->field('path,id_path')->find();
        $path = $row['path'];
        $path = substr($path, 0, -4) . (1000 + ($sort > 1000 ? 999 : $sort));

        $rs = M('article_nodes')->where('id', $id)->save(['sort' => $sort, 'path' => $path, 'uptime' => time(),]);
        $rs or $this->error();

        $len = strlen($path) + 1;
        M('article_nodes')->where('id_path', 'like', $row['id_path'] . ',%')
            ->exp('path', "concat('{$path}',substring(path,{$len}))")->update();

        $this->updateCache();//更新缓存

        $this->success('', true);
    }

    /**
     * 更新数据缓存
     */
    function updateCache() {
        $lib = new \app\home\model\nodes();
        $lib->updateCache();//更新数据
    }

    /**
     * 删除栏目
     * @throws \Exception
     */
    function delNode() {
        $id = I('get.id');
        is_numeric($id) or $this->error('参数不合法！');

        $path = M('article_nodes')->where('id', $id)->value('id_path');
        $where = ' id=' . $id;
        $path and $where .= " or(id_path like '{$path},%') ";

        $ids = M('article_nodes')->whereRaw($where)->column('id');

        $rs = M('article_nodes')->whereRaw($where)->save(['deltime' => time(),]);
        $rs === false and $this->error('操作失败！');

        M('articles')->where('node_id', 'in', $ids)->save(['deltime' => time(),]);

        $this->updateCache();

        $this->success('操作成功！', true);
    }

    /**
     * 添加栏目
     * @throws \Exception
     */
    function addNode() {
        if (!$this->request->isPost()) {
            $pid = I('get.pid');
            if (is_numeric($pid) && $pid > 0) {
                $title = M('article_nodes')->where('deltime',0)->where('id', $pid)->value('name');
                if ($title) {
                    $this->assign('ptitle', $title);
                    $this->assign('pid', $pid);
                }
            }
            $id = I('get.id');
            $id and $row = M('article_nodes')->where('id', $id)->find();
            if ($id && !empty($row)) {
                $nodes = M('article_nodes')->field('id,name,depth,id_path')
                    ->order('path asc,id asc')->where('deltime', 0)->select()->toArray();
                $nodes = tierMenusList($nodes);
                foreach ($nodes as &$item) {
                    $item['name'] = str_repeat('&nbsp;', ($item['depth'] - 1) * 4) . $item['name'];
                }
                $this->assign('nodes', $nodes);
            }

            $this->assign('view_route', '{typedir}/{aid}.html');
            $this->assign('list_route', '{typedir}/list_{tid}_{page}.html');

            $this->assign('row', $row ?? []);

            $temp = new \app\admin\lib\Template('/pc');
            $templist = $temp->getTmplPath('node');
            $this->assign('templist', $templist);

            $templist_view = $temp->getTmplPath('article');
            $this->assign('templist_view', $templist_view);

            $channelList = M('channeltype')->where('type', 'articles')->field('id,title')->select()->toArray();
            $this->assign('channelList', $channelList);

            return $this->fetch();
        }

        $id = I('get.id');
        $data = I('post.', null, 'trim');

        $data['name'] = trim($data['name']) or $this->error('请填写栏目名称！');
        if (!$data['seo_description'] && $data['content']) {
            $data['seo_description'] = @msubstr(checkStrHtml($data['content']), 0, C('config.seo_description_length'), false);
        }

        if (strpos($data['view_route'], '{aid}') === false) {
            $this->error('文章命名规则 必须存在 {aid} ！');
        }
        if (strpos($data['list_route'], '{page}') === false) {
            $this->error('列表命名规则 必须存在 {page} ！');
        }

        /*=== 处理目录名==开始===*/
        if ($data['dir_name'] && preg_match('/[^a-zA-Z0-9_\/]/', $data['dir_name'])) {
            $this->error('目录名称错误，仅支持字母、数字、下划线、斜杠！');
        }
        $pinyin = new \app\admin\lib\Piyin('article_nodes');

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
                $this->error('自己不能成为自己的上级栏目');
            }
            /*--end*/

            $oldInfo = M('article_nodes')->where('id', $id)->find();
            if ($oldInfo['parent_id'] != $data['parent_id']) {
                $pInfo = M('article_nodes')->where('id', $data['parent_id'])->field('depth,path,id_path')->find();
                $sort = M('article_nodes')->where('parent_id', $data['parent_id'])->max('sort');
                $sort = $sort ? $sort + 1 : 1;

                $data['depth'] = ($pInfo['depth'] ?? 0) + 1;
                $data['path'] = ($pInfo['path'] ?? '') . (1000 + ($sort > 1000 ? 999 : $sort));
                $data['sort'] = $sort;
                $data['id_path'] = empty($pInfo['id_path']) ? $id : $pInfo['id_path'] . ',' . $id;
            }

            $rs = M('article_nodes')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');

            if ($oldInfo['parent_id'] != $data['parent_id']) {
                $list = M('article_nodes')->where('id_path', 'like', $oldInfo['id_path'] . ',%')->field('id,depth,path,id_path')
                    ->order('path asc,id asc')->select()->toArray();
                foreach ($list as $item) {
                    $path = $data['path'] . substr($item['path'], strlen($oldInfo['path']));
                    $id_path = $data['id_path'] . substr($item['id_path'], strlen($oldInfo['id_path']));
                    $depth = substr_count($id_path, ',') + 1;
                    M('article_nodes')->where('id', $item['id'])->save([
                        'depth' => $depth,
                        'path' => $path,
                        'id_path' => $id_path,
                    ]);
                }
                $oldInfo['id_path'] = $data['id_path'];//赋值最新数据
            }

            //更新下级栏目的属性
            if ($upNext) {
                $next = array_columns([$data], 'channel_id,tmpl_path,tmpl_view,view_route,list_route');
                M('article_nodes')->where('id_path', 'like', $oldInfo['id_path'] . ',%')->save($next[0]);
            }
            $this->success('保存成功！', 'Article/nodes');
        }
        $pid = isset($data['parent_id']) && is_numeric($data['parent_id']) ? $data['parent_id'] : 0;

        $pInfo = M('article_nodes')->where('id', $pid)->field('depth,path,id_path')->find();
        $sort = M('article_nodes')->where('parent_id', $pid)->max('sort');
        $sort = $sort ? $sort + 1 : 1;

        $data['depth'] = ($pInfo['depth'] ?? 0) + 1;
        $data['path'] = ($pInfo['path'] ?? '') . (1000 + ($sort > 1000 ? 999 : $sort));
        $data['sort'] = $sort;
        $data['uptime'] = time();

        $rId = M('article_nodes')->insert($data, true);
        $rId or $this->error('保存失败！');

        M('article_nodes')->where('id', $rId)->save([
            'id_path' => (isset($pInfo['id_path']) && $pInfo['id_path'] ? $pInfo['id_path'] . ',' : '') . $rId
        ]);

        $this->success('保存成功！', 'Article/nodes');
    }

    /*
     * 文章远程图片本地化
     */
    function imgRemoteToLocal() {
        $content = input('post.content/s', '', null);
        $content = remoteimg_tolocal($content);
        $this->success('远程图片本地化已完成', '', ['content' => $content]);
    }

    /*
     * 清除文章内容中外部链接
     */
    function linkClear() {
        $content = input('post.content/s', '', null);

        // 读取允许的超链接设置
        $host = request()->host(true);
        $allow_urls = [$host];

        $host_rule = join('|', $allow_urls);
        $host_rule = preg_replace("#[\n\r]#", '', $host_rule);
        $host_rule = str_replace('.', "\\.", $host_rule);
        $host_rule = str_replace('/', "\\/", $host_rule);
        $arr = '';
        preg_match_all("#<a([^>]*)>(.*)<\/a>#iU", $content, $arr);
        if (is_array($arr[0])) {
            $rparr = array();
            $tgarr = array();
            foreach ($arr[0] as $i => $v) {
                if ($host_rule != '' && preg_match('#' . $host_rule . '#i', $arr[1][$i])) {
                    continue;
                } else {
                    $rparr[] = $v;
                    $tgarr[] = $arr[2][$i];
                }
            }
            if (!empty($rparr)) {
                $content = str_replace($rparr, $tgarr, $content);
            }
        }
        $arr = $rparr = $tgarr = '';
        $this->success('清除成功', '', ['content' => $content]);
    }
}