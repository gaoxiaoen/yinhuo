<?php

/**
 * @copyright Jec
 * @package Jec框架
 * @link
 * @author jecelyin peng
 * @license 转载或修改请保留版权信息
 * 公共函数库
 */

/**
 * 自动加载方法
 * @param string $class 类名
 * @return bool
 */
function Jec_autoload($class)
{
    $class = ucfirst($class);
    if (class_exists($class, false) || interface_exists($class, false))
        return true;
    $file = str_replace('_', DS, $class) . '.php';
    require $file;
    return true;
}

/**
 * 载入一个插件
 * @param $file_basename 插件名称，不包含扩展名.php
 * @return bool|mixed
 */
function loadPlugins($file_basename)
{
    return require(PLUGINS_PATH . DS . $file_basename . '.php');
}

/**
 *  格式化打印
 */
function p($x)
{
    echo '<pre>';
    print_r($x);
    echo '<pre>';
}

/**
 *  打日志调试
 */
function loglog($content='',$filename='',$path='')
{
    if(!$path) $path = VAR_PATH.'/debug_log/'.date('Ymd',time());
    if(!is_dir($path)) mkdir($path,0777,true);
    $file_name = $path . '/' . $filename;
    if(!file_exists($file_name))
    {
        $fs = fopen($file_name,'w+');
    }else{  
        $fs = fopen($file_name,'a+');
    }
    fwrite($fs, "\n".print_r($content."\n",1));
    fclose($fs);
}

/**
 * 调试函数
 * @param $var 要调试的变量
 * @param $exit 是否退出
 * @return null
 */
function d($var, $exit = 1)
{
    $debug = debug_backtrace();
    $dfile = '';
    foreach ($debug as $dval) {
        if ($dval['function'] == 'd') {
            $dfile = "{$dval['file']} ({$dval['line']})\n";
        }
    }
    $result = var_export($var, true);
    //$result = str_replace("\n", "<br />", $result);
    JecException::showError($dfile . $result, 0, '', 0, null, 1, $exit);
}

/**
 * 是否使用命令行模式执行PHP
 * @return bool
 */
function isCLI()
{
    /**
     * php_sapi_name
     * Returns the interface type, as a lowercase string.
     * Although not exhaustive, the possible return values include aolserver, apache, apache2filter, apache2handler, caudium, cgi (until PHP 5.3), cgi-fcgi, cli, continuity, embed, isapi, litespeed, milter, nsapi, phttpd, pi3web, roxen, thttpd, tux, and webjames.
     **/
    return php_sapi_name() == 'cli';
}

/**
 * 记录调试信息到一个日志文件
 * @param mixed $var 调试变量
 * @param string $logFile 日志文件名
 * @return bool
 */
function _log($var, $logFile = 'ibug.log')
{
    $t = date('Ymd-H');
    $rt = date('Y/m/d H:i:s') . "\n";
    if (!$var) {
        if (is_array($var))
            $rt .= 'array()';
        elseif ($var === false)
            $rt .= 'false';
        elseif ($var === null)
            $rt .= 'null';
        else
            $rt .= var_export($var, true);
    } else {
        $rt .= var_export($var, true);
    }
    $rt .= "\n\n";
    $file = VAR_PATH . '/log/' . $t . $logFile;

    return file_put_contents($file, $rt, FILE_APPEND);
}

/**
 * 取得一个安全的系统环境变量
 * @param string $key 变量名
 * @return string 环境值
 */
function _getEnv($key)
{
    $ret = '';
    if (isset($_SERVER[$key]) || isset($_ENV[$key]))
        $ret = isset($_SERVER[$key]) ? $_SERVER[$key] : $_ENV[$key];
    switch ($key) {
        case 'PHP_SELF':
        case 'PATH_INFO':
        case 'PATH_TRANSLATED':
        case 'HTTP_USER_AGENT':
            $ret = htmlspecialchars($ret, ENT_QUOTES);
            break;
    }
    return getenv($key);
}

