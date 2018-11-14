<?php
/**
 * Created by PhpStorm.
 * User: fancy
 * Date: 15/3/19
 * Time: 下午11:21
 */

class Cron_Data {

    public $db = null;

    public function __construct($data)
    {


        $this->db = DB::getInstance('db_game');
        $this->data = $data;
        $method = $data['method'];
        $this->$method();

    }



    /**
     * 更新每日统计
     */
    public function calc_summary()
    {
        $today_st = strtotime(date('Y-m-d'));
        $today_et = $today_st + 86400;
        $today_login = (int)$this->db->getOne("select count(pkey) from player_login where last_login_time > $today_st and last_login_time < $today_et");


        $total_reg = $this->db->getAll("select reg_time from player_login where reg_time > $today_st and reg_time < $today_et");
        $total_reg_num = count($total_reg);
        $time_reg = array();
        foreach($total_reg as $reg){
            $h = date('G',$reg['reg_time']);
            $time_reg[$h] += 1;
        }
        $this->db->replace('cron_daily',array('time'=>$today_st,'login_num'=>$today_login,'reg_num'=>$total_reg_num,'reg_time_data'=>serialize($time_reg)));
    }

    /**
     * 统计玩家流失率
     */

    public function rate_player()
    {
        $args = $this->data['args'][0];
        if($args)
        {
             list($y,$m,$d)=explode('-',$args);
            if(!checkdate($m,$d,$y)) exit("请检查输入的时间是否合法\n");
            $time = strtotime($args);
        }else{
            $time = time();
        }
        $today = strtotime(date('Y-m-d',$time - 60));
        $days = array(1,2,3,4,5,6,9,14,29);
        $rate = array();
        foreach($days as  $day){
            $regtime = $today - $day * 86400;
            $regend = $regtime + 86400;
            $regnum = (int) $this->db->getOne("select count(*) from player_login where reg_time > $regtime and reg_time < $regend");
            $loginnum = (int) $this->db->getOne("select count(*) from player_login where last_login_time > $today and reg_time > $regtime and reg_time < $regend");
            $rate[$day] = $regnum > 0 ? round($loginnum / $regnum,3) : 0;
        }
        $this->db->query("replace into cron_rate_player set date = $today ,d1 = {$rate[1]} ,d2 = {$rate[2]},d3 = {$rate[3]},d4 = {$rate[4]},d5 = {$rate[5]},d6 = {$rate[6]},d7 = {$rate[9]},d15 = {$rate[14]} ,d30 = {$rate[29]}");

    }

    /**
     * ltv 计算
     */
    public function rate_ltv($time = false)
    {
        $today = $time ?  strtotime(date('Y-m-d',$time)) : strtotime(date('Y-m-d',time()- 60));
        $days = array(0,1,2,3,4,5,6,9,14,29);
        $rate = array();
        foreach($days as $day){
            $regtime = $today - $day * 86400;
            $regend = $regtime + 86400;
            $regnum = (int) $this->db->getOne("select count(*) from player_login where reg_time > $regtime and reg_time < $regend");
            $chargesum = (int) $this->db->getOne("select sum(total_fee) from recharge re left join player_login pl on re.app_role_id = pl.pkey where pl.reg_time > $regtime and pl.reg_time < $regend");
            $rate[$day] = $regnum > 0 ? round($chargesum / 100 / $regnum,2) : 0;
        }
        $this->db->query("replace into cron_rate_ltv set date = $today ,d1 = {$rate[0]},d2 = {$rate[1]},d3 = {$rate[2]},d4 = {$rate[3]} ,d5 = {$rate[4]},d6 = {$rate[5]},d7 = {$rate[6]},d10 = {$rate[9]},d15 = {$rate[14]},d30 = {$rate[29]}");
    }

    /**
     * 在线时长比率
     */

