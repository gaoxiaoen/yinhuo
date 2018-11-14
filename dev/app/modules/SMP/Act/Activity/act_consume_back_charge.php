<?php
/**
 * Created by PhpStorm.
 * User: li
 * Date: 2017/6/5
 * Time: 11:25
 */

include_once 'act_config.php';

class act_consume_back_charge{

    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_consume_back_charge";
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
        $tre = "";
        $actType = 1;

        foreach($arr as $item){
            $item = trim($item," \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|",$item);
            $actType = $info[0];
            $BackNum = $info[1];
            $ConsumeGold = $info[2];
            $tre .= "{{$BackNum}, $ConsumeGold},";
        }
        $tre = trim($tre,",");

        $rec = "
get({$row['act_id']}) ->
    #base_act_consume_back_charge{
        open_info={$openInfo},
        act_id={$row['act_id']},
        act_type = {$actType},
        list=[".$tre."],
        act_info={$actInfo}
    };\n";
        return $rec;
    }
}