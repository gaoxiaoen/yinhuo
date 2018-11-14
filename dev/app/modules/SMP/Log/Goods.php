<?php
class SMP_Log_Goods extends AdminController {
	public function __construct() {
		parent::__construct();
		$this->assign('title','物品产销日志');
		$this->show();
	}

	public function show() {
		global $Ggoods;
        $goods = $Ggoods;
        unset($Ggoods);
		$time = $this->getWhereTime('time','0 day',true);
		$uwhere = $cwhere = ' where '.$time;
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) {
        	$cwhere .= " and `create`.`pkey`={$kw_key}";
        	$uwhere .= " and `use`.`pkey`={$kw_key}";
        }
        $kw_name = g(Jec::getVar('kw_name'));
        if($kw_name) {
        	$cwhere .= " and `create`.`nickname` ='{$kw_name}'";
        	$uwhere .= " and `use`.`nickname` ='{$kw_name}'";
        }
        $kw_goods_id = g(Jec::getVar('kw_goods_id'));
        $kw_goods_name = g(Jec::getVar('kw_goods_name'));
        if(!$kw_goods_id) {
            if($kw_goods_name) {
                $kw_goods_id = array_search($kw_goods_name, $goods);
                if(!$kw_goods_id) {
                    $cwhere .= " and `create`.`goods_id`= -1";
                    $uwhere .= " and `use`.`goods_id`= -1";
                }else{
                    $cwhere .= " and `create`.`goods_id`='{$kw_goods_id}'";
                    $uwhere .= " and `use`.`goods_id`='{$kw_goods_id}'";
                }
                $kw_goods_id = '';
            }
        }else{
            $cwhere .= " and `create`.`goods_id`='{$kw_goods_id}'";
            $uwhere .= " and `use`.`goods_id`='{$kw_goods_id}'";
        }
           

        $total_num = 0;

        $data = $pagedata = [];
        $pager = new Pager();
		$offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $res_create = $this->db_game->getAll('select 1 as `is_create`,`create`.*  
        	from `log_goods_add` as `create` '
        		.$cwhere."
        		order by time desc
        		limit $offset,$limit
        		");        
		
		$createNum = $this->db_game->getOne('select count(pkey) 
				from `log_goods_add`  as `create` '
        		.$cwhere);  
        		
        $useNum = $this->db_game->getOne('select count(pkey)
        		from log_goods_subtract as `use` '
        		.$uwhere);
		$total_num = $createNum + $useNum;
		
		$pagedata = [];
		$selCNum = sizeof($res_create);
		if($limit <= $selCNum)
		{
			$pagedata = $res_create;		
		}
		else
		{
			$useNum = $limit - $selCNum;
			$uoffSet = $offset - $createNum;
			
			if($uoffSet <= 0)
				$uoffSet = 0;
			
			$res_use = $this->db_game->getAll('select 0 as `is_create`, `use`.*
        	from `log_goods_subtract` as `use` '
        	.$uwhere." order by time desc
        	limit $uoffSet,$useNum");
			$pagedata = array_merge($res_create, $res_use);
		}	
        	
        
        $pager->setTotalRows($total_num);
		
		// 如果是下载的话， 慢就慢吧
        if (Jec::getVar('download')) {
        	$this->csv_download($cwhere,$uwhere);			
        }
		
        $this->assign('data',$pagedata);
        $this->assign('page', $pager->render());
        $this->assign('params',['kw_key'=>$kw_key,'kw_name'=>$kw_name,'kw_goods_id'=>$kw_goods_id,'kw_goods_name'=>$kw_goods_name]);
        $this->assign('goods', $goods);
        $this->display();
	}

	/*
	 * @param array $data 需要导出的数据data
	 */
    private function csv_download($cwhere,$uwhere)
    {
    	$res_create = $this->db_game->getAll('select 1 as `is_create`,`create`.*  
        	from `log_goods_add` as `create` '
        		.$cwhere.' 
        		order by time desc');
		
		$res_use = $this->db_game->getAll('select 0 as `is_create`, `use`.*
        	from `log_goods_subtract` as `use` '
        	.$uwhere.' order by time desc');
        
        $data = array_merge($res_create,$res_use);
		$data_down  = [];
    	foreach ($data as $result) {
             $data_down[] = [
                'is_create'     =>  $result['is_create'],
                'id'            =>  $result['id'],
                'pkey'          =>  $result['pkey'],
                'nickname'      =>  $result['nickname'],
                'goods_id'      =>  $result['goods_id'],
                'goods_name'    =>  isset($goods[$result['goods_id']]) ? $goods[$result['goods_id']] : 'undefine_'.$result['goods_id'],
                'goods_num'     =>  $result['num'],
                'source'        =>  $result['res'],
                'source_desc'   =>  isset($this->consume_type[$result['res']]) ? $this->consume_type[$result['res']] : 'undefine_'.$result['res'],
                'time'          =>  getDateStr($result['time'])
            ];
        }
    	array_unshift($data_down, ['is_create','id','pkey','nickname','goods_id','goods_name','goods_num','source','source_desc','time']);
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data_down);
        $csv->download('log_goods.csv');
		unset($data);
		unset($data_down);
    }
}