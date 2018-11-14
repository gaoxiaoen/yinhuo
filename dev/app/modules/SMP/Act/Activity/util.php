<?php

function erl_head($moduleName,$export_function,$hrl_array){
    $showtime=date("Y-m-d H:i:s");
    $erlCode="%% 配置生成时间 ".$showtime."";
    $erlCode .= "
-module({$moduleName}).\n";
    if(is_array($export_function))
        foreach($export_function as $function)
            $erlCode.= "-export([$function]).\n";
    else {
        echo "$moduleName export_function error \n";
        print_r($export_function);
        echo "\n";
    }


    foreach($hrl_array as $hrl){
        $erlCode.= "-include(\"$hrl\").\n";
    };

    return $erlCode;
}

function lua_head($configName){
    $createAt = Date("Y-m-d H:i:s");
    $luaCode ="
-- @Author  : 苍狼工作室
Config = Config or {}
Config.$configName = ";
    return $luaCode;
}

function to_file($fileName, $Code){
    if (@$fp = fopen($fileName, 'w')) {
        if(fwrite($fp, $Code) && fclose($fp)){
            return true;
        }else{
            return false;
        }
    }

}


function attr_list_to_array($attr_list){
    $attr1  = explode("|",$attr_list);
    for ($i = 0;$i<count($attr1);$i++){
        $attr_tmp = explode(",",$attr1[$i]);
        $attr1[$i] = $attr_tmp;
    }
    return $attr1;
}

function attr_array_to_lua_string($attr_array){
    $rec = "";
    for ($i = 0;$i<count($attr_array);$i++){
        $attr = $attr_array[$i];
        if($attr[0] !="")
            $rec .= "{\"{$attr[0]}\",{$attr[1]}},";
    }
    return rtrim($rec,',');
}

function attr_array_to_erlang_string($attr_array){
    $rec = "";
    for ($i = 0;$i<count($attr_array);$i++){
        $attr = $attr_array[$i];
        if($attr[0] !=""){
            $rec .= "{";
            for($iii = 0;$iii<count($attr);$iii++)
                $rec.= "{$attr[$iii]},";
            $rec = rtrim($rec,',')."},";

        }

    }
    return rtrim($rec,',');
}

function make_open_info($row){
//    $gamehelper = new SMP_Game_Helper();
//    $platforms = $gamehelper->getActPlatformLists(true);
//    _log($platforms);
    $gp = $row['gp_id'] == "" ? array() : explode("|",$row['gp_id']);
    $gsstr = "[";
    $data = SMP_Act_Activity::get_server_list();
//    $servers=$data['servers'];
    $group=$data['group'];
    foreach($gp as $g){
        $ginfo = $group[$g];
        if($ginfo){
            $gsstr .= "{{$ginfo['st']},{$ginfo['et']}},";
        }
    }
    $gsstr = trim($gsstr,",")."]";
    $gs = str_replace("|",",",$row['gs_id']);
    if($row['priority']==""){
        $priority = 0;
    }else{
        $priority = $row['priority'];
    }
    if($row['after_open_day']==""){
        $after_open_day = 0;
    }else{
        $after_open_day = $row['after_open_day'];
    }
    if($row['merge_st_day']=="") $row['merge_st_day'] = 0;
    if($row['merge_et_day']=="") $row['merge_et_day'] = 0;
    if($row['kf_state']=="") $row['kf_state'] = 0;
    $row['start_time'] = $row['start_time'] == 0 ? 0 : date('{{Y,m,d},{H,i,s}}', strtotime($row['start_time']));
    $row['end_time'] = $row['end_time'] == 0 ? 0 : date('{{Y,m,d},{H,i,s}}', strtotime($row['end_time']));
    $mergeTimeList = "";
    if ($row['merge_times_list'] != "") {
        $mergeTimeList = str_replace("|", ",", $row['merge_times_list']);
    };
    if ($row['conflict_list'] != "") {
        $row['conflict_list'] = str_replace("|", ",", $row['conflict_list']);
    };
    $str = "#open_info{gp_id = $gsstr,gs_id=[$gs],open_day={$row['open_day']},end_day={$row['end_day']},start_time={$row['start_time']},end_time={$row['end_time']},merge_st_day={$row['merge_st_day']},merge_et_day={$row['merge_et_day']},ignore_gs=[{$row['ignore_gs']}],priority=$priority,after_open_day=$after_open_day,merge_times_list = [{$mergeTimeList}],kf_state = {$row['kf_state']},conflict_list = [{$row['conflict_list']}]}";
    return $str;
}

function make_act_info($row){
    $arg_str = "";
    if($row['icon'] != ""){
        $arg_str .= "icon = {$row['icon']},";
    }
    if($row['ad_pic'] != ""){
        $arg_str .= "ad_pic = [{$row['ad_pic']}],";
    }
    if($row['show_pos_day'] != ""){
        $arg_str .= "show_pos_day = {$row['show_pos_day']},";
    }
    if($row['act_name'] != ""){
        $arg_str .= "act_name = ?T(\"{$row['act_name']}\"),";
    }
    if($row['act_desc'] != ""){
        $arg_str .= "act_desc=?T(\"{$row['act_desc']}\"),";
    }
    if($row['show_goods_list'] != ""){
        $arg_str .= "show_goods_list={$row['show_goods_list']},";
    }
    $arg_str = trim($arg_str, ",");
    if($arg_str == ""){
        $str = "#act_info{}";
    }else{
        $str = "#act_info{{$arg_str}}";
    }
    return $str;
}

?>