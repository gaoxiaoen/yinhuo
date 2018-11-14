<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_gold_rank{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_gold_rank";
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
        $mail_title = "";
        $mail_content = "";
        foreach($arr as $item){
            if($index == 0){
                $mail_title = $item;
            } else if($index == 1){
                $mail_content = $item;
            } else if($index == 2){
                $act_wlv_limit = $item;
            }else{
                $item = trim($item," \t\n\r\0\x0B");
                $info = $item == "" ? array() : explode("|",$item);
                $id = $info[0];
                $rank= $info[1];
                $show_goods_list = $info[2];
                $goods_list = $info[3];
                $tre .= "{{$id},{$rank},{$show_goods_list},{$goods_list}},";
            }
            $index ++;
        }
        $tre = trim($tre,",");
        $rec .= "
get({$row['act_id']}) ->
    #base_act_gold_rank{
        open_info={$openInfo},
        act_info={$actInfo},
        act_id={$row['act_id']},
        act_wlv_limit = {$act_wlv_limit},
        list=[".$tre."],
        mail_title = ?T(\"{$mail_title}\"),
        mail_content = ?T(\"{$mail_content}\")
    };\n";
        return $rec;
    }
}