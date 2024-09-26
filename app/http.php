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

namespace app;


class http
{

    /**
     * 支持类型
     * @var string
     */
    private $support = 'file_get_contents';

    public function __construct() {
        $support = cache('HTTP_SUPPORT');
        if ($support) {
            $this->support = $support;
            return;
        }
        $url = U('/rrz/state', [], 'asp', true);
        if (function_exists('curl_init')) {
            $rs = $this->curl($url);
            if ($rs && $rs == 'passing') {
                $this->support = 'curl';
            }
        }
        if ($this->support == 'file_get_contents' && !ini_get('allow_url_fopen')) {
            $this->support = '';
        }
        cache('HTTP_SUPPORT', $this->support);
    }

    /**
     * 发送http请求
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public function send($url = '', $options = []) {
        if (!$this->support || !method_exists($this, $this->support)) {
            return false;
        }
        return $this->{$this->support}($url, $options);
    }

    /**
     * file_get_contents 实现
     * @param string $url
     * @param array $options
     * @return mixed
     */
    private function file_get_contents($url = '', $options = []) {

        $options = $this->getOptions($url, $options, $dataType);
        $headers = $options[CURLOPT_HTTPHEADER];
        $options[CURLOPT_COOKIE] and $headers[] = 'Cookie:' . $options[CURLOPT_COOKIE];
        $options[CURLOPT_REFERER] and $headers[] = 'Referer:' . $options[CURLOPT_REFERER];

        $options = [
            'http' => [
                'method' => $options[CURLOPT_CUSTOMREQUEST],//请求类型
                'header' => $headers,//请求头
                'user_agent' => $options[CURLOPT_USERAGENT],//
                'content' => $options[CURLOPT_POSTFIELDS],//post 或 put 内容
                'timeout' => $options[CURLOPT_TIMEOUT], // 超时时间（单位:s）
                'follow_location' => (int)$options[CURLOPT_FOLLOWLOCATION],//跟随 Location header 的重定向。设置为 0 以禁用
                'max_redirects' => $options[CURLOPT_MAXREDIRS],//跟随重定向的最大次数。值为 1 或更少则意味不跟随重定向
            ],
        ];
        try {
            $context = stream_context_create($options);
            $res = file_get_contents($url, false, $context);
        } catch (\Exception $e) {
            return false;
        }
        return $this->response($res, $dataType);
    }

    /**
     * curl 实现
     * @param string $url
     * @param array $options
     * @return mixed
     */
    private function curl($url = '', $options = []) {

        $options = $this->getOptions($url, $options, $dataType);

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        curl_close($ch);
        return $this->response($res, $dataType);
    }

    /**
     * 处理返回信息
     * @param $res
     * @param string $dataType
     * @return mixed
     */
    private function response($res, $dataType = 'text') {
        if ($dataType == 'json') {
            $res = $res ? json_decode($res, true) : $res;
        } elseif ($dataType instanceof \Closure || function_exists($dataType)) {
            $res = $dataType($res);
        }
        return $res;
    }

    /**
     * 获取请求信息
     * @param string $url
     * @param array $options
     * @param string $dataType
     * @return array
     */
    private function getOptions(&$url = '', $options = [], &$dataType = 'text') {
        if (is_array($url)) {
            $options = $url;
            $url = null;
        }
        $options = $options ?: [];
        $url = $url ?: $options['url'];
        $method = strtoupper($options['method'] ?? ($options['type'] ?? 'GET')) ?: 'GET';
        $data = $options['data'] ?? '';
        $data = is_array($data) ? http_build_query($data) : $data;

        $dataType = $options['dataType'] ?? 'text';

        if ($method == 'GET' && $data) {
            $url .= (strpos($url, '?') ? '&' : '?') . $data;
            $data = '';
        }
        $config = [
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => 15,//在发起连接前等待的时间，如果设置为0，则无限等待
            CURLOPT_TIMEOUT => $options['timeout'] ?? 10,//设置cURL允许执行的最长秒数
            CURLOPT_USERAGENT => $options['userAgent'] ?? 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0',
            CURLOPT_REFERER => $options['referer'] ?? '',
            CURLOPT_COOKIE => $options['cookie'] ?? '',//cookie
            CURLOPT_HTTPHEADER => $options['headers'] ?? [],//请求头
            CURLOPT_RETURNTRANSFER => true,// true 将curl_exec()获取的信息以字符串返回，而不是直接输出。
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POST => $method == 'POST',//是否post 请求
            CURLOPT_POSTFIELDS => $data,//post 请求数据
            CURLOPT_SSL_VERIFYPEER => false,//https请求 不验证证书和hosts
            CURLOPT_SSL_VERIFYHOST => false,//不从证书中检查SSL加密算法是否存在
            CURLOPT_FOLLOWLOCATION => false,//跟踪301,302
            CURLOPT_MAXREDIRS => 2,//指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的
            CURLOPT_AUTOREFERER => true,//TRUE 时将根据 Location: 重定向时，自动设置 header 中的Referer:信息
            CURLINFO_HEADER_OUT => true,//TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
        ];


        if (ini_get('open_basedir') == '' && !ini_get('safe_mode')) {
            $config[CURLOPT_FOLLOWLOCATION] = true;//跟踪301,302
        }

        return $config;
    }

}