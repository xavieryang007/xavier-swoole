# ThinkPHP 5.0 Swoole 扩展

参考官方think-swoole2.0和easyswoole开发基于TP5.0的swoole扩展包

增加异步任务投递
QQ499873958


```php
<?php

namespace app\index\controller;

use xavier\swoole\Task;


class Index
{
    public function index()
    {
        return "index";
    }

    public function test()
    {
        echo 1;
        $param = request()->param();
        $post  = request()->post();

        //异步任务投递
        Task::async(function ($serv, $task_id, $data) {
            $i = 0;
            while ($i < 10) {
                $i++;
                echo $i;
                sleep(1);
            }
        });
        //使用swoole4.0协程
        \go(function () {
            $i = 0;
            while ($i < 10) {
                echo 2;
                $i++;
                \co::sleep(1);
            };
        });
        return json($post);
    }
}

```


### 配置文件
第三方包的配置文件必须在application/extra下，文件名为swoole.php

```php
<?php
return [
    'host'                  => '0.0.0.0', // 监听地址
    'port'                  => 9501, // 监听端口
    'mode'                  => '', // 运行模式 默认为SWOOLE_PROCESS
    'sock_type'             => '', // sock type 默认为SWOOLE_SOCK_TCP
    'app_path'              => getcwd() . '/application', // 应用地址 如果开启了 'daemonize'=>true 必须设置（使用绝对路径）
    'file_monitor'          => false, // 是否开启PHP文件更改监控（调试模式下自动开启）
    'file_monitor_interval' => 2, // 文件变化监控检测时间间隔（秒）
    'file_monitor_path'     => [], // 文件监控目录 默认监控application和config目录
    // 可以支持swoole的所有配置参数
    'pid_file'              => getcwd()  . '/runtime/swoole.pid',
    'log_file'              => getcwd()  . '/runtime/swoole.log',
    'task_worker_num'       => 20,
    //'document_root'         => getcwd() . 'public',
    //'enable_static_handler' => true,
    'daemonize'                => 1,//守护
    'worker_num' => 8,    //worker process num
    'max_request' => 10000,
];
```


注意：\think\Request 增加如下静态方法。
由于TP运行在Apache或者NGINX下，是每次请求后都会销毁，所以这里的单例并不会造成什么问题，但是在Swoole下，由于常驻内存，所以请求单例一旦实例化则不会改变，所以这里就将其删除，每次请求后重新实例化

```php
public static function deletethis()
    {
        if (!is_null(self::$instance)) {
            self::$instance=null;
        }
    }
```

修改如下代码,由于采用命令行模式运行无论什么请求都会被认为是get

```php
/**
     * 当前的请求类型
     * @access public
     * @param bool $method true 获取原始请求类型
     * @return string
     */
    public function method($method = false)
        {
            if (true === $method) {
                // 获取原始请求类型
                $this->method = IS_CLI ?(defined('IS_SWOOLE')?((isset($this->server['REQUEST_METHOD'])? $this->server['REQUEST_METHOD'] : isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'GET')):'GET') : (isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD']);
            } elseif (!$this->method) {
                if (isset($_POST[Config::get('var_method')])) {
                    $this->method = strtoupper($_POST[Config::get('var_method')]);
                    $this->{$this->method}($_POST);
                } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                    $this->method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
                } else {
                    $this->method = IS_CLI ?(defined('IS_SWOOLE')?((isset($this->server['REQUEST_METHOD'])? $this->server['REQUEST_METHOD'] : isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'GET')):'GET') : (isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD']);
                }
            }
    
            return $this->method;
        }
```

启动命令
```sh
php think swoole start
```

守护启动
```sh
php think swoole start -d
```

停止服务

```sh
php think swoole stop
```


## Demo


```php
<?php

namespace app\index\controller;

use xavier\swoole\Task;
use xavier\swoole\Timer;

use xavier\swoole\Component\HttpClient;

class Index
{
    public function __construct()
    {

    }

    public function index()
    {
        return "indexss";
    }

    public function test()
    {
        $param = request()->param();
        $post  = request()->post();


        Task::async(function ($serv, $task_id, $data)use($post) {
            $i = 0;
            while ($i < 10) {
                $i++;
                echo $i;
                //var_dump($post);
                sleep(1);
            }
        });

        //Task::async("\\app\\lib\\Task");
        $task=new \app\lib\Task($post,1);
        Task::async($task);
        \go(function ()use($post) {
            $i = 0;
            while ($i < 10) {
                $i++;
                //var_dump($post);
                \co::sleep(1);
            };
        });
        return json($post);
    }

    public function send()
    {
        $data  = [1, 2, 3,];
        $datas = ['a', 'b1232'];
        $redt  = HttpClient::instance("http://127.0.0.1:9501")->url('index/index')->gets([
            'data'  => $data,
            'datas' => $datas,
        ]);
        var_dump($redt);
    }

    public function sendhtml()
    {
        $data  = [1, 2, 3,];
        $datas = ['a', 'b1232'];
        $redt  = HttpClient::instance("http://127.0.0.1:9501")->url('index/index')->setContentType('html')->test([
            'data'  => $data,
            'datas' => $datas,
        ]);
        var_dump($redt);
    }

    public function gets($data=null,$datas=null)
    {
        return json(array_merge($data,$datas));

    }


}


```

### 定时器

如下是定时器接口的实现

```php
<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/15
 * Time: 下午5:45
 */

namespace app\lib;

use xavier\swoole\Component\Timer as TimerC;
class Timer extends TimerC
{
    public function _initialize(...$arg)
    {
        // TODO: Implement _initialize() method.
    }

    public function run()
    {
        // TODO: Implement run() method.
        var_dump('timer');
    }
}
```


只需要在定时器配置中配置定时任务的时间

```php
<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/15
 * Time: 下午2:14
 * 秒 分 时 日 月 星期几
 * crontab 格式 * *  *  *  * *    => "类"
 * *中间一个空格
 * 系统定时任务需要在swoole.php中开启
 * 自定义定时器不受其影响
 */

return [
    '*/5 * * * * *'=>'\\app\\lib\\Timer',//每5秒执行一次，从第一位一次表示 秒，分，时，日，月
];
```

同时定时任务支持在任务进程执行

```php
<?php
use xavier\swoole\Timer;

//支持回调
Timer::tick(1000,function(){
    
});

//支持执行定时器接口实现的类
Timer::tick(1000,'\\app\\lib\\Timer');

```


不建议在任意进程随意使用定时器，建议使用系统配置的定时器，请注意自定义定时器使用和销毁

系统配置的定时器，在第一个worker创建一个定时器，根据任务是否到期需要执行来进行异步任务投递，并不是对当前进程造成阻塞
但是需要配置task_work_num


手册 https://www.kancloud.cn/xavier007/xavier_swoole
