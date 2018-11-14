<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/5/11
 * Time: 上午10:52
 */

include_once 'act_config.php';

class back_gold_turntable{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_back_gold_turntable";
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
        $arr1 = $row['content'] == "" ? array() : explode("\n", $row['content']);
        $ratiostr = "";
        foreach($arr1 as $a){
            $arr = $a == "" ? array() : explode("|", $a);
            $chargeval = $arr[0];
            $chargeval = trim($chargeval," \t\n\r\0\x0B");
            $ratiolist = $arr[1];
            $ratiolist = trim($ratiolist," \t\n\r\0\x0B");
            $ratiostr .= "{ $chargeval,[$ratiolist] },";
        }
        $ratiostr = trim($ratiostr, ",");
        $rec = "get({$row['act_id']}) -> #base_back_gold_turntable{open_info={$openInfo},act_id={$row['act_id']},mul_list=[$ratiostr],act_info={$actInfo} };\n";
        return $rec;
    }

}