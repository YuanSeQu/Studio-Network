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


class Articles extends Base
{

    /*
     * 生成前台url
     */
    public function getUrl($id = 0, $type = '', $typeId = 0, $domain = false) {
        if ($typeId === true) {
            if ($type == 'article') {
                $typeId = M('articles')->where('id', $id)->value('node_id');
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
     * 获取文章列表
     */
    public function getList($nodeId = 0, $order = '', $limit = 0, $page = 1, $flag = '', $filter = 0) {
        if (!$limit) return [];
        $where = [
            ['a.ifpub', '=', 'true',],
            ['a.tmpl_path', '=', ''],
            ['a.deltime', '=', 0],
            ['a.pubtime', '<', time()],
        ];
        $whereRaw = [];

        if (is_numeric($nodeId) && $nodeId > 0) {
            $path = Dm('nodes')->where('id', $nodeId)->value('id_path');
            $_where = ' b.id=' . $nodeId;
            $path and $_where .= " or(b.id_path like '{$path},%') ";

            $whereRaw[] = $_where;
        } elseif (is_string($nodeId) && strpos($nodeId, ',') !== false) {//多个id的情况处理
            $arrIds = explode(',', $nodeId);
            $arrIds = array_filter($arrIds, 'is_numeric');
            $whereOr = [];
            foreach ($arrIds as $nodeId) {
                $path = Dm('nodes')->where('id', $nodeId)->value('id_path');
                $_where = ' b.id=' . $nodeId;
                $path and $_where = '(' . $_where . " or (b.id_path like '{$path},%') )";

                $whereOr[] = $_where;
            }
            $whereOr and $whereRaw[] = implode(' or ', $whereOr);
        } elseif (is_array($nodeId) && $nodeId) {
            $where[] = $nodeId;
        } elseif ($this->rrz && $this->env['page'] == 'tag') {
            $subQuery = M('tag_rel')->field('rel_id')
                ->where('tag_id', $this->rrz['id'])
                ->where('tag_type', 1)->fetchSql(true)->select();
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

            $fs = ['h' => 'a.is_head', 'c' => 'a.is_recom', 'a' => 'a.is_special'];
            $whereOr = [];
            foreach ($arr as $v) {
                if ($v == 'p') {
                    $whereOr[] = 'length(a.img)' . ($not ? '=' : '>') . '0';
                } elseif (isset($fs[$v])) {
                    $whereOr[] = $fs[$v] . ($not ? '<>' : '=') . '1';
                }
            }
            $whereOr and $whereRaw[] = implode($orAnd, $whereOr);
        }
        $model = M('articles')->alias('a')
            ->field('a.*,b.name as type_name,b.id as type_id')
            ->join('article_nodes b', 'a.node_id=b.id', 'left');
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
            $item['url'] = $item['is_jump'] ? $item['jump_url'] : $this->getUrl($item['id'], 'article', $item['type_id']);
            if ($item['wap_content'] && request()->isMobile() && strlen(trim($item['wap_content'])) > 20) {
                $item['content'] = $item['wap_content'];
            }
            $item['brief'] = $item['seo_description'];
            $item['type_url'] = $item['type_id'] ? $this->getUrl($item['type_id'], 'node', $item['type_id']) : '';
            $item['img'] = $item['img'] ?: $this->env['defImg'];
            $list[$key] = $item;
        }
        return ['list' => $list, 'count' => $count, 'cur' => $page, 'limit' => $limit, 'max' => $count ? ceil($count / $limit) : 0,];
    }

    /**
     * 获取文章信息
     */
    public function getInfo($id = 0, $field = '*', $order = 'id desc') {
        $where = [];
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
        $row = M('articles')->where($where)->where('deltime',0)->field($field)->order($order)->find();
        if ($row) {
            $row['url'] = $this->getUrl($row['id'], 'article', $row['node_id'] ?? true);
            if (isset($row['wap_content']) && request()->isMobile() && strlen(trim($row['wap_content'])) > 20) {
                $row['content'] = $row['wap_content'];
            }
            isset($row['seo_title']) and $row['seo_title'] = $row['seo_title'] ?: $row['title'];
            isset($row['seo_keywords']) and $row['seo_keywords'] = $row['seo_keywords'] ?: $row['title'];
            isset($row['seo_description']) and $row['brief'] = $row['seo_description'];
            isset($row['seo_description']) and $row['seo_description'] = $row['seo_description'] ?: $row['title'];
            if (isset($row['author'])) {
                $row['author'] or $row['author'] = sysConfig('webinfo.name');
            }
            $row['typeId'] = $row['node_id'] ?? 0;
            if ($row['typeId']) {
                $row['node_name'] = Dm('nodes')->where('id', $row['node_id'])->value('name');
                $row['typeInfo'] = $row['node'] = $this->getNodeInfo($row['node_id']);
            } else {
                $row['node_name'] = '';
                $row['typeInfo'] = $row['node'] = [];
            }
        }
        return $row ?: [];
    }

    /*
     * 增加文章浏览量
     */
    public function incInfoView($id = 0) {
        M('articles')->where('id', $id)->inc('view_count')->update();
    }

    /*
     * 获取商品分类列表
     */
    public function getNodeList($id = 0, $limit = 0, $type = 'all', $curId = 0) {

        if ($type == 'sonself') {
            if (!is_numeric($id)) return [];
            $list = $this->getNodeList($id, $limit, 'son', $curId);
            if ($list) return $list;
            $id = Dm('nodes')->where('id', $id)->value('parent_id');
            return $this->getNodeList($id, $limit, 'son', $curId);
        }

        $model = Dm('nodes');
        if (strpos($id, ',') !== false) {
            $ids = array_filter(explode(',', $id), 'is_numeric');
            if (!$ids) return [];
            $id = $ids;
        }
        if ($type == 'top' || $type == 'son' || $type == 'self') {
            $type == 'top' and $id = 0;
            if (!is_array($id) && !is_numeric($id) || ($type == 'son' && !$id)) return [];
            $key = $type == 'self' ? 'id' : 'parent_id';
            $model->where($key, is_array($id) ? 'in' : '=', $id);
        }
        if ($type == 'top' || $type == 'son') {
            $model->where('ifpub', 'true');
        }

        $order = 'path asc,id asc';
        if ($type == 'self' && is_array($id) && $id) {
            $order = 'field(id,' . implode(',', $id) . ')';
        }
        $model->order($order);
        $data = $this->setLimit($model, $limit)->select();
        $list = [];
        foreach ($data as $item) {
            $ids = array_filter(explode(',', $item['id_path']));
            if (isset($item['wap_content']) && request()->isMobile() && strlen(trim($item['wap_content'])) > 20) {
                $item['content'] = $item['wap_content'];
            }
            $item = array_merge($item, [
                'title' => $item['name'],
                'url' => $this->getUrl($item['id'], 'node', $item['id']),
                'class' => $item['id'] == $curId ? 'on' : '',
                'brief' => $item['seo_description'],
                'keywords' => $item['seo_keywords'],
                'type' => 'node',
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
     * 获取分类信息
     */
    public function getNodeInfo($id = 0) {
        if (!is_numeric($id) || !$id) return [];

        $row = Dm('nodes')->where('id', $id)->find();
        if ($row) {
            $row['title'] = $row['name'];
            $row['url'] = $this->getUrl($row['id'], 'node', $row['id']);
            $row['seo_title'] = $row['seo_title'] ?: $row['name'];
            $row['seo_keywords'] = $row['seo_keywords'] ?: $row['name'];
            $row['brief'] = $row['seo_description'];
            $row['seo_description'] = $row['seo_description'] ?: $row['name'];
            if (isset($row['wap_content']) && request()->isMobile() && strlen(trim($row['wap_content'])) > 20) {
                $row['content'] = $row['wap_content'];
            }
            $ids = explode(',', $row['id_path']);
            $row['top'] = $ids[0] ? Dm('nodes')->where('id', $ids[0])->find() : [];
            if ($row['top']) {
                $row['top']['title'] = $row['top']['name'];
                $row['top']['url'] = $this->getUrl($row['top']['id'], 'node', $row['top']['id']);
            }
            $row['type'] = 'node';
        }
        return $row ?: [];
    }

    public function getCrumbsList($nodeId = 0) {
        $ids = Dm('nodes')->where('id', $nodeId)->value('id_path');
        $list = Dm('nodes')->where('id', 'in', $ids)->order('path asc,id asc')->select();
        foreach ($list as &$item) {
            $item['title'] = $item['name'];
            $item['url'] = $this->getUrl($item['id'], 'node', $item['id']);
        }
        return $list;
    }
}