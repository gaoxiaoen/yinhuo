<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/12
 * Time: 下午3:21
 */

include_once 'act_config.php';

class act_equip_sell{
    public function make($data){
        return $this->toerl($data);
    }
    public function toerl($data){
        $moduleName = "data_act_equip_sell";
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
        $tre = "";

        foreach($arr as $item){
            $item = trim($item," \t\n\r\0\x0B");
            $info = $item == "" ? array() : explode("|",$item);
            $id = $info[0];
            $type = $info[1];
            $dec = $info[2];
            $goods_id = $info[3];
            $sell_num = $info[4];
            $price = $info[5];
            $cbp = $info[6];
            $bind = $info[7];
            $tre .= "\n            #base_act_equip_sell_sub{id=$id,page=$type,dec = ?T(\"$dec\"),goods_id=$goods_id,sell_num=$sell_num,price=$price,cbp=$cbp,bind=$bind},";
        }
        $tre = trim($tre,",");

        $rec = "
get({$row['act_id']}) ->
    #base_act_equip_sell{
        open_info={$openInfo},
        act_id={$row['act_id']},
        list=[".$tre."
        ],
        act_info={$actInfo}
    };\n";
        return $rec;
    }
}