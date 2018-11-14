<?php
/**
 * Created by PhpStorm.
 * User: fancy
 * Date: 15/3/18
 * Time: 下午3:39
 */

require '../../Jec/booter.php';
$sign = Jec::getString('sign');
$ts = Jec::getString('ts');
$type = Jec::getString('type');
$mysign =md5("clConFigSyNcData".$ts);
if($mysign != $sign)
    exit("-1");
if($type == 'menu'){
    $menus = SMP_Menu_Helper::getMenus(true);
    exit(json_encode($menus));
}
if($type == 'group'){
    $db = DB::getInstance();
    $groups = $db->getAll("select * from user_groups");
    exit(json_encode($groups));
}
if($type == 'import_menu'){
    $datamenus = Jec::getVar('datamenus');
    $datagroups = Jec::getVar('datagroups');
    if($datamenus && $datagroups){
        global $CONFIG;
        $menus = unserialize(urldecode($datamenus));
        $groups = unserialize(urldecode($datagroups));
        if(is_array($menus) && count($menus) >0 && !$CONFIG['center']['state']){
            $db = DB::getInstance('db_admin');
            $db ->query("truncate table menus");
            foreach($menus as $parent){
                $sub = $parent['sub'];
                unset($parent['sub']);
                $db->insert("menus",$parent);
                foreach($sub as $m){
                    $db->insert('menus',$m);
                }
            }
            $db->query("truncate table user_groups");
            foreach($groups as $group){
                $db->insert('user_groups',$group);
            }
            SMP_Menu_Helper::clearCache();
        }else{
            exit("20001");
        };
    }
}
if($type == "gameinfo"){
    $db = DB::getInstance('db_game');
    $onlines = $db->getAll("select game_channel_id from player_login where online = 1");
    $onlieInfo = array();
    foreach($onlines as $ol){
        $onlieInfo['online_data'][$ol['game_channel_id']] += 1;
    }
    $onlieInfo['total'] = count($onlines);
    $data = array('online'=>$onlieInfo);
    exit(json_encode($data));
}
exit("0");