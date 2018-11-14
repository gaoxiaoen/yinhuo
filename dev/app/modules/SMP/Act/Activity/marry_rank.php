<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/6/21
 * Time: 下午3:49
 */
include_once 'act_config.php';

class marry_rank
{
    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_marry_rank";
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
        $rec = "";
        $tre1 = "";
        foreach($arr as $item){
            $item = trim($item," \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|",$item);
            $tre1 .= "{$info[0]}";
        }

        $rec .= "get({$row['act_id']}) -> #base_marry_rank{open_info={$openInfo},act_id={$row['act_id']},reward_list = $tre1,act_info={$actInfo} };\n";
        return $rec;
    }

}