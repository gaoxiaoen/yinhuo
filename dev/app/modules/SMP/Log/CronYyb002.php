<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 0:18
 */
class SMP_Log_CronYyb002 extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '流失统计表');
        $this->show();
    }

    private function show()
    {
        $where = "";
        $kw_key = g(Jec::getVar('kw_key'));
        if ($kw_key) $where = " and pl.pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if ($kw_name) $where .= " and ps.nickname ='{$kw_name}'";
        $kw_key_list = g(Jec::getVar('kw_key_list'));
        if ($kw_key_list) $where = " and pl.pkey not in ({$kw_key_list})";
        $kw_name_list = Jec::getVar('kw_name_list');
        if ($kw_name_list) {
            $filter_name = "";
            foreach (explode(",", $kw_name_list) as $key => $val) {
                $filter_name .= "'" . "$val" . "',";
            }
            $filter_name = rtrim($filter_name, ",");
            $where .= "and ps.nickname not in ($filter_name)";
        }
        $kw_filter_charge = Jec::getVar('kw_filter_charge');
        if ($kw_filter_charge == 1) $where .= " and total_fee > 0 ";
        $time = $this->getWhereTime('time', '0 day', true);
        $pager = new Pager();
        $st = $this->getStartTime();
        $et = $this->getEndTime();
        $u_st = strtotime($st);
        $u_et = strtotime($et);
        $u_et_pre1 = $u_et - 86400;
        $now = time();
        $match_time = time() - 86400 * 2;
        $career_info = $this->_getCareerInfo();
        $Sql = "select
pl.pkey as pkey,
pl.sn as sn,
pl.pf as pf,
pl.last_login_time as last_login_time,
pl.logout_time as logout_time,
pl.reg_time as reg_time,
ps.nickname as pname,
ps.lv as lv,
ps.combat_power as cbp,
ps.vip_lv as vip_lv,
lps.sex,
lps.career,
IFNULL(sum(r.total_fee),0) as total_fee,
IFNULL(max(r.time),0) as last_time
from player_login pl
left join player_state ps on pl.pkey = ps.pkey
left join recharge r on pl.pkey = r.app_role_id and r.time <= {$u_et}
left join log_player_state lps on lps.pkey = pl.pkey and DATE_FORMAT(FROM_UNIXTIME(lps.time),'%Y%m%d') = DATE_FORMAT(FROM_UNIXTIME(pl.logout_time),'%Y%m%d')
where pl.logout_time >= {$u_st} and pl.logout_time <= {$u_et} and pl.logout_time <= $match_time  {$where}
group by pkey order by logout_time asc,reg_time asc,logout_time asc,total_fee desc ,vip_lv desc
 ";
        $RowCount = count($this->db_game->getAll($Sql));
        $pager->setTotalRows($RowCount);
        if (Jec::getVar('download')) {
        } else {
            $offset = $pager->getOffset();
            $limit = $pager->getLimit();
            $Sql .= " limit $offset,$limit";
        }

        $data = $this->db_game->getAll($Sql);
        foreach ($data as $key => $val) {
            if (empty($data[$key]['pkey'])) continue;
            $last_r_time = $data[$key]["last_time"];
            if (!empty($last_r_time)) {
                $r_dif = round(($now - $last_r_time) / 86400);
            }else{
                $r_dif = "";
            }
            //获得当日累计充值金额
            $AccValSql = "select IFNULL(sum(total_fee),0) as total_fee from recharge r where r.app_role_id  = {$data[$key]['pkey']} and r.time >= {$u_et_pre1} and r.time <= {$u_et}";
            $AccValResult = $this->db_game->getOne($AccValSql);
            $data[$key]['acc_val'] = $AccValResult;
            $TaskSql = "select bag from player_task where pkey = {$data[$key]['pkey']} ";
            $TaskResult = $this->db_game->getOne($TaskSql);
            $TaskResult = substr($TaskResult, 1, strlen($TaskResult) - 2);
            $MatchResult = preg_match('/({\w*?,1,\w*?,\S*?,\w*?,\w*?,\w*?,\w*?})/', $TaskResult, $match);
            if ($MatchResult > 0) {
                $TaskString = substr($match[0], 1, strlen($match[0]) - 2);
                $TaskArr = explode(",", $TaskString);
                $TaskId = $TaskArr[0];
            }else{
                $TaskId = "";
            }
            $date = date("Y-m-d", $data[$key]['logout_time']);
            $zerotime = strtotime($date . "0:0:0");
            $curnextzerotime = $zerotime + 86400;
            $LastOnlineTime = $this->db_game->getOne("select sum(online_time) from log_out where pkey = {$data[$key]['pkey']} and time >= {$zerotime} and time <= {$curnextzerotime}");
            $curnextzerotime = $zerotime;
            $zerotime = $zerotime - 86400;
            $LastOnlineTime1 = $this->db_game->getOne("select sum(online_time) from log_out where pkey = {$data[$key]['pkey']} and time >= {$zerotime} and time <= {$curnextzerotime}");
            $curnextzerotime = $zerotime;
            $zerotime = $zerotime - 86400;
            $LastOnlineTime2 = $this->db_game->getOne("select sum(online_time) from log_out where pkey = {$data[$key]['pkey']} and time >= {$zerotime} and time <= {$curnextzerotime}");
            $curnextzerotime = $zerotime;
            $zerotime = $zerotime - 86400;
            $LastOnlineTime3 = $this->db_game->getOne("select sum(online_time) from log_out where pkey = {$data[$key]['pkey']} and time >= {$zerotime} and time <= {$curnextzerotime}");
            $data[$key] = array(
                "time" => date("Y-m-d", $data[$key]['logout_time']),
                "pname" => $data[$key]['pname'],
                "pkey" => $data[$key]['pkey'],
                "sn" => $data[$key]['sn'],
                "pf" => $data[$key]['pf'],
                "reg_time" => getDateStr($data[$key]['reg_time']),
                "acc_val" => round($data[$key]['acc_val'] / 100),
                "lv" => $data[$key]['lv'],
                "cbp" => $data[$key]['cbp'],
                "total_fee" => round($data[$key]['total_fee'] / 100),
                "vip_lv" => $data[$key]['vip_lv'],
                "task_id" => $TaskId,
                "r_dif" => $r_dif,
                "online_time" => round($LastOnlineTime / 60),
                "online_time1" => round($LastOnlineTime1 / 60),
                "online_time2" => round($LastOnlineTime2 / 60),
                "online_time3" => round($LastOnlineTime3 / 60),
                "career"   =>  $career_info[$val['sex'].'_'.$val['career']]
            );
        }
        if (Jec::getVar('download')) $this->csv_download($data);
        $this->assign('data', $data);
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
        $csv->download('cron_yyb002.csv');
    }

    private function _getCareerInfo()
    {
        return [
            '1_1'   =>  '战将',
            '1_2'   =>  '破军斩将',
            '1_3'   =>  '天罡战将',
            '1_4'   =>  '凌霄战皇',
            '1_5'   =>  '无双战神',
            '2_1'   =>  '战姬',
            '2_2'   =>  '破军战姬',
            '2_3'   =>  '天罡战姬',
            '2_4'   =>  '凌霄战姬',
            '2_5'   =>  '无双战姬'
        ];
    }

}

