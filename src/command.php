<?php
/**
 * 参考think-swoole2.0开发
 * author:xavier
 * email:499873958@qq.com
 */
if (!defined("APP_PATH")){
    define('APP_PATH', __DIR__ . '/../application/');
}


// 注册命令行指令
\think\Console::addDefaultCommands([
    'swoole'        => '\\xavier\\swoole\\command\\Swoole',
    'swoole:server' => '\\xavier\\swoole\\command\\Server',
]);
