<?php

// 本地数据库
switch ($_SERVER['SERVER_NAME']) {
    case 'www.hawkaoe.net':
    // 运行在官方服务器
    define('DB_NAME'    , 'amt');
    define('DB_CHARSET' , 'utf8');
    define('DB_HOST'    , '121.199.22.226:5899');
    define('DB_USERNAME', '');
    define('DB_PASSWORD', '');
    break;
    
    case 'localhost':
    // 运行在独立服务器
    define('DB_NAME'    , 'ssrc');
    define('DB_CHARSET' , 'utf8');
    define('DB_HOST'    , '121.42.152.168');
    define('DB_USERNAME', '');
    define('DB_PASSWORD', '');
    break;
    
    default:
    // 本地调试
    define('DB_NAME'    , 'ssrc');
    define('DB_CHARSET' , 'utf8');
    define('DB_HOST'    , '127.0.0.1');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', 'root');
    break;
}

// 论坛数据库，只读权限
define('BBS_DB_NAME'    , '///');
define('BBS_DB_CHARSET' , 'gbk');
define('BBS_DB_HOST'    , '///');
define('BBS_DB_USERNAME', '///');
define('BBS_DB_PASSWORD', '///');
