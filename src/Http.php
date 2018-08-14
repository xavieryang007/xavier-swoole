<?php
/**
 * 参考think-swoole2.0开发
 * author:xavier
 *  email:499873958@qq.com
 */
namespace xavier\swoole;

use Swoole\Http\Server as HttpServer;
use Swoole\Table;
use think\Facade;
use think\Loader;
use think\App;
use think\Error;
use think\exception\HttpException;
use think\Response;
use think\Request;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
/**
 * Swoole Http Server 命令行服务类
 */
class Http extends Server
{
    protected $app;
    protected $appPath;
    protected $table;
    protected $monitor;
    protected $lastMtime;
    protected $fieldType = [
        'int'    => Table::TYPE_INT,
        'string' => Table::TYPE_STRING,
        'float'  => Table::TYPE_FLOAT,
    ];

    protected $fieldSize = [
        Table::TYPE_INT    => 4,
        Table::TYPE_STRING => 32,
        Table::TYPE_FLOAT  => 8,
    ];

    /**
     * 架构函数
     * @access public
     */
    public function __construct($host, $port, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP)
    {
        $this->swoole = new HttpServer($host, $port, $mode, $sockType);
    }

    public function getSwoole()
    {
        return $this->swoole;
    }

    public function setAppPath($path)
    {
        $this->appPath = $path;
    }

    public function setMonitor($interval = 2, $path = [])
    {
        $this->monitor['interval'] = $interval;
        $this->monitor['path']     = (array)$path;
    }

    public function table(array $option)
    {
        $size        = !empty($option['size']) ? $option['size'] : 1024;
        $this->table = new Table($size);

        foreach ($option['column'] as $field => $type) {
            $length = null;

            if (is_array($type)) {
                list($type, $length) = $type;
            }

            if (isset($this->fieldType[$type])) {
                $type = $this->fieldType[$type];
            }

            $this->table->column($field, $type, isset($length) ? $length : $this->fieldSize[$type]);
        }

        $this->table->create();
    }

    public function option(array $option)
    {
        // 设置参数
        if (!empty($option)) {
            $this->swoole->set($option);
        }

        foreach ($this->event as $event) {
            // 自定义回调
            if (!empty($option[$event])) {
                $this->swoole->on($event, $option[$event]);
            } elseif (method_exists($this, 'on' . $event)) {
                $this->swoole->on($event, [$this, 'on' . $event]);
            }
        }
    }

    /**
     * 此事件在Worker进程/Task进程启动时发生,这里创建的对象可以在进程生命周期内使用
     *
     * @param $server
     * @param $worker_id
     */
    public function onWorkerStart($server, $worker_id)
    {
        // 应用实例化
        $this->app       = new Application($this->appPath);
        $this->app->setSwoole($this->swoole);
        $this->lastMtime = time();

        if ($this->table) {
            $this->app['swoole_table'] = $this->table;
        }

//        // 指定日志类驱动
//        Loader::addClassMap([
//            'think\\log\\driver\\File' => __DIR__ . '/log/File.php',
//        ]);


        if (0 == $worker_id && $this->monitor) {
            $this->monitor($server);
        }
    }

    /**
     * 文件监控
     *
     * @param $server
     */
    protected function monitor($server)
    {
        $paths = $this->monitor['path'] ?: [APP_PATH];
        $timer = $this->monitor['interval'] ?: 2;

        $server->tick($timer, function () use ($paths, $server) {
            foreach ($paths as $path) {
                $dir      = new \RecursiveDirectoryIterator($path);
                $iterator = new \RecursiveIteratorIterator($dir);

                foreach ($iterator as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) != 'php') {
                        continue;
                    }

                    if ($this->lastMtime < $file->getMTime()) {
                        $this->lastMtime = $file->getMTime();
                        echo '[update]' . $file . " reload...\n";
                        $server->reload();
                        return;
                    }
                }
            }
        });
    }

    /**
     * request回调
     * @param $request
     * @param $response
     */
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        $this->app->swoole($request,$response);

    }

    public function onTask(HttpServer $serv, $task_id, $fromWorkerId,$data)
    {
        if($data instanceof SuperClosure){
            return $data($serv,  $task_id,  $data);
        }else{
            $serv->finish($data);
        }

    }

    public function onFinish(HttpServer $serv,  $task_id,  $data)
    {
        if($data instanceof SuperClosure){
             $data($serv,  $task_id,  $data);
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
}
