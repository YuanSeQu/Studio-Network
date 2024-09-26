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


class Goods extends Base
{
    /*
     * 生成前台url
     */
    public function getUrl($id = 0, $type = '', $typeId = 0, $domain = false) {

        if ($typeId === true) {
            if ($type == 'item') {
                $typeId = M('goods')->where('id', $id)->value('cat_id');
            } else {
                $typeId = 0;
            }
        }
        $data = [
            'id' => $id,
            'typeId' => $typeId,
        ];
        return $this->Route::getRouteUrl($type, $data, $domain);
    }


    /*
     * 获取商品列表
     * @return array
     */
    public function getList($catId = 0, $order = '', $limit = 0, $page = 1, $flag = '', $filter = 0) {
        if (!$limit) return [];
        $where = [
            ['a.ifpub', '=', 'true',],
            ['a.deltime', '=', 0],
            ['a.pubtime', '<', time()],
        ];
        $whereRaw = [];

        if (is_numeric($catId) && $catId > 0) {
            $path = Dm('cats')->where('id', $catId)->value('id_path');
            $_where = ' b.id=' . $catId;
            $path and $_where .= " or(b.id_path like '{$path},%') ";

            $whereRaw[] = $_where;
        } elseif (is_string($catId) && strpos($catId, ',') !== false) {//多个id的情况处理
            $arrIds = explode(',', $catId);
            $arrIds = array_filter($arrIds, 'is_numeric');
            $whereOr = [];
            foreach ($arrIds as $catId) {
                $path = Dm('cats')->where('id', $catId)->value('id_path');
                $_where = ' b.id=' . $catId;
                $path and $_where = '(' . $_where . " or (b.id_path like '{$path},%') )";

                $whereOr[] = $_where;
            }
            $whereOr and $whereRaw[] = implode(' or ', $whereOr);
        } elseif (is_array($catId) && $catId) {
            $where[] = $catId;
        } elseif ($this->rrz && $this->env['page'] == 'tag') {
            $subQuery = M('tag_rel')->field('rel_id')
                ->where('tag_id', $this->rrz['id'])
                ->where('tag_type', 2)->fetchSql(true)->select();
            $whereRaw[] = 'a.id in(' . $subQuery . ')';
        }

        if ($flag) {
            $not = false;
            if (strpos($flag, 'not:') !== false) {
                $not = true;
                $flag = str_replace('not:', '', $flag);
            }
            $orAnd = ' or ';
            if (strpos($flag, ',')) {
                $arr = explode(',', $flag);
            } elseif (strpos($flag, '&')) {
                $arr = explode('&', $flag);
                $orAnd = ' and ';
            } else {
                $arr = [$flag];
            }

            $fs = ['h' => 'a.is_head', 'c' => 'a.is_recom', 'a' => 'a.is_special', 'n' => 'a.is_news'];
            $whereOr = [];
            foreach ($arr as $v) {
                if ($v == 'p') {
                    $whereOr[] = 'length(a.def_img)' . ($not ? '=' : '>') . '0';
                } elseif (isset($fs[$v])) {
                    $whereOr[] = $fs[$v] . ($not ? '<>' : '=') . '1';
                }
            }
            $whereOr and $whereRaw[] = implode($orAnd, $whereOr);
        }
        $model = M('goods')->alias('a')
            ->field('a.*,b.name as type_name,b.id as type_id,c.title as brand_name')
            ->join('goods_cat b', 'a.cat_id=b.id', 'left')
            ->join('goods_brand c', 'a.brand_id=b.id', 'left');
        $where and $model->where($where);

        foreach ($whereRaw as $raw) {
            $model->whereRaw($raw);
        }

        $filter and $model = $this->setFilter($model, $filter);
        $isPage = true;
        if (strpos($limit, ',') !== false) {
            $model = $this->setLimit($model, $limit);
            $isPage = false;
        }
        if ($isPage) {
            $count_model = clone $model;
            $count = $count_model->count();
            $model = $model->page($page, $limit);
        }
        $order = $this->getOrder($order);
        $list = $model->order($order)->select()->toArray();
        if (!$isPage) {
            $count = count($list);
            $limit = $count;
        }
        foreach ($list as $key => $item) {
            $item['url'] = $item['is_jump'] ? $item['jump_url'] : $this->getUrl($item['id'], 'item', $item['type_id']);
            $item['title'] = $item['name'];
            $item['uptime'] = $item['addtime'];
            $item['img'] = $item['def_img'] ?: $this->env['defImg'];
            $item['add_time'] = $item['addtime'];
            $item['type_url'] = $item['type_id'] ? $this->getUrl($item['type_id'], 'cat', $item['type_id']) : '';
            $item['price'] = price_format($item['price']);
            $item['del_price'] = price_format($item['del_price']);
            $list[$key] = $item;
        }
        return ['list' => $list, 'count' => $count, 'cur' => $page, 'limit' => $limit, 'max' => $count ? ceil($count / $limit) : 0,];
    }

