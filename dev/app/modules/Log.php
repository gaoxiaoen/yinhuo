<?php
/**
 * User: jecelyin 
 * Date: 12-2-20
 * Time: 下午3:14
 *
 */
 
class Log
{
    public static function info($module, $memo='')
    {
        $ins = array();
        $ins['module'] = $module;
        $ins['ctime'] = getDateStr();
        $ins['nickname'] = $_SESSION['nickname'];
        $ins['ip'] = Net::getIP();
        $ins['memo'] = $memo;
        DB::getInstance()->insert('logs', $ins);
    }


}