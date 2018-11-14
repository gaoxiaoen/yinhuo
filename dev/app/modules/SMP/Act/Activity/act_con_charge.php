<?php
/**
 * Created by PhpStorm.
 * User: luobq
 * Date: 17/6/19
 * Time: 下午3:49
 */
include_once 'act_config.php';

class act_con_charge
{
    public function make($data)
    {
        return $this->toerl($data);
    }

    public function toerl($data)
    {
        $moduleName = "data_act_con_charge";
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
        $arr0 = $arr[0];
        $arr1 = $arr[1];
        $arr2 = $arr[2];

        $daylist = $arr0;

        $dailylist = "[";
        $arr1 = $arr1 == "" ? array() : explode("\n", $arr1);
        array_splice($arr1, 0, 1);
        foreach ($arr1 as $item) {
            $item = trim($item, " \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|", $item);
            $record = "\n   #base_con_recharge_award{ id={$info[0]},day={$info[1]},gold={$info[2]},award = {$info[3]} },";
            $dailylist .= $record;
        }
        $dailylist = trim($dailylist, ",");
        $dailylist .= "]";

        $arr2 = $arr2 == "" ? array() : explode("\n", $arr2);
        array_splice($arr2, 0, 1);
        $cumlist = "[";
        foreach ($arr2 as $item) {
            $item = trim($item, " \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|", $item);
            $record0 = "\n   #base_con_recharge_award{ id={$info[0]},day={$info[1]},gold={$info[2]},award = {$info[3]} },";
            $cumlist .= $record0;
        }
        $cumlist = trim($cumlist, ",");
        $cumlist .= "]";

        $rec = "get({$row['act_id']}) -> #base_act_con_recharge{open_info={$openInfo},act_id={$row['act_id']},
        day_list=$daylist,
        daily_list = $dailylist,
        cum_list = $cumlist,
        act_info={$actInfo} };\n";
        return $rec;
    }

}