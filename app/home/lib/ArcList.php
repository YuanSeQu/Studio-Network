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


class ArcList extends Base
{

    public function getList($typeId = 0, $type = '', $order = '', $limit = 0, $page = 1, $flag = '', $filter = 0) {
        if (!$type && $this->env['page'] == 'tag' && $this->rrz) {
            $type = $this->rrz['type'] == 1 ? 'node' : 'cat';
        }
        if (!$type || !in_array($type, ['node', 'cat', 'article', 'item'])) {
            return false;
        }
        $lib = in_array($type, ['node', 'article']) ? new \app\home\lib\Articles($this->rrz, $this->env) : new \app\home\lib\Goods($this->rrz, $this->env);

        $data = $lib->getList($typeId, $order, $limit, $page, $flag, $filter);
        $q = I('q', '', 'trim');
        if ($q && $data['list']) {
            foreach ($data['list'] as &$item) {
                $item['red_title'] = preg_replace_callback('/' . $q . '/i', function ($matches) {
                    return '<font color="red">' . $matches[0] . '</font>';
                }, $item['title']);
                $item['red_title'] = $item['red_title'] ?: $item['title'];
            }
        }
        return $data;
    }

    /*
    * 搜索列表
    */
    function search($typeId = 0, $type = 'article', $limit = 5, $page = 1) {
        if (!in_array($type, ['article', 'item'])) return [];
        $keywords = I('q', '', 'trim');
        if ($keywords) {
            $ip = getClientIP();
            $count = M('search_keywords')->where([
                'keywords' => $keywords,
                'ip' => $ip,
            ])->count();
            if ($count) {
                M('search_keywords')->where('keywords', $keywords)->inc('hot')->update();
            } else {
                M('search_keywords')->insert([
                    'keywords' => $keywords,
                    'ip' => $ip,
                    'add_time' => time(),
                    'type' => $type,
                ]);
                M('search_keywords')->where('keywords', $keywords)->inc('hot')->update();
            }
        }
        return $this->getList($typeId, $type, '', $limit, $page, '', true);
    }
}