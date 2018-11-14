<?php
/**
 * Created by PhpStorm.
 * User: luobq
 * Date: 21/6/19
 * Time: 上午11:01
 */
include_once 'act_config.php';

class red_goods_exchange
{
    public function make($data)
    {
        return $this->toerl($data);
    }

    public function toerl($data)
    {
        $moduleName = "data_red_goods_exchange";
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

        $ExchangeList = "[";
        $arr1 = $arr1 == "" ? array() : explode("\n", $arr1);
        foreach ($arr1 as $item) {
            $item = trim($item, " \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|", $item);
            $record = "\n   #base_red_goods_exchange_list{ id={$info[0]},index={$info[1]} },";
            $ExchangeList .= $record;
        }
        $ExchangeList = trim($ExchangeList, ",");
        $ExchangeList .= "]";

        $rec = "get({$row['act_id']}) -> #base_red_goods_exchange{open_info={$openInfo},act_id={$row['act_id']},
        exchange_list = $ExchangeList,
        act_info={$actInfo} };\n";
        return $rec;
    }
}