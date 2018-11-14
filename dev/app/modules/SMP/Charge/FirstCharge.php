<?php
/**
* 
*/
class SMP_Charge_FirstCharge extends AdminController
{
	public $cache = NULL;
	private static $interval_cache_key = NULL;
	private static $lv_interval 	   = NULL;
	private static $money_interval     = NULL;

	public function __construct()
	{
		parent::__construct();
		$this->cache = Cache::getInstance();
		self::$interval_cache_key = "smp_center_firstcharge_interval_cache_key";
		self::$lv_interval     	  = "0-10#11-20#21-30#31-40#41-50#51-60#61-70#71-80#";
		self::$money_interval     = "1-10#11-50#51-100#101-200#201-500#501-1000#1000-10000000#";
		$this->show();
	}

	public function show()
	{
		$req_params['money_interval'] = Jec::getVar('money_interval');
		if($req_params['money_interval'] && self::$money_interval != $req_params) $this->_setDataInterval(1,$req_params['money_interval']);
		$req_params['lv_interval'] = Jec::getVar('lv_interval');
		if($req_params['lv_interval'] && self::$money_interval != $req_params) $this->_setDataInterval(2,$req_params['lv_interval']);
		#	init data
		$total = $date_total = $interval_money = $interval_lv = [];
		#	build mysql where condition
		$where = $this->getWhereTime('rec.time','-30 day',true);
		#	execute mysql search
		$sql = "select rec.app_role_id,rec.time,rec.total_fee,pl.total_online_time,rec.lv 
			    from recharge rec 
			    left join player_login pl on rec.app_role_id = pl.pkey 
			    group by rec.app_role_id 
			    having $where 
			    order by rec.time";
		$db_data = $this->db_game->getAll($sql);
		#	get money and lv interval
		$interval_money = $this->_getDataInterval();
		$interval_lv    = $this->_getDataInterval(2);
		#	total,date_total,interval_money and interval_lv data calculate
		foreach ($db_data as $dd) 
		{
			$lv = $dd['lv'] == '' ? 0 : $dd['lv'];
			$total_fee = $dd['total_fee'] == '' ? 0 : $dd['total_fee'] / 100;
			$total_online_time = $dd['total_online_time'] == '' ? 0 : round($dd['total_online_time'] / 3600,0);

			$date = date("Ymd",$dd['time']);
			$total['num'] += 1;
			$total['total_fee'] += $total_fee;
			$total['total_online_time'] += $total_online_time;
			$date_total[$date]['num'] += 1;
			//$date_total[$date]['detail'][] = $dd;
			$date_total[$date]['total_fee'] += $total_fee;
			$date_total[$date]['total_online_time'] += $total_online_time;

			foreach ($interval_money as $k=>$im) 
			{
				if ($total_fee >= $im['st'] && $total_fee <= $im['et'])
				{
					$interval_money[$k]['count'] += 1;
					break;
				}
			}

			foreach ($interval_lv as $k=>$im) 
			{
				if ($lv >= $im['st'] && $lv <= $im['et']) 
				{
					$interval_lv[$k]['count'] += 1;
					break;
				}
			}
		}
		#	首充金额图表数据
		foreach ($date_total as $d=>$v) 
		{
			$xaxis[] = $d;
			$series_charge_data[] = $v['total_fee'] > 0 ? round($v['total_fee'] / $v['num'],2) : 0;
			$series_online_data[] = $v['total_online_time'] > 0 ? round($v['total_online_time'] / $v['num'],0) : 0;
			$series_charge_data_total[] = $total['total_fee'] > 0 ? round($total['total_fee'] / $total['num'],2) : 0;
			$series_online_data_total[] = $total['total_online_time'] > 0 ? round($total['total_online_time'] / $total['num'],0) : 0;
		}
		$charts_charge = [
			'title'			=>		'平均充值金额',
			'legend'		=>		['data'=>[0=>'每天玩家平均首充金额',1=>'期间玩家平均首充金额']],
			'xaxis'			=>		['data'=>$xaxis],
			'yaxis'			=>		[0=>['type'=>'value','name'=>'每天玩家平均首充金额'],1=>['type'=>'value','name'=>'期间玩家平均首充金额']],
			'series'		=>		[0=>['name'=>'每天玩家平均首充金额','type'=>'line','data'=>$series_charge_data],1=>['name'=>"期间玩家平均首充金额",'type'=>'line','data'=>$series_charge_data_total],]	
		];
		#	首充在线时长数据
		$charts_online = [
			'title'			=>		'平均在线时长 [ 小时 ]',
			'legend'		=>		['data'=>[0=>'每天玩家首充平均在线时长',1=>'期间玩家首充平均在线时长']],
			'xaxis'			=>		['data'=>$xaxis],
			'yaxis'			=>		[0=>['type'=>'value','name'=>'每天玩家首充平均在线时长'],1=>['type'=>'value','name'=>'期间玩家首充平均在线时长']],
			'series'		=>		[0=>['name'=>'每天玩家首充平均在线时长','type'=>'line','data'=>$series_online_data],1=>['name'=>"期间玩家首充平均在线时长",'type'=>'line','data'=>$series_online_data_total],]	
		];
		$this->assign('charts_charge',Echarts::create($charts_charge,'800px','300px'));
		$this->assign('charts_online',Echarts::create($charts_online,'800px','300px'));
		$this->assign('tabs',['Total'=>['checked'=>1,'name'=>'概况'], 'FirstCharge_Money'=>['checked'=>0,'name'=>'玩家首充金额'], 'FirstCharge_Lv'=>['checked'=>0,'name'=>'玩家首充等级']]);
		$this->assign('total',$total);
		$this->assign('date_total',$date_total);
		$this->assign('interval_lv',$interval_lv);
		$this->assign('interval_money',$interval_money);
		$this->assign('lv_interval',self::$lv_interval);
		$this->assign('money_interval',self::$money_interval);
		$this->assign("title","首充分析");
		$this->display();
	}