/**
 * 获取php.ini配置
 * @param string $varname 变量名
 * @return string
 */
function getCfg($varname)
{
    $result = function_exists('get_cfg_var') ? get_cfg_var($varname) : 0;
    if ($result == 0)
        return 'No';
    elseif ($result == 1)
        return 'Yes';
    else
        return $result;
}

/**
 * 扩展类似JS的alert函数，响应后直接退出php执行脚本
 * @param $msg 提示信息
 * @param $act 默认动作返回上一页，其它：href转到链接，close关闭当前窗口
 * @param $href 网址
 * @return null
 */
function alert($msg = '操作失败 :-(', $act = 'href', $href = '')
{
    global $CONFIG;
    $js = '';
    switch ($act) {
        case 'href':
            if (!$href) $href = $_SERVER['HTTP_REFERER'];
            $js = "location.href='$href';";
            break;
        case 'close':
            $js = "window.open('','_parent','');window.close();";
            break;
        default:
            $js = "history.go(-1);";
    }
    echo '
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=' . $CONFIG['html_charset'] . '" /></head>
<body>
<script type="text/javascript">
alert("' . $msg . '");' . $js . '
</script>
</body>
</html>';
    exit();
}

/**
 * 清理空格 - 支持数组
 * @param mixed $var
 * @return mixed
 */
function _trim($var)
{
    if (is_array($var))
        return array_map("_trim", $var);
    return trim($var);
}

//PHP stdClass Object转array
function object_array($array)
{
    if (is_object($array)) {
        $array = (array)$array;
    }
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}

/**
 * @param $goodstype
 * @return mixed|string 获取物品名称
 */
function get_goods_name($goodstype)
{
    $cache = Cache::getInstance()->get('goodsname' . $goodstype);
    if ($cache != false) {
        return $cache;
    }
    if ((int)$goodstype > 0) {
        if ($cache != false) {
            return $cache;
        }
        $ret = net::rpc_game_server(gm, goods_name, array('goodstype' => $goodstype));
        return json_decode($ret);
    } else
        return "未知物品";
}

/**
 * @param $pid
 * @return bool|string
 */
function get_player_name($pkey)
{
    $cache = Cache::getInstance()->get('playername' . $pkey);
    if ($cache != false) {
        return $cache;
    }
    $name = DB::getInstance('db_game')->getOne("select nickname from player_state where pkey = $pkey");
    if ($name != false) {
        Cache::getInstance()->set('playername' . $pkey, $name, 86400);
        return $name;
    } else {
        return "未知玩家";
    }
}

/**
 * @return mixed
 * 跟游戏服一直的唯一值
 */
function unique_key()
{
    global $CONFIG;
    $server = 10000 + (int)$CONFIG['game']['sn'];
    $time = microtime(true) / 1000;
    $time = floatval(sprintf("%.7f", $time)) * 10000000;
    return str_replace(',', '', number_format($server . $time));
}

/**
 * 格式化物品列表
 * @param $goodslist
 * @return string
 */
function format_goods_list($goodslist)
{
    global $Ggoods;
    $goodsStr = str_replace(array('[', ']', '{', '}'), '', $goodslist);
    $goodsArr = explode(',', $goodsStr);
    $goodsInfo = '';
    $len = count($goodsArr);
    for ($i = 0; $i < $len;) {
        $id = $Ggoods[$goodsArr[$i]] ? $Ggoods[$goodsArr[$i]] : $goodsArr[$i];
        $num = $goodsArr[$i + 1];
        $goodsInfo .= '(' . $id . '*' . $num . ')';
        $i = $i + 2;
    }
    return $goodsInfo;
}

/**
 * @param $data
 * @param $app_secret
 * @return string
 */
function createSign($data, $app_secret)
{
    ksort($data);
    $str = "";
    foreach ($data as $k => $v) {
        if ($k != 'sign') {
            $str .= $k . "=" . $v . "&";
        }
    }
    $str = substr($str, 0, -1);
    $str .= $app_secret;
    return md5($str);
}

