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

use think\facade\Route as uRoute;

class route
{

    /**
     * 多语言列表
     * @var array
     */
    public $langList = [];

    /**
     * SEO URL 伪静态是否隐藏后缀
     * @var int|mixed|null
     */
    public $navHideSuffix = 0;
    /**
     * SEO URL 是否去除index.php
     * @var int|mixed|null
     */
    public $adminInlet = 0;
    /**
     * SEO URL 伪静态
     * 多级目录组合模式，0：组合上级目录，1：不组合上级目录
     * @var int|mixed|null
     */
    public $isLevelDir = 0;

    /**
     * 默认路由
     * @var array
     */
    private $defRoute = [
        'formSubmit' => 'Index/formSubmit',//表单提交页
        'search' => 'Index/search',//搜索列表页
        'menu/<id>' => 'Index/menu',//导航页
        'article/<id>' => 'Index/article',//文章详情页
        'node/<id>' => 'Index/node',//文章栏目页
        'node/list_<id>_<p>' => 'Index/node',//文章栏目页
        'cat/<id>' => 'Index/cat',//产品分类页
        'cat/list_<id>_<p>' => 'Index/cat',//产品分类页
        'cats' => 'Index/cat',//全部产品页
        'cats/list_<p>' => 'Index/cat',//全部产品页
        'brand' => 'Index/brand',//产品品牌页
        'item/<id>' => 'Index/item',//产品详情页
        'tags/<id>' => 'Index/tags',//标签
        'tags/<id>_<p>' => 'Index/tags',//标签
    ];

    /**
     * 详情页路由规则
     * @var string
     */
    protected $viewRoute = '{typedir}/{aid}.html';
    /**
     * 列表页路由规则
     * @var string
     */
    protected $listRoute = '{typedir}/list_{tid}_{page}.html';


    protected static $data;

    public function __construct() {
        $this->navHideSuffix = sysConfig('seo.nav_hide_suffix');
        $this->adminInlet = sysConfig('admin.inlet');
        $this->isLevelDir = sysConfig('seo.url_level_dir');
    }

    /**
     * 生成路由数据缓存
     * @param bool $cache
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function schema($cache = true) {
        if (self::$data) return self::$data;
        $data = F('home_route') ?: [];
        if (!env('app_debug', false) && $cache && $data) return $data;
        $data = [];
        $this->generate('menu', $data, $cache);//生成导航路由
        $this->generate('node', $data, $cache);//生成文章栏目路由
        $this->generate('cat', $data, $cache);//生成产品分类路由

        self::$data = $data;
        F('home_route', $data);//设置缓存
        return $data;
    }


    public function getRoute($type = '', $typeId = 0) {
        $route = [];
        if (!in_array($type, ['menu', 'node', 'cat', 'cats',])) return $route;
        $data = $this->schema();//获取缓存数据
        if ($typeId === 'cats' || ($type == 'cat' && !$typeId)) {
            $route = $data['cats'] ?? [];
        } else {
            $route = $data[$type . '_' . $typeId] ?? [];
        }
        return $route;
    }

    /**
     * 获取路由URL
     * @param string $type
     * @param array $data
     * @param bool $domain
     * @return mixed|string
     */
    public function getRouteUrl($type = '', $data = [], $domain = false) {

        //栏目页、详情页
        if (in_array($type, ['node', 'article', 'cat', 'item'])) {

            $rtype = in_array($type, ['node', 'article']) ? 'node' : 'cat';
            $route = $this->getRoute($rtype, $data['typeId']);

            $path = $route['rule'] ?? '';
            if (preg_match('/^http/', $path)) {
                return $path;
            }
            //栏目分类是否隐藏后缀
            $suffix = in_array($type, ['node', 'cat']) && $this->navHideSuffix ? false : true;

            if ($path) {
                if (in_array($type, ['article', 'item'])) {
                    $path = str_replace(['{typedir}', '{aid}'], [$path, $data['id']], $route['view_route'] ?? $this->viewRoute);
                    $suffix = false;
                } elseif (!empty($data['isList'])) {
                    //列表页
                    $path = str_replace(['{typedir}', '{tid}', '{page}'], [$path, $data['id'], $data['p'],], $route['list_route'] ?? $this->listRoute);
                    $suffix = false;
                }
            } else {
                //默认路由处理，兼容老版本
                $suffix = true;
                if (!empty($data['isList'])) {
                    //列表页
                    $path = $type == 'node' ? 'node/list_{id}_{p}' : 'cat/list_{id}_{p}';
                    if ($type == 'cat' && !$data['id']) {
                        $path = 'cats/list_{p}';//全部产品页
                    }
                    $path = str_replace(['{id}', '{p}'], [$data['id'], $data['p'],], $path);
                } else {
                    $path = "{$type}/{$data['id']}";
                    //
                    if ($type == 'cat' && !$data['id']) {
                        $path = 'cats';
                    }
                }
            }

            //只有栏目/分类 才需要增加斜杠
            if (in_array($type, ['node', 'cat']) && empty($data['isList'])) {
                $suffix or $path .= '/';
            }

            return getRrzUrl('/' . $path, $suffix, $domain);

        } elseif ($type == 'tag') {
            $path = '/tags/' . $data['id'];
            if (!empty($data['p']) && $data['p'] > 1) {
                $path .= '_' . $data['p'];
            }
            return getRrzUrl($path, true, $domain);
        }

        //默认首页URL
        return getRrzUrl('/', false, $domain);
    }