    /*
     * 获取商品信息
     */
    public function getInfo($id = 0, $field = '*', $order = 'id desc') {
        $where = [];
        $isAttrs = false;
        if (!$id) return [];
        if (is_numeric($id)) {
            $where = [
                ['id', '=', $id],
            ];
        } elseif (is_array($id)) {
            $where = $id;
        } else {
            return [];
        }
        if ($field && $field != '*' && (is_string($field) && strpos(',' . $field . ',', ',attrs,') !== false)) {
            $isAttrs = true;
            $field = str_replace(',attrs,', ',', ',' . $field . ',');
            $field = trim($field, ',') or $field = 'id';
        }

        $row = M('goods')->where($where)->where('deltime',0)->field($field)->order($order)->find();
        if ($row) {
            $row['url'] = $this->getUrl($row['id'], 'item', $row['cat_id'] ?? true);
            if (isset($row['wap_content']) && request()->isMobile() && strlen(trim($row['wap_content'])) > 20) {
                $row['content'] = $row['wap_content'];
            }
            isset($row['sku_desc']) && $row['sku_desc'] and $row['sku_desc'] = @unserialize($row['sku_desc']);
            if (isset($row['sku_desc']) && $row['sku_desc']) {
                $skus = M('goods_skus')->where('goods_id', $id)->select()->toArray();
                $row['skus'] = array_map(function ($item) {
                    $item['sku_desc'] = @unserialize($item['sku_desc']);
                    $item['sku_key'] = '';
                    if ($item['sku_desc']) {
                        $ids = array_column($item['sku_desc'], 'id');
                        $item['sku_key'] = implode('_', $ids);
                    }
                    $item['price'] = price_format($item['price']);
                    return $item;
                }, $skus);
                if ($row['skus']) {
                    $row['skus'] = array_column($row['skus'], null, 'sku_key');
                }
            }
            if (isset($row['del_price']) && isset($row['price'])) {
                if (!$row['del_price'] || $row['del_price'] <= 0) {
                    $row['del_price'] = $row['price'];
                }
                $row['del_price'] = price_format($row['del_price']);
            }
            isset($row['price']) and $row['price'] = price_format($row['price']);
            isset($row['imgs']) and $row['imgs'] = array_filter(explode(',', $row['imgs']));
            isset($row['def_img']) and $row['img'] = $row['def_img'];
            $row['title'] = isset($row['name']) ? $row['name'] : '';
            isset($row['seo_title']) and $row['seo_title'] = $row['seo_title'] ?: $row['title'];
            isset($row['seo_keywords']) and $row['seo_keywords'] = $row['seo_keywords'] ?: $row['title'];
            isset($row['seo_description']) and $row['seo_description'] = $row['seo_description'] ?: $row['title'];

            $row['typeId'] = $row['cat_id'] ?? 0;
            if ($row['typeId']) {
                $row['cat_name'] = Dm('cats')->where('id', $row['cat_id'])->value('name');
                $row['typeInfo'] = $row['cat'] = $this->getCatInfo($row['cat_id']);
            } else {
                $row['cat_name'] = '';
                $row['typeInfo'] = $row['cat'] = [];
            }
            $row['brand_name'] = '';
            $row['brand'] = [];
            if ($row['brand_id'] ?? 0) {
                $row['brand_name'] = M('goods_brand')->where('id', $row['brand_id'])->value('title');
                $row['brand'] = M('goods_brand')->where('id', $row['brand_id'])->find();
            }

            if ($field == '*' || $isAttrs) {
                $attrs = M('goods_attr')->alias('a')->field('a.id,b.name,a.attr_value as value')
                    ->join('goods_attribute b', 'a.attr_id=b.id', 'left')
                    ->where('a.goods_id', $row['id'])
                    ->order('a.id asc')->select()->toArray();
                $row['attrs'] = $attrs ?: [];
            }

        }
        return $row ?: [];
    }

    /*
     * 增加产品浏览量
     */
    public function incInfoView($id = 0) {
        M('goods')->where('id', $id)->inc('view_count')->update();
    }

