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
 * 替换根目录
 * @param string $content
 * @param bool $domain
 * @param bool $escape 字符存在转义
 * @return string|string[]|null
 */
function viewReplacePath($content = '', $domain = false, $escape = false) {
    $root = getRootUrl() . PUBLIC_PATH;
    $domain and $root = '//' . request()->host() . $root;
    $path = root_path('public');
    $replace = [];
    $list = scandir($path);
    foreach ($list as $item) {
        if (!is_dir($path . $item) || strpos($item, '.') === 0) continue;
        $replace[] = $escape ? '/("|\&quot\;|\'|\()\\\\\/(' . $item . ')\\\\\//' : '/("|\&quot\;|\'|\()\/(' . $item . ')\//';
    }
    if ($escape) {
        $root = str_replace('/', '\/', $root);
        $content = preg_replace($replace, "\$1" . $root . "\$2" . '\/', $content);
        $content = preg_replace('/("|\&quot\;|\'|\()\\\\\/(public)\\\\\//', "\$1" . $root, $content);
    } else {
        $content = preg_replace($replace, "\$1" . $root . "\$2" . '/', $content);
        $content = preg_replace('/("|\&quot\;|\'|\()\/(public)\//', "\$1" . $root, $content);
    }
    return $content;
}

/**
 * 获取url根目录
 * @return mixed|string
 */
function getRootUrl($domain = false) {
    $file = request()->baseFile($domain);
    $url = str_replace(['/public/index.php', '/index.php'], '', $file);
    $url = rtrim($url, '/') . '/';
    return $url;
}

/*
 * 获取当前app 名称
 * @return string
 */
function getAppName($name = '') {
    $name = $name ?: app()->http->getName();
    $map = array_flip(C('app.app_map'));
    return $map[strtolower($name)] ?? $name;
}

/**
 * 获取当前app根目录的url
 * @return string
 */
function getAppRootUrl($name = '') {
    $name = getAppName($name);
    return U('/' . $name, [], false);
}

/*
 * 获取 人人站CMS 地址
 */
function getRrzUrl($url = '', $suffix = true, $domain = false) {
    $lang = I('param.lang');
    $path = $lang ? '/' . $lang : '';

    if (preg_match('/^http/', $url)) {
        return $url;
    }
    if ($url == '/' || $url === '') {
        return $lang ? U($path, [], false, $domain) : getRootUrl($domain);
    }

    if ($url === '/cats') {
        $lib = new \app\home\lib\Menus;
        $route = $lib->getUrl('cats');
        if (!empty($route['rule'])) {
            $url = '/' . $route['rule'];
            if ($lib->Route::get('navHideSuffix')) {
                $suffix = false;
                $url .= '/';
            }
        } else {
            $suffix = true;
        }
    }

    return U($path . $url, [], $suffix, $domain);
}

function set_upload_config() {
    $imgspace = include root_path() . 'app/admin/config/imgspace.php';

    $upConfig = &$imgspace['config'];
    $upload = sysConfig('upload');

    if ($upload) {
        foreach ($upload as $key => $value) {
            [$name, $k] = explode('_', $key);
            if ($k == 'ext' || $k == 'mime') {
                $arry = array_unique(array_filter(explode(',', $value)));//去重
                $value = $arry ? implode(',', $arry) : '';
                $upConfig[$name][$k == 'ext' ? 'fileExt' : 'fileMime'] = $value;
            } elseif ($k == 'size') {
                $upConfig[$name]['fileSize'] = intval($value) * 1024 * 1024;
            }
        }
    }
    C($imgspace, 'imgspace');
    return $imgspace;
}

/**
 * 价格处理
 * @param int $price
 * @param bool $Int
 * @return float|int
 */
function price_format($price = 0, $Int = true) {
    $price = $price ? $price : 0;
    if ($Int && (int)$price == $price) {
        return (int)$price;
    }
    return round($price, 2);
}


/**
 * 判断url是否完整的链接
 * @param  string $url 网址
 * @return boolean
 */
