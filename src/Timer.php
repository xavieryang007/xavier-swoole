<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/15
 * Time: 上午11:59
 */

namespace xavier\swoole;

use Swoole\Timer as SwooleTimer;

/**
 * Class Timer
 * 可以执行回调函数，同时可以执行定时器模板
 * @package xavier\swoole
 */
use think\Config;
use think\Cache;
class Timer
{
    private static $instance = null;
    private $config=[];
    public function __construct()
    {
        $this->config=Config::get('timer');
        if (empty($this->config)){
            throw new \think\Exception("timer setting file is not exits");
        }
    }

    public static function instance()
    {
        if (is_null(self::$instance)){
            self::$instance=new static();
            return self::$instance;
        }
        return self::$instance;
    }

    public function run($serv)
    {
        foreach ($this->config as $key=>$val){
            foreach ($val as $k=>$v){

            }
        }
    }

    public function checkrun()
    {

    }

    public function syncTask($class)
    {
        if (is_string($class)&&class_exists($class)){
            Task::async(function()use($class){
                $obj=new $class();
                $obj->run();
                unset($obj);
            });
        }
    }

    public static function tick(int $time, $callback)
    {
        if ($callback instanceof \Closure) {
            return SwooleTimer::tick($time, $callback);
        } else if (is_object($callback) && method_exists($callback, 'run')) {
            return SwooleTimer::tick($time, function () use ($callback) {
                $callback->run();
            });
        }
        return false;

    }

    public static function after(int $time, $callback)
    {
        if ($callback instanceof \Closure) {
            return SwooleTimer::after($time, $callback);
        } else if (is_object($callback) && method_exists($callback, 'run')) {
            return SwooleTimer::after($time, function () use ($callback) {
                $callback->run();
                unset($callback);
            });
        }
        return false;
    }

    public static function clear(int $timer_id)
    {
        return SwooleTimer::clear($timer_id);
    }
}