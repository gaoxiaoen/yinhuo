<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_luck_yg{
    public function make($data){
        return $this->toerl($data);
    }
    public function toerl($data){
        $moduleName = "data_luck_yg";
        $export_function = array('get/1','get_all/0');
        $hrl_array = array('activity.hrl');
        $erlCode = erl_head($moduleName, $export_function, $hrl_array);

        foreach($data as $row){
            $openInfo = make_open_info($row);
            $actInfo = make_act_info($row);
            $arr = $row['content'] == "" ? array() : explode("\n",$row['content']);
            $index = 0;
            $one_need_gold = 0;
            $max_share = 0;
            $default_goods_list = "[]";
            $win_goods_list = "[]";
            $default_show_goods_list = "[]";
            foreach($arr as $item){
                $item = trim($item," \t\n\r\0\x0B");
                switch($index){
                    case 0 :
                        $index ++;
                        $one_need_gold = $item;
                        break;
                    case 1 :
                        $index ++;
                        $max_share = $item;
                        break;
                    case 2 :
                        $index ++;
                        $default_goods_list = $item;
                        break;
                    case 3 :
                        $index++;
                        $win_goods_list = $item;
                        break;
                    case 4 :
                        $index++;
                        $default_show_goods_list = $item;
                        break;
                    default :
                        $index ++;
                        break;
                }
            }
            $rec = "
    get({$row['act_id']}) ->
        #base_luck_yg{
            open_info={$openInfo},
            act_id={$row['act_id']},
            one_need_gold = {$one_need_gold},
            max_share = {$max_share},
            default_goods_list = {$default_goods_list},
            win_goods_list = {$win_goods_list},
            default_show_goods_list = {$default_show_goods_list},
            act_info={$actInfo}
        };\n";
            $erlCode .= $rec;
        }
        $erlCode .= "get(_) -> [].\n\n";

        $erlCode .= "get_all() -> [";
        foreach($data as $row){
            $erlCode .= $row['act_id'].",";
        }
        $erlCode = trim($erlCode,",")."].";

        $fileName = SERVER_DIR."/$moduleName.erl";
        $res = to_file($fileName, $erlCode);
        return $res;
    }

    public function get_erl_content($row){

    }
}