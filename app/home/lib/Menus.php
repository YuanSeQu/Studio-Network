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

namespace app\home\lib;


class Menus extends Base
{

    /*
     * 生成导航链接
     */
    public function getUrl($id = 0, $domain = false) {

        $route = $this->Route::getRoute('menu', $id);
        if ($id === 'cats') {
            return $route;
        }

        $url = $route['url'] ?? '';
        if (preg_match('/^http/', $url)) {
            return $url;//自定义url
        }
        if ($url === 'home') {
            return getRrzUrl('/', false, $domain);
        }

        $path = $route['rule'] ?? '';
        $suffix = $this->Route::get('navHideSuffix') ? false : true;

        if (!$path) {
            $path = 'menu/' . $id;
            if ($url === '/cats.html') {
                $path = 'cats';
            }
            $suffix = true;
        }
        $suffix or $path .= '/';

        return getRrzUrl('/' . $path, $suffix, $domain);
    }

    /*
     * 获取当前页面的最上级分类信息，可指定具体级别
     */
    public function getTopType($rrz = [], $page = '', $grade = 1, $sons = 0) {
        $top = [];
        $curId = $this->getCurTypeId($page, $rrz);
        if ($page == 'search') {
            $top = ['id' => 0, 'title' => $rrz['title'], 'en_title' => 'Search', 'type' => 'search', 'img' => '', 'url' => getRrzUrl('/search'), 'list' => $rrz['menus'],];
        } elseif ($page == 'article' || $page == 'node') {
            $typeId = $page == 'article' ? $rrz['node_id'] ?? 0 : $rrz['id'];

            $row = Dm('nodes')->where('id', $typeId)->find();
            $ids = array_filter(explode(',', $row['id_path']));
            $topId = $ids[$grade - 1] ?? 0;
            if ($topId) {
                $lib = new \app\home\lib\Articles;
                $top = $lib->getNodeInfo($topId);
                $sons and $top['list'] = $lib->getNodeList($topId, 0, 'son', $curId);
            }
        } elseif ($page == 'cat' || $page == 'item') {
            $all = $this->getPageMenu(0, 'cat');
            if (!$all) {
                $typeId = $page == 'item' ? $rrz['cat_id'] ?? 0 : $rrz['id'];

                $row = Dm('cats')->where('id', $typeId)->find();
                $ids = array_filter(explode(',', $row['id_path']));
                $topId = $ids[$grade - 1] ?? 0;
            } else {
                $topId = 0;
            }
            if (is_numeric($topId)) {
                $lib = new \app\home\lib\Goods;
                $top = $lib->getCatInfo($topId ?: 'all');
                $sons and $top['list'] = $lib->getCatList($topId, 0, 'son', $curId);
            }
        }
        return $top;
    }

    /*
     * 导航或分类的子集获取
     */
    public function getSons($id = 0, $type = '', $son = 'son', $limit = 0, $page = '', $rrz = []) {
        $info = [];
        $son = $son ?: 'son';
        if (is_array($id)) {
            $info = $id;
            $type = $info['type'] ?? $type;
            $id = $info['id'] ?? 0;
        }
        if (!is_numeric($id) || !in_array($type, ['menu', 'node', 'cat'])) {
            return ['ishas' => false, 'list' => [],];
        }
        $curId = $this->getCurTypeId($page, $rrz);
        $list = [];
        if ($type == 'menu') {
            $list = $this->getSonList($id, $limit, $son);
            if (!$list) {
                $info = $info ?: $this->getInfo($id);
                return $this->getSons($info['pageId'] ?? '', $info['page'] ?? '', $son, $limit, $page, $rrz);
            }
        } elseif ($type == 'node') {
            $lib = new \app\home\lib\Articles;
            $list = $lib->getNodeList($id, $limit, $son, $curId);
        } elseif ($type == 'cat') {
            $lib = new \app\home\lib\Goods;
            $list = $lib->getCatList($id, $limit, $son, $curId);
        }
        return ['ishas' => count($list) > 0, 'list' => $list,];
    }

