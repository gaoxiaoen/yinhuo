<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/8
 * Time: 下午3:30
 */

include_once 'act_config.php';

class invest_act{

    public static $count = 0;

    public function make($data){
        return $this->toerl($data);
    }

    public function toerl($data){
        $moduleName = "data_invest_act";
        $export_function = array('get/1','get_all/0');
        $hrl_array = array('activity.hrl');
        $erlCode = erl_head($moduleName, $export_function, $hrl_array);

        foreach($data as $row){
            $rec = $this->get_erl_content($row);
            $erlCode .= $rec;
        }
        $erlCode .= "get(_) -> [].\n\n";

        $erlCode .= "get_all() -> [";
        for($i=1;$i<=self::$count;$i++){
            $erlCode .= $i.",";
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
        self::$count = self::$count+1;
        $n = self::$count;
        $rec = "get($n) -> #base_invest_act{open_info={$openInfo},act_id={$row['act_id']},act_info={$actInfo} };\n";
        return $rec;
    }

}