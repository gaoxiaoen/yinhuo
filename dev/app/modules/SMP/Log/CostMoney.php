<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/3/24
 * Time: 下午2:51
 */

class SMP_Log_CostMoney extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '货币消耗率');

        $this->show();
    }

    private function show(){

        $type = Jec::getInt('money_type');
        $time = $this->getWhereTime('time','0 day',true);

        if(!$type || $type == 1){
            $name = '钻石';
            //$sum = $this->db_game->getOne("select sum(oldgold-newgold) as sum_cost from log_gold where oldgold > newgold and time >= $st and time <= $et");
            $all = $this->db_game->getAll("select sum(oldgold-newgold) as cost,addreason from log_gold where $time and oldgold > newgold group by addreason");
            $alltimes = $this->db_game->getAll("select count(*) as num,addreason from (select * from log_gold where oldgold > newgold and $time group by addreason,pkey) as tt group by tt.addreason;");
        }elseif($type == 2){
            $name = '绑定钻石';
            $all = $this->db_game->getAll("select sum(oldbgold-newbgold) as cost,addreason from log_gold where $time and oldbgold > newbgold group by addreason");
            $alltimes = $this->db_game->getAll("select count(*) as num,addreason from (select * from log_gold where $time and oldbgold > newbgold group by addreason,pkey) as tt group by tt.addreason;");
        }else{
            $name = '金币';
            $all = $this->db_game->getAll("select sum(-addcoin) as cost,addreason from log_coin where $time and addcoin < 0 group by addreason");
            $alltimes = $this->db_game->getAll("select count(*) as num,addreason from (select * from log_coin where $time and addcoin < 0 group by addreason,pkey) as tt group by tt.addreason;");
        }

        //总消耗
        $sum = 1;
        foreach($all as $a){
            $sum += $a['cost'];
        }

        foreach($all as &$a){
            $a['cost_pro'] = round($a['cost']/$sum*10000)/100 . "%" ;
            $a['cost'] .= $name;
            foreach($alltimes as $at){
                if($at['addreason'] == $a['addreason']){
                    $a['player'] = $at['num'];
                    continue;
                }
            }
        }

        $money_type = array(1=>'钻石',2=>'绑定钻石',3=>'金币');
        $this->assign('money_type',$money_type);
        $this->assign('data',$all);
        $this->display();
    }

}