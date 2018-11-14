<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class open_all_rank{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_open_all_rank";
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
        $rankType = 0;
        $client_show = "";
        foreach($arr as $item){
            if($index == 0){
                $rankType = $item;
            }else if($index == 1){
                $client_show_arr= $item =="" ? array():explode(",",$item);
                foreach($client_show_arr as $key => $val){
                    $client_show.="\"{$val}\",";
                }
                $client_show = rtrim($client_show,",");
            }
            else{
               $item = trim($item," \t\n\r\0\x0B");
               $info = $item == "" ? array() : explode("|",$item);
                $Id = $info[0];
               $rank_min = $info[1];
               $rank_max = $info[2];
                $value_min = $info[3];
                $value_max = $info[4];
                $notice_id = $info[5];
               $goodsList = $info[6];
               $tre .= "{  {$Id},{$rank_min},{$rank_max},{$value_min},{$value_max},{$notice_id},{$goodsList}},";
           }
            $index = $index +1;
        }
        $tre = trim($tre,",");

        $rec .= "
get({$row['act_id']}) ->
    #base_open_act_all_rank{
        open_info={$openInfo},
        act_id={$row['act_id']},
        rank_type = {$rankType},
        client_show = [$client_show],
        list=[".$tre."],
        act_info={$actInfo}
    };\n";
        return $rec;
    }
}