<?php

require '../../Jec/booter.php';
// 允许跨域方位，任何的域都可访问
header('Access-Control-Allow-Origin:*');
	
function getData()
{
	
	$db = DB::getInstance('db_game');
	$date = Jec::getVar('date') ? strtotime(Jec::getVar('date')) : '';
	if(!$date) $date = $opentime = Jec::getInt('opentime');
	$st = strtotime(date('Y-m-d',$date));
	$et =  $st + 86400 * 31;
	$dn = [1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,10=>10,15=>15,30=>30];
	# 1.获取ltv数据
	$query_res = $db->query('select * from cron_rate_ltv where date >= '.$st.' and date < '.$et.' order by date asc');
	$ltv = [1=>null,2=>null,3=>null,4=>null,5=>null,6=>null,7=>null,10=>null,15=>null,30=>null];
	while ( $result = $db->fetchArray($query_res) ) 
	{
			$d = ($result['date'] - $st) / 86400 + 1;
			if (in_array($d, $dn))
			{
				$ltv[$d] = $result['d'.$d];
			}
	}

	# 2.获取vip数据
	$query_res = $db->query('select vip_lv,count(*) as role_num,DATE_FORMAT(FROM_UNIXTIME(time),"%Y%m%d") as date from log_player_state where time >= '.$st.' and time < '.$et.' and vip_lv in (2,4,6) group by concat(date,vip_lv);');
	$vip = [1=>[2=>0,4=>0,6=>0],2=>[2=>0,4=>0,6=>0],3=>[2=>0,4=>0,6=>0],4=>[2=>0,4=>0,6=>0],5=>[2=>0,4=>0,6=>0],6=>[2=>0,4=>0,6=>0],7=>[2=>0,4=>0,6=>0],10=>[2=>0,4=>0,6=>0],15=>[2=>0,4=>0,6=>0],30=>[2=>0,4=>0,6=>0]];
	while ( $result = $db->fetchArray($query_res) ) 
	{
		$d = (strtotime($result['date']) - $st) / 86400 + 1;
		if (in_array($d, $dn))
		{
			$vip[$d][$result['vip_lv']] = $result['role_num'];
		}
	}

	# 3.获取付费率
	$rate_charge = $db->fetchAll('select count(distinct(app_role_id)) as role_num,date_format(from_unixtime(time),"%Y%m%d") as date,sum(original_total_fee) as total_fee from recharge where time >= '.$st.' and time < '.$et.' group by date','key','date');
	$rate_reg    = $db->fetchAll('select login_num,date_format(from_unixtime(time),"%Y%m%d") as date from cron_daily where time >= '.$st.' and time < '.$et,'key','date');
	foreach ($dn as $n) 
	{
		$date = date('Ymd',$st + 86400*($n - 1));
		if (isset($rate_charge[$date])) 
		{
			$rate[$n]['arpu'] = $rate_charge[$date]['role_num'] > 0 ? sprintf('%.2f',round($rate_charge[$date]['total_fee'] / 100 / $rate_charge[$date]['role_num'],4) * 100) : '0.00';
			if (isset($rate_reg[$date])) 
			{
				$rate[$n]['rate'] = $rate_reg[$date]['login_num']   > 0 ? sprintf('%.2f',round($rate_charge[$date]['role_num']  / $rate_reg[$date]['login_num'],4)*100) . '%': '0.00%';
			}else{
				$rate[$n]['rate'] = null;
			}
		}else{
			$rate[$n]['arpu'] = null;
			if (isset($rate_reg[$date])) 
				$rate[$n]['rate'] = '0.00%';
			else
				$rate[$n]['rate'] = null;
		}
	}

	# 4.获取世界等级
	$wlv = $db->getOne('select act_wlv from act_wlv_log where key1 = '.$st);

	# 5.获取留存数据
	$r_st = $st + 86400;
	$r_et = $et + 86400;
	$query_res = $db->query('select * from cron_rate_player where date >= '.$r_st.' and date < '.$r_et.' order by date asc');
	#	ddatabase中的: 1 实际对应d2 d7 实际对应 d10
	$retain = [1=>null,2=>null,3=>null,4=>null,5=>null,6=>null,7=>null,10=>null,15=>null,30=>null];
	while ( $result = $db->fetchArray($query_res) )
	{
		$d = ($result['date'] - $r_st) / 86400 + 1;
		if (in_array($d, $dn))
		{
			if(10 === $d)
			{
				$retain[$d] = sprintf('%.2f',$result['d7']*100).'%';
			}else{
				$retain[$d] = sprintf('%.2f',$result['d'.$d]*100).'%';
			}
		}
	}

	return   [
		'ltv'	=>		$ltv,
		'vip'	=>		$vip,
		'retain'=>		$retain,
		'rate'	=>		$rate,
		'wlv'	=>		$wlv
	];
}

