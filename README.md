# ThinkPHP 5.0 Swoole 扩展

参考官方think-swoole2.0开发基于TP5.0的swoole扩展包

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
    'pid_file'              => APP_PATH . 'swoole.pid',
    'log_file'              => APP_PATH . 'swoole.log',
    'task_worker_num'       => 20,
    //'document_root'         => getcwd() . 'public', //是否开启静态文件支持需要同时开启enable_static_handler
    //'enable_static_handler' => true,
];
```