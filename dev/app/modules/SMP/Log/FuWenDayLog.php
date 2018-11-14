<?php
class SMP_Log_FuWenDayLog extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		$this->show();
	}

	public function show()
	{
		$this->getWhereTime('time','0 day',true);
		$st_date = date('Ymd',strtotime($this->getStartTime()));
		$st = strtotime($st_date);
		$where = ' where time < '.$st;
		$req_params['pkey'] = g(Jec::getVar('pkey'));
		if($req_params['pkey'])	$where .= ' and pkey = '.$req_params['pkey'];
		$req_params['pname'] = g(Jec::getVar('pname'));
		if($req_params['pname'])	$where .= ' and pname = "'.$req_params['pname'].'"';
		$data = $pos_data = [];
		$sql = "select
				t1.pkey,
				t1.pname,
				t1.goods_name,
				t1.fuwen_lv,
				t1.pos,
				t2.pos as pnum,
				t3.sn,
				t3.pf,
				t3.reg_time,
				t3.lv,
				t3.vip_lv,
				t3.cbp,
				t4.total_fee
				FROM (SELECT * FROM log_fuwen_change $where ORDER BY time desc) t1
				LEFT JOIN log_fuwen_pos t2 on t1.pkey = t2.pkey
				LEFT JOIN log_player_state t3 on t1.pkey = t3.pkey and DATE_FORMAT(FROM_UNIXTIME(t3.time),'%Y%m%d') = $st_date
				LEFT JOIN player_recharge t4 on t1.pkey = t4.pkey
				group by CONCAT(t1.pkey,t1.pos)";
		$db_data = $this->db_game->getAll($sql);
		foreach ($db_data as $item)
		{
			$item['total_fee'] = $item['total_fee'] > 0 ? number_format(round($item['total_fee']/100,2)) : 0;
			$item['reg_time'] = $item['reg_time'] > 0 ? date('Y-m-d H:i:s',$item['reg_time']) : 0;
			$pos_data[$item['pkey']][$item['pos']] = '{'.$item['goods_name'].', '.$item['fuwen_lv'].'}';
			unset($item['fuwen_lv'],$item['goods_name'],$item['pos']);
			$data[$item['pkey']] = $item;
		}
		unset($db_data);
		foreach ($data as &$d) 
		{
			for ($i=1; $i <9; $i++) 
			{ 
				$init_data = "{无,0}";
				if(!isset($pos_data[$d['pkey']][$i]))
				{
					$d['pos_'.$i] = $init_data;
				}else{
					$d['pos_'.$i] = $pos_data[$d['pkey']][$i];
				}
			}
		}
		unset($pos_data);
		if(Jec::getVar("download"))
		{
			array_unshift($data, ['PKEY','昵称','开孔数','服区','渠道','注册时间','等级','VIP等级','战力','累计充值','孔位1','孔位2','孔位3','孔位4','孔位5','孔位6','孔位7','孔位8']);
			Helper::csv_download($data,"RunesDayLog.csv");
		}
		$this->assign('data',$data);
		$this->assign('req_params',$req_params);
		$this->assign('title',"玩家每天符文日志");
		$this->display();
	}
}