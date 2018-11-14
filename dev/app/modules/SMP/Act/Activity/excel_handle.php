<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/4/25
 * Time: 下午4:45
 */
include_once JEC.'libraries/PHPExcel.php';
include_once 'util.php';
include_once 'act_config.php';

class Excelhandle extends AdminController{
    public static  $key = 'activity_excel_cacehe_key';

    public function __construct()
    {
        header("Content-Type:text/html; charset=utf-8");
    }

    public function update_cache($file,$data) {
        Cache::getInstance()->set(self::$key.$file,$data,600);
    }

    public function get_excel($file) {
//        $cache = Cache::getInstance()->get(self::$key.$file);
//        if(is_array($cache) && count($cache) > 0){
//            return $cache;
//        }else{
            $excelDir = DATA;
            $path = $excelDir.'/'.$file.'.xlsx';
            $reader = new PHPExcel_Reader_Excel2007();
            if(!$reader->canRead($path)){
                echo "can't read $path";
            }else{
                $Excel = $reader->load($path);
                $currentSheet = $Excel->getSheet(0);
                $data = $this->sheet_process($currentSheet);
//                $this->update_cache($file,$data);
//                foreach($data as $key=>&$d){
//                    if(is_numeric($d['start_time'])){
//                        if($d['start_time'] > 0){
//                            $d['start_time'] = date('Y-m-d H:i:s', $d['start_time']);
//                        }
//                        if($d['end_time'] > 0){
//                            $d['end_time'] = date('Y-m-d H:i:s', $d['end_time']);
//                        }
//                    }
//                }
//                $this->write_excel($data,$file);
                return $data;
            }
//        }
    }

    public function sheet_process($currentSheet){
        $allColumn = PHPEXCEL_CELL::columnIndexFromString($currentSheet->getHighestColumn());
        $allRow = $currentSheet->getHighestRow();
        $data = array();
        $keys = array();
        //字段列表
        for($currentColumn = 0;$currentColumn < $allColumn;$currentColumn ++){
            $keys[$currentColumn]= $currentSheet->getCellByColumnAndRow($currentColumn,1)->getValue();
        }
        for($currentRow = 2;$currentRow <= $allRow;$currentRow ++){
            for($currentColumn = 0;$currentColumn < $allColumn;$currentColumn ++){
                $val = $currentSheet->getCellByColumnAndRow($currentColumn,$currentRow)->getValue();
                if($val instanceof PHPExcel_RichText){
                    $val = $val->__toString();
                }
                if($currentColumn == 0 && $val===NULL) break;
                $data[$currentRow-2][$keys[$currentColumn]]=$val;
            }
        }
        return $data;
    }

    public function write_excel($data,$file){
        sort($data);
        $list = array();
        foreach ($data as $key => &$val) {
            foreach ($val as $k => &$v) {
                $v = stripslashes($v);
                $list[$k][] = stripslashes($v);
            }
        }

        $objPHPExcel = new PHPExcel();
        $i = 0;
        $j = 2;
        foreach ($list as $key => $val) {
            $fields = isset($fieldarr[$key]) ? $fieldarr[$key] : $key;
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i, 1, $fields);
            foreach ($val as $v) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i, $j++, trim($v));
            }
            $i++;
            $j = 2;
        }
        $objPHPExcel->getActiveSheet()->setTitle($file);
        $excelName = $file . '.xlsx';
//        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

//        $this->update_cache($file,$data);
        $objWriter->save(DATA.$excelName);
    }

    public function get_rows($file, $where){
        $data = $this->get_excel($file);

        if(count($data) == 0){
            return array();
        }

        $res = array();
        foreach($data as $val){
            $iswhere = true;
            foreach($where as $k=>$v){
                if($val[$k] != $v){
                    $iswhere = false;
                    continue;
                }
            }
            if($iswhere){
                $res[] = $val;
            }
        }
        return $res;
    }

    public function del($file, $where, $write = true){
        $data = $this->get_excel($file);

        if(count($data) == 0){
            return array();
        }

        $res = array();
        foreach($data as $val){
            $iswhere = true;
            foreach($where as $k=>$v){
                if($val[$k] != $v){
                    $iswhere = false;
                    continue;
                }
            }
            if(!$iswhere){
                $res[] = $val;
            }
        }
        if($write){
            $this->write_excel($res,$file);
        }
        return $res;
    }

    public function add($file, $adddata){
        if($file == 'base_activity_data'){
            $data = $this->del($file, array('type'=>$adddata['type'],'act_id'=>$adddata['act_id']), false);
        }elseif($file == 'base_activity'){
            $data = $this->del($file, array('type'=>$adddata['type']), false);
        }else{
            $data = $this->del($file, array('type'=>$adddata['type'], 'act_id'=>$adddata['act_id']), false);
        }
        $data[] = $adddata;
        $this->write_excel($data,$file);

//        $excel = new Excelhandle();
//        $acttype = $excel->get_rows("base_activity",array());
//        foreach($acttype as $act){
//            $where1 = array('type'=>$act['type']);
//            if($act['type'] >= 30){
//                $actdata = $excel->get_rows("base_activity_data",$where1);
//                if($actdata){
//                    $this->write_excel($actdata,"base_activity_data_".$act['type']);
//                }
//            }
//        }
    }
}