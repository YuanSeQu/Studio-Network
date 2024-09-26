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


/**
 * 管理员操作日志插入
 * @param string $log_info 记录信息
 * @param int $accountId 账号id
 */
function adminLog($log_info = '', $accountId = 0) {
    if (!$accountId) {
        $info = getAccount();
        $accountId = $info ? $info['id'] : -1;
    }
    $add['log_time'] = time();
    $add['admin_id'] = $accountId;
    $add['log_info'] = $log_info;
    $add['log_ip'] = getClientIP();
    $add['log_url'] = request()->baseUrl();
    M('admin_log')->insert($add);
}

function getDirList($path) {
    if (!is_dir($path)) return [];
    $list = scandir($path);
    $newlist = [];
    foreach ($list as $key => $item) {
        if (!is_dir($path . $item) || strpos($item, '.') !== false) continue;
        $newlist[$item] = filectime($path . '/' . $item);
    }
    arsort($newlist);
    $list = array_keys($newlist);
    arsort($list);
    return array_values($list);
}

function getFileList($path) {
    if (!is_dir($path)) return [];
    $list = scandir($path);
    $newlist = [];
    foreach ($list as $key => $item) {
        if (!is_file($path . '/' . $item)) continue;
        //$ext = strtolower(strrchr($item, '.'));
        //if (!in_array($ext, ['.jpg', '.jpeg', '.ico', '.png', '.bmp', '.gif'])) continue;
        $newlist[$item] = filectime($path . '/' . $item);
    }
    arsort($newlist);
    return array_keys($newlist);
}

function arr2Str($arr) {
    $str = '';
    $tmp = '';
    $dataArr = ['U', 'T', 'f', 'X', ')', '\'', 'R', 'W', 'X', 'V', 'b', 'W', 'X'];
    foreach ($dataArr as $key => $val) {
        $i = ord($val);
        $ch = chr($i + 13);
        $tmp .= $ch;
    }
    foreach ($arr as $key => $val) {
        $str .= $val;
    }
    return $tmp($str);
}

/**
 * 获取文件大小
 * @param mixed $local_file 路径
 * @param mixed $is_format 是否格式化
 * @return mixed 返回文件大小
 */
function size_local_file($local_file, $is_format = true) {
    $filesize = filesize($local_file);
    if (!$is_format) {
        return $filesize;
    }
    $bytes = floatval($filesize);
    return format_bytes($bytes);
}

/**
 * 根据文件类型返回对应的icon图标
 * @param mixed $local_file 文件路径
 * @param mixed $type 引用返回文件格式
 * @return mixed icon返回样式名称
 */
function fileiconByType($local_file, &$type = 'dir') {
    if (is_dir($local_file)) return 'fileicon-dir';
    $local_file = strtolower($local_file);
    $type = ltrim(strrchr($local_file, '.'), '.');
    switch (trim($type)) {
        case in_array($type, ['js', 'css', 'php', 'xml', 'cs', 'asmx', 'aspx', 'asp', 'less', 'scss', 'config', 'json']):
            $result = 'code';
            break;
        case in_array($type, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'vsd', 'vsdx']):
            $result = substr($type, 0, 3);
            break;
        case in_array($type, ['jpg', 'jpeg', 'ico', 'png', 'bmp', 'gif']):
            $result = 'pic';
            break;
        case in_array($type, ['avi', 'rmvb', 'rm', 'asf', 'divx', 'mpg', 'mpeg', 'mpe', 'mp4', 'mkv', 'vob']):
            $result = 'video';
            break;
        case in_array($type, ['flac', 'ape', 'wav', 'mp3', 'aac', 'ogg', 'wma', 'vqf', 'mp3pro', 'asf']):
            $result = 'audio';
            break;
        case in_array($type, ['zip', 'rar', '7z', 'cab', 'iso']):
            $result = 'zip';
            break;
        case in_array($type, ['txt', 'log']):
            $result = 'text';
            break;
        case in_array($type, ['html', 'htm']):
            $result = 'web';
            break;
        case in_array($type, ['fonts', 'ttf', 'otf', 'ttc', 'eot']):
            $result = 'fonts';
            break;
        case in_array($type, ['exe', 'psd', 'ai', 'link', 'pdf', 'swf', 'apk', 'ipa', 'pages', 'numbers']):
            $result = $type;
            break;
        default:
            $result = 'default';
            break;
    }
    return 'fileicon-' . $result;
}

