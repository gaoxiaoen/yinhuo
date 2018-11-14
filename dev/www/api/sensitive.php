<?php
require '../../Jec/booter.php';

$time = Jec::getInt('time');
$sign = Jec::getVar('sign');
$param = Jec::getVar('param');
#排除缺少参数情况
if(!$time || !$sign || !$param) exit('0');
# 排除签名不对情况
$mysign = md5('set_sensitive_word_to_game_servers_key'.$time);
if($mysign != $sign) exit('0');
$res = DB::getInstance('db_game')->insert('sensitive_word',['word_list'=>$param,'time'=>time()]);
if($res){
	exit('1');
}else{
	exit('0');
}
