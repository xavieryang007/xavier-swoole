<?php
/**
 * 参考think-swoole2.0开发
 * author:xavier
 *  email:499873958@qq.com
 */

namespace xavier\swoole;

use SuperClosure\Serializer;

class SuperClosure
{
    private $closure;
    private $serialized;

    function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    final public function __sleep()
    {
        $serializer = new Serializer();
        $this->serialized = $serializer->serialize($this->closure);
        unset($this->closure);
        return ['serialized'];
    }

    final public function __wakeup()
    {
        $serializer = new Serializer();
        $this->closure = $serializer->unserialize($this->serialized);
    }

    final public function __invoke()
    {
        // TODO: Implement __invoke() method.
        $args = func_get_args();
        return Invoker::callUserFuncArray($this->closure,$args);
    }

    final function call(...$args)
    {
        return Invoker::callUserFuncArray($this->closure,$args);
    }
}