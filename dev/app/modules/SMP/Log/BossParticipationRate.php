<?php
class SMP_Log_BossParticipationRate extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		$act = Jec::getVar("act");
		switch ($act) {
			case 'getMonPlayerDetail':
				$this->show_player();
				break;
			
			default:
				$this->show_mon();
				break;
		}
	}

	/**
	 *	BOSS怪物维度数据
	 */
	public function show_mon()
	{
		$where = ' where '.$this->getWhereTime('cb.time',"0 day",1);
		$req_params['mon_name'] = g(Jec::getVar('mon_name'));
		if($req_params['mon_name'] != '') $where .= ' and mon_name = "'.$req_params['mon_name'].'" ';

		$req_params['mon_type'] = g(Jec::getVar('mon_type'));
		if($req_params['mon_type'] != '') $where .= ' and mon_type = "'.$req_params['mon_type'].'" ';

		$sql_mon = "select mon_id,mon_name,mon_type,people_num,all_lv,all_cbp,pkey_list from cron_boss_join cb $where";
		$db_data = $this->db_game->getAll($sql_mon);

		$data = [];
		foreach ($db_data as $v) 
		{
			$key = $v['mon_type'].'_'.$v['mon_name'];
			$data[$key]['mon_id'] 	  = $v['mon_id'];
			$data[$key]['mon_name']   = $v['mon_name'];
			$data[$key]['mon_type']   = $v['mon_type'];
			$data[$key]['kill_num']  += 1;
			$data[$key]['kill_pnum'] += $v['people_num'];
			$data[$key]['pnum'] 	 .= $v['pkey_list'];
			$data[$key]['total_lv']  += $v['all_lv'];
			$data[$key]['total_cbp'] += $v['all_cbp'];
		}
		unset($db_data);
		foreach ($data as &$v) 
		{
			$v['pnum'] = count(array_diff(array_unique(explode(',',implode(',',explode('][',substr($v['pnum'], 1,-1))))),array ("")));
		}
		$this->assign('sdate',$this->getStartTime());
		$this->assign('data',$data);
		$this->assign('title',"BOSS击杀参与率");
		$this->assign('req_params',$req_params);
		$this->display();
	}

	/**
	 *	玩家BOSS怪物击杀维度数据
	 */
	public function show_player()
	{
		$req_params['sdate'] = Jec::getVar('sdate') ? strtotime(date('Ymd',strtotime(g(Jec::getVar('sdate'))))) : strtotime(date('Ymd',time()));
		$edate = $req_params['sdate'] + 86400;
		$where = ' where cb.time >= '.$req_params['sdate'].' and cb.time < '.$edate;

		$req_params['mon_name'] = g(Jec::getVar('mon_name'));
		if($req_params['mon_name']) $where .= ' and mon_name = "'.$req_params['mon_name'].'" ';

		$req_params['mon_type'] = g(Jec::getVar('mon_type'));
		if($req_params['mon_type'] != '') $where .= ' and mon_type = '.$req_params['mon_type'];

		$page = new Pager();
		$offset = $page->getOffset();
		$limit  = $page->getLimit();
		$page->setTotalRows($this->db_game->getOne("select count(*) from cron_boss_join_player cb $where"));

		$sql = "select
				cb.time,cb.mon_id,cb.mon_name,cb.hurt,cb.is_kill,cb.pkey,cb.pname,cb.mon_type,
				lps.sn,lps.pf,lps.reg_time,lps.lv,lps.vip_lv,lps.cbp,
				pr.total_fee
				from cron_boss_join_player AS cb
				LEFT JOIN log_player_state AS lps ON lps.pkey = cb.pkey AND DATE_FORMAT(FROM_UNIXTIME(lps.time),'%Y%m%d') = DATE_FORMAT(FROM_UNIXTIME(cb.time),'%Y%m%d')
				LEFT JOIN player_recharge AS pr ON pr.pkey = cb.pkey
				$where
				order by mon_id
				limit $offset,$limit";
		$db_data = $this->db_game->getAll($sql);
		foreach ($db_data as &$v) 
		{
			$v['time'] 		= date('Y-m-d H:i:s',$v['time']);
			$v['reg_time'] 	= $v['reg_time'] == '' ? '' : date('Y-m-d H:i:s',$v['reg_time']);
			$v['total_fee'] = $v['total_fee'] == '' ? '' : ($v['total_fee'] > 0 ? number_format($v['total_fee'] / 100,2) : 0);
			$v['is_kill'] 	= $v['is_kill'] == 1 ? '是' : '否';
		}
		if(Jec::getVar('download')) $this->csv_download($db_data);
		$this->assign('data',$db_data);
		$this->assign('title','玩家BOSS击杀详情');
		$this->assign('req_params',$req_params);
		$this->assign('page',$page->render());
		$this->display('SMP/Log/Views/BossParticipationPlayer.html');
	}

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data)
    {
    	array_unshift($data,['日期','怪物ID','怪物名称','输出','是否击杀','pkey','昵称','怪物类型','服区','渠道','注册时间','等级','VIP','战力','累计充值']);
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('bossParticipationPlayer.csv');
    }
}