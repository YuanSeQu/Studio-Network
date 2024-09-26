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

namespace app\home\middleware;

use think\middleware\CheckRequestCache as base;
use think\Request;
use think\Response;
use Closure;

/**
 * Class CheckRequestCache
 * @package app\home\middleware
 */
class CheckRequestCache extends base
{

    /**
     * 设置当前地址的请求缓存
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param mixed $cache
     * @return Response
     */
    public function handle($request, Closure $next, $cache = null) {
        $browser = sysConfig('webcache.browser', null, 1);//客户端缓存
        $html = sysConfig('webcache.html', null, 1);//静态页缓存
        if ($request->isGet() && false !== $cache) {
            if (false === $this->config['request_cache_key']) {
                // 关闭当前缓存
                $cache = false;
            }

            try {
                $cache = $cache ?? $this->getRequestCache($request);

                if ($cache) {
                    if (is_array($cache)) {
                        [$key, $expire, $tag] = array_pad($cache, 3, null);
                    } else {
                        $key = md5($request->url(true));
                        $expire = $cache;
                        $tag = null;
                    }

                    $key = $this->parseCacheKey($request, $key);
                    if ($browser && $key && $this->cache->has($key) && strtotime($request->server('HTTP_IF_MODIFIED_SINCE', '')) + $expire > $request->server('REQUEST_TIME')) {
                        // 读取缓存
                        return Response::create()->code(304);
                    } elseif ($html && $key && ($hit = $this->cache->get($key)) !== null) {
                        [$content, $header, $when] = $hit;
                        return Response::create($content)->header($header);
                        //chuan 注释掉，只要存在缓存就返回缓存
//                        if (null === $expire || $when + $expire > $request->server('REQUEST_TIME')) {
//                            return Response::create($content)->header($header);
//                        }
                    }
                }

            } catch (\Throwable | \Exception $e) {
                clearCache(['cache', 'log']);
            }
        }

        $response = $next($request);

        if ($html && isset($key) && $key && 200 == $response->getCode() && $response->isAllowCache()) {
            $header = $response->getHeader();
            $header['Cache-Control'] = 'max-age=' . $expire . ',must-revalidate';
            $header['Last-Modified'] = gmdate('D, d M Y H:i:s') . ' GMT';
            $header['Expires'] = gmdate('D, d M Y H:i:s', time() + $expire) . ' GMT';
            try {
                $this->cache->tag($tag)->set($key, [$response->getContent(), $header, time()], $expire);
            } catch (\Throwable | \Exception $e) {
                clearCache(['cache', 'log']);//可能空间满了写不进去缓存了
            }
        }

        return $response;
    }

    /**
     * 读取当前地址的请求缓存信息
     * @access protected
     * @param Request $request
     * @return mixed
     */
    protected function getRequestCache($request) {
        $key = $this->config['request_cache_key'];
        $expire = $this->config['request_cache_expire'];
        $except = $this->config['request_cache_except'];
        $tag = $this->config['request_cache_tag'];

        foreach ($except as $rule) {
            if (stripos($request->url(), $rule) !== false) {
                return;
            }
        }

        return [$key, $expire, $tag];
    }

    /**
     * 读取当前地址的请求缓存信息
     * @access protected
     * @param Request $request
     * @param mixed $key
     * @return null|string
     */
    protected function parseCacheKey($request, $key) {
        $key = parent::parseCacheKey($request, $key);
        if (!$key) return $key;

        $url = $request->url();

        if (strpos($url, '?') && strpos($url, 'filter')) {
            return false;
        }

        $url = preg_replace(['/^\/index\.php/i', '/\?.*$/i'], '', $url);
        $url = $url ?: '/';

        $key = md5($url);
        //区分手机端缓存
        if ($request->isMobile() && is_dir(public_path('template/mobile'))) {
            $key = 'Mobile:' . $key;
        }
        return $key;
    }

}