    /*
     * 获取当前页面分类id
     */
    public function getCurTypeId($page = '', $rrz = []) {
        $curId = 0;
        if ($page == 'article' || $page == 'node') {
            $curId = $page == 'article' ? $rrz['node_id'] ?? 0 : $rrz['id'];
        } elseif ($page == 'cat' || $page == 'item') {
            $curId = $page == 'item' ? $rrz['cat_id'] ?? 0 : $rrz['id'];
        }
        return $curId ?: 0;
    }

    /*
     * 获取下级菜单列表
     */
    public function getSonList($id = 0, $limit = 0, $type = 'all', $pageInfo = false) {
        if ($type == 'sonself') {
            if (!is_numeric($id) || $id < 0) return [];
            $list = $this->getSonList($id, $limit, 'son');
            if ($list) return $list;
            $id = Dm('menus')->where('id', $id)->value('parent_id');
            if (!$id) return [];
            return $this->getSonList($id, $limit, 'son');
        }

        $model = Dm('menus');
        if (strpos($id, ',') !== false) {
            $ids = array_filter(explode(',', $id), function ($item) {
                return is_numeric($item) && $item > 0;
            });
            if (!$ids) return [];
            $id = $ids;
        }
        if ($type == 'top' || $type == 'son' || $type == 'self') {
            $type == 'top' and $id = 0;
            if (!is_array($id) && (!is_numeric($id) || $id < 0) || ($type == 'son' && !$id)) return [];
            $key = $type == 'self' ? 'id' : 'parent_id';
            $model->where($key, is_array($id) ? 'in' : '=', $id);
        }
        $curIds = [];
        $this->curMenu and $curIds = $this->curMenu['topIds'];

        $model->order('path asc,id asc');
        $data = $this->setLimit($model, $limit)->select();
        $list = [];
        foreach ($data as $item) {
            $k = $item['depth'] - 1;
            $menu = [
                'id' => $item['id'],
                'title' => $item['title'],
                'en_title' => $item['en_title'],
                'url' => $this->getUrl($item['id']),
                'href' => $item['url'],
                'target' => $item['target_blank'] === 'true' ? '_blank' : '_self',
                'class' => (isset($curIds[$k]) && $curIds[$k] == $item['id']) ? 'on' : '',
                'type' => 'menu',
            ];
            $menu = array_merge($menu, $this->getPageInfo($item['url'], $pageInfo));
            $list[] = $menu;
        }
        return $list;
    }

    /*
     * 获取菜单列表
     */
    public function getList($id = 0, $pageInfo = false) {
        if ($id < 0) return [];
        $where = [];
        $topId = 0;
        $curIds = [];
        if ($this->curMenu) {
            $curIds = $this->curMenu['topIds'];
            if ($id == $this->curMenu['id']) {
                $topId = $curIds[0];
            } elseif ($id == $curIds[0]) {
                $topId = $id;
            }
        }
        if ($id > 0 && !$topId) {
            $info = $this->getInfo($id);
            if ($info && $info['topId']) {
                $topId = $info['topId'];
            }
        }
        $model = Dm('menus');
        if ($topId > 0) {
            $model->where('id_path', 'like%', $topId . ',');
        }
        $data = $model->order('path asc,id asc')->select();
        $list = [];
        foreach ($data as $item) {
            $ids = array_filter(explode(',', $item['id_path']));
            $k = $item['depth'] - 1;
            $menu = [
                'id' => $item['id'],
                'title' => $item['title'],
                'en_title' => $item['en_title'],
                'url' => $this->getUrl($item['id']),
                'href' => $item['url'],
                'target' => $item['target_blank'] === 'true' ? '_blank' : '_self',
                'class' => (isset($curIds[$k]) && $curIds[$k] == $item['id']) ? 'on' : '',
                'type' => 'menu',
            ];
            $menu = array_merge($menu, $this->getPageInfo($item['url']));

            $topId > 0 and $k = $k - 1;
            if ($k == 0) {
                $list[$item['id']] = $menu;
            } elseif ($k > 0) {
                $str = '';
                for ($i = 0; $i < $k; $i++) {
                    $str .= "[\$ids[" . $i . "]]['children']";
                }
                eval('$list' . $str . '[$item[\'id\']] = $menu;');
            }
        }
        return array_values($list);
    }

