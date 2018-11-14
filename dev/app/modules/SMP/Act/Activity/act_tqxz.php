<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_tqxz{

    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_tqxz";
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
        $index = 0;
        $desc_string = "-1";
        $bg_string = "-1";
        $page_list = "";
        $list = "";
        foreach($arr as $item){
            if($index == 0){
                $item = trim($item," \t\n\r\0\x0B");
                $desc_string = $item;
            }
            else if($index == 1){
                $item = trim($item," \t\n\r\0\x0B");
                $bg_string = $item;
            }
            else if($index == 2){
                $item = trim($item," \t\n\r\0\x0B");
                $page_arr = explode("|",$item);
                foreach($page_arr as $val){
                    $page_val_arr = explode(",",$val);
                    $page_list .= "{"."{$page_val_arr[0]},?T(\"{$page_val_arr[1]}\")"."}, ";
                }
                $page_list = rtrim($page_list,", ");
            }
            else{
                $item = trim($item," \t\n\r\0\x0B");
                $item_arr = explode("|",$item);
                $list .= "#base_act_tqxz_item{"." id = {$item_arr[0]},page = {$item_arr[1]},can_buy_times = {$item_arr[2]},gold = {$item_arr[3]},goods_list = {$item_arr[4]} "."} , ";
            }
            $index ++ ;
        }
        $list = rtrim($list,", ");
        $rec .= "
get({$row['act_id']}) ->
    #base_act_tqxz{
        open_info={$openInfo},
        act_id={$row['act_id']}
       ,desc_string = \"{$desc_string}\"
       ,bg_string = \"{$bg_string}\"
       ,page_list = [{$page_list}]
       ,list = [{$list}]
       ,act_info = {$actInfo}
    };\n";
        return $rec;
    }
}