/**
 * @param $url
 * @param $data_string
 * @param string $type
 * @return string
 */
function postData($url, $data_string, $type = "text")
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    if ($type == "json") {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string))
        );
    } else
        curl_setopt($ch, CURLOPT_HEADER, false);
    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
    return $return_content;
}

/**
 * @return bool 是否管理员登陆
 */
function isAdmin()
{
    return  $_SESSION['login_name'] == 'admin' ||  $_SESSION['groupname'] == '管理员' ;
}

/**
 * @return bool 是否超级管理员
 */
function isSupAdmin() {
    return  $_SESSION['login_name'] == 'admin';
}

/**
 * 获取中央服的游戏包
 * @return str | [];
 */
function getGameChannelIdInfo () {
    $cache = Cache::getInstance();
    $gcInfoKey = 'gamechannelInfo_cache';
    #通过缓存 或者 接口 获取gamechannelInfo信息
    if(!$gcInfo = $cache->get($gcInfoKey))
    {   
        global $CONFIG;
        $url = $CONFIG['center']['api']."/gc_info.php";
        $time = time();
        $params = ['time'=>$time,'sign'=>md5('getCenterGameChannel_infoData'.$time)];
        $tmpArr = json_decode(postData($url,$params));
        #如果tmpArr为空的话 可能是center接口返回结果为空 也可能是接口调用失败(1.url是否设置正确 2.接口参数是否齐全 3.接口签名是否正确)
        if(!empty($tmpArr))
        {
            foreach($tmpArr as $v)
            {
                $gcInfo[$v->gc_id] = $v->name;
            }
            $cache->set($gcInfoKey,$gcInfo,86400);
        }else{
            $gcInfo = [];
        }
    }
    return $gcInfo;
}
/**
 * 获取中央服平台信息
 */
function getCenterPlatFormInfo () {
    $cache = Cache::getInstance();
    $gcInfoKey = 'center_platform_cache';
    #通过缓存 或者 接口 获取gamechannelInfo信息
    if(!$pfInfo = $cache->get($gcInfoKey))
    {   
        global $CONFIG;
        $url = $CONFIG['center']['api']."/platform_info.php";
        $time = time();
        $params = ['time'=>$time,'sign'=>md5('getCenterPlatForm_InfoData'.$time)];
        $tmpArr = json_decode(postData($url,$params),true);
        #如果tmpArr为空的话 可能是center接口返回结果为空 也可能是接口调用失败(1.url是否设置正确 2.接口参数是否齐全 3.接口签名是否正确)
        if(!empty($tmpArr))
        {
            $pfInfo = $tmpArr;
            $cache->set($gcInfoKey,$tmpArr,86400);
        }else{
            $pfInfo = [];
        }
    }
    return $pfInfo;
}

/**
 * 获取对应时间戳/日期所属星期名称
 */
function getWeekDate ($time='') {
    if(!$time) return $time;
    if(!is_timestamp($time)) $time = strtotime($time);
    $week_name = ['日','一','二','三','四','五','六'];
    return '周'.$week_name[date('w',$time)];
}

/**
 * 判断是否为时间戳格式时间
 */
function is_timestamp ($timestamp='') {
    if(!$timestamp) return $timestamp;
    if(strtotime(date('Y-m-d :H:i:s',$timestamp)) === $timestamp) {
        return $timestamp;
    }else{
        return false;
    }
}
/**
 * 字符串长度截取
 * @param string $str  需要截取的字符串
 * @param int $stpo  字符段截取开始位置
 * @param int $len   字符段截取长度
 * @param string $html  返回结果中尾部后缀
 * @return string|void
 */
function substring($str='',$stpo=0,$len=30,$html='.....')
{
    if(!$str || !is_string($str)) return;
    if($len >= strlen($str)){
        return $str;
    }else{
        return substr($str,$stpo,$len).$html;
    }
}