    /*
     * 获取菜单信息
     */
    public function getInfo($id = 0, $pageInfo = false) {
        if (!is_numeric($id) || !$id) return [];
        $row = Dm('menus')->where('id', $id)->find();
        if ($row) {
            $row['href'] = $row['url'];
            $row['url'] = $this->getUrl($row['id']);
            $ids = explode(',', $row['id_path']);
            $row['topIds'] = $ids;
            $row['topId'] = $ids[0];
            $row['target'] = $row['target_blank'] === 'true' ? '_blank' : '_self';
            $row = array_merge($row, $this->getPageInfo($row['href'], $pageInfo));
            $row['type'] = 'menu';
        }
        return $row ?: [];
    }

    /*
     * 根据url获取页面信息
     */
    public function getPageInfo($url, $pageInfo = false) {
        $url = str_replace('/index.php', '', $url);
        $row = [];
        $alib = new \app\home\lib\Articles;
        $glib = new \app\home\lib\Goods;
        if ($url == '/' || $url === '') {
            $row['page'] = 'index';
            $row['pageId'] = 0;
        } elseif (preg_match('/^\/article\/(\d+)/', $url, $matches)) {
            $row['page'] = 'article';
            $row['article_id'] = $row['pageId'] = $matches[1];
            $pageInfo and $row['pageInfo'] = $alib->getInfo($row['pageId']);
        } elseif (preg_match('/^\/node\/(\d+)/', $url, $matches)) {
            $row['page'] = 'node';
            $row['node_id'] = $row['pageId'] = $matches[1];
            $pageInfo and $row['pageInfo'] = $alib->getNodeInfo($row['pageId']);
        } elseif (preg_match('/^\/cat\/(\d+)/', $url, $matches)) {
            $row['page'] = 'cat';
            $row['cat_id'] = $row['pageId'] = $matches[1];
            $pageInfo and $row['pageInfo'] = $glib->getCatInfo($row['pageId']);
        } elseif (preg_match('/^\/cats/', $url)) {
            $row['page'] = 'cat';
            $row['cat_id'] = $row['pageId'] = 0;
        } elseif (preg_match('/^\/item\/(\d+)/', $url, $matches)) {
            $row['page'] = 'item';
            $row['item_id'] = $row['pageId'] = $matches[1];
            $pageInfo and $row['pageInfo'] = $glib->getInfo($row['pageId']);
        } elseif (preg_match('/^\/brand/', $url)) {
            $row['page'] = 'brand';
            $row['brand_id'] = $row['pageId'] = 0;
        } elseif (preg_match('/^http/', $url)) {
            $row['page'] = 'url';
        } else {
            $row['page'] = '';
        }
        $row['pageInfo'] = $row['pageInfo'] ?? [];
        return $row;
    }

