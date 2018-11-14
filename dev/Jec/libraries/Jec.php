<?php
/**
 * @copyright Jec
 * @package Jec框架
 * @link 
 * @author jecelyin peng
 * @license 转载或修改请保留版权信息
 *
 * Jec基础类
 */
class Jec
{

    /**
     * @static
     * 获取一个正数
     * @param string $index GPC请求变量键名
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return int
     */
    public static function getInt($index, $method = '')
    {
        $value = self::_getGPC($index, $method);
        return parseInt($value);
    }

    /**
     * 取得请求为浮数值
     * @param string $index GPC请求变量键名
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return float
     */
    public static function getFloat($index, $method = '')
    {
        $value = self::_getGPC($index, $method);
        return parseFloat($value);
    }

    /**
     * 返回一个Unix时间戳
     * @param string $index GPC请求变量键名
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return int
     */
    public static function getTime($index, $method = '')
    {
        $value = self::_getGPC($index, $method);
        return self::_getTime($value);
    }

    private static function _getTime($value)
    {
        if(!$value)return 0;
        //phpdoc: 成功则返回时间戳，否则返回 FALSE。在 PHP 5.1.0 之前本函数在失败时返回 -1
        $value = strtotime($value);
        if ($value < 1)
            return 0;
        return $value;
    }

    /**
     * 获取一个标准的日期描述
     * @param string $index GPC请求变量键名
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return string 返回空字符串或2012-01-01 23:23:23这样的格式
     */
    public static function getDate($index, $method = '')
    {
        $ts = self::getTime($index, $method);
        if($ts == 0)
            return '';

        return getDateStr($ts);
    }

    /**
     * 获取一个经过HTML过滤和转义的GPC变量
     * @param string $index GPC请求变量键名
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return bool|string|array 返回将返回false,否则则可能是字符串或数组
     */
    public static function getVar($index, $method = '')
    {
        $value = self::_getGPC($index, $method);
        return self::_getVar($value);
    }

    private static function _getVar($value)
    {
        if(!$value)return $value;
        $value = _htmlspecialchars($value);
        $value = _addslashes($value);
        return _trim($value);
    }

    /**
     * 获取一个数组请求，如果index请求值的键不在map的键中则抛出一个错误
     * @static
     * @param string $index GPC请求变量键名
     * @param array $map 字段数组，格式：array(key=>type,,)，如: array('name'=>'var'),
     *             type类型有（区分大小写）:int,float,date,time,keyword,var；默认为var
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return array
     * @throws JecException
     */
    public static function getMap($index, $map, $method = '')
    {
        $value = self::_getGPC($index, $method);
        if(!is_array($value))
            throw new JecException('非数组请求：'.$index);
        foreach($value as $k => &$Pv)
        {
            if(!isset($map[$k]))throw new JecException('非法的MAP KEY: '.$k);
            switch($map[$k])
            {
                case 'int':
                    $Pv = parseInt($Pv);
                    break;
                case 'float':
                    $Pv = parseFloat($Pv);
                    break;
                case "date" :
                    $Pv = getDateStr(self::_getTime($Pv));
                    break;
                case 'time':
                    $Pv = self::_getTime($Pv);
                    break;
                case 'keyword':
                    $Pv = preg_replace('/[^\w\-]/', '', $Pv);
                    break;
                case 'var':
                default:
                    $Pv = self::_getVar($Pv);
            }
        }
        return $value;
    }

    /**
     * 获取一个经过HTML及特殊字符过滤和转义后的请求变量，一般用来过滤搜索关键字
     * @param string $index GPC请求变量键名
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return bool|string|array 失败将返回false
     */
    public static function getKeyWord($index, $method = '')
    {
        $value = self::_getGPC($index, $method);
        if(!$value)return $value;
        /*$value = str_filter($value);
        $value = _htmlspecialchars($value);
        $value = _addslashes($value);*/
        $value = preg_replace('/[^\w\-]/', '', $value);
        return $value;
    }

    /**
     * 获取一个经过转义后的请求变量
     * @param string $index GPC请求变量键名
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return bool|string|array
     */
    public static function getString($index, $method = '')
    {
        $value = self::_getGPC($index, $method);
        if(!$value)return $value;
        $value = _addslashes($value);
        return _trim($value);
    }

    /**
     * 获取一个经过HTML安全过滤和转义后的请求变量
     * @param string $index GPC请求变量键名
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return bool|string
     */
    public static function getXhtml($index, $method = '')
    {
        $value = self::_getGPC($index, $method);
        if(!$value)return $value;
        //去掉自动转换不然kses不正常
        if (MAGIC_QUOTES_GPC)
            $value = stripslashes($value);
        loadPlugins('kses');
        loadPlugins('iXhtml');
        $kses_config = require PLUGINS_PATH . '/kses.cfg.php';
        $value = kses($value, $kses_config[0], $kses_config[1]);
        $value = html2xhtml($value);
        $value = _addslashes($value, 1);
        return $value;
    }

    /**
     * 从GPC数组中寻找索引值
     * @param string $index GPC请求变量键名
     * @param string $method 请求方式限制,值为: GET,POST,COOKIE
     * @return bool|string|array
     */
    private static function _getGPC($index, $method = '')
    {
        if ($method) {
            $name = '_' . $method;
            if (!isset($GLOBALS[$name][$index]))
                return false;
            return $GLOBALS[$name][$index];
        }
        if (isset($_POST[$index]))
            return $_POST[$index];
        if (isset($_GET[$index]))
            return $_GET[$index];

        return false;
    }
}