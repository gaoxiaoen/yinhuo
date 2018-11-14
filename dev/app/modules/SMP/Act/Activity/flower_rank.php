<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/6/21
 * Time: 下午3:49
 */
include_once 'act_config.php';

class flower_rank
{
    public function make($data)
    {
        return $this->toerl($data);
    }

    public function toerl($data)
    {
        $moduleName = "data_flower_rank";
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
        $arr2 = $arr[1];
        $givelist = "[";
        $arr1 = $arr1 == "" ? array() : explode("\n", $arr1);
        foreach ($arr1 as $item) {
            $item = trim($item, " \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|", $item);
            $record = "\n   #base_flower_rank{ id={$info[0]},must={$info[1]},award={$info[2]} },";
            $givelist .= $record;
        }
        $givelist = trim($givelist, ",");
        $givelist .= "]";

        $arr2 = $arr2 == "" ? array() : explode("\n", $arr2);
        array_splice($arr2, 0, 1);
        $getlist = "[";
        foreach ($arr2 as $item) {
            $item = trim($item, " \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|", $item);
            $record0 = "\n   #base_flower_rank{ id={$info[0]},must={$info[1]},award={$info[2]} },";
            $getlist .= $record0;
        }
        $getlist = trim($getlist, ",");
        $getlist .= "]";

        $rec = "get({$row['act_id']}) -> #base_act_flower_rank{open_info={$openInfo},act_id={$row['act_id']},
        give_list=$givelist,
        get_list = $getlist,
        act_info={$actInfo} };\n";
        return $rec;
    }

}