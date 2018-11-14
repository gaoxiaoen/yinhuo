<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/7
 * Time: 下午3:13
 * 活动配置文件
 */

//开发服dev/稳定服stable
define('VERSION', 'dev');

define('ROOT',              str_replace('\\', '/', realpath(dirname(__FILE__))));
define('SERVER_DIR',        'G:\newpoj\activity\\');  //游戏服活动data目录
define('JEC',               ROOT.'/../../../../../Jec/');
define('DATA',               'G:\newpoj\activity\\'); //基础数据目录

define('UPSVN_URL',          'http://127.0.0.1:8888/act/?act=upactivity');
define('CMSVN_URL',          'http://127.0.0.1:8888/act/?act=cmactivity');
define('SYNC_PLATFORM_URL',  'http://127.0.0.1:8888/act/?act=syncplatform');

define('CENTER_URL',         'http://111.230.146.122/rpc_activity.php');


//本地测试
//define('SERVER_DIR',        ROOT.'/../../../../../../../server/dev/src/data/create/activity/');
//define('JEC',               ROOT.'/../../../../../../../web/dev/Jec/');
//define('DATA',              ROOT.'/../../../../../../../data/dev/excel/');