    /*
     * 获取商品分类列表
     */
    public function getCatList($id = 0, $limit = 0, $type = 'all', $curId = 0) {

        if ($type == 'sonself') {
            if (!is_numeric($id)) return [];
            $list = $this->getCatList($id, $limit, 'son', $curId);
            if ($list) return $list;
            $id = Dm('cats')->where('id', $id)->value('parent_id');
            return $this->getCatList($id, $limit, 'son', $curId);
        }

        $model = Dm('cats');
        if (strpos($id, ',') !== false) {
            $ids = array_filter(explode(',', $id), 'is_numeric');
            if (!$ids) return [];
            $id = $ids;
        }

        if ($type == 'top' || $type == 'son' || $type == 'self') {
            $type == 'top' and $id = 0;
            if (!is_array($id) && !is_numeric($id)) return [];
            $key = $type == 'self' ? 'id' : 'parent_id';
            $model->where($key, is_array($id) ? 'in' : '=', $id);
        }
        if ($type == 'top' || $type == 'son') {
            $model->where('ifpub', 'true');
        }
        $order = 'path asc,id asc';
        if ($type == 'self' && is_array($id)) {
            $order = 'field(id,' . implode(',', $id) . ')';
        }
        $model->order($order);
        $data = $this->setLimit($model, $limit)->select();
        $list = [];
        foreach ($data as $item) {
            $ids = array_filter(explode(',', $item['id_path']));
            $item = array_merge($item, [
                'title' => $item['name'],
                'url' => $this->getUrl($item['id'], 'cat', $item['id']),
                'class' => $item['id'] == $curId ? 'on' : '',
                'brief' => $item['seo_description'],
                'keywords' => $item['seo_keywords'],
                'type' => 'cat',
            ]);
            if ($type == 'all') {
                $k = $item['depth'] - 1;
                if ($k == 0) {
                    $list[$item['id']] = $item;
                } elseif ($k > 0) {
                    $str = '';
                    for ($i = 0; $i < $k; $i++) {
                        $str .= "[\$ids[" . $i . "]]['children']";
                    }
                    eval('$list' . $str . '[$item[\'id\']] = $item;');
                }
            } else {
                $list[] = $item;
            }
        }
        return array_values($list);
    }

    /*
     * 获取商品分类信息
     */
    public function getCatInfo($id = 0) {
        if (in_array($id, ['all', 'cats'])) {
            $row = M('site_menus')->field('id,title,en_title,id_path,dir_name,url')
                ->where('url', '=', '/cats.html')->order('depth desc')->find();
            if (!$row) {
                $row = [
                    'title' => '产品中心',
                    'en_title' => 'Products',
                    'dir_name' => 'cats',
                    'url' => getRrzUrl('/cats'),
                ];
            } else {
                $lib = new \app\home\lib\Menus();
                $row['url'] = $lib->getUrl($row['id']);
            }
            $row = array_merge($row, [
                'id' => 0,
                'name' => $row['title'],
                'seo_title' => $row['title'],
                'seo_keywords' => $row['title'],
                'brief' => $row['title'],
                'seo_description' => $row['title'],
                'type' => 'cat',
                'tmpl_path' => 'all',
                'ifpub' => 'true',
            ]);
            return $row;
        }
        if (!is_numeric($id) || !$id) return [];

        $row = Dm('cats')->where('id', $id)->find();
        if ($row) {
            $row['title'] = $row['name'];
            $row['url'] = $this->getUrl($row['id'], 'cat', $row['id']);
            $row['seo_title'] = $row['seo_title'] ?: $row['name'];
            $row['seo_keywords'] = $row['seo_keywords'] ?: $row['name'];
            $row['brief'] = $row['seo_description'];
            $row['seo_description'] = $row['seo_description'] ?: $row['name'];
            $ids = explode(',', $row['id_path']);
            $row['top'] = $ids[0] ? Dm('cats')->where('id', $ids[0])->find() : [];
            if ($row['top']) {
                $row['top']['title'] = $row['top']['name'];
                $row['top']['url'] = $this->getUrl($row['top']['id'], 'cat', $row['top']['id']);
            }
            $row['type'] = 'cat';
        }
        return $row ?: [];
    }

    /**
     * 获取 品牌 列表
     */
    public function getBrandList($limit = 0, $page = 1, $rrz = [], $env = []) {
        $model = M('goods_brand')->order('sort asc,id desc');
        $isPage = true;
        if (strpos($limit, ',') !== false) {
            $model = $this->setLimit($model, $limit);
            $isPage = false;
        }
        if ($isPage) {
            $count_model = clone $model;
            $count = $count_model->count();
            $model = $model->page($page, $limit);
        }
        $list = $model->select()->toArray();
        if (!$isPage) {
            $count = count($list);
            $limit = $count;
        }
        foreach ($list as $key => $item) {
            $arg = $env["page"] == 'cat' ? ($_GET ?: []) : [];
            $arg['filter']['brand_id'] = $item['id'];
            $url = '?' . http_build_query($arg);
            if ($env["page"] != 'cat') {
                $url = getRrzUrl('/cats') . $url;
            }
            $item['img'] = $item['logo'];
            $item['url'] = $url;
            $list[$key] = $item;
        }
        return ['list' => $list, 'count' => $count, 'cur' => $page, 'limit' => $limit, 'max' => $count ? ceil($count / $limit) : 0,];
    }


    public function getCrumbsList($catId = 0) {
        $ids = Dm('cats')->where('id', $catId)->value('id_path');
        $list = Dm('cats')->where('id', 'in', $ids)->order('path asc,id asc')->select();
        foreach ($list as &$item) {
            $item['title'] = $item['name'];
            $item['url'] = $this->getUrl($item['id'], 'cat', $item['id']);
        }
        $all = M('site_menus')->field('id,title,dir_name,id_path,url')
            ->where('url', '=', '/cats.html')->order('depth desc')->find();
        if ($all) {
            $lib = new \app\home\lib\Menus();
            $all['url'] = $lib->getUrl($all['id']);
            array_unshift($list, ['title' => $all['title'], 'url' => $all['url'],]);
        }
        return $list;
    }

}