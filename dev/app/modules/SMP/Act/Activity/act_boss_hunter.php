<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_boss_hunter{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_boss_hunter";
        $export_function = array('get/1','get_all/0');
        $hrl_array = array('boss_hunter.hrl');
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
        $index = 0;
        $score_list = "";
        $score_reward_list = "";
        $rank_reward_list = "";
        $rank_score = 0;
        $limit_mon_lv = 0;
        foreach($arr as $item){
            if($index == 0){
                $item = trim($item," \t\n\r\0\x0B");
                $score_list = str_replace("|",",",$item);
            }
            else if($index == 1){
                $item = trim($item," \t\n\r\0\x0B");
                $score_reward_list = str_replace("|",",",$item);
            }
            else if($index == 2){
                $item = trim($item," \t\n\r\0\x0B");
                $rank_reward_list = str_replace("|",",",$item);
            }
            else if($index == 3){
                $rank_score = $item;
            }
            else if($index == 4){
                $limit_mon_lv = $item;
            }
            $index ++ ;
        }
        $rec .= "
get({$row['act_id']}) ->
    #base_act_boss_hunter{
        open_info={$openInfo},
        act_id={$row['act_id']}
       , score_list =[{$score_list}]
        , score_reward_list = [{$score_reward_list}]
        , rank_reward_list = [{$rank_reward_list}]
        , rank_score = {$rank_score}
        , limit_mon_lv = {$limit_mon_lv}
        , act_info={$actInfo}
    };\n";
        return $rec;
    }
}