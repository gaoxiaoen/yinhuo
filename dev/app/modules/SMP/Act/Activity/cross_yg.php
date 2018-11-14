<?php
/**
 * Created by PhpStorm.
 * User: wangmin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class cross_yg{


    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_cross_yg";
        $export_function = array('get/1','get_all/0');
        $hrl_array = array('cross_yg.hrl');
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
        $arr = $row['content'] == "" ? array() : explode(";", $row['content']);

        $list0 = explode("\n", trim($arr[0],"\n"));
        $str0 = "[";
        foreach($list0 as $a) {
            $item = explode("|", $a);
            $type = $item[0];
            $coflist = $item[1];
            $str0 .= "\n                   {{$type},{$coflist}},";
        }
        $str0 = trim($str0,",");
        $str0 .= "]";

        $cost = trim($arr[1],"\n");
        $cost1 = explode("\n",$cost);
        $str = "[";
        foreach($cost1 as $a) {
            $item = explode("|", $a);
            $type = $item[0];
            $costlist = $item[1];
            $str .= "{{$type},{$costlist}},";
        }
        $str = trim($str,",");
        $str .= "]";

        $list1 = explode("\n", trim($arr[2],"\n"));
        $str1 = "[";
        foreach($list1 as $a) {
            $item = explode("|", $a);
            $type = $item[0];
            $coflist = $item[1];
            $str1 .= "\n                   {{$type},{$coflist}},";
        }
        $str1 = trim($str1,",");
        $str1 .= "]";

        $list = explode("\n", trim($arr[3],"\n"));
        $str2 = "[";
        foreach($list as $a){
            $item = explode("|", $a);
            $type = $item[0];
            $wlv_section = $item[1];
            $wlv = explode(",", $wlv_section);
            $award_pool = $item[2];
            $base_award = $item[3];
            $str2 .= "\n        #base_cross_yg_award{type = {$type}, min_wlv = {$wlv[0]}, max_wlv = {$wlv[1]}, award_pool = {$award_pool}, base_award = {$base_award}},";
        }
        $str2 = trim($str2,",");
        $str2 .= "]";
        $rec = "get({$row['act_id']}) -> #base_act_cross_yg{\n    open_info={$openInfo},\n    act_info={$actInfo},\n    act_id={$row['act_id']},\n    times_list = $str0,\n    cost_list = $str,\n    config_list = $str1,\n    award_list = $str2};\n";
        return $rec;
    }

}