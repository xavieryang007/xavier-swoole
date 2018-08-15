<?php
/**
 * 参考think-swoole2.0开发
 * author:xavier
 * email:499873958@qq.com
 */
if (!defined("APP_PATH")){
    define('APP_PATH', __DIR__ . '/../application/');
}

if (!defined("IS_SWOOLE")){
    define('IS_SWOOLE',true);
}
// 注册命令行指令
\think\Console::addDefaultCommands([
    '\\xavier\\swoole\\command\\Swoole',
]);