    /**
     * 注册路由
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function regRoute() {
        $app = app();
        if ($app->http->getName() != 'home') return false;

        $langList = $this->getTemplateLang();
        //设置默认语言
        if ($langList) {
            $app->lang->setLangSet($langList[0]['name']);
        }

        $suffixHide = ($this->navHideSuffix && $this->adminInlet == 1);

        // 支持批量添加
        uRoute::pattern(['id' => '\d+', 'p' => '\d+',]);
        $routes = $this->schema();
        $list = [];
        foreach ($routes as $key => $item) {
            $path = $item['rule'];
            if (!$path) continue;
            $type = $item['type'];
            $append = $item['append'];

            $routing = [
                'route' => $item['route'],
                'append' => $append,
                'completeMatch' => $item['complete_match'],
            ];
            $list[$path] = $routing;
            $list[$path . '/'] = $routing;//chuan 如果隐藏了后缀，并且去除了index.php 则追加斜杠

            //uRoute::rule($path, $item['route'])->append($append)->completeMatch($item['complete_match']);
            //chuan 如果隐藏了后缀，并且去除了index.php 则追加斜杠
            //$suffixHide and uRoute::rule($path . '/', $item['route'])->append($append)->completeMatch($item['complete_match']);


            $viewRoute = '';//详情页路由
            $viewRule = '';//详情页规则

            $listRule = '';//列表页规则
            if (in_array($type, ['node', 'cat']) || $key == 'cats') {
                $viewRoute = $type == 'node' ? 'Index/article' : 'Index/item';
                $viewRule = str_replace(['{typedir}', '{aid}'], [$path, '<id>'], $item['view']['rule']);
                //详情页路由
                //uRoute::rule($viewRule, $viewRoute)->completeMatch($item['complete_match'])->ext($item['view']['ext']);
                $list[$viewRule] = [
                    'route' => $viewRoute,
                    'completeMatch' => $item['complete_match'],
                    'ext' => $item['view']['ext'],
                ];

                //列表页路由
                $listRule = str_replace(['{typedir}', '{tid}', '{page}'], [$path, '<id>', '<p>'], $item['list']['rule']);
                //uRoute::rule($listRule, $item['route'])->append($append)->completeMatch($item['complete_match'])->ext($item['list']['ext']);
                $list[$listRule] = [
                    'route' => $item['route'],
                    'append' => $append,
                    'completeMatch' => $item['complete_match'],
                    'ext' => $item['list']['ext'],
                ];
            }

            //注册多语言路由
            foreach ($langList as $lang) {
                $lg = $lang['name'] ?? '';
                if (!$lg) continue;

                $append['lang'] = $lg;//追加语言变量

                $routing = [
                    'route' => $item['route'],
                    'append' => $append,
                    'completeMatch' => $item['complete_match'],
                ];
                $list[$lg . '/' . $path] = $routing;
                $list[$lg . '/' . $path . '/'] = $routing;

                //uRoute::rule($lg . '/' . $path, $item['route'])->append($append)->completeMatch($item['complete_match']);
                //如果隐藏了后缀，并且去除了index.php 则追加斜杠
                //$suffixHide and uRoute::rule($lg . '/' . $path . '/', $item['route'])->append($append)->completeMatch($item['complete_match']);

                //注册多语言详情页路由
                if ($viewRoute && $viewRule) {
                    $list[$lg . '/' . $viewRule] = [
                        'route' => $viewRoute,
                        'append' => ['lang' => $lg,],
                        'completeMatch' => $item['complete_match'],
                        'ext' => $item['view']['ext'],
                    ];
                    //uRoute::rule($lg . '/' . $viewRule, $viewRoute)->append(['lang' => $lg,])->completeMatch($item['complete_match'])->ext($item['view']['ext']);
                }
                $listRule and $list[$lg . '/' . $listRule] = [
                    'route' => $item['route'],
                    'append' => $append,
                    'completeMatch' => $item['complete_match'],
                    'ext' => $item['list']['ext'],
                ];
                //$listRule and uRoute::rule($lg . '/' . $listRule, $item['route'])->append($append)->completeMatch($item['complete_match'])->ext($item['list']['ext']);
            }
        }

        foreach ($list as $key => $item) {
            $rule = uRoute::rule($key, $item['route']);
            isset($item['complete_match']) and $rule->completeMatch($item['complete_match']);
            isset($item['append']) and $rule->append($item['append']);
            isset($item['ext']) and $rule->ext($item['ext']);
        }

        //注册默认路由
        foreach ($this->defRoute as $rule => $route) {
            uRoute::rule($rule, $route)->completeMatch(true);
            //注册多语言路由
            foreach ($langList as $lang) {
                $lg = $lang['name'] ?? '';
                if (!$lg) continue;
                //注册多语言首页路由
                uRoute::rule($lg, 'Index/index')->append(['lang' => $lg,])->completeMatch(true);
                //注册多语言默认页面路由
                uRoute::rule($lg . '/' . $rule, $route)->append(['lang' => $lg,])->completeMatch(true);
            }
        }

        return true;
    }

    /**
     * 生成路由数据
     * @param string $type
     * @param array $data
     * @param bool $cache
     * @return array
     */
    private function generate($type = '', &$data = [], $cache = true) {
        $types = ['menu' => 'menus', 'node' => 'nodes', 'cat' => 'cats',];
        if (!isset($types[$type])) return $data;
        $table = $types[$type];

//        $field = 'id,id_path,path,depth,dir_name';
//        //增加特殊字段
//        $field .= $type == 'menu' ? ',url' : ',view_route,list_route';

        //数据列表
        $list = Dm($table, $cache)->order('path asc,id asc');

        foreach ($list as $item) {
            $key = $type . '_' . $item['id'];
            $data[$key] = [
                'type' => $type,
                'url' => '',
                'rule' => '',//规则
                'route' => 'Index/' . $type,//具体执行的页面
                'append' => ['id' => $item['id'],],//追加参数
                'complete_match' => true,//
                'list_route' => empty($item['list_route']) ? $this->listRoute : $item['list_route'],//自定义列表页路由
                'view_route' => empty($item['view_route']) ? $this->viewRoute : $item['view_route'],//自定义详情页路由
            ];
            $route = &$data[$key];

            //格式化列表页规则
            $ext = strrchr($route['list_route'], '.');
            $route['list'] = [
                'rule' => ltrim(rtrim($route['list_route'], $ext), '/'),
                'ext' => ltrim($ext, '.'),
            ];
            //格式化详情页规则
            $ext = strrchr($route['view_route'], '.');
            $route['view'] = [
                'rule' => ltrim(rtrim($route['view_route'], $ext), '/'),
                'ext' => ltrim($ext, '.'),
            ];

            if ($type == 'menu') {
                if ($item['url'] == '/' || !$item['url']) {
                    $route['url'] = 'home';
                    continue;
                }
                if (preg_match('/^http/', $item['url'])) {
                    $route['url'] = $item['url'];
                    continue;
                }
            }

            $ids = explode(',', $this->isLevelDir ? (string)$item['id'] : $item['id_path']);

            $dirs = $list->filter(function ($item) use ($ids) {
                return in_array($item['id'], $ids);
            })->order('path')->column('dir_name');

            $dirs = array_filter($dirs);
            //组合上级目录时判断是否缺失层级
            if (!$this->isLevelDir && $item['depth'] != count($dirs)) {
                $dirs = [];
            }
            if ($dirs && !$this->isLevelDir && $type == 'cat' && !empty($data['cats']['rule'])) {
                array_unshift($dirs, $data['cats']['rule']);//组合上级目录
            }

            $path = $this->checkLangRoute(implode('/', $dirs));

            if (!$path) {
                //$path = $type . '/' . $item['id'];
                if ($type == 'menu' && $item['url'] === '/cats.html') {
                    $route['url'] = '/cats.html';
                }
            } else {
                $route['rule'] = $path;
            }

            if ($type == 'menu' && $item['url'] === '/cats.html') {
                $data['cats'] = $route;//全部产品页路由
            }
        }

        return $data;
    }


