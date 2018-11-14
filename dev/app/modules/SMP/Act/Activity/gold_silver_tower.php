<?php
/**
 * Created by PhpStorm.
 * User: luobq
 * Date: 21/6/19
 * Time: 上午11:01
 */
include_once 'act_config.php';

class gold_silver_tower
{
    public function make($data)
    {
        return $this->toerl($data);
    }

    public function toerl($data)
    {
        $moduleName = "data_gold_silver_tower";
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
        $CostOne = $arr[0];
        $CostTen = $arr[1];
        $CostFifty = $arr[2];
        $FashionId = $arr[3];
        $arr1 = $arr[4];

        $rewardlist = "[";
        $arr1 = $arr1 == "" ? array() : explode("\n", $arr1);
        array_splice($arr1, 0, 1);
        foreach ($arr1 as $item) {
            $item = trim($item, " \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|", $item);
            $record = "\n   #base_gold_silver_tower_goods{ floor={$info[0]},reset_id={$info[1]},lower={$info[2]},up = {$info[3]},goods_list = {$info[4]} },";
            $rewardlist .= $record;
        }
        $rewardlist = trim($rewardlist, ",");
        $rewardlist .= "]";

        $rec = "get({$row['act_id']}) -> #base_gold_silver_tower{open_info={$openInfo},act_id={$row['act_id']},
        cost_one=$CostOne,
        cost_ten = $CostTen,
        cost_fifty = $CostFifty,
        fashion_id = $FashionId,
        reward_list = $rewardlist,
        act_info={$actInfo} };\n";
        return $rec;
    }
}