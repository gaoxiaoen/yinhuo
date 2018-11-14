<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/13
 * Time: 上午11:21
 */
include_once 'act_config.php';

class one_charge{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_one_charge";
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
        $rec = "get({$row['act_id']}) -> #base_one_charge{open_info={$openInfo},act_id={$row['act_id']},gift_list=[{$row['content']}],act_info={$actInfo} };\n";
        return $rec;
    }

}