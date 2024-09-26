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


use \app\facade\route;

class Paginator
{
    protected $list = [];
    protected $count = 0;
    protected $cur = 1;
    protected $limit = 0;
    protected $max = 1;
    protected $style = 'cur';
    protected $tag = '';

    protected $hasMore;

    protected $lang = 'zh-cn';

    protected $rrz;
    protected $env;

    function __construct($data = [], $style = 'cur', $tag = '', $lang = 'zh-cn', $rrz = [], $env = []) {
        isset($data['list']) and $this->list = $data['list'];
        isset($data['count']) and $this->count = $data['count'];
        isset($data['cur']) and $this->cur = $data['cur'];
        isset($data['limit']) and $this->limit = $data['limit'];
        isset($data['max']) and $this->max = $data['max'];
        $this->tag = $tag;

        $this->hasMore = $this->max > $this->cur;
        $this->style = $style;
        $this->lang = $lang;

        $this->rrz = $rrz;
        $this->env = $env;
    }

    protected function getLinks($size = 2) {

        $block = [
            'first' => null,
            'slider' => null,
            'last' => null
        ];

        $side = $size;
        $window = $side * 2;

        if ($this->max < $window + 2) {
            $block['first'] = $this->getUrlRange(1, $this->max);
        } elseif ($this->cur < ($side + 1)) {
            $block['first'] = $this->getUrlRange(1, $window + 1);
        } elseif ($this->cur > ($this->max - $side)) {
            $block['last'] = $this->getUrlRange($this->max - $window, $this->max);
        } else {
            $block['slider'] = $this->getUrlRange($this->cur - $side, $this->cur + $side);
        }

        $html = '';

        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }

        if (is_array($block['slider'])) {
            $html .= $this->getUrlLinks($block['slider']);
        }

        if (is_array($block['last'])) {
            $html .= $this->getUrlLinks($block['last']);
        }

        return $html;
    }

    function getText($text = '') {
        return __($text, $this->lang);
    }

    function render($item = '', $size = 2) {
        if ($this->hasPages()) {
            $itemArr = explode(',', $item);
            foreach ($itemArr as $key => $val) {
                $itemArr[$key] = trim($val);
            }
            $pageArr = [];
            if (in_array('index', $itemArr)) {
                $html = $this->getLinkWrapper($this->cur > 1 ? $this->url(1) : '', $this->getText('首页'), 'index');
                array_push($pageArr, $html);
            }
            if (in_array('pre', $itemArr) && $this->cur > 1) {
                $html = $this->getLinkWrapper($this->cur > 1 ? $this->url($this->cur - 1) : '', $this->getText('上一页'), 'pre');
                array_push($pageArr, $html);
            }
            if (in_array('pageno', $itemArr)) {
                array_push($pageArr, $this->getLinks($size));
            }
            if (in_array('next', $itemArr) && $this->hasMore) {
                $html = $this->getLinkWrapper($this->hasMore ? $this->url($this->cur + 1) : '', $this->getText('下一页'), 'next');
                array_push($pageArr, $html);
            }
            if (in_array('end', $itemArr)) {
                $html = $this->getLinkWrapper($this->hasMore ? $this->url($this->max) : '', $this->getText('末页'), 'end');
                array_push($pageArr, $html);
            }
            if (in_array('info', $itemArr)) {
                $html = $this->getLinkWrapper('', $this->getTotalResult(), 'info');
                array_push($pageArr, $html);
            }
            $pageStr = implode('', $pageArr);

            return $pageStr;
        }
        return $this->getTotalResult();
    }

    protected function getTotalResult() {
        $format = $this->getText('共') . ' <strong>%s</strong> ' . $this->getText('页') . ' <strong>%s</strong> ' . $this->getText('条');
        return sprintf($format, $this->max, $this->count);
    }

    protected function url($page) {
        $arg = $_GET;
        $type = $this->env['page'] ?? 'index';
        $url = '';
        if (in_array($type, ['node', 'cat'])) {
            $data = [
                'id' => $this->rrz['id'] ?? 0,
                'typeId' => $this->rrz['id'] ?? 0,
                'isList' => $page > 1 ? true : false,
                'p' => $page,
            ];
            $url = route::getRouteUrl($type, $data);
        } elseif ($type == 'search') {
            $arg['p'] = $page;
            $url = getRrzUrl('/search');
        } elseif ($type == 'tag') {
            $data = [
                'id' => $this->rrz['id'] ?? 0,
                'p' => $page,
            ];
            $url = route::getRouteUrl($type, $data);
        } else {
            $arg = [
                'p' => $page
            ];
        }
        //判断是否有其他参数
        if (!empty($arg)) {
            $url .= '?' . http_build_query($arg);
        }
        return $url;
    }

    protected function getLinkWrapper($url, $text, $style = '') {
        $str = sprintf(' %s>%s</a>', ($text != $this->cur && $url) ? 'href="' . $url . '"' : '', $text);

        $class = $this->cur == $text ? ' class="' . $this->style . '" ' : ($style ? ' class="' . $style . '" ' : '');
        if ($this->tag) {
            return implode('', [
                '<' . $this->tag . $class . ' style="display:inline-block;list-style-type:none;">',
                '<a ' . $str,
                '</' . $this->tag . '>',
            ]);
        }
        return implode('', [
            '<a ', $class,
            $str,
        ]);
    }

    protected function getUrlRange($start, $end) {
        $urls = [];
        for ($page = $start; $page <= $end; $page++) {
            $urls[$page] = $this->url($page);
        }
        return $urls;
    }

    protected function getUrlLinks(array $urls) {
        $html = '';
        foreach ($urls as $page => $url) {
            $html .= $this->getLinkWrapper($url, $page);
        }
        return $html;
    }

    protected function hasPages() {
        return !(1 == $this->cur && !$this->hasMore);
    }

}