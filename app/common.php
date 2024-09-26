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

use think\facade\Cache;

/*
 * 获取配置信息
 */
function sysConfig($key = '', $data = null, $def = null, $isUpdate = false) {
    $param = array_values(array_filter(explode('.', $key)));
    if (!$param) return null;

    $key = strtoupper('config_' . $param[0]);//缓存键

    if ($data === null) {//取值
        $cache = cache($key);//获取缓存数据
        if (isset($param[1]) && !$isUpdate) {
            if ($cache && isset($cache[$param[1]])) {
                return $cache[$param[1]];
            }
        } elseif ($cache && !$isUpdate) {
            return $cache;
        }
        $where = ['type' => $param[0],];
        $rows = think\facade\Db::name('config')->where($where)->field('name,value')->select()->toArray();
        $config = $rows ? array_column($rows, 'value', 'name') : [];
        $config and cache($key, $config);//设置缓存

        if (isset($param[1])) {
            return $config[$param[1]] ?? $def;
        }
        return $config ?: ($def ?: []);
    }
    //更新
    if (!is_array($data) && count($param) > 1) {
        $_data[$param[1]] = $data;
        $data = $_data;
        unset($_data);
    }
    $config = sysConfig($param[0], null, null, true);

    $news = [];
    foreach ($data as $k => $v) {
        $v = trim((string)$v);
        $newArr = ['name' => $k, 'value' => $v, 'type' => $param[0],];
        if (!$config || !isset($config[$k])) {
            $news[] = $newArr;
            $config[$k] = $v;
        } elseif ($config[$k] != $v) {
            $where = ['name' => $k, 'type' => $newArr['type'],];
            think\facade\Db::name('config')->where($where)->save(['value' => $v,]);
            $config[$k] = $v;
        }
    }
    $news and think\facade\Db::name('config')->insertAll($news);
    cache($key, $config);//设置缓存
}


/**
 * 获取用户session信息
 * @param string $appName 应用名称
 * @return mixed
 */
function getAccount($appName = '') {
    $appName = $appName ?: app()->http->getName();
    $key = 'account.' . $appName;
    return session($key);
}

/**
 * 获取和设置配置参数
 * @param string|array $name 参数名
 * @param mixed $value 参数值
 * @return mixed
 */
function C($name = '', $value = null) {
    return config($name, $value);
}

/**
 * Url生成
 * @param string $url 路由地址
 * @param array $vars 变量
 * @param bool|string $suffix 生成的URL后缀
 * @param bool|string $domain 域名
 * @return string
 */
function U(string $url = '', array $vars = [], $suffix = true, $domain = false) {
    $inlet = sysConfig('admin.inlet');
    return url($url, $vars, $suffix, $domain)->build($inlet ?: 0);
}

/**
 * 插件Url生成
 * @param string $url
 * @param array $vars
 * @param bool $suffix
 * @param bool $domain
 * @return string
 */
function PU(string $url = '', array $vars = [], $suffix = true, $domain = false) {
    $url = trim($url);
    $pluginUrl = preg_match('/^\//', $url) ? $url : '/' . getAppName('admin') . '/plugin/' . $url;
    return U($pluginUrl, $vars, $suffix, $domain);
}

/**
 * @param string $name 表名称
 * @param bool $isCache
 * @return \think\facade\Db
 */
function M($name = '', $isCache = true) {
    $model = think\facade\Db::name($name);
//    if (!env('app_debug', false)) {
//        $app = app();
//        if ($isCache && $app->http->getName() == 'home') {
//            $isCache = sysConfig('webcache.data', null, 1);//数据缓存
//            $isCache and $model = $model->cache();
//        }
//    }
    return $model;
}


/**
 * 获取数据
 * @param string $name
 * @param bool $cache
 * @return \app\home\model\Collection
 */
function Dm($name = '', $cache = true) {
    $obj = '\\app\\home\\facade\\' . $name;
    if (class_exists($obj)) {
        return $obj::getData($cache);
    }
    return new \app\home\model\Collection();
}

/**
 * 获取输入数据 支持默认值和过滤
 * @param string $key 获取的变量名
 * @param mixed $default 默认值
 * @param string $filter 过滤方法
 * @return mixed
 */
function I(string $key = '', $default = null, $filter = '') {
    return input($key, $default, $filter);
}

/**
 * 缓存管理
 * @param string $name 缓存名称
 * @param mixed $value 缓存值
 * @return mixed
 * @throws \Psr\SimpleCache\InvalidArgumentException
 */
function F(string $name = null, $value = '') {
    if (is_null($name)) {
        return false;
    }

    $cache = Cache::store('ftemp');
    if ('' === $value) {
        // 获取缓存
        return 0 === strpos($name, '?') ? $cache->has(substr($name, 1)) : $cache->get($name);
    } elseif (is_null($value)) {
        // 删除缓存
        return $cache->delete($name);
    }

    return $cache->set($name, $value, 0);
}

/**
 * 清除缓存
 * @param array|bool|string $clears
 * @return bool
 */
