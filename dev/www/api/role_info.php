<?php
/**
 * Created by PhpStorm.
 * User: fancy
 * Date: 15/3/26
 * Time: 上午10:56
 */

require '../../Jec/booter.php';

$sn = Jec::getInt('sn');
$accname = g($_GET['accname']);
if($sn > 0 && $accname){
    $sql = "select ps.pkey,ps.sn,ps.nickname ,ps.lv from player_state ps left join player_login pl on ps.pkey = pl.pkey where pl.accname = '$accname'";
    $roleInfo = DB::getInstance('db_game')->getAll($sql);
    if(count($roleInfo)>0){
        $content = array();
        foreach($roleInfo as $key => $role){
            $content[$key]['user_id'] = $accname;
            $content[$key]['game_role_id'] = $role['pkey'];
            $content[$key]['game_role_lv'] = $role['lv'];
            $content[$key]['server_id'] = $role['sn'];
            $content[$key]['game_role_name'] = $role['nickname'];
        }
        $ret = array('ret' => 1,'msg'=> "",'content'=>$content);
    }else{
        $ret = array('ret' => 0,'msg'=> "没有角色",'content'=>"");
    }
    exit(json_encode($ret));
}

