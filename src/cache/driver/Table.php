<?php

namespace think\cache\driver;

use think\cache\Driver;
use xavier\swoole\Http;

/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/9/1
 * Time: 上午11:36
 * Email:499873958@qq.com
 */
class Table extends Driver
{
    protected $options = [
        'expire'    => 0,
        'prefix'    => '',
        'serialize' => true,
    ];

    public function __construct($options = [])
    {
        $swoole        = Http::getHttp();
        $this->handler = $swoole->getCacheTable();
    }

    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }

        if ($this->tag && !$this->has($name)) {
            $first = true;
        }

        $key    = $this->getCacheKey($name);
        $expire = $this->getExpireTime($expire);

        $value = is_scalar($value) ? $value : 'think_serialize:' . serialize($value);

        if ($expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
        }

        isset($first) && $this->setTagItem($key);

        return $result;
    }

    protected function getExpireTime($expire)
    {
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }

        return $expire;
    }

    public function dec($name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) - $step;
            $expire = $this->expire;
        } else {
            $value  = -$step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }

    public function clear($tag = null)
    {

        return $this->handler->clear();
    }

    public function get($name, $default = false)
    {

        $value = $this->handler->get($this->getCacheKey($name));

        if (is_null($value) || false === $value) {
            return $default;
        }

        try {
            $result = 0 === strpos($value, 'think_serialize:') ? unserialize(substr($value, 16)) : $value;
        } catch (\Exception $e) {
            $result = $default;
        }
        return $result;
    }

    public function has($name)
    {
        return $this->handler->exists($this->getCacheKey($name));
    }

    public function rm($name)
    {
        return $this->handler->del($this->getCacheKey($name));
    }

    public function inc($name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) + $step;
            $expire = $this->expire;
        } else {
            $value  = $step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }
}