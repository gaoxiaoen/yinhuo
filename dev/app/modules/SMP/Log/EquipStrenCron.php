<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-12
 * Time: 13:45
 */

class SMP_Log_EquipStrenCron extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '装备强化日志');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and pname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $Sql = "select lps.pname as pname,lps.pkey as pkey,lps.sn as sn,lps.pf as pf,lps.reg_time as reg_time,lps.lv as lv,lps.cbp as cbp,lps.vip_lv as vip_lv,lps.time as time from log_player_state  lps where $time $where order by time asc ";
        if(Jec::getVar('download')) {
            $pager->setTotalRows($pager->pageRows);
        }
        else
        {
            $offset = $pager->getOffset();
            $limit = $pager->getLimit();
            $Sql .= "limit $offset,$limit";
            $pager->setTotalRows($this->db_game->getOne("select count(*) from log_player_state  where $time $where order by time asc ") * 10);
            $pager->pageRows = $pager->pageRows * 10;
        }
        $data = $this->db_game->getAll($Sql);
        $data1 = array();
        foreach($data as $key => $val){
            $date = date("Y-m-d ", $val['time']);
            $zerotime = strtotime($date."0:0:0");
            $nexttime = $zerotime + 86400;
            $TodayRecharge = $this->db_game->getOne("select IFNULL(sum(total_fee),0) as total_fee from recharge where  app_role_id =  {$val['pkey']} and time >= {$zerotime} and time <= {$nexttime} ");
            $AllRecharge = $this->db_game->getOne("select IFNULL(sum(total_fee),0) as total_fee from recharge where  app_role_id =  {$val['pkey']} and time <= {$nexttime} ");
            $AllLv= 0;
            $LvArr = array();
            for($i = 1;$i <=10 ;$i++){
                $StrLv = $this->db_game->getOne("select new_stren from log_equip_stren where pkey =  {$val['pkey']} and subtype = {$i} and  time <= {$nexttime} order by time desc limit 1");
                if(empty($StrLv)) $StrLv = 0;
                $AllLv = $AllLv + $StrLv;
                $LvArr[$i] = $StrLv;
            }
            $aver_lv = $AllLv / 10;
            foreach($LvArr as $k => $v){
                $data1[] = array(
                    "time" => getDateStr($data[$key]['time'],'Y-m-d'),
                    "pkey" =>  $val['pkey'],
                    "pname" => $val['pname'],
                    "sn" => $val['sn'],
                    "pf" => $val['pf'],
                    "reg_time" => getDateStr($val['reg_time']),
                    "today_recharge" => round( $TodayRecharge / 100),
                    "lv" => $val['lv'],
                    "cbp" => $val['cbp'],
                    "recharge" => round($AllRecharge / 100),
                    "vip_lv" => $val['vip_lv'],
                    "aver_lv" => $aver_lv,
                    "subtype" => $k,
                    "str_lv" => $v,
                );
            }
        }
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
        $csv->download('log_equip_stren_cron.csv');
    }

}
