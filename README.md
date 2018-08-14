# ThinkPHP 5.0 Swoole 扩展

参考官方think-swoole2.0开发基于TP5.0的swoole扩展包

增加异步任务投递



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


