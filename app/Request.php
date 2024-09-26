<?php

namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public function isSsl(): bool {
        $isSsl = parent::isSsl();
        try {
            if (!$isSsl && sysConfig('admin.is_https')) {
                $isSsl = true;
            }
        } catch (\Throwable | \Exception $e) {
        }
        return $isSsl;
    }

    /**
     * 获取当前请求URL的pathinfo信息（含URL后缀）
     * @access public
     * @return string
     */
    public function pathinfo(): string {
        $url = $this->url();
        $path = $_SERVER['PATH_INFO'] ?? '';
        if (preg_match('/^(.*)\/index\.php$/i', $path, $m) && strpos($url, $path) === false) {
            $_SERVER['PATH_INFO'] = $m[1];
            $this->server = $_SERVER;
        }
        return parent::pathinfo();
    }
}
