<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/6/21
 * Time: 下午3:49
 */
include_once 'act_config.php';

class act_local_lucky_turn
{
    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_local_lucky_turn";
        $export_function = array('get/1','get_all/0');
        $hrl_array = array('activity.hrl');
        $erlCode = erl_head($moduleName, $export_function, $hrl_array);

        foreach($data as $row){
            $rec = $this->get_erl_content($row);
            $erlCode .= $rec;
        }
        $erlCode .= "get(_) -> [].\n\n";

        $erlCode .= "get_all() -> [";
        foreach($data as $row){
            $erlCode .= $row['act_id'].",";
        }
        $erlCode = trim($erlCode,",")."].
";

        $fileName = SERVER_DIR."/$moduleName.erl";
        $res = to_file($fileName, $erlCode);
        return $res;
    }

    public function get_erl_content($row){
        $openInfo = make_open_info($row);
        $actInfo = make_act_info($row);

        $arr = $row['content'] == "" ? array() : explode(";", $row['content']);
        $freetime = $arr[0];
        $onecost = $arr[1];
        $onescore = $arr[2];
        $tencost = $arr[3];
        $tenscore = $arr[4];
        $initgold = $arr[5];
        $rangelist = $arr[6];
        $scorelist = $arr[7];
        $backlist = $arr[8];

        $rangelist = $rangelist == "" ? array() : explode("\n", $rangelist);
        array_splice($rangelist, 0, 1);
        $str = "[";
        foreach($rangelist as $a){
            $item = explode("|", $a);
            $str .= "{{$item[0]},{$item[1]}},";
        }
        $str = trim($str,",");
        $str .= "]";
        $rec = "get({$row['act_id']}) -> #base_act_lucky_turn{open_info={$openInfo},act_id={$row['act_id']},award_list=$str,act_info={$actInfo},score_list = {$scorelist},free_time = {$freetime},one_cost = {$onecost},one_score = {$onescore},ten_cost = {$tencost},ten_score = {$tenscore},backlist = {$backlist},initgold = {$initgold}};\n";
        return $rec;
    }

}