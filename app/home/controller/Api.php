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


class Api
{
    /**
     * 获取文章或产品的点击量
     */
    function incInfoView() {
        $id = I('get.id', 0);
        $type = I('get.t', '');
        if (!is_numeric($id) || !$id || !in_array($type, ['item', 'article', 'tag'])) {
            exit('document.getElementById(\'rrzJsIdViewCount\').innerHTML=0;');
        }
        $count = 0;
        if ($type == 'item') {
            $lib = new \app\home\lib\Goods;
            $lib->incInfoView($id);
            $count = M('goods', false)->where('id', $id)->value('view_count', 0);
        } elseif ($type == 'article') {
            $lib = new \app\home\lib\Articles;
            $lib->incInfoView($id);
            $count = M('articles', false)->where('id', $id)->value('view_count', 0);
        } elseif ($type == 'tag') {
            $lib = new \app\home\lib\Tags();
            $lib->incInfoView($id);
        }
        exit('document.getElementById(\'rrzJsIdViewCount\').innerHTML=' . $count . ';');
    }

    /**
     * 百度地图部分不支持https走这里
     */
    function baiduMap() {
        exit;
//        $url = I('url', '');
//        if (!$url) exit;
//        $ref = $_SERVER['HTTP_REFERER'];
//        if (!preg_match('/show.html|map.html/i', $ref)) exit;
//        $res = '';
//        try {
//            $res = get_curl($url);
//        } catch (\Exception $e) {
//        }
//        exit($res);
    }

    /**
     * 用于检测站点是否可以访问
     */
    function state() {
        exit('passing');
    }
}