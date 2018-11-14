<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_exchange{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_exchange";
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
        $tre = "";
        $index = 0;
        $act_wlv_limit = "[]";
        foreach($arr as $item){
           if($index == 0){
                $act_wlv_limit = $item;
            }else{
                $item = trim($item," \t\n\r\0\x0B");
                $info = $item == "" ? array() : explode("|",$item);
                $id = $info[0];
                $per_limit = $info[1];
                $server_limit = $info[2];
                $need_goods_list = $info[3];
                $goods_list = $info[4];
                $tre .= "{{$id},{$per_limit},{$server_limit},{$need_goods_list},{$goods_list}},";
            }
            $index ++;
        }
        $tre = trim($tre,",");

        $rec .= "
get({$row['act_id']}) ->
    #base_act_exchange{
        open_info={$openInfo},
        act_id={$row['act_id']},
        act_wlv_limit = {$act_wlv_limit},
        list=[".$tre."],
        act_info={$actInfo}
    };\n";
        return $rec;
    }
}