function is_http_url($url) {
    preg_match("/^((\w)*:)?(\/\/).*$/", $url, $match);
    if (empty($match)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 获取随机字符串
 * @param int $randLength 长度
 * @param int $addtime 是否加入当前时间戳
 * @param int $includenumber 是否包含数字
 * @return string
 */
function get_rand_str($randLength = 6, $addtime = 1, $includenumber = 0) {
    if (1 == $includenumber) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
    } else if (2 == $includenumber) {
        $chars = '123456789';
    } else {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
    }
    $len = strlen($chars);
    $randStr = '';
    for ($i = 0; $i < $randLength; $i++) {
        $randStr .= $chars[rand(0, $len - 1)];
    }
    $tokenvalue = $randStr;
    if ($addtime) {
        $tokenvalue = $randStr . time();
    }
    return $tokenvalue;
}

/**
 * 递归删除文件夹
 * @param string $path 目录路径
 * @param boolean $delDir 是否删除空目录
 * @return boolean
 */
function delFile($path, $delDir = false) {
    $handle = is_dir($path) ? @opendir($path) : false;
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delFile("$path/$item", $delDir) : @unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir) {
            return @rmdir($path);
        }
    } else {
        if (file_exists($path)) {
            return @unlink($path);
        } else {
            return false;
        }
    }
}


/**
 * 获取文章内容html中第一张图片地址
 *
 * @param  string $html html代码
 * @return boolean
 */
function get_html_first_imgurl($html) {
    $pattern = '~<img [^>]*[\s]?[\/]?[\s]?>~';
    preg_match_all($pattern, $html, $matches);//正则表达式把图片的整个都获取出来了
    $img_arr = $matches[0];//图片
    $first_img_url = "";
    if (!empty($img_arr)) {
        $first_img = $img_arr[0];
        $p = "#src=('|\")(.*)('|\")#isU";//正则表达式
        preg_match_all($p, $first_img, $img_val);
        if (isset($img_val[2][0])) {
            $first_img_url = $img_val[2][0]; //获取第一张图片地址
        }
    }

    return $first_img_url;
}

/**
 * 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param int $start 开始位置
 * @param null $length 截取长度
 * @param bool $suffix 截断显示字符
 * @param string $charset 编码格式
 * @return string
 */
function msubstr($str = '', $start = 0, $length = null, $suffix = false, $charset = "utf-8") {
    if (function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }

    $str_len = strlen($str); // 原字符串长度
    $slice_len = strlen($slice); // 截取字符串的长度
    if ($slice_len < $str_len) {
        $slice = $suffix ? $slice . '...' : $slice;
    }

    return $slice;
}


/**
 * 自定义只针对htmlspecialchars编码过的字符串进行解码
 * @param string $str 需要转换的字符串
 * @return string
 */
function rrz_htmlspecialchars_decode($str = '') {
    if (is_string($str) && stripos($str, '&lt;') !== false && stripos($str, '&gt;') !== false) {
        $str = htmlspecialchars_decode($str);
    }
    return $str;
}

/**
 * 截取内容清除html之后的字符串长度，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param int $start 开始位置
 * @param null $length 截取长度
 * @param bool $suffix 截断显示字符
 * @param string $charset 编码格式
 * @return string
 */
function html_msubstr($str = '', $start = 0, $length = null, $suffix = false, $charset = "utf-8") {
    if (false) {
        $length = $length * 2;
    }
    $str = rrz_htmlspecialchars_decode($str);
    $str = checkStrHtml($str);
    return msubstr($str, $start, $length, $suffix, $charset);
}

/**
 * html 转 文本 截取
 * @param string $str
 * @param null $length
 * @param int $start
 * @param bool $suffix
 * @param string $charset
 * @return string
 */
function html2text($str = '', $length = null, $start = 0, $suffix = true, $charset = "utf-8") {
    return html_msubstr($str, $start, $length, $suffix, $charset);
}

function subtext($str = '', $length = null, $start = 0, $suffix = true, $charset = "utf-8") {
    return text_msubstr($str, $length, $start, $suffix, $charset);
}

/**
 * 针对多语言截取，其他语言的截取是中文语言的2倍长度
 * @param string $str
 * @param null $length
 * @param int $start
 * @param bool $suffix
 * @param string $charset
 * @return string
 */
function text_msubstr($str = '', $length = null, $start = 0, $suffix = false, $charset = "utf-8") {
    if (false) {
        $length = $length * 2;
    }
    return msubstr($str, $start, $length, $suffix, $charset);
}

/**
 * 过滤Html标签
 *
 * @param     string $string 内容
 * @return    string
 */
function checkStrHtml($string) {
    $string = trim_space($string);

    if (is_numeric($string)) return $string;
    if (!isset($string) or empty($string)) return '';
    $string = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', '', $string);
    $string = ($string);
    $string = strip_tags($string, ""); //清除HTML如<br />等代码
    $string = str_replace(["\n", "\t", PHP_EOL, "\r"], "", $string);//去掉换行、制表符号、回车

    $string = str_replace("'", "‘", $string); //替换单引号
    $string = str_replace("&amp;", "&", $string);
    $string = str_replace("&nbsp;", "", $string);

    // --过滤微信表情
    $string = preg_replace_callback('/[\xf0-\xf7].{3}/', function ($r) {
        return '';
    }, $string);

    return $string;
}

