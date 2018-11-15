<?php

/**
 * @copyright Jec
 * @link 
 * @author jecelyin peng
 * @license 转载或修改请保留版权信息
 */
/**
 * 定义程序内部的常量
 */
define('Jec', 'Jec Framework Ver.2012.01');

//目录分割符
define('DS', DIRECTORY_SEPARATOR);
//Jec核心根目录
define('Jec_PATH', dirname(__FILE__));

//-----------网站定义配置--------------------------------------------------
define('CWD_PATH',      realpath(Jec_PATH.'/../')       ); //工作目录
define('LIB_PATH',      Jec_PATH.'/libraries'           ); //类库目录
define('PLUGINS_PATH',  Jec_PATH.'/plugins'             ); //插件目录
define('WWW_PATH',      CWD_PATH.'/www'                 ); //站点目录
define('APP_PATH',      CWD_PATH.'/app'                 ); //站点PHP目录
define('MOD_PATH',      APP_PATH.'/modules'             ); //模块目录
define('CONFIG_PATH',   APP_PATH.'/config'              ); //网站配置目录
define('VAR_PATH',      CWD_PATH.'/var'                 ); //数据存放目录
define('SYNC_PATH',     VAR_PATH.'/sync'                ); //同步数据存放目录
define('INSTALL_PATH',   CWD_PATH.'/install'            ); // 同步安装目录
//-----------核心参数定义--------------------------------------------------
define('MAGIC_QUOTES_GPC',     get_magic_quotes_gpc()    ); //自动转义状态
//当前时间戳
define('TIME',         $_SERVER['REQUEST_TIME'] ? $_SERVER['REQUEST_TIME'] : time() );
/* 错误级别定义 */
//直接显示所有错误
define('ERROR_ALL',       1 );
//直接显示所有错误并保存到错误日志文件
define('ERROR_TO_FILE',  -1 );
//禁止错误输出
define('ERROR_NONE',      0 );
//------------ END -----------------------------------------------------
//设置自动包含路径
set_include_path(PATH_SEPARATOR . LIB_PATH
        . PATH_SEPARATOR . MOD_PATH
        . PATH_SEPARATOR . get_include_path());

//加载常用函数库获得更高性能，因此不用类来自动加载
require LIB_PATH . '/Helper/base.func.php';
require LIB_PATH . '/Helper/string.func.php';
require LIB_PATH . '/Helper/math.func.php';
require LIB_PATH . '/Helper/file.func.php';
require CONFIG_PATH. '/menu.php';
require CONFIG_PATH. '/goods.php';

//异常处理类，出错再自动加载此类
require LIB_PATH . '/JecException.php';
require LIB_PATH . '/waf.php';
//设置全局错误处理
set_error_handler(array('JecException', 'doError'));
//设置全局异常处理
set_exception_handler(array('JecException', 'doException'));

//基础类
require LIB_PATH . '/Jec.php';
//自动加载handler,通过此函数，就可以自动的添加需要设置的类
spl_autoload_register('Jec_autoload');

//初始化站点配置
$CONFIG = array();
//静态配置，非程序改动，包含站点和Jec框架配置
require CONFIG_PATH . '/static.php';
//后台可控站点配置
require CONFIG_PATH . '/site.php';
//设置时区
if(!isset($CONFIG['timezone']))
    $CONFIG['timezone'] = 'Asia/Shanghai';
date_default_timezone_set($CONFIG['timezone']);

//错误级别
define('ERROR_LEVEL',  $CONFIG['error_level']);

//防止cookie通过js读取
@ini_set("session.cookie_httponly", 1);

//处理必需的环境变量
if(!isset($CONFIG['app_name']) || !$CONFIG['app_name'])
    $CONFIG['app_name'] = 'Jec';


if (ERROR_LEVEL != ERROR_NONE)
{
    //@ini_set("display_errors", "on");
    error_reporting(E_ALL ^E_NOTICE);
}else{
    error_reporting(0);
}

//header("Cache-Control: no-store, no-cache");
// 检查浏览器是否支持 gzip 编码, HTTP_ACCEPT_ENCODING
if ($CONFIG['gzip'] && isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
        strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && extension_loaded('zlib'))
{
    ob_end_clean();
    ob_start('ob_gzhandler');
    // 告诉浏览器内容已用gzip压缩
    //header("Content-Encoding:gzip");
}

//使用其它方式保存session
Session::init();
//还原session_start造成浏览器 后退按钮所有区域内容为空的bug
session_cache_limiter('private, must-revalidate');




