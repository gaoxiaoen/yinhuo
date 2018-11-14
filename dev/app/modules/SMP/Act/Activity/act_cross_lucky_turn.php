<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 18-8-27
 * Time: 19:45
 */

include_once 'act_config.php';

class act_cross_lucky_turn
{
    public function make($data)
    {
        return $this->toerl($data);
    }

    public function toerl($data)
    {
        $moduleName = "data_act_cross_lucky_turn";
        $export_function = array('get/1', 'get_all/0');
        $hrl_array = array('activity.hrl');
        $erlCode = erl_head($moduleName, $export_function, $hrl_array);

        foreach ($data as $row) {
            $rec = $this->get_erl_content($row);
            $erlCode .= $rec;
        }
        $erlCode .= "get(_) -> [].\n\n";

        $erlCode .= "get_all() -> [";
        foreach ($data as $row) {
            $erlCode .= $row['act_id'] . ",";
        }
        $erlCode = trim($erlCode, ",") . "].
";

        $fileName = SERVER_DIR . "/$moduleName.erl";
        $res = to_file($fileName, $erlCode);
        return $res;
    }

    public function get_erl_content($row)
    {
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
        foreach ($rangelist as $a) {
            $item = explode("|", $a);
            $str .= "{{$item[0]},{$item[1]}},";
        }
        $str = trim($str, ",");
        $str .= "]";

        $scorelist = $scorelist == "" ? array() : explode("\n", $scorelist);
        array_splice($scorelist, 0, 1);
        $str1 = "[";
        foreach ($scorelist as $a) {
            $item = explode("|", $a);
            $str1 .= "{{$item[0]},{$item[1]}},";
        }
        $str1 = trim($str1, ",");
        $str1 .= "]";
        $rec = "get({$row['act_id']}) -> #base_act_cross_lucky_turn{open_info={$openInfo},act_id={$row['act_id']},award_list=$str,act_info={$actInfo},score_list = $str1,free_time = {$freetime},one_cost = {$onecost},one_score = {$onescore},ten_cost = {$tencost},ten_score = {$tenscore},backlist = {$backlist},initgold = {$initgold}};\n";
        return $rec;
    }

}