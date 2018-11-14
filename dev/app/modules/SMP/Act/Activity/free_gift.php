<?php
/**
 * Created by PhpStorm.
 * User: luobq
 * Date: 21/6/19
 * Time: 上午11:01
 */
include_once 'act_config.php';

class free_gift
{
    public function make($data)
    {
        return $this->toerl($data);
    }

    public function toerl($data)
    {
        $moduleName = "data_free_gift";
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
        $arr1 = $row['content'] == "" ? array() : explode("\n", $row['content']);

        $rewardlist = "[";
       // array_splice($arr1, 0, 1);
        foreach ($arr1 as $item) {
            $item = trim($item, " \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|", $item);
            $record = "\n   #base_act_free_gift_help{ type={$info[0]},cost={$info[1]},delay_day={$info[2]},reward = [{$info[3]}],re_reward = [{$info[4]}],desc = ?T(\"{$info[5]}\") },";
            $rewardlist .= $record;
        }
        $rewardlist = trim($rewardlist, ",");
        $rewardlist .= "]";

        $rec = "get({$row['act_id']}) -> #base_act_free_gift{open_info={$openInfo},act_id={$row['act_id']},
        gift_list = $rewardlist,
        act_info={$actInfo} };\n";
        return $rec;
    }
}