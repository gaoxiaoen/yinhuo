<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_smash_egg extends AdminController{

    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_act_smash_egg";
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
        $arr1 = $row['content'] == "" ? array() : explode("|", $row['content']);
        $freecnt = trim( $arr1[0], " \t\n\r\0\x0B");
        $freeref =trim( $arr1[1], " \t\n\r\0\x0B");
        $refcost = trim( $arr1[2], " \t\n\r\0\x0B");
        $wglist = trim( $arr1[3], " \t\n\r\0\x0B");
        $showlist =trim( $arr1[4], " \t\n\r\0\x0B");
        $eggnum =trim( $arr1[5], " \t\n\r\0\x0B");

        $rec = "get({$row['act_id']}) -> #base_act_smash_egg{open_info={$openInfo},act_id={$row['act_id']},
        freecnt = $freecnt,
        free_fresh = $freeref,
        fresh_cost = $refcost,
        wglist = $wglist,
        show_list = $showlist,
        egg_num = $eggnum,
        act_info={$actInfo} };\n";
        return $rec;
    }
}