/**
 * 过滤前后空格等多种字符
 *
 * @param string $str 字符串
 * @param array $arr 特殊字符的数组集合
 * @return string
 */
function trim_space($str, $arr = array()) {
    if (empty($arr)) {
        $arr = array(' ', '　');
    }
    foreach ($arr as $key => $val) {
        $str = preg_replace('/(^' . $val . ')|(' . $val . '$)/', '', $str);
    }

    return $str;
}

/**
 * 格式化字节大小
 *
 * @param  number $size 字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 字符转码
 * @param $text
 * @param string $charset
 * @return false|string
 */
function convert_encoding($text, $charset = 'UTF-8') {
    if (!$text) return $text;
    $encoding = mb_detect_encoding($text, mb_detect_order(), false);
    if ($encoding == $charset) {
        $text = mb_convert_encoding($text, $charset, $charset);
    }
    $out = iconv(mb_detect_encoding($text, mb_detect_order(), false), $charset . "//IGNORE", $text);
    return $out ?: $text;
}


/**
 * 自定义字符截取
 * @param mixed $pattern 正则
 * @param mixed $content 字符内容
 * @param string|int $str 数字：获取正则返回的数组的键，字符：方法或eval，
 * @param mixed $args 方法的参数或正则返回的数组的键
 * @return string 返回需要的内容
 */
function strMatch($pattern, $content, $str = 1, $args = '') {
    $_ret = '';
    preg_match($pattern, $content, $matches);
    if (is_numeric($str)) {
        $_ret = trim($matches[$str] ?? '');
    } elseif (is_string($str) && substr($str, 0, 1) == '/' && substr($str, -2) == '/e') {
        $str = substr($str, 1, -2);
        $str = str_replace('###', $matches[(is_numeric($args) ? $args : 1)] ?? '', $str);
        @eval("\$_ret=$str;");
    } elseif (is_string($str) && function_exists($str)) {
        $args or $_ret = $str($matches[1] ?? '');
        $args and $_ret = $str($matches[1] ?? '', $args);
    } elseif (is_object($str) && get_class($str) == 'Closure') {
        $_ret = $str($matches);
    }
    return $_ret;
}

/**
 * 安全检测，验证注入、木马内容
 * @param string $str
 * @return bool
 */
function preventShell($str = '') {
    //注入检测
    if (is_string($str) && (preg_match('/^phar:\/\//i', $str) || stristr($str, 'phar://'))) {
        return false;
    } elseif (preg_match('#__HALT_COMPILER()#i', $str) || preg_match('#/script>#i', $str) || preg_match('#<([^?]*)\?php#i', $str) || preg_match('#<\?\=(\s+)#i', $str)) {
        return false;
    }
    return true;
}

/**
 * 显示指定插件内容
 * @param string $code 插件编号
 * @param string $param 插件参数
 * @return string
 */
function pluginexec($code, $param = null) {
    $info = "";
    $status = M('plugin')->where("code", $code)->value('status');
    if ($status == 1) {
        $class = \app\plugin\lib\Common::plugin_get_class($code);
        if (class_exists($class)) {
            $plugin = new $class;
            $info = $plugin->getHtml($param);
        }
    }
    echo $info;
}


/**
 * 图片水印
 * @param string $imgFile
 * @return bool
 */