	/**
	 * 获取金额 和 等级 数据区间
	 * @params: int type 1 (default) is money and 2 is lv.
	 * @return: array
	 */
	private function _getDataInterval($type = 1)
	{

		$string = "";

		switch ($type) {
			case '2':
				if (!$string = $this->cache->get(self::$interval_cache_key)['lv']) 
					$string = self::$lv_interval;
				else
					self::$lv_interval = $string;
				break;
			
			default:
				if (!$string = $this->cache->get(self::$interval_cache_key)['money']) 
					$string = self::$money_interval;
				else
					self::$money_interval = $string;
				break;
		}

		$interval_arr = explode('#',$string );
		foreach ($interval_arr as $k=>&$item) 
		{
			$tmp_arr = explode('-', $item);
			if ($tmp_arr[0] != '' && $tmp_arr[1] != '')
			{
				$item = ['name'=>$item,'st'=>$tmp_arr[0],'et'=>$tmp_arr[1],'count'=>0];
			}else if($tmp_arr[0] != ''){
				$item = ['name'=>$item,'st'=>$tmp_arr[0],'et'=>10000000,'count'=>0];
			}else{
				unset($interval_arr[$k]);
			}
		}
		return $interval_arr;
	}

	/** 
	 * @desc: set interval cache
	 * @params: type
	 * @params: val
	 * @return: void
	 */
	private function _setDataInterval($type =1, $val)
	{
		$data = [];
		# privious cache data
		$data = $this->cache->get(self::$interval_cache_key);
		# expire time setttiong
		$expire_time = TIME + 365*86400;
		switch ($type) {
			case '2':
				$data['lv']    = $val;
				break;
			
			default:
				$data['money'] = $val;
				break;
		}
		$this->cache->set(self::$interval_cache_key,$data,$expire_time);
	}
}