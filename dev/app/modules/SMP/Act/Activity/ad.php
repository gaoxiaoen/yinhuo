<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/5/25
 * Time: 下午7:50
 */

include_once 'act_config.php';

class ad{

    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_ad";
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
        $arr = $row['content'] == "" ? array() : explode("\n", $row['content']);
        $minlv = $arr[0];
        $minlv = trim($minlv," \t\n\r\0\x0B");
        $goodslist = $arr[1];
        $goodslist = trim($goodslist," \t\n\r\0\x0B");
        $rec = "get({$row['act_id']}) -> #base_ad{open_info={$openInfo},act_id={$row['act_id']},min_lv=$minlv,pic_list=$goodslist,act_info={$actInfo} };\n";
        return $rec;
    }

}