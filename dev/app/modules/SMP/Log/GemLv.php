<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-12
 * Time: 13:45
 */

class SMP_Log_GemLv extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '全身宝石日志');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and pname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $Sql = "select * from log_player_state  where $time $where order by time asc ";
        if(Jec::getVar('download')) {
            $pager->setTotalRows($pager->pageRows);
        }
        else
        {
            $offset = $pager->getOffset();
            $limit = $pager->getLimit();
            $Sql .= "limit $offset,$limit";
            $pager->setTotalRows($this->db_game->getOne("select count(*) from log_player_state  where $time $where order by time asc"));
        }
        $data = $this->db_game->getAll($Sql);
        $data1 = array();
        $row_num = 0;
        foreach($data as $key => $val){
            $pkey = $val['pkey'];
            $time = $val['time'];
            $date = date("Y-m-d ",$time);
            $zerotime = strtotime($date."0:0:0");
            $nexttime = $zerotime + 86400;
            $TodayRecharge = $this->db_game->getOne("select IFNULL(sum(total_fee),0) as total_fee from recharge where  app_role_id = {$pkey} and time >= {$zerotime} and time <= {$nexttime} ");
            $AllRecharge = $this->db_game->getOne("select IFNULL(sum(total_fee),0) as total_fee from recharge where  app_role_id = {$pkey} and time <= {$nexttime} ");
            $PackData = array(
                "time" => getDateStr($data[$key]['time'],'Y-m-d'),
                "pkey" => $pkey,
                "pname" => $val['pname'],
                "sn" => $val['sn'],
                "pf" => $val['pf'],
                "reg_time" => getDateStr($val['reg_time']),
                "today_recharge" => round( $TodayRecharge / 100),
                "lv" => $val['lv'],
                "cbp" => $val['cbp'],
                "recharge" => round($AllRecharge / 100),
                "vip_lv" => $val['vip_lv'],
                "all_red_gem_num" => $val['all_red_gem_num'],
                "all_red_gem_lv" => $val['all_red_gem_lv'],
                "all_blue_gem_num" => $val['all_blue_gem_num'],
                "all_blue_gem_lv" => $val['all_blue_gem_lv'],
            );
            $data1[] = $PackData;
        }
        $pager->setTotalRows($row_num);
        if (Jec::getVar('download')) $this->csv_download($data1);
        $this->assign('data',$data1);
        $this->assign('page', $pager->render());
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
        $csv->download('log_gem_lv.csv');
    }

}

