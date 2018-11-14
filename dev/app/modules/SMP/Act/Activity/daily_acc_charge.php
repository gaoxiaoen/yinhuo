<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/4/26
 * Time: 下午5:29
 */

include_once 'act_config.php';

class daily_acc_charge{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_daily_acc_charge";
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
        $arr = $row['content'] == "" ? array() : explode("\n",$row['content']);
        $content = "";
        foreach($arr as $item) {
            $item = trim($item," \t\n\r\0\x0B");
            $content .=trim($item,",") . "," ;
        }
        $content = trim($content,",");
        $rec = "get({$row['act_id']}) -> #base_daily_acc_charge{open_info={$openInfo},act_id={$row['act_id']},gift_list=[{$content}],act_info={$actInfo} };\n";
        return $rec;
    }

}