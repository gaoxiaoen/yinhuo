<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/5/12
 * Time: 下午2:08
 */

include_once 'act_config.php';

class collect_exchange{

    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_collect_exchange";
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
        $goodslist = "[";
        foreach($arr as $item){
            $item = trim($item," \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|",$item);
            $record = "\n   #base_ce{ id={$info[0]},get_goods={$info[1]},cost_goods={$info[2]},limit_list={$info[3]}},";
            $goodslist .= $record;
        }
        $goodslist = trim($goodslist,",");
        $goodslist .= "]";

        $rec = "get({$row['act_id']}) -> #base_act_collect_exchange{open_info={$openInfo},
        act_id={$row['act_id']},
        exchange_list=$goodslist,
        act_info={$actInfo} };\n";
        return $rec;
    }

}