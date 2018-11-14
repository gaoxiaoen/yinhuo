<?php
/**
 * Created by PhpStorm.
 * User: luobq
 * Date: 17/5/10
 * Time: 下午5:49
 */
include_once 'act_config.php';

class hundred_return
{
    public function make($data)
    {
        return $this->toerl($data);
    }

    public function toerl($data)
    {
        $moduleName = "data_hundred_return";
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
        $arr = $row['content'] == "" ? array() : explode("\n", $row['content']);
        $cost = $arr[0];
        $cost = trim($cost," \t\n\r\0\x0B");
        $value = $arr[1];
        $value = trim($value," \t\n\r\0\x0B");
        $get_list = $arr[2];
        $get_list = trim($get_list," \t\n\r\0\x0B");
        $rec = "get({$row['act_id']}) -> #base_hundred_return{open_info={$openInfo},act_id={$row['act_id']},
        cost=$cost,
        value = $value,
        get_list=$get_list,
        act_info = $actInfo };\n";
        return $rec;
    }

}