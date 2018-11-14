<?php
/**
 * Created by PhpStorm.
 * User: wangmin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class gold_tower{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_gold_tower";
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
        $info = $row['content'] == "" ? array() : explode(";",$row['content']);
        $info0 = $info[0];
        $info1 = $info[1];
        $info2 = $info[2];
        $item0 = trim($info0," \t\n\r\0\x0B");
        $pool = $item0 == "" ? array() : explode("\n",$item0);
        $pool_list = "";
        foreach($pool as $one){
            $temp = explode("|", $one);
            $pool_list .= "\n#base_gold_tower_award{min_wlv={$temp[0]}, max_wlv={$temp[1]}, lucky_min={$temp[2]}, lucky_max={$temp[3]}, goods_list=[{$temp[4]}]},";
        }
        $pool_list = trim($pool_list,",");

        $item1 = trim($info1," \t\n\r\0\x0B");
        $cost = $item1 == "" ? array() : explode("\n",$item1);
        $cost_list = "";
        foreach($cost as $one){
            $temp = explode("|", $one);
            $cost_list .= "\n#base_gold_tower_cost{min_wlv={$temp[0]}, max_wlv={$temp[1]}, daily_free_times={$temp[2]}, purchase_goods={$temp[3]}, one_cost_goods={$temp[4]}, one_get_score={$temp[5]}, ten_cost_goods={$temp[6]}, ten_get_score={$temp[7]}, fifty_cost_goods={$temp[8]}, fifty_get_score={$temp[9]}, ex_list=[{$temp[10]}]},";
        }
        $cost_list = trim($cost_list,",");

        $item2 = trim($info2," \t\n\r\0\x0B");
        $tower = $item2 == "" ? array() : explode("\n",$item2);
        $floor_list = "";
        foreach($tower as $one){
            $temp = explode("|", $one);
            $floor_list .= "\n#base_gold_tower_floor{min_wlv={$temp[0]}, max_wlv={$temp[1]}, floor={$temp[2]}, goods_list=[{$temp[3]}]},";
        }
        $floor_list = trim($floor_list,",");

        $rec = "
get({$row['act_id']}) ->
    #base_gold_tower{
        open_info={$openInfo},
        act_info={$actInfo},
        act_id={$row['act_id']},
        pool_list=[".$pool_list."],
        cost_list=[".$cost_list."],
        floor_list=[".$floor_list."]
    };\n";
        return $rec;
    }

}