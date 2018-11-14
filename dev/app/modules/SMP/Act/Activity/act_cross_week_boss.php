<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/6/21
 * Time: 下午3:49
 */
include_once 'act_config.php';

class act_cross_week_boss
{
    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_cross_week_boss";
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
        $fxnum = trim($arr[0],"\n");
        $list = explode("\n", trim($arr[1],"\n"));
        $str = "[";
        foreach($list as $a){
            $item = explode("|", $a);
            $index = $item[0];
            $time = $item[1];
            $longtime = $item[2];
            $bosslist = $item[3];
            $str .= "\n#base_open_week_boss{index = {$index},time = {$time},longtime = {$longtime},bosslist = {$bosslist}},";
        }
        $str = trim($str,",");
        $str .= "]";
        $rec = "get({$row['act_id']}) -> #base_act_cross_week_boss{open_info={$openInfo},act_info={$actInfo},act_id={$row['act_id']},fxnum = $fxnum,open_list=
                  $str};\n";
        return $rec;
    }

}