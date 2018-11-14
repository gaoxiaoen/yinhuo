<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/6/21
 * Time: 下午3:49
 */
include_once 'act_config.php';

class cross_flower
{
    public function make($data)
    {
        return $this->toerl($data);
    }

    public function toerl($data)
    {
        $moduleName = "data_cross_flower";
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
        $arr1 = $arr[0];
        $arr2  = trim($arr[1], " \t\n\r\0\x0B");
        $arr3  = trim($arr[2], " \t\n\r\0\x0B");
        $givelist = "[";
        $arr1 = $arr1 == "" ? array() : explode("\n", $arr1);
        foreach ($arr1 as $item) {
            $item = trim($item, " \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|", $item);
            $record = "\n   #base_cross_flower{ rank={$info[0]},rank_val = {$info[1]}, award_girl={$info[3]},award_boy={$info[2]}},";
            $givelist .= $record;
        }
        $givelist = trim($givelist, ",");
        $givelist .= "]";

        $rec = "get({$row['act_id']}) -> #base_act_cross_flower{open_info={$openInfo},act_id={$row['act_id']},
        rank_num = $arr2,
        rank_value = $arr3,
        rank_list = $givelist,
        act_info={$actInfo} };\n";
        return $rec;
    }

}