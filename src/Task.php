<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/14
 * Time: 下午4:09
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