<?php
/**
 * Created by PhpStorm.
 * User: fancy
 * Date: 15/3/26
 * Time: 上午10:56
 */

require '../../Jec/booter.php';

$time = Jec::getString('time');
$sign = Jec::getString('sign');
$mysign = md5($time."ConfigTimeAsYnc2016");
if($mysign != $sign) exit("-1");
$ret = Net::rpc_game_server(sys, config, array('time' => $time));
exit($ret);
