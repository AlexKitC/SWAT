<?php
/**
 * 开发环境配置
 */

return [
    'host'          => '0.0.0.0',
    'port'          => 9501,
    'daemonize'     => 0,
    'worker_num'    => 2,

    'mysql' => [
        'host'      => 'localhost',
        'user'      => 'alex',
        'pass'      => 'root',
        'port'      => 3306,
        'database'  => 'mysql',
        'timeout'   => 3,
        
        'min'       => 2,
        'max'       => 4,
        'freeTime'  => 1800
    ]
];