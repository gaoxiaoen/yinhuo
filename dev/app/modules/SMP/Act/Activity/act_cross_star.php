<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 18-8-27
 * Time: 15:38
 */


include_once 'act_config.php';

class act_cross_star{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_cross_star";
        $export_function = array('get/1','get_all/0');
        $hrl_array = array('cross_ms.hrl');
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
        $tre = "";
        $index = 0;
        $open_list = "";
        foreach($arr as $item){
            if($index == 0){
                $open_list = $item;
            }else{
                $item = trim($item," \t\n\r\0\x0B");
                $info = $item == "" ? array() : explode("|",$item);
                $times_id = $info[0];
                $long_time = $info[1];
                $item_list = $info[2];
                $tre .= "{{$times_id},{$long_time},{$item_list}},";
            }
            $index ++ ;
        }
        $tre = trim($tre,",");

        $rec .= "
get({$row['act_id']}) ->
    #base_act_cross_star{
        open_info={$openInfo},
        act_id={$row['act_id']},
        list=[".$tre."],
        open_list = [{$open_list}],
        act_info={$actInfo}
    };\n";
        return $rec;
    }
}