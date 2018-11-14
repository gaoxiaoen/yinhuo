<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_9377_vip_service
{

    public function make($data)
    {
        return $this->toerl($data);
    }

    public function toerl($data)
    {
        $moduleName = "data_act_9377_vip_service";
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

    public function arrary_to_string($des_list)
    {
        $infoex = $des_list == "" ? array() : explode("/", $des_list);
        $rec1 = "";
        foreach ($infoex as $item1) {
            $rec1 .= "?T(\"{$item1}\"),";
        }
        return substr($rec1, 0, strlen($rec1) - 1);
    }

    public function get_erl_content($row)
    {
        $openInfo = make_open_info($row);
        $actInfo = make_act_info($row);
        $arr = $row['content'] == "" ? array() : explode("\n", $row['content']);
        $rec = "";
        $tre = "";
        foreach ($arr as $item) {
            $item = trim($item, " \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|", $item);
            $need_money = $info[0];
            $desc_list = $info[1];
            $service_desc = $info[2];
            $service_time = $info[3];
            $qq_url = $info[4];
            $arrary_string = $this->arrary_to_string($desc_list);
        }

        $rec .= "
get({$row['act_id']}) ->
    #base_act_9377_vip_service{
        open_info={$openInfo},
        act_id={$row['act_id']},
        act_info={$actInfo},
        need_money = {$need_money},
        desc_list = [$arrary_string],
        service_desc = ?T(\"{$service_desc}\"),
        service_time = ?T(\"{$service_time}\"),        
        qq_url = ?T(\"{$qq_url}\")
    };\n";
        return $rec;
    }

}
