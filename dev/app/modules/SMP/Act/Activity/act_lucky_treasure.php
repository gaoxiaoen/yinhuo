<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_lucky_treasure{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_lucky_treasure";
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
        $arr = $row['content'] ;
        $rec = "";
        $info  =explode("|", $arr);
        $rec .= "
get({$row['act_id']}) ->
    #base_act_lucky_treasure{
        open_info={$openInfo},
        act_info={$actInfo},
        act_id={$row['act_id']},
        cost_goods = {$info[0]},
        cost = {$info[1]},
        mlucky = {$info[2]},
        blist = {$info[3]}
    };\n";
        return $rec;
    }
}