    /**
     * 处理多语言路由路径
     * @param string $path
     * @return string|string[]|null
     */
    private function checkLangRoute($path = '') {
        if (!$path) return $path;
        $langList = $this->getTemplateLang();
        if (count($langList) > 1) {
            $langList[] = ['name' => 'zhongwenjianti',];//中文简体
            $langList[] = ['name' => 'jiantizhongwen',];//简体中文
            $langList[] = ['name' => 'english',];//english
        }

        foreach ($langList as $lang) {
            $lg = $lang['name'] ?? '';
            if (!$lg) continue;
            $path = preg_replace('/^' . $lg . '\//', '', $path);
        }
        return $path;
    }

    /**
     * 获取模版多语言配置
     * @return array
     */
    public function getTemplateLang() {
        if ($this->langList) return $this->langList;
        //模版配置=>获取多语言配置
        $confPath = public_path('template') . 'config.json';
        $langList = [];
        if (is_file($confPath)) {
            $conf = @json_decode(file_get_contents($confPath), true);
            $langList = $conf['lang'] ?? [];
            if (is_string($langList)) {
                $langList = array_filter(explode(',', $langList));
            }
            $langList = array_values(array_unique($langList));
            $langList = array_map(function ($item) {
                if (is_string($item)) {
                    $item = ['name' => trim($item),];
                }
                return $item;
            }, $langList);
        }
        $this->langList = $langList;
        return $langList;
    }

    /**
     * 获取环境变量
     * @access public
     * @param string $name 参数名
     * @return mixed
     */
    public function get(string $name) {
        return $this->has($name) ? $this->{$name} : null;
    }

    /**
     * 检测是否存在环境变量
     * @access public
     * @param string $name 参数名
     * @return bool
     */
    private function has(string $name): bool {
        return isset($this->{$name});
    }
}