    public function rate_online($time = false)
    {
        $today = $time ?  strtotime(date('Y-m-d',$time)) : strtotime(date('Y-m-d',time()- 60));
        $today_end = $today + 86400;
        $logs = $this->db->getAll("select online_time from log_login where time >= $today and time < $today_end");

        $total = count($logs);
        $min10 = 60 * 10; $min20 = 2* $min10; $min30 = 3* $min10; $min40=4*$min10;$min50=5*$min10;$h1=3600;
        $data = array(
            'min10'=>0,'min20'=>0,'min30'=>0,'min40'=>0,'min50'=>0,'h1'=>0,'h2'=>0,'h3'=>0,'h4'=>0,'h5'=>0,'h6'=>0,'h7'=>0,'h8'=>0,'h9'=>0,
            'h10'=>0,'h11'=>0,'h12'=>0,'h13'=>0,'h14'=>0,'h15'=>0,'h16'=>0,'h17'=>0,'h18'=>0,'h19'=>0,'h20'=>0,'h21'=>0,'h22'=>0,'h23'=>0,'h24'=>0

        );
        foreach($logs as $login){
            if($login['online_time'] < $min10) {
                $data['min10'] += 1;
                continue;
            }
            if($login['online_time'] < $min20) {
                $data['min20'] += 1;
                continue;
            }
            if($login['online_time'] < $min30) {
                $data['min30'] += 1;
                continue;
            }
            if($login['online_time'] < $min40) {
                $data['min40'] += 1;
                continue;
            }
            if($login['online_time'] < $min50){
                $data['min50'] += 1;
                continue;
            }
            for($i = 1;$i <= 24;$i ++){
                if($login['online_time'] < $h1 * $i){
                    $data['h'.$i] += 1;
                    break;
                }
            }

        }
        foreach($data as $key=>$num){
            if($total > 0 )
                $pec = round($num/$total,2) * 100;
            else
                $pec = 0;
            $data[$key] = array('num'=>$num ,'pec'=>"$pec");
        }
        $datastr = serialize($data);
        $this->db->query("replace into cron_rate_online set date = $today ,online_data = '$datastr'");

    }

    /**
     * 时间流失率
     */
    public function reate_time()
    {
        $today = strtotime(date('Y-m-d',time() - 60));
        $regtime = $today - 2 * 86400;
        $regend = $regtime + 86400;
        $lose_players = $this->db->getAll("select total_online_time from player_login where reg_time > $regtime and reg_time < $regend and last_login_time < $regend");
        $lose_total = count($lose_players);
        $min10 = 60 * 10; $min20 = 2* $min10; $min30 = 3* $min10; $min40=4*$min10;$min50=5*$min10;$h1=3600;
        $lose_data = array(
            'min10'=>0,'min20'=>0,'min30'=>0,'min40'=>0,'min50'=>0,'h1'=>0,'h2'=>0,'h3'=>0,'h4'=>0,'h5'=>0,'h6'=>0,'h7'=>0,'h8'=>0,'h9'=>0,
            'h10'=>0,'h11'=>0,'h12'=>0,'h13'=>0,'h14'=>0,'h15'=>0,'h16'=>0,'h17'=>0,'h18'=>0,'h19'=>0,'h20'=>0,'h21'=>0,'h22'=>0,'h23'=>0,'h24'=>0

        );
        foreach($lose_players as $player){
            if($player['total_online_time'] < $min10){
                $lose_data['min10'] += 1;
                continue;
            }
            if($player['total_online_time'] < $min20){
                $lose_data['min20'] += 1;
                continue;
            }
            if($player['total_online_time'] < $min30){
                $lose_data['min30'] += 1;
                continue;
            }
            if($player['total_online_time'] < $min40){
                $lose_data['min40'] += 1;
                continue;
            }
            if($player['total_online_time'] < $min50){
                $lose_data['min50'] += 1;
                continue;
            }
            for($i = 1;$i <= 24;$i ++){
                if($player['total_online_time'] < $h1 * $i){
                    $lose_data['h'.$i] += 1;
                    break;
                }
            }

        }
        foreach($lose_data as $key=>$num){
            if($lose_total > 0 )
                $pec = round($num/$lose_total,2) * 100;
            else
                $pec = 0;
            $lose_data[$key] = array('num'=>$num ,'pec'=>"$pec");
        }
        $datastr = serialize($lose_data);
        $this->db->query("replace into cron_rate_time set date = $regtime ,ratetime = '$datastr'");
    }


