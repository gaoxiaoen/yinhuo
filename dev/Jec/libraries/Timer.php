<?php
/**
 * User: jecelyin
 * Date: 12-3-9
 * Time: 下午4:18
 * 计时类，支持嵌套
 */
class Timer
{
    private static $st = array();
    private static $et = array();

    public static function start()
    {
        self::$st[] = microtime(true);
    }

    public static function end($tag)
    {
        self::$et[] = microtime(true);

        $st = array_pop(self::$st);
        $et = array_pop(self::$et);

        $elapsed = round($et - $st, 6);
        echo "##{$tag}: ".$elapsed." sec at ".date('Y-m-d H:i:s',$st).'~'.date('Y-m-d H:i:s',$et);
        if(isCLI())
            echo "\n";
        else
            echo "<br />";
    }
}
