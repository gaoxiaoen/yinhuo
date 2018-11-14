<?php

$CONFIG['title']        = '游戏管理后台';
$CONFIG['ver']          = '1'; //版本号，用于控制css,js相关静态资源更新
$CONFIG['rss']          = 'RSS2.0';
$CONFIG['error_level']  = ERROR_TO_FILE;
$CONFIG['realtime_make']= 0;
$CONFIG['timezone']     = 'Asia/Shanghai';
$CONFIG['cache']        = '1';
$CONFIG['gzip']         = '1';
$CONFIG['html_charset'] = 'utf-8';
$CONFIG['openWaterMark']= 1;

//多语言设置
$CONFIG['lang'] = array(
    'locale' => 'zh_TW',//'zh_CN.utf8',
    'encoding' => 'utf-8', //语言文件编码
);

//程序名称
$CONFIG['app_name'] = 'cl';
//加密密钥
$CONFIG['secret_key'] = 'cl168(#%@#+L:JGS"F#TGSDGVS';
//数据库配置
$CONFIG['db'] = array(
    'host' => '127.0.0.1',
    'user' => 'root',
    'pwd' => '123456',
    'charset' => 'utf8',
    'pconnect' => '0',
    'type' => 'mysql',
    'db_admin' => 'adm',
    'db_game'  => 'czjy',
    'prefix' => 'smp_'
);


//$CONFIG['db'] = array(
//  'host' => '120.92.182.18',
//  'user' => 'mysqldev',
//  'pwd' => 'h5Poj#devl',
//  'charset' => 'utf8',
//  'pconnect' => '0',
//  'type' => 'mysql',
//  'db_admin' => 'adm',
//  'db_game'  => 'h5_dev',
//  'prefix' => 'smp_'
//);

//游戏服务器ip端口配置
$CONFIG['game'] = array(
    'ip' => '127.0.0.1',
    'port' => 8001,
    'tick' => '3e1f8f56ad582a7e76f8ef8adef0a54c',
    'sn' => 20001,
    'opentime' => 1448603546
);

$CONFIG['title'] = $CONFIG['game']['sn'] . '服游戏管理后台';

//中央服
$CONFIG['center'] = array(
    'api'=>'http://127.0.0.1:901'
);

//session缓存方式，仅限DB, Memcache, Redis，留空则使用php默认缓存方式
$CONFIG['sess_cache_driver'] = '';
$CONFIG['sess_timeout'] = 6000;
//缓存引擎, Memcache, File, Var, Redis
$CONFIG['cache_driver'] = 'Var';
$CONFIG['cache_expire'] = 3600;


$CONFIG['memcache'] = array(
    'host' => '127.0.0.1',
    'port' => '11211',
);

$CONFIG['redis'] = array(
    'host' => '127.0.0.1',
    'port' => '6379',
);
//测试环境下需要开启
$CONFIG['debug'] = true;
//是否开发服
$CONFIG['dev'] = true;