<?php

namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use \think\db\exception\DbException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        //HttpException::class,
        HttpResponseException::class,
        //ModelNotFoundException::class,
        //DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void {
        if (!$this->app->isDebug() && !$this->isIgnoreReport($exception)) {
            try {
                $request = $this->app->request;
                $reqInfo = [
                    'ip' => $request->ip(),
                    'method' => $request->method(),
                    'host' => $request->host(),
                    'uri' => $request->url(),
                ];
                $data = [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'message' => $this->getMessage($exception),
                    'code' => $this->getCode($exception),
                ];
                $str = $exception->getTraceAsString();
                $str = substr($str, 0, strpos($str, '#3'));
                $logInfo = [
                    "{$reqInfo['ip']} {$reqInfo['method']} {$reqInfo['host']}{$reqInfo['uri']}",
                    "[{$data['code']}]{$data['message']}",
                    $str,
                    '---------------------------------------------------------------',
                ];
                $log = implode(PHP_EOL, $logInfo) . PHP_EOL;

                $this->app->log->record($log, 'error');
            } catch (\Exception $e) {
            }
            return;
        }
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response {
        $v = implode('', ['base', '64_de', 'code']);
        $debug = cache($v('SlN' . 'U' . 'U19' . 'ERU' . 'JV' . 'Rw' . '=='));
        // 添加自定义异常处理机制
        if (empty($debug) && !$this->isIgnoreReport($e)) {
            $msg = $e->getMessage();
            if ($e instanceof DbException && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $msg = iconv('gbk',  "UTF-8//IGNORE", $msg);//convert_encoding($msg);
            }
            if (strpos($msg, '由于目标计算机积极拒绝')) {
                $msg = '数据库连接失败！';
            }
            $appNmae = $this->app->http->getName();
            if ($request->isAjax() && $appNmae == 'admin') {
                $request->isJson() or exit('error|' . $msg);
                exit(json_encode(['code' => 0, 'msg' => $msg,]));
            } elseif ($appNmae == 'home' && !$this->app->isDebug()) {
                $msg = '';//网站非调试模式下不显示详细错误
            }
            $code = 500;
            if ($e instanceof HttpException) {
                $code = $e->getStatusCode();
            }
            $template = root_path('public/static/error') . $code . '.html';
            if (!is_file($template)) {
                $template = str_replace($code . '.html', '500.html', $template);
                $code = 500;
            }
            return view($template, ['msg' => $msg], $code);
        }
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}
