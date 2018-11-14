<?php
/**
 * Created by PhpStorm.
 * User: fancy
 * Date: 15/3/26
 * Time: 上午10:56
 * desc: 玩家反馈回复
 */

require '../../Jec/booter.php';

$sign = Jec::getString('sign');
$pkey = Jec::getInt('pkey');
$content = urldecode(Jec::getString('content'));
$mysign = md5($pkey."clreply128");
if($mysign != $sign) exit("-1");
$mkey = unique_key();
$time = time();
$overtime = time() + 86400 * 7;
$sql = "insert into mail set mkey = $mkey,pkey = $pkey ,type = 0,title = 'GM回复',content = '$content',goodslist = '[]',time = $time,overtime = $overtime ";
DB::getInstance('db_game')->query($sql);
Net::rpc_game_server(gm, update_online_mail, array('pkey' => $pkey));
exit("1");
