<?php
/**
 * 参考think-swoole2.0开发
 * author:xavier
 * email:49987958@qq.com
 */

namespace xavier\swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;
use think\App;
use think\Error;
use think\exception\HttpException;
use think\Request as thinkRequest;
use think\Config;
use think\Cache;

/**
 * Swoole应用对象
 */
class Application extends App
{
    private static $swoole = null;

    /**
     * 处理Swoole请求
     * @access public
     * @param  \Swoole\Http\Request $request
     * @param  \Swoole\Http\Response $response
     * @param  void
     */
    public function swooleHttp(Request $request, Response $response)
    {
        try {
            thinkRequest::destroy();
            ob_start();
            // 重置应用的开始时间和内存占用
            $this->beginTime = microtime(true);
            $this->beginMem  = memory_get_usage();

            $_COOKIE = $request->cookie ?: [];
            $_GET    = $request->get ?: [];
            $_POST   = $request->post ?: [];
            $_FILES  = $request->files ?: [];
            $_SERVER = array_change_key_case($request->server ?: [], CASE_UPPER);

            $_SERVER['HTTP_HOST'] = Config::get('app_host') ? Config::get('app_host') : "127.0.0.1";
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

    public function swooleWebSocket($server,$frame)
    {
        try {
            thinkRequest::destroy();
            WebSocketFrame::destroy();
            $request=$frame->data;
            $request=json_decode($request,true);
            $debugclient=Config::get('swoole.debug_client');
            if ($debugclient){
                $debug_client_key=Config::get('swoole.debug_client_key');
                $fd=Cache::get($debug_client_key);
                if ($fd){
                    $server->push($fd,$frame->data);
                }
            }
            // 重置应用的开始时间和内存占用
            $this->beginTime = microtime(true);
            $this->beginMem  = memory_get_usage();
            WebSocketFrame::getInstance($server,$frame);
            $_COOKIE = isset($request['arguments']['cookie'])?$request['arguments']['cookie']:[];
            $_GET    =  isset($request['arguments']['get'])?$request['arguments']['get']:[];
            $_POST   = isset($request['arguments']['post'])?$request['arguments']['post']:[];
            $_FILES  =  isset($request['arguments']['files'])?$request['arguments']['files']:[];
            $_SERVER["PATH_INFO"] = $request['url'] ?: '/';
            $_SERVER["REQUEST_URI"] = $request['url'] ?: '/';
            $_SERVER["SERVER_PROTOCOL"] = 'http';
            $_SERVER["REQUEST_METHOD"]  = 'post';

            $_SERVER['HTTP_HOST'] = Config::get('app_host') ? Config::get('app_host') : "127.0.0.1";
            $_SERVER['argv'][1] = $_SERVER["PATH_INFO"];
            $resp               = $this->run();            
        } catch (HttpException $e) {
            $this->webSocketException($server, $frame,$e);
        } catch (\Exception $e) {
            $this->webSocketException($server,$frame ,$e);
        } catch (\Throwable $e) {
            $this->webSocketException($server, $frame,$e);
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

    protected function webSocketException($server, $frame,$e)
    {
        $response=[
            'code'=>500,
            'content'=>$e->getMessage()
        ];
        $server->push($frame->fd,json_encode($response));

        throw $e;
    }

    public function setSwoole($swoole)
    {
        self::$swoole = $swoole;
    }

    public static function getSwoole()
    {
        return self::$swoole;
    }
}
