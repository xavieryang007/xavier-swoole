<?php
/**
 * 参考think-swoole2.0开发
 * author:xavier
 *  email:499873958@qq.com
 */
namespace xavier\swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;
use think\App;
use think\Error;
use think\exception\HttpException;
use think\Request as thinkRequest;

/**
 * Swoole应用对象
 */
class Application extends App
{
    private static $swoole=null;
    /**
     * 处理Swoole请求
     * @access public
     * @param  \Swoole\Http\Request $request
     * @param  \Swoole\Http\Response $response
     * @param  void
     */
    public function swoole(Request $request, Response $response)
    {
        try {
            thinkRequest::deletethis();
            ob_start();

            // 重置应用的开始时间和内存占用
            $this->beginTime = microtime(true);
            $this->beginMem  = memory_get_usage();


            $_COOKIE = $request->cookie ?: [];//array_change_key_case($request->cookie?:[], CASE_UPPER);
            $_GET    = $request->get ?: [];//array_change_key_case($request->get?:[], CASE_UPPER) ;
            $_POST   = $request->post ?: [];//array_change_key_case($request->post?:[], CASE_UPPER);
            $_COOKIE = $request->cookie ?: [];//array_change_key_case($request->cookie?:[], CASE_UPPER);
            $_FILES  = $request->files ?: [];//array_change_key_case($request->files?:[], CASE_UPPER);
            $_SERVER = array_change_key_case($request->server ?: [], CASE_UPPER);


            $_SERVER['HTTP_HOST'] = '127.0.0.1';
            $_SERVER["PATH_INFO"] = $_SERVER["PATH_INFO"] ?: '/';

            $_SERVER['argv'][1] = $_SERVER["PATH_INFO"];
            $resp               = $this->run();
            $resp->send();
            $content = ob_get_clean();
            $status  = $resp->getCode();


            // 发送状态码
            $response->status($status);

            // 发送Header
            foreach ($resp->getHeader() as $key => $val) {
                $response->header($key, $val);
            }
            $response->end($content);
        } catch (HttpException $e) {
            $this->exception($response, $e);
        } catch (\Exception $e) {
            $this->exception($response, $e);
        } catch (\Throwable $e) {
            $this->exception($response, $e);
        }
    }

    protected function exception($response, $e)
    {
        if ($e instanceof \Exception) {
            $handler = Error::getExceptionHandler();
            $handler->report($e);

            $resp    = $handler->render($e);
            $content = $resp->getContent();
            $code    = $resp->getCode();

            $response->status($code);
            $response->end($content);
        } else {
            $response->status(500);
            $response->end($e->getMessage());
        }

        throw $e;
    }
    public function setSwoole($swoole)
    {
        self::$swoole=$swoole;
    }
    public static function getSwoole()
    {
        return self::$swoole;
    }
}
