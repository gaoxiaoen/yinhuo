<?php
/**
 * Created by PhpStorm.
 * User: fancy
 * Date: 15/3/19
 * Time: 下午11:21
 */

class Cron_Charge {

    public $db = null;

    public function __construct($data)
    {


        $this->db = DB::getInstance('db_game');
        $method = $data['method'];
        $this->$method();

    }

    /**
     * 当天充值统计
     */
    public function charge_calc()
    {

        $yesterday = strtotime("yesterday");
        $this->db->query("delete from cron_recharge where time = $yesterday");
        $st = $yesterday;
        $et = $st + 86399;
        $sql = "select re.* ,pl.reg_time  from recharge re left join player_login pl on pl.pkey = re.app_role_id where re.time >= $st and re.time < $et";
        $rows = $this->db->getAll($sql);
        if(count($rows)>0){
            $totalFee = 0;
            $user_old = array();
            foreach($rows as $d){
                $user[$d['app_role_id']][] = 1;
                if($d['reg_time'] < $st){
                    $user_old[$d['app_role_id']][] = 1;
                }
                $totalFee += $d['total_fee'];
            }
            $userNum = count($user);
            $oldUserNum = count($user_old);
            $newUserNum = $userNum - $oldUserNum;
            $chargeNum = count($rows);
            $daily = $this->db->getRow("select login_num,reg_num from cron_daily where time = $st");
            $chargeNewRate = $chargeActRate = 0;
            if(isset($daily['login_num'])){
                $oldUserTotal = $daily['login_num'] - $daily['reg_num'];
                $newUserTotal = $daily['reg_num'];
                $chargeNewRate = $newUserTotal > 0 ? round($newUserNum / $newUserTotal,4) : 0;
                $chargeActRate = $oldUserTotal > 0 ? round($oldUserNum / $oldUserTotal,4) : 0 ;
            }
            $this->db->insert('cron_recharge',array('time'=>$st,'total_fee'=>$totalFee,'charge_times'=>$chargeNum,'charge_users'=>$userNum,
                'charge_new_users'=>$newUserNum,'charge_new_rate'=>$chargeNewRate,'charge_act_users'=>$oldUserNum,'charge_act_rate'=>$chargeActRate));
        }else{
            $this->db->insert('cron_recharge',array('time'=>$st,'total_fee'=>0,'charge_times'=>0,'charge_users'=>0,
                'charge_new_users'=>0,'charge_new_rate'=>0,'charge_act_users'=>0,'charge_act_rate'=>0));
        }


    }




}