    /**
     * 全服邮件检查
     */
    public function global_mail()
    {
        global $CONFIG;
        $url = $CONFIG['center']['api'];
        $sn = $CONFIG['game']['sn'];
        if($url){
            $mail = json_decode(file_get_contents($url."/mail.php?act=getGlobalMail&sn=$sn"),true);
            if($mail['id'] > 0){
                $now = time();
                $time = strtotime($mail['time']);
                //5分钟有效果
                $st = $time;
                $et = $time + 600;
                if($st <= $now && $now < $et){
                    $mail_adm = $this->db->getRow("select * from mail_adm where gm_id = {$mail['id']}");
                    if(!$mail_adm['id']){
                        $m['gm_id'] = $mail['id'];
                        $m['time'] = $time;
                        $m['type'] = $mail['type'] == '' ? 1 : $mail['type'];
                        $m['user'] = "global";
                        $m['state'] = 1;
                        $m['players'] = $mail['players'];
                        $m['title'] = $mail['title'];
                        $m['content'] = $mail['content'];
                        $m['goodslist'] = $mail['goodslist'];
                        $m['lv_s'] = $mail['lv_s'];
                        $m['lv_e'] = $mail['lv_e'];
                        $m['reg_time_s'] = $mail['reg_time_s'];
                        $m['reg_time_e'] = $mail['reg_time_e'];
                        $m['login_time_s'] = $mail['login_time_s'];
                        $m['login_time_e'] = $mail['login_time_e'];
                        $m['game_channel_id'] = $mail['game_channel_id'];
                        if($m['type'] === '0')
                        {
                            $mkey = unique_key();
                            $overtime = $now + 86400 * 7;
                            $sql = "insert into mail set mkey = $mkey,pkey = ".$m['players']." ,type = 0,title = '".$m['title']."',content = '".$m['content']."',goodslist = '".$m['goodslist']."',time = $now,overtime = $overtime ";
                            $this->db->query($sql);
	                        Net::rpc_game_server(gm, update_online_mail, array('pkey' => $m['players']));
                            $m['state'] = 2;
                            $m['send_time'] = $now;
                            $this->db->insert('mail_adm',$m);
                        }else{
                            $this->db->insert('mail_adm',$m);
                            $id = $this->db->getInsertId();
                            Net::rpc_game_server(gm,send_mail,array("id"=>$id));
                        }
                    }
                }


            }
        }

    }


    /**
     * 同步充值数据 *历史数据同步回来，临时使用
     */
    public function sync_charge()
    {
        $ts = time();
        $sign = md5("clConFigSyNcData".$ts);
        global $CONFIG;
        $url = $CONFIG['center']['api'];
        $sn =  $CONFIG['game']['sn'];
        if($url && $CONFIG['dev'] != true && $sn < 40000){
            $cache = Cache::getInstance();
            $tag = $cache->get("sync_charge");
            if($tag)
                $start = $tag;
            else
                $start = 0;
            $all = $this->db->getAll("select id,jh_order_id ,app_order_id ,app_role_id,user_id,channel_id,server_id,`time`,total_fee,pay_result,product_id from recharge where id > $start order by id asc limit 50");
            if(!empty($all)){
                $max = 0;
                foreach($all as $d){
                    $max = $d['id'] > $max ? $d['id'] : $max;
                }
                $cache->set('sync_charge',$max,86400);
                $data = json_encode($all);
                $post="type=ccrecharge&sign=$sign&ts=$ts&charge=$data";
                postData($url.'/sync.php',$post);
            }
        }

    }



}