    /*
     * 根据页面类型获取当前导航
     */
    public function getPageMenu($id, $type = '') {
        if (!is_numeric($id) || !$type) return false;
        if ($type == 'index') {
            $mId = Dm('menus')->where('url', '/')->order('depth asc')->value('id');
            if ($mId) {
                return $this->getInfo($mId);
            }
            return false;
        } elseif ($type == 'article') {
            $mId = Dm('menus')->where('url', '/article/' . $id . '.html')->order('depth desc')->value('id');
            if ($mId) {
                return $this->getInfo($mId);
            }
            $nodeId = M('articles')->where('id', $id)->value('node_id');
            if (!$nodeId) return false;
            return $this->getPageMenu($nodeId, 'node');
        } elseif ($type == 'cat' && $id == 0) {
            $mId = Dm('menus')->where('url', '/cats.html')->value('id');
            if ($mId) {
                return $this->getInfo($mId);
            }
            return false;
        } elseif (in_array($type, ['node', 'cat'])) {
            $model = Dm($type == 'node' ? 'nodes' : 'cats');
            $path = $model->where('id', $id)->value('id_path');
            $ids = array_reverse(array_filter(explode(',', $path)));
            foreach ($ids as $nId) {
                $mId = Dm('menus')->where('url', '/' . $type . '/' . $nId . '.html')->value('id');
                if ($mId) {
                    return $this->getInfo($mId);
                }
            }
            if ($type == 'cat') {
                return $this->getPageMenu(0, 'cat');
            }
            return false;
        } elseif ($type == 'item') {
            $mId = Dm('menus')->where('url', '/item/' . $id . '.html')->order('depth desc')->value('id');
            if ($mId) {
                return $this->getInfo($mId);
            }
            $catId = M('goods')->where('id', $id)->value('cat_id');
            return $this->getPageMenu($catId ?: 0, 'cat');
        } elseif ($type == 'brand') {
            $mId = Dm('menus')->where('url', '/brand.html')->value('id');
            if ($mId) {
                return $this->getInfo($mId);
            }
            return false;
        }
        return false;
    }

    public function getSidebar($page = '', $rrz = [], $limit = 0) {
        if (!in_array($page, ['article', 'node', 'cat', 'item', 'search', 'brand']) || !$rrz) {
            return ['title' => $rrz['title'] ?? '', 'en_title' => '', 'url' => '', 'top' => $rrz ?? [], 'list' => [],];
        }
        $top = $rrz ?: [];
        $list = [];
        $url = $rrz['title']['url'] ?? '';
        if ($page == 'search') {
            return ['title' => $rrz['title'], 'en_title' => 'Search', 'url' => getRrzUrl('/search'), 'top' => $rrz, 'list' => $rrz['menus'],];
        }
        if ($page == 'article' || $page == 'item') {
            $title = $rrz['typeInfo'] ? $rrz['typeInfo']['title'] : '';
            $en_title = $rrz['typeInfo'] ? $rrz['typeInfo']['en_title'] : '';
        } else {
            $title = $rrz['title'];
            $en_title = $rrz['en_title'];
        }

        if ($page == 'article' || $page == 'node') {
            $nodeId = $page == 'article' ? $rrz['node_id'] ?? 0 : $rrz['id'];
            if ($nodeId) {
                $lib = new \app\home\lib\Articles;
                $list = $lib->getNodeList($nodeId, $limit, 'son', $nodeId);
                if ($list) {
                    $top = $lib->getNodeInfo($nodeId);
                } else {
                    $row = Dm('nodes')->where('id', $nodeId)->find();
                    $ids = array_filter(explode(',', $row['id_path']));
                    $id = $row['parent_id'] > 0 ? $row['parent_id'] : $ids[0];

                    $list = $lib->getNodeList($id, $limit, 'son', $nodeId);
                    $top = $lib->getNodeInfo($id);
                }
                $url = $lib->getUrl($nodeId, 'node');
            } elseif ($page == 'article') {
                $title = $rrz['title'];
                $en_title = '';
            }
        } elseif ($page == 'cat' || $page == 'item') {
            $catId = $page == 'item' ? $rrz['cat_id'] ?? 0 : $rrz['id'];
            if (is_numeric($catId)) {
                $lib = new \app\home\lib\Goods;
                if ($catId > 0) {
                    $list = $lib->getCatList($catId, $limit, 'son', $catId);
                    if ($list) {
                        $id = $catId;
                    } else {
                        $row = Dm('cats')->where('id', $catId)->find();
                        $ids = array_filter(explode(',', $row['id_path']));
                        $id = $row['parent_id'] > 0 ? $row['parent_id'] : $ids[0];
                    }
                } else {
                    $id = 0;
                }
                $all = $this->getPageMenu(0, 'cat');
                $list = $list ?: $lib->getCatList($id, $limit, 'son', $catId);
                if (!$list && $all && $id == $catId) {
                    $list = $lib->getCatList(0, $limit, 'son', $catId);
                    $catId = $id = 0;
                }
                $url = $lib->getUrl($catId, 'cat');
                $top = $lib->getCatInfo($id > 0 ? $id : 'all');
            }
        }
        $curMenu = $this->curMenu ?: $this->getPageMenu($rrz['id'], $page);
        if ($curMenu) {
            $id = ($top && $curMenu['pageId'] == $top['id'] && $list) ? $curMenu['id'] : ($curMenu['parent_id'] > 0 ? $curMenu['parent_id'] : $curMenu["topId"]);
            $_list = $this->getSonList($id, $limit, 'son');
            if (count($_list) >= count($list) && count($_list) > 0) {
                $list = $_list;
                $title = $curMenu['title'];
                $en_title = $curMenu['en_title'];
                $url = $curMenu['url'];
                $top = $this->getInfo($id);
            }
        }
        return ['title' => $title, 'en_title' => $en_title, 'url' => $url, 'top' => $top ?: [], 'list' => $list,];
    }

