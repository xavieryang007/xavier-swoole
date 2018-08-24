<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/15
 * Time: 上午11:42
 */

namespace xavier\swoole\template;


abstract class Task
{
    protected $arg = null;

    public function __construct(...$arg)
    {
        $this->arg = $arg;
        $this->_initialize(...$arg);
    }

    abstract protected function _initialize(...$arg);

    abstract protected function run($serv, $task_id, $fromWorkerId);
}