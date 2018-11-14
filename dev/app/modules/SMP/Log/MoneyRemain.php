<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/3/24
 * Time: 下午2:51
 */

class SMP_Log_MoneyRemain extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '货币滞留率');

        $this->show();
    }

    private function show(){

        $data = $this->db_game->getRow("select sum(gold) as l_gold,sum(bgold) as l_bgold,sum(coin) as l_coin,sum(bcoin) as l_bcoin from player_state");
        $data1 = $this->db_game->getRow("select gold,bgold,coin,bcoin from cron_money order by `time` desc limit 1");
        $d = array();
        $d[0] = array('goods_name'=>'钻石','num'=>$data1['gold'],'leave_num'=>$data['l_gold'],'pro'=>round($data['l_gold']/max(0.0001,$data1['gold']),2));
        $d[1] = array('goods_name'=>'绑定钻石','num'=>$data1['bgold'],'leave_num'=>$data['l_bgold'],'pro'=>round($data['l_bgold']/max(0.0001,$data1['bgold']),2));
        $d[2] = array('goods_name'=>'金币','num'=>$data1['coin'],'leave_num'=>$data['l_coin'],'pro'=>round($data['l_coin']/max(0.0001,$data1['coin']),2));
        $d[3] = array('goods_name'=>'绑定金币','num'=>$data1['bcoin'],'leave_num'=>$data['l_bcoin'],'pro'=>round($data['l_bcoin']/max(0.0001,$data1['bcoin']),2));

        $todayStr = date("Y-m-d",time());
        $todayTime = strtotime($todayStr);
        $data2 = $this->db_game->getRow("select sum(gold) as l_gold,sum(bgold) as l_bgold,sum(coin) as l_coin,sum(bcoin) as l_bcoin from player_state a LEFT JOIN player_login b on a.pkey=b.pkey where b.last_login_time > $todayTime");
        $data3 = $this->db_game->getRow("select gold,bgold,coin,bcoin from cron_cur_money order by `time` desc limit 1");

        $d[4] = array('goods_name'=>'今天钻石','num'=>$data3['gold'],'leave_num'=>$data2['l_gold'],'pro'=>round($data2['l_gold']/max(0.0001,$data3['gold']),2));
        $d[5] = array('goods_name'=>'今天绑定钻石','num'=>$data3['bgold'],'leave_num'=>$data2['l_bgold'],'pro'=>round($data2['l_bgold']/max(0.0001,$data3['bgold']),2));
        $d[6] = array('goods_name'=>'今天金币','num'=>$data3['coin'],'leave_num'=>$data2['l_coin'],'pro'=>round($data2['l_coin']/max(0.0001,$data3['coin']),2));
        $d[7] = array('goods_name'=>'今天绑定金币','num'=>$data3['bcoin'],'leave_num'=>$data2['l_bcoin'],'pro'=>round($data2['l_bcoin']/max(0.0001,$data3['bcoin']),2));
        $this->assign('data',$d);
        $this->display();
    }

}