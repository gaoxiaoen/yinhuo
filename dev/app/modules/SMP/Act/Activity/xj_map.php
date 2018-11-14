<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class xj_map{

    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_xj_map";
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
        $act_type = 0;
        $goods_list = 0;
        $saizi = 0;
        $free_go_num = 0;
        $one_go_cast = 0;
        $one_go_consume = 0;
        $reset_num = 0;
        $one_reset_cast = 0;
        $reset_reward_list = 0;

        foreach($arr as $item){
            $item = trim($item," \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|",$item);
            $act_type = $info[0];
            $goods_list = $info[1];
            $saizi = $info[2];
            $free_go_num = $info[3];
            $one_go_cast = $info[4];
            $one_go_consume = $info[5];
            $reset_num = $info[6];
            $one_reset_cast = $info[7];
            $reset_reward_list = $info[8];
        }

        $rec = "
get({$row['act_id']}) ->
    #base_xj_map{
        open_info={$openInfo},
        act_id = {$row['act_id']},
        act_info={$actInfo},
        act_type = {$act_type},
        goods_list = {$goods_list},
        saizi_list = {$saizi},
        free_go_num = {$free_go_num},
        one_go_cast = {$one_go_cast},
        one_go_consume = {$one_go_consume},
        reset_num = {$reset_num},
        one_reset_cast = {$one_reset_cast},
        reset_reward_list = {$reset_reward_list}
        };\n";
        return $rec;
    }

}