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
 * 模版普通变量输出过滤
 * @param $text
 * @return string
 */
function view_filter($text) {
    if (strpos($text, '/view/count.asp?')) return $text;
    return htmlentities($text);
}

/**
 * 语言文本
 * @param string $name
 * @param array $vars
 * @param string $lang
 * @return string
 */
function langText(string $name = null, array $vars = [], string $lang = 'cn') {
    $app = app();
    if (!$app->lang->get(null, [], $lang)) {
        $files = glob($app->getAppPath() . 'lang' . DIRECTORY_SEPARATOR . $lang . '.*');
        $app->lang->load($files, $lang);
    }
    return $app->lang->get($name, $vars, $lang);
}

//获取英文
function enLang($text = '', $lang = 'en') {
    return langText($text, [], $lang);
}

/**
 * 语言文本
 * @param string $text
 * @param array $vars
 * @param string $lang
 * @return string
 */
function __text($text = '', $vars = [], $lang = '') {
    $lang = $lang ?: app()->lang->getLangSet();
    return langText($text, $vars, $lang);
}

/**
 * 语言文本
 * @param string $text
 * @param array $vars
 * @param string $lang
 * @return string
 */
function __($text = '', $vars = [], $lang = '') {
    if (is_string($vars)) {
        return __text($text, [], $vars);
    }
    return __text($text, $vars, $lang);
}