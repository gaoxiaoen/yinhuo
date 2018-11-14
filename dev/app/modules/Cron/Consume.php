<?php
/**
 *	消费统计脚本
 */
class Cron_Consume {

	public $circle_time = 0;

	public function __construct($data) {
		$this->db = DB::getInstance('db_game');
		$this->db_adm = DB::getInstance('db_admin');
        $this->data = $data;
        $method = $data['method'];
        $this->$method();
	}

	public function cal_consume_type () {
		$time = $this->data['args'][0] ? strtotime($this->data['args'][0]) : time();
		$now = $time - 5;
		$day = date('Y-m-d',$now);
		$st  = strtotime($day);
		$et  = $st + 86400;
		global $CONFIG;
		$sid = $CONFIG['game']['sn'];
//		$bgold_data =$this->db->getAll("select addreason,count(*) as num,count(DISTINCT acc_name) as account,abs(sum(oldbgold-newbgold)) as currency from log_gold where time >= $st and time < $et and addgold < 0 and newbgold  < oldbgold group by addreason");
		$gold_data  =$this->db->getAll("select addreason,count(*) as num,count(DISTINCT acc_name) as account,abs(sum(oldgold - newgold)) as currency from log_gold where time >= $st and time < $et and addgold < 0 and newgold   < oldgold  group by addreason");
		$coin_data  =$this->db->getAll("select addreason,count(*) as num,count(DISTINCT acc_name) as account,abs(sum(addcoin)) as currency from log_coin where addcoin < 0 and  time >= $st and time < $et group by addreason");
		$this->db->query("replace into cron_consume_type (time,type,data) values('".$st."',1,'".serialize($gold_data) ."')");
//		$this->db->query("replace into cron_consume_type (time,type,data) values('".$st."',2,'".serialize($bgold_data)."')");
		$this->db->query("replace into cron_consume_type (time,type,data) values('".$st."',3,'".serialize($coin_data) ."')");
		//推送数据到中央服
		$time = time();
//		$params = [ 'data'=> [0 => ['time'=>$st,'type'=>'1','data'=>serialize($gold_data)],1 => ['time'=>$st,'type'=>'2','data'=>serialize($bgold_data)],2 => ['time'=>$st,'type'=>'3','data'=>serialize($coin_data)]],
//					'time'=> $time,
//					'sign'=> md5('push_cron_consume_data_to_center_key'.$time),
//					'sn'  => $sid,
//		];
		$params = [ 'data'=> [0 => ['time'=>$st,'type'=>'1','data'=>serialize($gold_data)],2 => ['time'=>$st,'type'=>'3','data'=>serialize($coin_data)]],
					'time'=> $time,
					'sign'=> md5('push_cron_consume_data_to_center_key'.$time),
					'sn'  => $sid,
		];
		$this->pushData($CONFIG['center']['api'].'/consume_cate.php?&act=pushConsumeCronData',json_encode($params));
	}

	private function pushData($url,$params){
    	$res = postData($url,$params,'json');
    	if($res == '0' && $this->circle_time <=3){
    		sleep(rand(1,5));
    		$this->circle_time += 1;
    		$this->pushData($url,$params);
    	}
    }
}