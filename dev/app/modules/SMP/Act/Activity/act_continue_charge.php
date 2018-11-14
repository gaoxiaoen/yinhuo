<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_continue_charge{
    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_continue_charge";
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
        $arr = $row['content'] == "" ? array() : explode(";", $row['content']);
        $charge = trim($arr[0],"\n");
        $chargelist = explode("\n",$charge);
        $str = "[";
        foreach($chargelist as $a) {
            $item = explode("|", $a);
            $index = $item[0];
            $acccharge = $item[1];
            $goodslist = $item[2];
            $str .= "{{$index},{$acccharge},[{$goodslist}]},";
        }
        $str = trim($str,",");
        $str .= "]";
        $list = explode("\n", trim($arr[1],"\n"));
        $str2 = "[";
        foreach($list as $a){
            $item = explode("|", $a);
            $day = $item[0];
            $dwlist = $item[1];
            $str2 .= "\n{{$day},[{$dwlist}]},";
        }
        $str2 = trim($str2,",");
        $str2 .= "]";
        $rec = "get({$row['act_id']}) -> #base_act_continue_charge{open_info={$openInfo},act_info={$actInfo},act_id={$row['act_id']},charge_list = $str,day_list = $str2};\n";
        return $rec;
    }
}