function img_watermark($imgFile = '') {
    //图片不存
    if (empty($imgFile) || !is_file($imgFile)) {
        return false;
    }
    $watermark = sysConfig('watermark', null, C('imgspace.watermark.default'));
    //没开启水印功能
    if (empty($watermark) || empty($watermark['is_enable'])) {
        return false;
    }

    $Image = \think\Image::open($imgFile);
    //过滤不符合条件的图片
    if ($Image->type() == 'ico' || $Image->width() < $watermark['min_width'] || $Image->height() < $watermark['min_height']) {
        return false;
    }
    //文本水印
    if ($watermark['type'] == 'text') {
        $ttf = C('imgspace.watermark.font_file');
        if (!is_file($ttf)) return false;
        $size = is_numeric($watermark['text_size']) ? $watermark['text_size'] : 30;
        $color = $watermark['text_color'] ?: '#000000';
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = '#000000';
        }
        $opacity = intval((100 - $watermark['opacity']) * (127 / 100));
        $color .= dechex($opacity);
        $watermark['text'] = mb_convert_encoding($watermark['text'], 'HTML-ENTITIES', 'UTF-8');
        $Image->text($watermark['text'], $ttf, $size, $color, $watermark['locate'])->save($imgFile);
        return true;
    }

    //图片水印
    $img = $watermark['img'];
    if (preg_match('/\/public\/(.*)$/i', '/' . $img, $m)) {
        $img = $m[1];
    }
    $img = public_path() . ltrim($img, '/');
    if (!is_file($img)) {
        return false;
    }
    $info = @getimagesize($img);
    $imgW = !empty($info[0]) ? $info[0] : 1000000;
    $imgH = !empty($info[1]) ? $info[1] : 1000000;
    //水印图太大
    if ($Image->width() < $imgW || $Image->height() < $imgH) {
        return false;
    }

    $quality = $watermark['img_quality'] ? $watermark['img_quality'] : 80;
    $tempPath = dirname($img) . '/temp_' . basename($img);
    \think\Image::open($img)->save($tempPath, null, $quality);//处理水印图片质量

    $Image->water($tempPath, $watermark['locate'], $watermark['opacity'])->save($imgFile);
    @unlink($tempPath);//删除临时图片

    return true;
}

//远程图片本地化
function remoteimg_tolocal($content, $img = '', $isWatermark = true) {
    $imgList = array();
    if ($img) {
        $imgList[] = $img;
        $content = $img;
    } else {
        preg_match_all("/src=[\"|'|\s]([^\"|^\'|^\s]*?)/isU", $content, $imgList);
        $imgList = array_unique($imgList[1]);
    }

    $filesystem = \think\facade\Filesystem::class;
    $config = $filesystem::getDiskConfig($filesystem::getDefaultDriver());
    $imgdir = DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
    $filedir = $config['root'] . $imgdir;
    $urldir = $config['url'] . $imgdir;
    $urldir = str_replace('\\', '/', $urldir);
    is_dir($filedir) or mkdir($filedir, 0777, true);

    $basedomain = request()->domain();
    foreach ($imgList as $imgurl) {
        $imgurl = trim($imgurl);

        // 本站图片
        if (preg_match("#" . $basedomain . "#i", $imgurl)) {
            continue;
        }
        if (strpos($imgurl, '//') === 0)
            $imgurl = 'https:' . $imgurl;
        // 不是合法链接
        if (!preg_match("#^http(s?):\/\/#i", $imgurl)) {
            continue;
        }

        $heads = @get_headers($imgurl, 1);

        // 获取请求头并检测死链
        if (empty($heads)) {
            continue;
        } else if (!(stristr($heads[0], "200") && !stristr($heads[0], "304"))) {
            continue;
        }
        // 图片扩展名
        $fileType = substr($heads['Content-Type'], -4, 4);
        if (!preg_match("#\.(jpg|jpeg|gif|png|ico|bmp|webp|svg)#i", $fileType)) {
            if ($fileType == 'image/gif') {
                $fileType = ".gif";
            } else if ($fileType == 'image/png') {
                $fileType = ".png";
            } else if ($fileType == 'image/x-icon') {
                $fileType = ".ico";
            } else if ($fileType == 'image/bmp') {
                $fileType = ".bmp";
            } else if ($fileType == 'image/webp') {
                $fileType = ".webp";
            } else if ($heads['Content-Type'] == 'image/svg+xml') {
                $fileType = ".svg";
            } else {
                $fileType = '.jpg';
            }
        }
        $fileType = strtolower($fileType);

        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(array('http' => array('follow_location' => false)));
        readfile($imgurl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();
        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgurl, $m);

        $file = [];
        $file['oriName'] = $m ? $m[1] : "";
        $file['filesize'] = strlen($img);
        $file['ext'] = $fileType;
        $file['name'] = md5((string)microtime(true)) . $file['ext'];
        $file['fullName'] = $filedir . $file['name'];
        $fullName = $file['fullName'];

        //检查文件大小是否超出限制
        if ($file['filesize'] >= 20480000) {
            continue;
        }
        //移动文件
        if (!(file_put_contents($fullName, $img) && is_file($fullName))) { //移动失败
            continue;
        }
        $isWatermark and img_watermark($fullName);//图片加水印
        $content = str_replace($imgurl, $urldir . $file['name'], $content);
    }
    return $content;
}