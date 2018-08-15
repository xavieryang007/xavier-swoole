<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/15
 * Time: 下午12:18
 */

namespace xavier\swoole\Component;

use xavier\swoole\Application;
/**
 * Class Timer
 * 定时器抽象类
 * @package xavier\swoole\Component
 */
abstract class Timer
{
    protected $arg = null;
    protected $lock=false;

    public function __construct(...$arg)
    {
        $key='timer'.static::class;
        if (Application::getSwoole()->getTable()->exist($key)){
            $this->lock=true;
        }else{
            Application::getSwoole()->getTable()->set($key,true);
        }
        $this->arg = $arg;
        $this->_initialize(...$arg);

    }

    abstract protected function _initialize(...$arg);

    abstract protected function run();

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $key='timer'.static::class;
        if (Application::getSwoole()->getTable()->exist($key)){
            Application::getSwoole()->getTable()->del($key);
        }
    }
}