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

namespace app\admin\lib;


class Template
{
    protected $tags = [
        'ads' => [
            'preg' => '/\<\!\-\-ads\_start\-\-\>(.*)\<\!\-\-ads\_end\-\-\>/isU',
        ],
        'links' => [
            'preg' => '/\<\!\-\-links\_start\-\-\>(.*)\<\!\-\-links\_end\-\-\>/isU',
        ],
        'img' => [
            'preg' => '/\<\!\-\-img(.*)\<\!\-\-img\_end\-\-\>/isU',
        ],
        'texts' => [
            'preg' => '/\<\!\-\-texts(.*)\<\!\-\-texts\_end\-\-\>/isU',
        ],
    ];
    protected $pattern = '/\<\!\-\-(ads|links|img|texts).*\<\!\-\-(ads|links|img|texts)\_end\-\-\>/isU';

    protected $temp_path = '';
    protected $temp_root = '';
    protected $img = '';

    function __construct($dir = '') {
        $type = strpos($dir . '/', '/pc/') !== false ? 'pc' : 'mobile';
        $this->img = '/template/' . $type . '/images';

        $view = include app()->getRootPath() . '/app/home/config/view.php';
        $this->temp_root = $view['view_path'];
        $this->temp_path = $view['view_path'] . $type . DIRECTORY_SEPARATOR;
    }

    function getTmplList() {
        $path = $this->temp_root . 'config.json';
        if (!is_file($path)) return [];
        $config = file_get_contents($path);
        $config = json_decode($config, true);
        if (!$config || !$config['fileList']) return [];
        return $config['fileList'];
    }

    function getTmplPath($type = '') {
        $list = [
            '' => '默认模板',
        ];
        if (!$type) return $list;
        $arr = glob($this->temp_path . $type . '_*.html');
        if (!$arr) return $list;
        foreach ($arr as $file) {
            $parts = pathinfo($file);
            $key = str_replace([$type . '_', '.html'], '', $parts['basename']);
            $list[$key] = $parts['basename'];
        }
        return $list;
    }

    function extract($content, $rep_default = true) {
        $list = [];
        preg_match_all($this->pattern, $content, $mAll);
        if (!$mAll) return $list;

        foreach ($mAll[0] as $k => $item) {
            $tag = strtolower($mAll[1][$k]);
            $method = 'tag' . ucwords($tag);
            if (!method_exists($this, $method) || !isset($this->tags[$tag])) continue;
            $ret = $this->{$method}($item, $rep_default);
            $ret and $list[] = [
                'type' => $tag,
                'value' => $ret
            ];
        }
        return $list;
    }

    /**
     * 替换模版内容
     * @param $content 模版文件内容
     * @param array $data 替换的数据
     * @return mixed
     */
    function replace($content, $data = []) {
        $old = $this->extract($content, false);
        foreach ($old as $k => $val) {
            $tag = $val['type'];
            $method = 'replace' . ucwords($tag);

            if (!method_exists($this, $method) || !isset($this->tags[$tag]) || !isset($data[$tag][$k])) continue;

            $content = $this->{$method}($data[$tag][$k], $val['value'], $content);
        }
        return $content;
    }

    private function checkImgUrl($url, $reverse = false) {
        if ($reverse) {
            return str_replace($this->img, '%IMAGES%', $url);
        }
        return str_replace('%IMAGES%', $this->img, $url);
    }

    private function tagAds($ads, $rep_default = true) {
        $ad = [
            'html' => $ads,
        ];
        preg_match_all('/\<\!\-\-item(.*)\<\!\-\-item\_end\-\-\>/isU', $ads, $m);
        foreach ($m[0] as $item) {
            preg_match('/\<\!\-\-item\((.*)\)\-\-\>/', $item, $m2);
            $arry = explode('※', $m2[1]);
            $title = $arry[3] ?? '';
            if ($rep_default && $title == '<span hidden>title</span>') {
                $title = '';
            }
            $ad['items'][] = [
                'img' => $this->checkImgUrl($arry[0] ?? ''),
                'img_value' => $arry[0] ?? '',
                'url' => $arry[1] ?? '',
                'target' => $arry[2] ?? '_blank',
                'title' => $title,
                'html' => $item,
            ];
        }
        return isset($ad['items']) ? $ad : false;
    }

    private function replaceAds($ads, $old, $content) {
        $ad_html = $old['html'];
        $item1 = $old['items'][0];
        $items = [];
        $ii = 0;
        foreach ($ads as $item) {
            $img = $this->checkImgUrl($item['img'], true);
            $url = trim($item['url']) ? $item['url'] : '#' . $ii;
            $title = trim($item['title']) ? $item['title'] : '<span hidden>title</span>';

            $search = [
                $item1['img_value'], $item1['url'], $item1['target'], $item1['title'],
            ];
            $replace = [
                $img, $url, $item['target'], $title,
            ];

            $items[] = str_replace($search, $replace, $item1['html']);
            $ii++;
        }
        $items_html = implode("\n", $items);
        $new_html = preg_replace('/\<\!\-\-item(.*)\<\!\-\-item\_end\-\-\>/isU', '{!----}', $ad_html, -1, $count);
        $str = '\{\!\-\-\-\-\}';
        for ($i = 1; $i < $count; $i++) {
            $str .= '\s+\{\!\-\-\-\-\}';
        }
        $new_html = preg_replace('/' . $str . '/i', $items_html, $new_html);
        return str_replace($ad_html, $new_html, $content);
    }