    private function getCrumbsList($page = '', $rrz = []) {
        if (!in_array($page, ['article', 'node', 'cat', 'item', 'search', 'brand', 'tag']) || !$rrz) return [];
        $list = [];
        if ($page == 'search') {
            $list[] = [
                'title' => $rrz['title'],
                'name' => $rrz['title'],
                'url' => getRrzUrl('/search'),
            ];
            return $list;
        } elseif ($page == 'tag') {
            $list[] = [
                'title' => '标签',
                'name' => '标签',
                'url' => '',
            ];
            $list[] = [
                'title' => $rrz['title'],
                'name' => $rrz['title'],
                'url' => $rrz['url'],
            ];
            return $list;
        }
        if ($page == 'article' || $page == 'node') {
            $nodeId = $page == 'article' ? $rrz['node_id'] ?? 0 : $rrz['id'];
            if ($nodeId) {
                $lib = new \app\home\lib\Articles;
                $list = $lib->getCrumbsList($nodeId);
            }
        } elseif ($page == 'cat' || $page == 'item') {
            $catId = $page == 'item' ? $rrz['cat_id'] ?? 0 : $rrz['id'];
            if ($catId) {
                $lib = new \app\home\lib\Goods;
                $list = $lib->getCrumbsList($catId);
            }
        }

        $curMenu = $this->curMenu ?: $this->getPageMenu($rrz['id'], $page);
        if (!$curMenu) return $list;

        $_list = Dm('menus')->where('id', 'in', $curMenu['id_path'])->order('path asc,id asc')->select();
        if (count($_list) >= count($list)) {
            $list = $_list;
            foreach ($list as &$item) {
                $item['url'] = $this->getUrl($item['id']);
            }
        }
        return $list;
    }

    /*
     * 获取菜单路径html
     */
    public function getCrumbs($class = '', $index = '首页', $page = '', $rrz = []) {
        $list = $this->getCrumbsList($page, $rrz);
        if (!$list) return '';
        $symbol = '>';
        $root = getRrzUrl('/');
        $index = __($index);
        $str = "<a href='{$root}' class='{$class}'>{$index}</a>";
        $i = 1;
        foreach ($list as $item) {
            $class = $i < count($list) ? "class='{$class}'" : '';
            $href = $item['url'] ? "href='{$item['url']}'" : '';
            $str .= " {$symbol} <a {$href} {$class}>{$item['title']}</a>";
            ++$i;
        }
        return $str;
    }
}