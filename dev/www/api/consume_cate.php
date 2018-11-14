<?php

require '../../Jec/booter.php';

$time = Jec::getInt('time');
$sign = Jec::getVar('sign');
$consume_type = Jec::getVar('consume_type');
$consume_cate = Jec::getVar('consume_cate');
if(!$time || !$sign) exit(json_encode('0'));
$mysign = md5($time.'center_push_consume_cate_to_ipservers_key');
if($sign != $mysign) exit(json_encode('-1'));
$consume_type_data = unserialize(urldecode($consume_type));
$consume_cate_data = unserialize(urldecode($consume_cate));
$type_sql = $cate_sql = '';
$flag = 1;
if(is_array($consume_type_data) && !empty($consume_type_data)) {
	foreach($consume_type_data as $v) {
		if(!$v['cate_id']) $v['cate_id'] = '0';
		$type_sql .= '('.$v['id'].',"'.$v['name'].'",'.$v['cate_id'].'),';
	}
	$type_sql = substr($type_sql,0,-1);
	$type_sql_arr[0] = 'truncate table consume_type';
	$type_sql_arr[1] = "insert into consume_type (id,name,cate_id) values $type_sql";
	$res_type = DB::getInstance('db_admin')->transaction($type_sql_arr);
	if(!$res_type) $flag = 0;
}

if(is_array($consume_cate_data) && !empty($consume_cate_data)) {
	foreach($consume_cate_data as $v) {
		if(!$v['name']) $v['name'] = '未定义';
		$cate_sql .= '('.$v['cate_id'].',"'.$v['name'].'"),';
	}
	$cate_sql = substr($cate_sql,0,-1);
	$cate_sql_arr[0] = 'truncate table consume_cate';
	$cate_sql_arr[1] = "insert into consume_cate (cate_id,name) values $cate_sql";
	$res_cate = DB::getInstance('db_admin')->transaction($cate_sql_arr);
	if(!$res_cate) $flag = 0;
}

if($flag) 
	exit(json_encode('1'));
else
	exit(json_encode('0'));