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


class Tags extends Base
{

    /*
     * 生成url
     */
    public function getUrl($id = 0, $domain = false) {
        $data = [
            'id' => $id,
        ];
        return $this->Route::getRouteUrl('tag', $data, $domain);
    }


    /*
     * 获取标签
     */
    public function getList($relId = 0, $page = 1, $typeId = 0, $limit = 30, $order = 'new', $getall = 0) {
        $getall = intval($getall);
        $relId = (is_numeric($relId) && $relId) ? $relId : 0;
        if (!$relId && in_array($this->env['page'], ['article', 'item']) && $this->rrz) {
            $relId = $this->rrz['id'];
        }
        if (in_array($page, ['article', 'item'])) {
            $page = $page == 'article' ? 1 : 2;
        }
        if (!$page) {
            if (in_array($this->env['page'], ['node', 'article'])) {
                $page = 1;
            } elseif (in_array($this->env['page'], ['cat', 'item'])) {
                $page = 2;
            } else {
                return [];
            }
        }

        if ($getall == 0 && $relId > 0) {
            $where = [
                'a.rel_id' => $relId,
                'a.tag_type' => $page,
            ];
            $model = M('tag_rel')->alias('a')
                ->leftJoin('tag b','a.tag_id=b.id')
                ->where($where)->field('a.tag_id as id,a.tag_title as title,b.total,b.view_count,b.weekcc,b.monthcc');
            $model = $this->setLimit($model, $limit);
            $list = $model->select()->toArray();
        } else {

            if (empty($typeId)) {
                if (in_array($this->env['page'], ['node', 'cat']) && $this->rrz) {
                    $typeId = $this->rrz['id'];
                }
            }

            $where = [
                ['type', '=', $page,]
            ];
            $whereRaw = '';
            if ($typeId) {
                $ids = $this->getTypeids($typeId, $page);
                $subQuery = M('tag_rel')->where('type_id', 'in', $ids)
                    ->where('tag_type', $page)->field('DISTINCT tag_id')->fetchSql(true)->select();
                $whereRaw = 'id in(' . $subQuery . ')';
            }

            if($order == 'rand') $orderby = '[rand]';
            else if($order == 'week') $orderby=' weekcc DESC ';
            else if($order == 'month') $orderby=' monthcc DESC ';
            else if($order == 'hot') $orderby='view_count DESC ';
            else if($order == 'total') $orderby='total DESC ';
            else if($order == 'id') $orderby='id ASC ';
            else $orderby = 'id desc';

            $model = M('tag')->where($where)->field('id,title,total,view_count,weekcc,monthcc');

            $whereRaw and $model->whereRaw($whereRaw);

            $model = $this->setLimit($model, $limit);
            $list = $model->order($orderby)->select()->toArray();
        }
        foreach ($list as $key => $val) {
            $val['url'] = $this->getUrl($val['id']);
            $list[$key] = $val;
        }

        return $list;
    }

    /**
     * 获取分类id及子id
     * @param $typeid
     * @param string $page
     * @return array
     */
    public function getTypeids($typeid, $page = ''){
        $typeidArr = $typeid;
        if (!is_array($typeidArr)) {
            $typeidArr = explode(',', $typeid);
        }
        $typeids = [];
        foreach ($typeidArr as $id){
            if (!is_numeric($id) || !$id) continue;
            $tb = $page == 1 ? 'nodes' : 'cats';
            $path = Dm($tb)->where('id', $id)->value('id_path');
            $ids = Dm($tb)->where('id_path', 'like%', $path . ',')->column('id');
            $typeids = array_merge($typeids, [$id], $ids);
        }
        return $typeids;
    }

    /*
     * 更新标签访问次数
     */
    public function incInfoView($id = 0) {
        M('tag')->where('id', $id)->inc('view_count')->inc('weekcc')->inc('monthcc')->update();
        $row = M('tag')->where('id', $id)->find();
        if (!$row) return;
        $time = time();
        $oneday = 24 * 3600;
        //周统计
        if (ceil(($time - $row['weekup']) / $oneday) > 7) {
            M('tag')->where('id', $id)->save(['weekcc' => 0, 'weekup' => $time]);
        }
        //月统计
        if (ceil(($time - $row['monthup']) / $oneday) > 30) {
            M('tag')->where('id', $id)->save(['monthcc' => 0, 'monthup' => $time]);
        }
    }

    /*
     * 获取单个标签信息
     */
    public function getInfo($id = 0) {

        $row = M('tag')->where('id', $id)->find();

        $row['name'] = $row['title'];
        $row['seo_title'] = $row['seo_title'] ?: $row['title'];
        $row['seo_keywords'] = $row['seo_keywords'] ?: $row['title'];
        $row['brief'] = $row['seo_description'];
        $row['seo_description'] = $row['seo_description'] ?: $row['title'];

        $row['en_title'] = '';
        $row['url'] = $this->getUrl($id);

        return $row ?: [];
    }

}