    private function tagLinks($links, $rep_default = true) {
        $ad = [
            'html' => $links,
        ];
        preg_match_all('/\<\!\-\-item(.*)\<\!\-\-item\_end\-\-\>/isU', $links, $m);
        foreach ($m[0] as $item) {
            preg_match('/\<\!\-\-item\((.*)\)\-\-\>/', $item, $m2);
            $arry = explode('※', $m2[1]);
            $title = $arry[0] ?? '';
            if ($rep_default && preg_match('/\<span hidden\>\#(\d+)\<\/span\>/', $title)) {
                $title = '';
            }
            $ad['items'][] = [
                'title' => $title,
                'url' => $arry[1] ?? '',
                'target' => $arry[2] ?? '_blank',
                'html' => $item,
            ];
        }
        return isset($ad['items']) ? $ad : false;
    }
    private function replaceLinks($links, $old, $content) {
        $ad_html = $old['html'];
        $item1 = $old['items'][0];
        $items = [];

        $ii = 0;
        foreach ($links as $item) {
            $url = trim($item['url']) ? $item['url'] : '#' . $ii;
            $title = trim($item['title']) ? $item['title'] : '<span hidden>#' . $ii . '</span>';
            $items[] = str_replace([
                $item1['title'], $item1['url'], $item1['target'],
            ], [
                $title, $url, $item['target'] ?: '_blank',
            ], $item1['html']);
            $ii++;
        }
        $items_html = implode("\n", $items);
        $new_html = preg_replace('/\<\!\-\-item(.*)\<\!\-\-item\_end\-\-\>/isU', '{!----}', $ad_html, -1, $count);
        $str = '\{\!\-\-\-\-\}';
        for ($i = 1; $i < $count; $i++) {
            $str .= '\s+\{\!\-\-\-\-\}';
        }
        $new_html = preg_replace('/' . $str . '/i', $items_html, $new_html);

        return str_replace($ad_html, $new_html, $content);
    }

    private function tagImg($img, $rep_default = true) {
        preg_match('/\<\!\-\-img\((.*)\)\-\-\>/', $img, $m);
        $arry = explode('※', $m[1]);

        $title = $arry[3] ?? false;
        if (is_string($title) && $rep_default && $title == '<span hidden>title</span>') {
            $title = '';
        }
        return [
            'img' => $this->checkImgUrl($arry[0] ?? ''),
            'img_value' => $arry[0] ?? '',
            'url' => $arry[1] ?? false,
            'target' => isset($arry[2]) ? ($arry[2] ?: '_blank') : false,
            'title' => $title,
            'html' => $img,
        ];
    }

    private function replaceImg($img, $old, $content) {
        $imgUrl = $this->checkImgUrl($img['img'], true);
        $search[] = $old['img_value'];
        $replace[] = $imgUrl;
        if ($old['url'] !== false && isset($img['url'])) {
            $search[] = $old['url'];
            $replace[] = trim($img['url']) ? $img['url'] : '#';
        }
        if ($old['title'] !== false && isset($img['title'])) {
            $search[] = $old['title'];
            $replace[] = trim($img['title']) ? $img['title'] : '<span hidden>title</span>';
        }
        if ($old['target'] !== false && isset($img['target'])) {
            $search[] = $old['target'];
            $replace[] = trim($img['target']) ? $img['target'] : '_blank';
        }
        $new_html = str_replace($search, $replace, $old['html']);
        return str_replace($old['html'], $new_html, $content);
    }

    private function tagTexts($text, $rep_default = true) {
        preg_match('/\<\!\-\-texts\((.*)\)\-\-\>/', $text, $m);
        $arry = explode('※', $m[1]);
        if ($rep_default) {
            foreach ($arry as &$item) {
                if (preg_match('/\<span hidden\>\#(\d+)\<\/span\>/', $item)) {
                    $item = '';
                }
            }
        }
        return [
            'html' => $text,
            'items' => $arry,
        ];
    }

    private function replaceTexts($texts, $old, $content) {
        $html = $new_html = $old['html'];
        uasort($old['items'], function ($a, $b) {
            return strlen($a) > strlen($b) ? 0 : 1;
        });
        foreach ($old['items'] as $key => $item) {
            $html = str_replace($item, '{{' . $key . '}}', $html);
        }
        $new_html = preg_replace_callback('/\{\{(\d+)\}\}/isU', function ($matches) use ($texts) {
            $i = $matches[1];
            $t = trim($texts[$i] ?? '');
            return $t ?: '<span hidden>#' . $i . '</span>';
        }, $html);
        return str_replace($old['html'], $new_html, $content);
    }

}