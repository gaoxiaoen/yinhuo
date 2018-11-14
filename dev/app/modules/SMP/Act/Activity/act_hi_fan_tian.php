<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/6/21
 * Time: 下午3:49
 */
include_once 'act_config.php';

class act_hi_fan_tian
{
    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_hi_fan_tian";
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
        $arr0 = $row['content'] == "" ? array() : explode(";", $row['content']);
        $arr = $arr0[0] == "" ? array() : explode("\n", $arr0[0]);
        $str = "[";
        foreach($arr as $a){
            $item = explode("|", $a);
            $str .= "{{$item[0]},{$item[1]},{$item[2]}},";
        }
        $str = trim($str,",");
        $str .= "]";

        if(strlen($arr0[1]) < 1)
        {
            $showActIds = "[]";
        }else {
            $showActIds = $arr0[1];
        }

        $rec = "get({$row['act_id']}) -> #base_hi_fan_tian{open_info={$openInfo},act_id={$row['act_id']},award_list=$str,act_info={$actInfo}, show_act_ids={$showActIds} };\n";
        return $rec;
    }

}