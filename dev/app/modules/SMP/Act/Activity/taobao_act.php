<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/3/4
 * Time: 上午10:48
 */

include_once 'act_config.php';

class taobao_act{

    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_taobao_act";
        $export_function = array('get/1','get_all/0');
        $hrl_array = array('activity.hrl','common.hrl');
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
        $erlCode = trim($erlCode,",")."].\n
        ";

        $fileName = SERVER_DIR."/$moduleName.erl";
        $res = to_file($fileName, $erlCode);
        return $res;
    }

    public function get_erl_content($row){
        $openInfo = make_open_info($row);
        $actInfo = make_act_info($row);
        $rec = "get({$row['act_id']}) -> #base_taobao_act{open_info={$openInfo},act_info={$actInfo},act_id={$row['act_id']},pool_id_list={$row['content']} };\n";
        return $rec;
    }

}