function clearCache($clears = []) {
    if ($clears === true || $clears === 'all') {
        $clears = ['cache', 'log', 'temp'];
    }

    $apps = ['admin', 'home', 'install', 'plugin'];//清除的应用
    $runtime = app()->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR;
    foreach ($clears as $item) {
        if ($item == 'cache') {
            delFile($runtime . $item, true);
            continue;
        }
        if ($item == 'log') {
            delFile($runtime . $item, true);
        }
        foreach ($apps as $app) {
            $path = $runtime . $app . DIRECTORY_SEPARATOR . $item;
            delFile($path, true);
        }
    }

    /*清除旧升级备份包，保留最后一个备份文件*/
    $backupArr = glob($runtime . 'data/backup/v*_www');
    for ($i = 0; $i < count($backupArr) - 1; $i++) {
        delFile($backupArr[$i], true);
    }

    delFile($runtime . 'schema');
    delFile($runtime . 'log');//调试时生成的日志文件

    \app\facade\route::schema(false);//重新生成路由

    return true;
}

/**
 * 事件的另一种执行方式
 * @param string $event
 * @param mixed ...$args
 * @return bool|mixed
 */
function service(string $event, &...$args) {
    $listeners = think\Facade\Event::getListener($event);
    $result = true;
    foreach ($listeners as $key => $listener) {
        if ($listener instanceof \Closure) {
            $result = $listener($args);
        } elseif (is_string($listener)) {
            if (strpos($listener, '->') !== false) {
                [$obj, $method] = explode('->', $listener);
            } else {
                $obj = $listener;
                $method = 'handle';
            }
            if ($obj && class_exists($obj)) {
                $class = new $obj();
                if ($method && method_exists($class, $method)) {
                    $result = call_user_func_array([$class, $method], $args);
                }
            }
        }
        if ($result === false) break;
    }
    return $result;
}

/**
 * 返回数组中指定多列
 * @param array $input 需要取出数组列的多维数组
 * @param string $column_keys 要取出的列名，逗号分隔，如不传则返回所有列
 * @param string $index_key 作为返回数组的索引的列
 * @return array
 */
function array_columns($input, $column_keys = null, $index_key = null) {
    $result = [];
    $keys = isset($column_keys) ? explode(',', $column_keys) : [];
    if ($input) {
        foreach ($input as $k => $v) {
            // 指定返回列
            if ($keys) {
                $tmp = [];
                foreach ($keys as $key) {
                    $tmp[$key] = $v[$key];
                }
            } else {
                $tmp = $v;
            }
            // 指定索引列
            if (isset($index_key)) {
                $result[$v[$index_key]] = $tmp;
            } else {
                $result[] = $tmp;
            }

        }
    }
    return $result;
}


//格式化导航数据层级关系
function tierMenusList($data, $child = 'children', $sort = true, $callBack = null) {
    $list = [];
    foreach ($data as $item) {
        $ids = array_filter(explode(',', $item['id_path']));
        $k = $item['depth'] - 1;
        $callBack && $callBack instanceof \Closure && $callBack($item);
        if ($k == 0) {
            $list[$item['id']] = $item;
        } elseif ($k > 0) {
            $str = '';
            for ($i = 0; $i < $k; $i++) {
                $str .= "[\$ids[" . $i . "]]['{$child}']";
            }
            eval('$list' . $str . '[$item[\'id\']] = $item;');
        }
    }
    unset($data);
    if (!$sort) {
        return menus_array_values($list, $child);
    }
    return sortMenusList($list, $child);
}

//数据树执行array_values
function menus_array_values($list, $child = 'children') {
    foreach ($list as $key => $item) {
        if (isset($item[$child]) && $item[$child]) {
            $list[$key][$child] = menus_array_values($item[$child], $child);
        } else {
            unset($list[$key][$child]);
        }
    }
    return array_values($list);
}

//按顺序合并层级后的数据
function sortMenusList($list, $child = 'children') {
    $data = [];
    foreach ($list as $item) {
        $children = isset($item[$child]) ? $item[$child] : [];
        unset($item[$child]);
        $item['ishas'] = !!$children;
        $data[] = $item;
        if ($children) {
            $data = array_merge($data, sortMenusList($children));
        }
    }
    unset($list);
    return $data;
}

/*
 * 重新生成单个数据表缓存字段文件
 */
function schemaTable($name) {
    if (!$name) return false;
    $table = $name;
    $app = app();
    $prefix = $app->db->getConnection()->getConfig('prefix');
    if (!preg_match('/^' . $prefix . '/i', $name)) {
        $table = $prefix . $name;
    }
    //调用命令行的指令
    $console = new \think\Console($app);
    $console->call('optimize:schema', ['--table', $table]);
}

/**
 * 重新生成全部数据表缓存字段文件
 */
function schemaAllTable() {
    $app = app();
    $dbtables = $app->db->query('SHOW TABLE STATUS');
    $console = new \think\Console($app);
    foreach ($dbtables as $k => $v) {
        //调用命令行的指令
        $console->call('optimize:schema', ['--table', $v['Name']]);
    }
}

/**
 * 客户端IP
 */
function getClientIP() {
    $ip = request()->ip();
    if (preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1 -9]?\d))))$/', $ip)) {
        return $ip;
    }
    return '';
}

function curl($url = '', $options = []) {
    return \app\facade\http::send($url, $options);
}

function get_curl($url = '', $dataType = 'text') {
    return curl($url, [
        'dataType' => $dataType,
    ]);
}

function post_curl($url = '', $data = [], $dataType = 'text') {
    return curl([
        'url' => $url,
        'type' => 'post',
        'data' => $data,
        'dataType' => $dataType,
    ]);
}


include_once __DIR__ . '/function.php';