function getShopData () 
{
	$date = Jec::getVar('date') ? strtotime(Jec::getVar('date')) : '';
	$shop_type  = Jec::getVar('shop_type') !== '' ? (int) Jec::getVar('shop_type') : '';
	$currency_type = Jec::getVar('currency_type') !== '' ? (int) Jec::getVar('currency_type') : '';
	if(!$date) return 'bad params';
	$et = strtotime(date('Y-m-d',$date)) + 86400;
	$st =  $et - 86400 * 3;
	$db = DB::getInstance('db_game');
	$where = '';
	if('' !== $shop_type) $where .= ' shop_type = '.$shop_type.' and ';
	if('' !== $currency_type) $where .= ' money_type = '.$currency_type.' and ';
	$ret = [];
	global $Ggoods;
	$query_res = $db->query('select goods_id,count(*) as time_num,count(distinct(pkey)) as role_num,sum(goods_num) as goods_num,sum(cost_all_money) as total_fee,date_format(from_unixtime(time),"%Y%m%d") as date from cron_new_shop_sell where '.$where.' time >= '.$st.' and time <= '.$et.' group by concat(date,goods_id)');
	while ($result = $db->fetchArray($query_res)) 
	{
		$ret[$result['date']][$result['goods_id']] = [
			'goods_name'	=>	isset($Ggoods[$result['goods_id']]) ? $Ggoods[$result['goods_id']] : 'undefine_'.$result['goods_id'],
			'time_num'		=>	$result['time_num'],
			'role_num'		=>	$result['role_num'],
			'goods_num'		=>	$result['goods_num'],
			'total_fee'		=>	$result['total_fee']
		];
	}
	return $ret;
}

/**
 *玩家等级数据 [ 单天查询 ]
 **/
function getLvCharge() 
{
	$date = Jec::getVar('date') ? strtotime(Jec::getVar('date')) : time();

	$st = strtotime(date('Y-m-d',$date));

	$et = $st + 86400;

	$db = DB::getInstance('db_game');

	$active_pkeys = $login_lv = $charge_lv = $lv = $res = [];

	$logout_res = $db->query('select pkey from log_out where time >= '.$st.' and time < '.$et.' group by pkey');
	while ($result = $db->fetchArray($logout_res)) {
		$active_pkeys[$result['pkey']] = $result['pkey'];
	}

	$login_res = $db->query('select pkey from log_login where time = '.$st.' group by pkey');
	while ($result = $db->fetchArray($login_res)) {
		$active_pkeys[$result['pkey']] = $result['pkey'];
	}

	//有登陆才有付费
	if(count($active_pkeys) > 0) {
		$lv_res = $db->query('select * from (select pkey,lv from log_player_state where time <= '.$et.' order by time desc) t group by pkey');
		if($lv_res->num_rows > 0) {
			while ($result = $db->fetchArray($lv_res)) {
				if(array_key_exists($result['pkey'], $active_pkeys)) {
					unset($active_pkeys[$result['pkey']]);
					$lv[$result['lv']] = $result['lv'];
					$login_lv[$result['lv']] += 1;
				}
			}
		}

		//log_player_state 找不到的登入或登出pkey则记录到等级为0的地方
		if(count($active_pkeys) > 0) {
			if (isset($login_lv[0])) {
				$login_lv[0] = count($active_pkeys) + $login_lv[0];
			}else{
				$login_lv[0] = count($active_pkeys);
			}
		}

		$charge_res = $db->query('select lv,count(*) as num,sum(original_total_fee) as money from recharge where time >= '.$st.' and time < '.$et .' group by lv');
		if($charge_res->num_rows >0) {
			while ($result = $db->fetchArray($charge_res)) {
				$lv[$result['lv']] = $result['lv'];
				$charge_lv[$result['lv']]['num'] = $result['num'];
				$charge_lv[$result['lv']]['charge'] = $result['money'];
			}
		}
		
		if (!empty($lv)) {
			foreach ($lv as $item) {
				$res[] = [
					'lv'	=>	$item,
					'login_num'	=>	isset($login_lv[$item]) ? $login_lv[$item] : 0,
					'charge_num'=>	isset($charge_lv[$item])? $charge_lv[$item]['num']: 0,
					'charge'	=>	$charge_lv[$item]['charge'] > 0 ? $charge_lv[$item]['charge'] / 100 : 0
				];
			}
		}
	}

	return $res;

}


$act = Jec::getVar('act');
switch ($act) {
	case 'shop':
		$res = getShopData();
		break;
	case 'getLvCharge':
		$res = getLvCharge();
		break;
	
	default:
		$res = getData();
		break;
}

exit(json_encode($res));