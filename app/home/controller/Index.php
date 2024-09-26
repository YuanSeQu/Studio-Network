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


class Index extends Base
{
    private $curMenu = [];

    /*
     * 首页
     */
    function index() {
        $this->assign('curMenu', []);//当前菜单信息
        $this->assign('rrz', [
            'title' => sysConfig('website.title'),
            'en_title' => 'Home',
            'seo_title' => sysConfig('website.title'),
            'seo_keywords' => sysConfig('website.keywords'),
            'seo_description' => sysConfig('website.description'),
        ]);
        $this->assign('page', __FUNCTION__);
        $this->getCurMenu(0, 'index');

        return $this->fetch(':index');
    }

    /**
     * 导航链接
     */
    function menu() {
        $id = I('id');
        $page = I('page', '');
        $pageId = I('pageId', '');
        is_numeric($id) or $this->error('非法参数！');
        $lib = new \app\home\lib\Menus();
        $row = $lib->getInfo($id);
        if (!$row || !$row['page']) $this->error('页面不存在！');

        $this->curMenu = $row;
        $this->assign('curMenu', $row);//当前菜单信息

        if ($row['page'] == 'url') {
            header('Location: ' . $row['url']);
            exit;
        }
        $pageId = is_numeric($pageId) ? $pageId : $row['pageId'];
        $page = $page ?: $row['page'];

        return $this->{$page}($pageId);
    }

    private function getCurMenu($id, $type = '', $isCurMenu = true) {
        if ($this->curMenu && $isCurMenu) {
            $curMenu = $this->curMenu;
        } else {
            $lib = new \app\home\lib\Menus();
            $curMenu = $lib->getPageMenu($id, $type);
            if ($curMenu) {
                $this->assign('curMenu', $curMenu);//当前菜单信息
            }
        }
        $this->env['menu'] = $curMenu ?: [];
        return $this->env['menu'];
    }

    /*
     * 文章页
     */
    function article($id = 0) {
        $id = $id ?: I('id');
        is_numeric($id) or $this->error('非法参数！');
        $this->getCurMenu($id, 'article', false);
        $lib = new \app\home\lib\Articles();
        $article = $lib->getInfo($id);
        $article or $this->error('文章不存在！');
        if ($article['is_jump']) {
            exit('<head><title>文档已移动</title></head><body><h1>对象已移动</h1>可在<a href="' . $article['jump_url'] . '">此处</a>找到该文档</body>');
        }
        $article['ifpub'] == 'true' or $this->error('文章尚未发布！');

        $article['view_count'] = '<s id="rrzJsIdViewCount" style="display: inline;color:inherit;text-decoration:none;"><script type="text/javascript" src="' . U('/view/count', ['t' => 'article', 'id' => $id], 'asp') . '" async></script></s>';
        $rrz = $this->setSeoTitle('article', $article);
        $this->assign('rrz', $rrz);

        $template = ':article';
        if (!$article['tmpl_path'] && $article['node_id']) {
            $tmpl_view = M('article_nodes')->where('id', $article['node_id'])->value('tmpl_view');
            $tmpl_view and $article['tmpl_path'] = $tmpl_view;
        }
        if ($article['tmpl_path']) {
            $template = ':article_' . $article['tmpl_path'];
            if (!$this->checkTemplateFile($template)) {
                $template = ':article';
            }
        }
        $this->env['page'] = 'article';
        $this->assign('page', 'article');
        return $this->fetch($template);
    }

    /*
     * 文章列表
     */
    function node($id = 0) {
        $id = $id ?: I('id');
        is_numeric($id) or $this->error('非法参数！');
        $this->getCurMenu($id, 'node');
        $lib = new \app\home\lib\Articles();
        $node = $lib->getNodeInfo($id) or $this->error('文章节点不存在！');
        $node['ifpub'] == 'true' or $this->error('文章栏目尚未发布！');

        $rrz = $this->setSeoTitle('node', $node);
        $this->assign('rrz', $rrz);

        $template = ':node';
        if ($node['tmpl_path']) {
            $template = ':node_' . $node['tmpl_path'];
            if (!$this->checkTemplateFile($template)) {
                $template = ':node';
            }
        }
        $this->env['page'] = 'node';
        $this->assign('page', 'node');
        return $this->fetch($template);
    }

    /*
     * 产品列表
     */
    function cat($id = 0) {
        $id = is_numeric($id) ? $id : I('id', 0);
        $this->getCurMenu($id, 'cat');
        $lib = new \app\home\lib\Goods();
        $cat = $lib->getCatInfo($id === 0 ? 'all' : $id);
        $cat or $this->error('产品分类不存在！');
        $cat['ifpub'] == 'true' or $this->error('产品分类尚未发布！');

        $rrz = $this->setSeoTitle('cat', $cat);
        $this->assign('rrz', $rrz);

        $template = ':cat';
        if (isset($cat['tmpl_path']) && $cat['tmpl_path']) {
            $template = ':cat_' . $cat['tmpl_path'];
            if (!$this->checkTemplateFile($template)) {
                $template = ':cat';
            }
        }
        $this->env['page'] = 'cat';
        $this->assign('page', 'cat');
        return $this->fetch($template);
    }

