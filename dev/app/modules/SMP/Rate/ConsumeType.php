<?php
Class SMP_Rate_ConsumeType extends AdminController {
	public function __construct () {
		parent::__construct();
		$this->assign('title','消费项目统计');
		$this->show();
	}

	public function show() {
		$params['select_type'] = Jec::getInt('type');
		$where = ' where '.$this->getWhereTime('time','-3 day','true');
		if($params['select_type']) $where .= ' and type = '.$params['select_type'];
		$cron_data = $this->db_game->getAll("select * from cron_consume_type $where order by time desc");
		if($cron_data) {
			$formCate = $this->getFormConsumeCate();
			foreach($cron_data as $item) {
				$unseria_data = unserialize($item['data']);
				$date = date('Y-m-d',$item['time']);
				if($unseria_data) {
					foreach ($unseria_data as $data) {
						$cate_id   = $formCate[$data['addreason']]['cate_id'] ? $formCate[$data['addreason']]['cate_id'] : 0;
						$cate_name = $formCate[$data['addreason']]['name'] ? $formCate[$data['addreason']]['name'] : '未定义类型';
						$count_data[$date][$cate_id]['cid']         = $cate_id;
						$count_data[$date][$cate_id]['addreason']   = $data['addreason'];
						$count_data[$date][$cate_id]['cate_name']   = $cate_name;
						$count_data[$date][$cate_id]['num']		   += $data['num'];
						$count_data[$date][$cate_id]['currency']   += $data['currency'];
						$count_data[$date][$cate_id]['account']	   += $data['account'];
						$count_data[$date][$cate_id]['detail'][$data['addreason']]['addreason'] = $data['addreason'];  //usort排好序后健名会重置，所以需要在把addreason添加到数组里
						$count_data[$date][$cate_id]['detail'][$data['addreason']]['num'] += $data['num'];
						$count_data[$date][$cate_id]['detail'][$data['addreason']]['account'] += $data['account'];
						$count_data[$date][$cate_id]['detail'][$data['addreason']]['currency'] += $data['currency'];
						$total_data[$date]['num'] 				   += $data['num'];
						$total_data[$date]['account'] 			   += $data['account'];
						$total_data[$date]['currency'] 			   += $data['currency'];
					}
				}
			}
			//处理分类数据排序问题
			if(is_array($count_data) && !empty($count_data)){
				foreach($count_data as &$v) {
					usort($v,function($a,$b){
						if($a['currency'] == $b['currency']) return 0;
						return $a['currency'] > $b['currency'] ? -1 : 1;
					});
					//处理分类里面消费点排序
					foreach($v as &$item) {
						if(is_array($item['detail']) && !empty($item['detail'])) {
							usort($item['detail'],function($c,$d){
								if($c['currency'] == $d['currency']) return 0;
								return $c['currency'] > $d['currency'] ? -1 : 1;
							});
						}
					}
				}
			}
			$this->assign('count_data',$count_data);
			$this->assign('total_data',$total_data);
			//获取消费分类名称
//			$consume_type_db = $this->db->getAll("select id,name from consume_type");
//			if($consume_type_db) {
//				foreach($consume_type_db as $value) {
//					$consume_type_info[$value['id']] = $value['name'];
//				}
//			}
			$this->assign('consume_type_data',$this->consume_type);
		}
		$type = ['1'=>'水晶','3'=>'金币'];
		$this->assign('type',$type);
		$this->assign('params',$params);
		$this->display();
	}

	/**
	 * 获取消费分类数据
	 */
	private function getFormConsumeCate () {
		$cate = $this->db->getAll("select t.id,t.cate_id,c.name from consume_type t left join consume_cate c on c.cate_id = t.cate_id");
		if(!$cate) { 
			$this->getCenterConsumeCate();
			$cate = $this->db->getAll("select t.id,t.cate_id,c.name from consume_type t left join consume_cate c on c.cate_id = t.cate_id");
		}
		$returnCate = [];
		foreach ($cate as $c) {
			$returnCate[$c['id']] = $c;
		}
		unset($cate);
		return $returnCate;
	}

	/**
	 * adm库的cate 和 type为空时，主动向中央服拉取数据
	 */
	private function getCenterConsumeCate() {
		global $CONFIG;
        $url    = $CONFIG['center']['api']."/consume_cate.php?&act=getCateInfo";
        $time   = time();
        $params = ['time'=>$time,'sign'=>md5('get_center_consume_cate_date_key'.$time)];
        $res = json_decode(postData($url,$params),true);
        if(is_array($res['type']) && !empty($res['type'])) {
			foreach($res['type'] as $v) {
				if(!$v['cate_id']) $v['cate_id'] = '0';
				$type_sql .= '('.$v['id'].',"'.$v['name'].'",'.$v['cate_id'].'),';
			}
			$type_sql = substr($type_sql,0,-1);
			$res_type = $this->db->query("insert into consume_type (id,name,cate_id) values $type_sql");
		}

		if(is_array($res['cate']) && !empty($res['cate'])) {
			foreach($res['cate'] as $v) {
				if(!$v['name']) $v['name'] = '未定义';
				$cate_sql .= '('.$v['cate_id'].',"'.$v['name'].'"),';
			}
			$cate_sql = substr($cate_sql,0,-1);
			$res_cate = $this->db->query("insert into consume_cate (cate_id,name) values $cate_sql");
		}
	}

}