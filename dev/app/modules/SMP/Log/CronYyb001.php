<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 0:18
 */
class SMP_Log_CronYyb001 extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '应用宝统计001');
        $this->show();
    }

    private function show()
    {
        $where = "";
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pl.pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and ps.nickname ='{$kw_name}'";
        $kw_key_list = g(Jec::getVar('kw_key_list'));
        if($kw_key_list) $where = " and pl.pkey not in ({$kw_key_list})";
        $kw_name_list = Jec::getVar('kw_name_list');
        if($kw_name_list){
            $filter_name = "";
            foreach(explode(",",$kw_name_list) as $key => $val){
                $filter_name .="'"."$val"."',";
            }
            $filter_name = rtrim($filter_name,",");
            $where .= "and ps.nickname not in ($filter_name)";
        }
        $kw_filter_charge = Jec::getVar('kw_filter_charge');
        if ($kw_filter_charge == 1) $where .= " and total_fee > 0 ";
        $time = $this->getWhereTime('time', '0 day', true);
        $pager = new Pager();
        $row_num = 0;
        $date_st = Jec::getVar('date_st') ? Jec::getVar('date_st') : $this->getStartTime();
        $date_et = Jec::getVar('date_et') ? Jec::getVar('date_et') : $this->getEndTime();
        $datetime1 = new DateTime($date_st);
        $datetime2 = new DateTime($date_et);
        if(abs(strtotime($date_et) - strtotime($date_st)) > 62*86400) throw new JecException("不能超过62天");
        $day_dif = $datetime1->diff($datetime2);
        $day_dif = $day_dif->days;
        $u_st = strtotime($date_st);
        $base_data = array();
        for ($i = 0; $i <= $day_dif; $i++) {
            $st = $u_st + $i * 86400;
            $et = $u_st + ($i + 1) * 86400 - 1;
            $Sql = "
select
distinct llg.pkey as pkey,
llg.pname as pname,
pl.accname,
llg.vip_lv as vip_lv,
pl.pf as pf,
pl.sn as sn,
pl.reg_time as reg_time ,
IFNULL(sum(r.total_fee),0) as total_fee,
llg.cbp as cbp
from log_player_state llg
left join player_login pl on llg.pkey = pl.pkey
left join recharge r on llg.pkey = r.app_role_id and r.time >= {$st} and r.time <= {$et}
left join player_state ps on llg.pkey = ps.pkey
where
llg.time >= {$st} and llg.time <= {$et} ".$where."
 group by pkey order by total_fee desc,pl.reg_time asc";
            $data = $this->db_game->getAll($Sql);
            foreach ($data as $key => $val) {
                $row_num ++ ;
                $data[$key]['time'] =  date("Y-m-d", $st);
                //获得当日最高等级
                $LvSql = "select lv from log_player_state where pkey = {$data[$key]['pkey']}  and time  >= {$st} and time <= {$et}";
                $LvResult = $this->db_game->getOne($LvSql);
                $data[$key]['lv'] = $LvResult;
                //获得至当日累计充值金额
                $AccValSql = "select IFNULL(sum(total_fee),0) as total_fee from recharge r where r.app_role_id  = {$data[$key]['pkey']} and r.time <= {$et}";
                $AccValResult = $this->db_game->getOne($AccValSql);
                $data[$key]['acc_val'] = $AccValResult;
                $pack_date = array(
                    "time" => getDateStr($data[$key]['time'],'Y-m-d'),
                    "pname" => $data[$key]['pname'],
                    'accname'=>$data[$key]['accname'],
                    "pkey" => $data[$key]['pkey'],
                    "sn" => $data[$key]['sn'],
                    "pf" => $data[$key]['pf'],
                    "reg_time" => getDateStr($data[$key]['reg_time']),
                    "total_fee" => round($data[$key]['total_fee'] / 100),
                    "lv" => $data[$key]['lv'],
                    "cbp" => $data[$key]['cbp'],
                    "acc_val" => round( $data[$key]['acc_val'] / 100 ),
                    "vip_lv" => $data[$key]['vip_lv'],
                );
                $data[$key] = $pack_date;
                $base_data[] = $data[$key];
            }
        }
        $data = $base_data;
        $pager->setTotalRows($row_num);
        if (Jec::getVar('download')) 
        {
//            $data[] = array(
//                "时间",
//                "玩家角色名",
//                "玩家ID",
//                "玩家区服",
//                "注册渠道",
//                "注册时间",
//                "当日充值额",
//                "当日等级",
//                "当日战力",
//                "当日为止的累计充值",
//                "当前VIP等级",
//            );
            $this->csv_download($data);
        }
        $this->assign('data', $data);
        $this->assign('date_st',$date_st);
        $this->assign('date_et',$date_et);
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
        $csv->download('cron_yyb001.csv');
    }

}

