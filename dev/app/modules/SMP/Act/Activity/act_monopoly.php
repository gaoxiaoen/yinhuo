<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/7/18
 * Time: 下午2:26
 */

include_once 'act_config.php';

class act_monopoly{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_monopoly";
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
        $arr = $row['content'] == "" ? array() : explode("\n", $row['content']);
        $icon = $arr[0];
        $icon = trim($icon," \t\n\r\0\x0B");
        $giftlist = $arr[1];
        $giftlist = trim($giftlist," \t\n\r\0\x0B");
        $rec = "get({$row['act_id']}) -> #base_act_monopoly{open_info={$openInfo},act_id={$row['act_id']},icon={$icon},gift_list={$giftlist},act_info={$actInfo} };\n";
        return $rec;
    }

}