<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Player_Detail extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '角色详细属性');
        $act = Jec::getVar('act');
        if($act == 'set_val') {
            $this->set_val();
        }
        if($act == 'make_order'){
            $this->make_order();
        }
        if($act == 'change_account'){
            $this->change_account();
        }
        if($act == 'change_vip'){
            $this->change_vip();
        }
        $this->show();
    }
    
     private function show()
    {
        $pkey = Jec::getVar('pkey');
        $player = $this->db_game->getRow("select * from player_state where pkey = $pkey");
        $login = $this->db_game->getRow("select * from player_login where pkey = $pkey");
        $this->assign('login',$login);
        $this->assign('player',$player);
        $this->display();
    }



    private function set_val()
    {
        $pkey = Jec::getVar('pkey');
        $val = Jec::getVar('val');
        $key = Jec::getVar('key');
        if($key != 'nickname'){
            $val = abs($val);
            $OldVal = $this->db_game->getOne("select $key from player_state where pkey = $pkey");
            if($val > $OldVal && !isAdmin())
                throw new JecException('你没有权限增加资源');
        }
        Log::info('SMP_Player_Detail',"修改[{$pkey}][$key=$val]");
        Net::rpc_game_server(gm,kick_off,array('pkey'=>$pkey));
        $this->db_game->update('player_state',array($key=>$val),array('pkey'=>$pkey));
    }

    private function make_order()
    {
        $pkey = Jec::getVar('pkey');
        $sn = Jec::getInt('sn');
        $pf = Jec::getInt('pf');
        $nickname = Jec::getVar('nickname');
        $productid = Jec::getInt('productid');
        $money = Jec::getInt('money');
        $time = time();
        $order = $time . rand(10000,90000);
        $data = array(
            'jh_order_id' => $order,
            'app_order_id' =>  $order,
            'app_role_id' => $pkey,
            'user_id' => "内部账号",
            'channel_id' => $pf,
            'server_id' => $sn,
            'time' => $time,
            'total_fee' => $money * 100,
            'product_id' => $productid,
            'pay_result' => 1,
            'lv' => 0,
            'career' => 0,
            'nickname' => $nickname,
            'state' => 1
        );
        $this->db_game->insert('recharge',$data);
        alert("内部充值订单已生成！");
    }

    private function change_account()
    {
        $pkey = Jec::getVar('pkey');
        $account = Jec::getVar('accname');
        $exists = $this->db_game->getRow("select pkey from player_login where accname = '$account'");
        if($exists['pkey']){
            alert("账号已存在，请使用唯一账号!");
        }else {
            $this->db_game->update('player_login', array('accname' => $account), array('pkey' => $pkey));
            Log::info('SMP_Player_Detail',"修改账号[{$pkey}][$account]");
            alert("修改成功！");
        }
    }

    private function change_vip()
    {
        $pkey = Jec::getVar('pkey');
        $vip = Jec::getInt('vip');
        Net::rpc_game_server(gm,kick_off,array('pkey'=>$pkey));
        $this->db_game->update('vip',array('val'=>$vip),array('pkey'=>$pkey));
        Log::info('SMP_Player_Detail',"修改vip[{$pkey}][$vip]");
        alert("修改成功!");
    }
    

}