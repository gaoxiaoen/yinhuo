<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-9
 * Time: 15:42
 */

class SMP_Log_RechargePoint extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '集市日志');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and app_role_id={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and nickname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $Sql = "select * from recharge  where $time $where order by time asc ";
        if(Jec::getVar('download')) {
            $pager->setTotalRows($pager->pageRows);
        }
        else
        {
            $offset = $pager->getOffset();
            $limit = $pager->getLimit();
            $Sql .= "limit $offset,$limit";
            $pager->setTotalRows($this->db_game->getOne("select count(*) from recharge  where $time $where order by time asc"));
        }
        $data = $this->db_game->getAll($Sql);
        foreach($data as $key => $val){
            $pkey = $val['app_role_id'];
            $localtime = $val['time'];
            $date = date("Y-m-d ",$localtime);
            $zerotime = strtotime($date."0:0:0");
            $nexttime = $zerotime + 86400;
            $state_row = $this->db_game->getRow("select * from log_player_state where pkey = {$pkey} and time <= {$nexttime}");
            $PackData = array(
                "localtime" => getDateStr($localtime,'Y-m-d'),
                "pkey" => $pkey,
                "pname" => $state_row['pname'],
                "sn" => $state_row['sn'],
                "pf" => $state_row['pf'],
                "reg_time" => getDateStr($state_row['reg_time']),
                "lv" => $state_row['lv'],
                "cbp" => $state_row['cbp'],
                "money" => round($val['total_fee'] / 100),
                "channel_id" => $val['channel_id'],
                "vip_lv" => $state_row['vip_lv'],
            );
            $data[$key] = $PackData;
        }

        if (Jec::getVar('download')) $this->csv_download($data);
        $this->assign('data',$data);
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
        $csv->download('recharge.csv');
    }

}