    /*
     * 产品详情
     */
    function item($id = 0) {
        $id = $id ?: I('id');
        is_numeric($id) or $this->error('非法参数！');
        $this->getCurMenu($id, 'item', false);
        $lib = new \app\home\lib\Goods;
        $data = $lib->getInfo($id) or $this->error('产品不存在！');
        if ($data['is_jump']) {
            exit('<head><title>文档已移动</title></head><body><h1>对象已移动</h1>可在<a href="' . $data['jump_url'] . '">此处</a>找到该文档</body>');
        }
        $data['view_count'] = '<s id="rrzJsIdViewCount" style="display: inline;color:inherit;text-decoration:none;"><script type="text/javascript" src="' . U('/view/count', ['t' => 'item', 'id' => $id], 'asp') . '" async></script></s>';
        $rrz = $this->setSeoTitle('item', $data);
        $this->assign('rrz', $rrz);

        $template = ':item';
        if (!$data['tmpl_path'] && $data['cat_id']) {
            $tmpl_view = M('goods_cat')->where('id', $data['cat_id'])->value('tmpl_view');
            $tmpl_view and $data['tmpl_path'] = $tmpl_view;
        }
        if ($data['tmpl_path']) {
            $template = ':item_' . $data['tmpl_path'];
            if (!$this->checkTemplateFile($template)) {
                $template = ':item';
            }
        }
        $this->env['page'] = 'item';
        $this->assign('page', 'item');
        return $this->fetch($template);
    }

    /**
     * 品牌页
     */
    function brand() {
        $this->getCurMenu(0, 'brand');

        $title = __('品牌页');
        $rrz = [
            'id' => 0,
            'title' => $title,
            'en_title' => 'Brand',
            'seo_title' => $title,
            'seo_keywords' => $title,
            'seo_description' => $title,
            'type' => 'brand',
        ];
        $rrz = $this->setSeoTitle('brand', $rrz);
        $this->assign('rrz', $rrz);

        $this->env['page'] = 'brand';
        $this->assign('page', __FUNCTION__);
        $this->getCurMenu(0, 'brand');

        return $this->fetch(':brand');
    }

    /*
     * 标签
     */
    function tags() {
        $id = I('id');
        is_numeric($id) or $this->error('非法参数！');

        $lib = new \app\home\lib\Tags();
        $rrz = $lib->getInfo($id);

        $rrz = $this->setSeoTitle('tag', $rrz);
        $this->assign('rrz', $rrz);


        $template = ':tag';
        if ($rrz['tmpl_path']) {
            $template = ':tag_' . $rrz['tmpl_path'];
            if (!$this->checkTemplateFile($template)) {
                $template = ':tag';
            }
        }
        if (!$this->checkTemplateFile($template)) {
            $template = $rrz['type'] == 1 ? ':node' : ':cat';
        }

        $this->env['page'] = 'tag';
        $this->assign('page', 'tag');
        $html = $this->fetch($template);
        $html .= '<s id="rrzJsIdViewCount" style="display: none;"><script type="text/javascript" src="' . U('/view/count', ['t' => 'tag', 'id' => $id], 'asp') . '" async></script></s>';
        return $html;
    }

    /**
     * 搜索
     */
    function search() {
        $type = I('t', 'article');
        $keywords = trim(I('q', ''));

        $title = __($type == 'article' ? '文章搜索页' : '产品搜索页');
        $title = $keywords . ($keywords ? '_' : '') . $title;

        $rrz = [
            'title' => __('搜索页'),
            'url' => $this->request->url(false),
            'en_title' => 'Search',
            'seo_title' => $title,
            'seo_keywords' => $title,
            'seo_description' => $title,
            'keywords' => $keywords,
            'type' => $type,
            'type_title' => __($type == 'article' ? '文章搜索页' : '产品搜索页'),
            'menus' => [
                ['class' => $type == 'article' ? 'on' : '', 'title' => __('文章搜索'), 'url' => getRrzUrl('/search') . '?t=article&q=' . $keywords,],
                ['class' => $type == 'item' ? 'on' : '', 'title' => __('产品搜索'), 'url' => getRrzUrl('/search') . '?t=item&q=' . $keywords,],
            ],
        ];
        $p = I('get.p', 1);
        $_GET['p'] = $p;

        $rrz = $this->setSeoTitle('search', $rrz);
        $this->assign('rrz', $rrz);

        $_GET['t'] = $type;

        $this->assign('curMenu', []);//当前菜单信息
        $this->assign('page', 'search');
        $this->env['page'] = 'search';
        $this->env['menu'] = [];
        return $this->fetch(':search');
    }


    /**
     * 表单提交
     * @throws \Exception
     */
    function formSubmit() {
        $lib = new \app\home\lib\Forms();
        $lib->formSubmit($msg) or $this->error($msg);
        $this->success($msg);
    }
}
