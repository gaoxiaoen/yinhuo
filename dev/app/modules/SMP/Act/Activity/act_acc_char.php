<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_acc_char{
    public function make($data){
        return $this->toerl($data);
    }
    public function toerl($data){
        $moduleName = "data_act_acc_char";
        $export_function = array('get/1','get_all/0','get_base_info/2');
        $hrl_array = array('act_acc_char.hrl');
        $erlCode = erl_head($moduleName, $export_function, $hrl_array);
        $base_arr=array();
        foreach($data as $row){
            $openInfo = make_open_info($row);
            $actInfo = make_act_info($row);
            $arr = $row['content'] == "" ? array() : explode("\n",$row['content']);
            $rec = "";
            $tre = "";
            $ids ="[";
            foreach($arr as $item){
                $item = trim($item," \t\n\r\0\x0B");
                $info = $item == "" ? array() : explode("|",$item);
                $ids.="{$info[0]},";
                $base_arr[] ="get_base_info({$row['act_id']},{$info[0]})->#base_act_acc_char_info{ id={$info[0]},max_times={$info[1]},limit_goods={$info[2]},exchange_goods={$info[3]} };\n";
            }
            $ids = trim($ids,",")."]";
            $tre = trim($tre,",");
            $rec .= "
    get({$row['act_id']}) ->
        #base_act_acc_char{
            open_info={$openInfo},
            act_id={$row['act_id']},
            ids={$ids},
            act_info={$actInfo}
        };\n";
            $erlCode .= $rec;
        }
        $erlCode .= "get(_) -> [].\n\n";

        $erlCode .= "get_all() -> [";
        foreach($data as $row){
            $erlCode .= $row['act_id'].",";
        }
        $erlCode = trim($erlCode,",")."].\n\n";
        foreach($base_arr as $key => $val){
            $erlCode .= $val;
        }
        $erlCode .="get_base_info(_ActId,_Id)->[].\n";
        $fileName = SERVER_DIR."/$moduleName.erl";
        $res = to_file($fileName, $erlCode);
        return $res;
    }

    public function get_erl_content($row){
        
    }
}