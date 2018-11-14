<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-9
 * Time: 15:42
 */

class SMP_Log_PlayerSceneEnter extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '今日进入场景次数统计');

        $this->show();
    }

    private function show(){
        $where = '';
        $req_params['kw_scene_id'] = g(Jec::getVar('kw_scene_id'));
        if($req_params['kw_scene_id']) $where .= " and scene_id= ".$req_params['kw_scene_id'];
        $req_params['kw_dun_type'] = g(Jec::getVar('kw_dun_type'));
        if($req_params['kw_dun_type']) $where .= " and dungeon_type= ".$req_params['kw_dun_type'];
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $Sql = "select * from log_player_scene_enter  where $time $where order by time asc ";
        if(Jec::getVar('download')) {
            $pager->setTotalRows($pager->pageRows);
        }
        else
        {
            $offset = $pager->getOffset();
            $limit = $pager->getLimit();
            $Sql .= "limit $offset,$limit";
            $pager->setTotalRows($this->db_game->getOne("select count(*) from log_player_scene_enter  where $time $where order by time asc"));
        }
        $data = $this->db_game->getAll($Sql);
        foreach($data as $key => $val ){
            $Pkey = $val['pkey'];
            $time = $val['time'];
            $date = date("Y-m-d ",$time);
            $zerotime = strtotime($date."0:0:0");
            $nexttime = $zerotime + 86400;
            $player_state =  $this->db_game->getRow("select * from log_player_state where pkey = {$Pkey} and time >= {$zerotime} and time <= {$nexttime}");
            $TodayRecharge = $this->db_game->getOne("select IFNULL(sum(total_fee),0) as total_fee from recharge where  app_role_id = {$Pkey} and time >= {$zerotime} and time <= {$nexttime} ");
            $AllRecharge = $this->db_game->getOne("select IFNULL(sum(total_fee),0) as total_fee from recharge where  app_role_id = {$Pkey} and time <= {$nexttime} ");
            $pack_date = array(
                "time" => getDateStr($data[$key]['time'],'Y-m-d'),
                "scene_id" => $data[$key]['scene_id'],
                "scene_name" => $data[$key]['scene_name'],
                "cnt" => $data[$key]['cnt'],
                "pname" => $player_state['pname'],
                "pkey" => $player_state['pkey'],
                "sn" => $player_state['sn'],
                "pf" => $player_state['pf'],
                "reg_time" => $player_state['reg_time'],
                "today_recharge" => round( $TodayRecharge / 100),
                "lv" => $player_state['lv'],
                "cbp" => $player_state['cbp'],
                "recharge" => round($AllRecharge / 100),
                "vip_lv" => $player_state['vip_lv'],
            );
            $data[$key] = $pack_date;
        }
        $scene_info = [];
        $scene_db = $this->db_game->getAll("select scene_id,scene_name from log_player_scene_enter where $time order by scene_name");
        foreach ($scene_db as $v) 
        {
            $scene_info[$v['scene_id']] = $v['scene_name'];
        }

        if (Jec::getVar('download')) $this->csv_download($data);
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->assign('req_params',$req_params);
        $this->assign('scene_info',$scene_info);
        $this->display();
    }

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data)
    {
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('log_player_scene_enter.csv');
    }

}

