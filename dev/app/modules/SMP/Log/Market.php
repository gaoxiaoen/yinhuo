<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-9
 * Time: 15:42
 */

class SMP_Log_Market extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '集市日志');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) {
            if(strpos($kw_key,",") > 0){
                $where = " and pkey in ({$kw_key})";
            }else
                $where = " and pkey={$kw_key}";
        };
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and nickname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $Sql = "select * from log_market  where $time $where order by time asc ";
        if(Jec::getVar('download')) {
            $pager->setTotalRows($pager->pageRows);
        }
        else
        {
            $offset = $pager->getOffset();
            $limit = $pager->getLimit();
            $Sql .= "limit $offset,$limit";
            $pager->setTotalRows($this->db_game->getOne("select count(*) from log_market  where $time $where order by time asc"));
        }
        $data = $this->db_game->getAll($Sql);
        global $Ggoods;
        foreach($data as $key => $val){
            $pkey = $val['pkey'];
            $localtime = $val['time'];
            $date = date("Y-m-d ",$localtime);
            $zerotime = strtotime($date."0:0:0");
            $nexttime = $zerotime + 86400;
            $state_row = $this->db_game->getRow("select * from log_player_state where pkey = {$pkey} and time <= {$nexttime}");
            $TodayRecharge = $this->db_game->getOne("select IFNULL(sum(total_fee),0) as total_fee from recharge where  app_role_id = {$pkey} and time >= {$zerotime} and time <= {$nexttime} ");
            $AllRecharge = $this->db_game->getOne("select IFNULL(sum(total_fee),0) as total_fee from recharge where  app_role_id = {$pkey} and time <= {$nexttime} ");
            $PackData = array(
                "localtime" => getDateStr($localtime,'Y-m-d'),
                "pkey" => $pkey,
                "pname" => $state_row['pname'],
                "sn" => $state_row['sn'],
                "pf" => $state_row['pf'],
                "reg_time" => getDateStr($state_row['reg_time']),
                "today_recharge" => round( $TodayRecharge / 100),
                "lv" => $state_row['lv'],
                "cbp" => $state_row['cbp'],
                "recharge" => round($AllRecharge / 100),
                "vip_lv" => $state_row['vip_lv'],
                "type" => $val['type'],
                "goods_id" => $val['goods_id'],
                "goods_name" => $Ggoods[$val['goods_id']],
                "num" => $val['num'],
                "price" => $val['price'],
                "time" => getDateStr($localtime),
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
        $csv->download('log_market.csv');
    }

}