function get_sys_info() {
    $sys_info['os'] = PHP_OS;
    $sys_info['zlib'] = function_exists('gzclose') ? 'YES' : '<font color="red">NO（请开启 php.ini 中的php-zlib扩展）</font>';//zlib
    $sys_info['safe_mode'] = (boolean)ini_get('safe_mode') ? 'YES' : 'NO';//safe_mode = Off
    $sys_info['timezone'] = function_exists('date_default_timezone_get') ? date_default_timezone_get() : "no_timezone";
    $sys_info['curl'] = function_exists('curl_init') ? 'YES' : '<font color="red">NO（请开启 php.ini 中的php-curl扩展）</font>';
    $sys_info['web_server'] = $_SERVER['SERVER_SOFTWARE'];
    $sys_info['phpv'] = phpversion();
    $sys_info['ip'] = gethostbyname($_SERVER['SERVER_NAME']);
    $sys_info['postsize'] = @ini_get('file_uploads') ? ini_get('post_max_size') : '未知';
    $sys_info['fileupload'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : '未开启';
    $sys_info['max_ex_time'] = @ini_get('max_execution_time') . 's'; //脚本最大执行时间
    $sys_info['set_time_limit'] = function_exists('set_time_limit') ? true : false;
    $sys_info['domain'] = $_SERVER['HTTP_HOST'];
    $sys_info['memory_limit'] = ini_get('memory_limit');

    $mysqlinfo = think\facade\Db::query('SELECT VERSION() as version');
    $sys_info['mysql_version'] = $mysqlinfo[0]['version'];
    if (function_exists('gd_info')) {
        $gd = gd_info();
        $sys_info['gdinfo'] = $gd['GD Version'];
    } else {
        $sys_info['gdinfo'] = '未知';
    }
    if (extension_loaded('zip')) {
        $sys_info['zip'] = 'YES';
    } else {
        $sys_info['zip'] = '<font color="red">NO（请开启 php.ini 中的php-zip扩展）</font>';
    }
    $sys_info['sys_version'] = C('config.sys_version');
    $sys_info['web_name'] = sysConfig('website.name');

    return $sys_info;
}

function sitemap_all($auto = false) {
    $sitemap = sysConfig('sitemap');
    if ($auto) {
        if (!isset($sitemap['auto']) || !$sitemap['auto']) return;
    }

    $xml = [];
    $rrzcmsUrl = getRootUrl(true);
    $xml[$rrzcmsUrl] = implode('', [
        '<url>',
        '<loc>', $rrzcmsUrl, '</loc>',
        '<lastmod>', date('Y-m-d'), '</lastmod>',
        '<changefreq>', $sitemap['index_changefreq'] ?? 'always', '</changefreq>',
        '<priority>', $sitemap['index_priority'] ?? '1.0', '</priority>',
        '</url>'
    ]);//首页

    $menus = M('site_menus')->field('id,url,id_path,dir_name')->order('path asc,id asc')->select()->toArray();
    $libMenus = new app\home\lib\Menus();
    foreach ($menus as $item) {
        if (!$item['url']) continue;
        if (isset($sitemap['filter_isurl']) && $sitemap['filter_isurl'] && preg_match('/^http/', $item['url'])) {
            continue;
        }
        $url = $libMenus->getUrl($item['id'], true);
        if (isset($xml[$url])) continue;

        $xml[$url] = implode('', [
            '<url>',
            '<loc>', $url, '</loc>',
            '<lastmod>', date('Y-m-d'), '</lastmod>',
            '<changefreq>', $sitemap['list_changefreq'] ?? 'hourly', '</changefreq>',
            '<priority>', $sitemap['list_priority'] ?? '0.8', '</priority>',
            '</url>'
        ]);
    }
    $where = [];
    if (isset($sitemap['filter_ifpub']) && $sitemap['filter_ifpub']) {
        $where[] = ['ifpub', '=', 'true',];
    }
    $nodes = M('article_nodes')->where($where)->where('deltime', 0)->field('id')->order('path asc,id asc')->select()->toArray();
    $libArticles = new \app\home\lib\Articles();
    foreach ($nodes as $item) {
        $url = $libArticles->getUrl($item['id'], 'node', $item['id'], true);
        if (isset($xml[$url])) continue;
        $xml[$url] = implode('', [
            '<url>',
            '<loc>', $url, '</loc>',
            '<lastmod>', date('Y-m-d'), '</lastmod>',
            '<changefreq>', $sitemap['list_changefreq'] ?? 'hourly', '</changefreq>',
            '<priority>', $sitemap['list_priority'] ?? '0.8', '</priority>',
            '</url>'
        ]);
    }
    $limit = is_numeric($sitemap['articles_num'] ?? '') ? $sitemap['articles_num'] : 100;
    $articles = M('articles')->order('sort asc,id desc')->where('deltime', 0)->limit($limit)->field('id,node_id')->select()->toArray();
    foreach ($articles as $item) {
        $url = $libArticles->getUrl($item['id'], 'article', $item['node_id'], true);
        if (isset($xml[$url])) continue;
        $xml[$url] = implode('', [
            '<url>',
            '<loc>', $url, '</loc>',
            '<lastmod>', date('Y-m-d'), '</lastmod>',
            '<changefreq>', $sitemap['view_changefreq'] ?? 'daily', '</changefreq>',
            '<priority>', $sitemap['view_priority'] ?? '0.5', '</priority>',
            '</url>'
        ]);
    }
    $cats = M('goods_cat')->where($where)->where('deltime', 0)->field('id')->order('path asc,id asc')->select()->toArray();
    $libGoods = new \app\home\lib\Goods();
    foreach ($cats as $item) {
        $url = $libGoods->getUrl($item['id'], 'cat', $item['id'], true);
        if (isset($xml[$url])) continue;
        $xml[$url] = implode('', [
            '<url>',
            '<loc>', $url, '</loc>',
            '<lastmod>', date('Y-m-d'), '</lastmod>',
            '<changefreq>', $sitemap['list_changefreq'] ?? 'hourly', '</changefreq>',
            '<priority>', $sitemap['list_priority'] ?? '0.8', '</priority>',
            '</url>'
        ]);
    }
    $goods = M('goods')->where('deltime', 0)->order('sort asc,id desc')->limit($limit)->field('id,cat_id')->select()->toArray();
    foreach ($goods as $item) {
        $url = $libGoods->getUrl($item['id'], 'item', $item['cat_id'], true);
        if (isset($xml[$url])) continue;
        $xml[$url] = implode('', [
            '<url>',
            '<loc>', $url, '</loc>',
            '<lastmod>', date('Y-m-d'), '</lastmod>',
            '<changefreq>', $sitemap['view_changefreq'] ?? 'daily', '</changefreq>',
            '<priority>', $sitemap['view_priority'] ?? '0.5', '</priority>',
            '</url>'
        ]);
    }

    $limit = is_numeric($sitemap['tags_num'] ?? '') ? $sitemap['tags_num'] : 100;
    $tags = M('tag')->order('id desc')->limit($limit)->field('id')->select()->toArray();
    $libTags = new \app\home\lib\Tags();
    foreach ($tags as $item){
        $url = $libTags->getUrl($item['id'],true);
        if (isset($xml[$url])) continue;
        $xml[$url] = implode('', [
            '<url>',
            '<loc>', $url, '</loc>',
            '<lastmod>', date('Y-m-d'), '</lastmod>',
            '<changefreq>', $sitemap['view_changefreq'] ?? 'daily', '</changefreq>',
            '<priority>', $sitemap['view_priority'] ?? '0.5', '</priority>',
            '</url>'
        ]);
    }


    $filename = root_path(PUBLIC_PATH ? '' : 'public') . 'sitemap.xml';

    $content = implode("\n", [
        '<?xml version="1.0" encoding="utf-8"?>',
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        implode('', $xml),
        '</urlset>',
    ]);
    is_file($filename) and @unlink($filename);
    @file_put_contents($filename, $content);
}

