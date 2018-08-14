<?php
/**
 * 参考think-swoole2.0开发
 * author:xavier
 *  email:499873958@qq.com
 */

namespace xavier\swoole;


class Task
{
    public static function async($task,$finishCallback = null,$taskWorkerId = -1)
    {
        if($task instanceof \Closure){
            try{
                $task = new SuperClosure($task);
            }catch (\Throwable $throwable){
                Trigger::throwable($throwable);
                return false;
            }
        }

        Application::getSwoole()->task($task,$taskWorkerId,$finishCallback);
    }
}