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


class Env extends Base
{

    function getEnv($name = '') {
        if (!$name) return '';
        if ($name == 'webinfo.thirdcode') {
            $name = request()->isMobile() ? 'webinfo.thirdcode_wap' : 'webinfo.thirdcode_pc';
        } elseif ($name == 'rrz.cmsurl') {
            return getRootUrl();
        } elseif ($name == 'rrz.catsurl') {
            return getRrzUrl('/cats');
        } elseif (strpos($name, 'rrz.lang.url') === 0) {
            $lang = str_replace(['rrz.lang.url.', 'rrz.lang.url'], '', $name);
            $lang = trim($lang);
            return $lang ? U('/' . $lang, [], false, false) : getRrzUrl('/');
        } elseif ($name == 'webinfo.tel') {
            $name = 'webinfo.telephone';
        } elseif ($name == 'webinfo.qr') {
            $name = 'webinfo.qrcode';
        }
        $tmp = $this->arrJoinStr(['c3lzQ2', '9uZmln']);
        $value = $tmp($name);
        if ($name == $this->arrJoinStr(['d2Vic2l', '0ZS5jb3B', '5cmlnaHQ='])) {
            $assignValue = $tmp($this->arrJoinStr(['d2Vic2l0ZS5pc19', 'hdXRob3JpemF0aW9u']));
            $value .= empty($assignValue) ? '' : $this->arrJoinStr(['PGEgaHJlZj0iaH', 'R0cDovL3d3dy5ycnpjbXMuY29tLy', 'IgdGFyZ2V0PSJfYmxhbmsiPiBQb', '3dlcmVkIGJ5IFJSWkNNUzwvYT4=']);
        } elseif ($name == 'website.recordnum') {
            $value = '<a href="http://beian.miit.gov.cn/" target="_blank">' . $value . '</a>';
        }
        return $value;
    }

    function getPre($rrz = [], $page = '', $field = '') {
        if (!$rrz || !in_array($page, ['article', 'item'])) return [];
        $field = $field ? array_values(array_filter(explode(',', $field))) : [];
        if ($page == 'article') {
            $lib = new \app\home\lib\Articles();
            $where = [
                ['node_id', '=', $rrz['node_id'],],
                ['id', '<', $rrz['id'],],
                ['ifpub', '=', 'true'],
            ];
            $field = array_unique(array_merge(['id', 'title', 'img'], $field ?: []));
            $field = implode(',', $field);
            return $lib->getInfo($where, $field, 'id desc');
        }
        $lib = new \app\home\lib\Goods();
        $where = [
            ['cat_id', '=', $rrz['cat_id'],],
            ['id', '<', $rrz['id'],],
        ];
        $field = array_unique(array_merge(['id', 'name', 'def_img'], $field ?: []));
        $field = implode(',', $field);
        return $lib->getInfo($where, $field, 'id desc');
    }

    function getNext($rrz = [], $page = '', $field = '') {
        if (!$rrz || !in_array($page, ['article', 'item'])) return [];
        $field = $field ? array_values(array_filter(explode(',', $field))) : [];
        if ($page == 'article') {
            $lib = new \app\home\lib\Articles();
            $where = [
                ['node_id', '=', $rrz['node_id'],],
                ['id', '>', $rrz['id'],],
                ['ifpub', '=', 'true'],
            ];
            $field = array_unique(array_merge(['id', 'title', 'img'], $field ?: []));
            $field = implode(',', $field);
            return $lib->getInfo($where, $field, 'id asc');
        }
        $lib = new \app\home\lib\Goods();
        $where = [
            ['cat_id', '=', $rrz['cat_id'],],
            ['id', '>', $rrz['id'],],
        ];
        $field = array_unique(array_merge(['id', 'name', 'def_img'], $field ?: []));
        $field = implode(',', $field);
        return $lib->getInfo($where, $field, 'id asc');
    }
}