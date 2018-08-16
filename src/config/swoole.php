<?php
return [
    'host'                  => '0.0.0.0', // 监听地址
    'port'                  => 9501, // 监听端口
    'mode'                  => '', // 运行模式 默认为SWOOLE_PROCESS
    'sock_type'             => '', // sock type 默认为SWOOLE_SOCK_TCP
    'app_path'              => getcwd() . '/application', // 应用地址 如果开启了 'daemonize'=>true 必须设置（使用绝对路径）
    'file_monitor'          => true, // 是否开启PHP文件更改监控（调试模式下自动开启）
    'file_monitor_interval' => 2, // 文件变化监控检测时间间隔（秒）
    'file_monitor_path'     => [], // 文件监控目录 默认监控application和config目录
    // 可以支持swoole的所有配置参数
    'pid_file'              => getcwd() . '/runtime/swoole.pid',
    'log_file'              => getcwd() . '/runtime/swoole.log',
    'task_worker_num'       => 20,
    //'document_root'         => getcwd() . 'public',
    //'enable_static_handler' => true,
    //'daemonize'                => true,
    'worker_num'            => 8,    //worker process num
    'max_request'           => 10000,
    'timer'                 => true,//开启系统定时器
    'interval'              => 500,//开启系统定时器
];