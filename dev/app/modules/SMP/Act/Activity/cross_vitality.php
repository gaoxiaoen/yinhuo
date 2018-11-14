<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/13
 * Time: 上午11:21
 */
include_once 'act_config.php';

class cross_vitality{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_cross_vitality";
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
        $cost_gold = $arr[0];
        $cost_goods = $arr[1];
        $p_times = $arr[2];
        $rand_reward_list = $arr[3];
        $times_reward_list = $arr[4];
        $server_reward_list = $arr[5];
        $rec = "get({$row['act_id']}) -> #base_cross_vitality{open_info={$openInfo},act_id={$row['act_id']},
        cost_gold=$cost_gold,cost_goods = $cost_goods,p_times=$p_times,
        rand_reward_list = $rand_reward_list,
        times_reward_list = $times_reward_list,
        server_reward_list = $server_reward_list,
        act_info={$actInfo} };\n";
        return $rec;
    }

}