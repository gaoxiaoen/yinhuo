<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/16
 * Time: 上午11:35
 */
class SMP_Data_Import extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '活动数据导入');
        $is_import = Jec::getVar('import');
        $is_export = Jec::getVar('export');
//        if($is_import != '')
//            $this->import_data();
//        if($is_export != '')
//            $this->export_data();
        $this->export_data();
        $this->display();
    }

    public function import_data()
    {
        ini_set('memory_limit', '500M');
        set_time_limit(1200);
        $table = Jec::getVar('table');
        if ($table == '') {
            throw new JecException('请选择要操作的数据表');
        }
        if (!$_FILES["file"] || $_FILES["file"]["error"] > 0) {
            alert("Error: " . $_FILES["file"]["error"]);
        } else {
            $path_parts = @pathinfo($_FILES["file"]["name"]);
            $extension = @strtolower($path_parts["extension"]);
            if (($_FILES["file"]["type"] == "application/vnd.ms-excel" || $_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
                && ($extension == "xls" || $extension == "xlsx")
                && ($_FILES["file"]["size"] < 5000000)
            ) {
                if (is_readable($_FILES["file"]["tmp_name"]) == false)
                    chmod($_FILES["file"]["tmp_name"], 0644);
                /*require_once('excel/reader.php');
                $data = new Spreadsheet_Excel_Reader();
                $data->setOutputEncoding('UTF-8');
                $data->read($_FILES["file"]["tmp_name"]);
                error_reporting(E_ALL ^ E_NOTICE);
                $cols = $data->sheets[0]['numCols'];//竖行数,从1开始
                $rows = $data->sheets[0]['numRows'];//横行数,从1开始*/
                $objPHPExcel = new PHPExcel();
                $objReader = PHPExcel_IOFactory::createReader('Excel5'); //use excel2007 for 2007 format
                $objPHPExcel = $objReader->load($_FILES["file"]["tmp_name"]);
                $data = $objPHPExcel->getSheet(0);
                $cols = $data->getHighestColumn(); //竖行数,从1开始
                $rows = $data->getHighestRow(); //横行数,从1开始
                $cols = PHPExcel_Cell::columnIndexFromString($cols); //总列数

                $colscount = 0;
                for ($j = 0; $j < $cols; $j++) { //计算实际有多和列，防止空白列
                    $realcols = $data->getCellByColumnAndRow($j, 1)->getValue();
                    if (!isset($realcols)) continue;
                    $colscount++;
                }
                if (in_array($table, array())) {
                    $sheetarray = array();
                    for ($si = 1; $si <= $rows; $si++) {
                        for ($sj = 0; $sj < $cols; $sj++) {
                            $sheetarray[$si][$sj] = $this->stripcode($data->getCellByColumnAndRow($sj, $si)->getValue());
                        }
                    }
                    $sql = $this->special_import($table, $sheetarray, $colscount);
                } else {
                    $sql = "INSERT INTO {$table}(";
                    for ($j = 0; $j < $colscount; $j++) {
                        $fields = substr($this->stripcode($data->getCellByColumnAndRow($j, 1)->getValue()), 0, strpos($this->stripcode($data->getCellByColumnAndRow($j, 1)->getValue()), '--'));
                        $sql .= "`$fields`";
                        if ($j < $colscount - 1) $sql .= ",";
                    }
                    $sql .= ")VALUES";

                    for ($i = 2; $i <= $rows; $i++) { //从第二行开始，第一行作为字段名
                        if ($this->stripcode($data->getCellByColumnAndRow(0, $i)->getValue()) == '') continue;
                        $sql .= "(";
                        for ($j = 0; $j < $colscount; $j++) {
                            $sql .= "'{$this->stripcode($data->getCellByColumnAndRow($j, $i)->getValue())}'";
                            if ($j < $colscount - 1) $sql .= ",";
                        }
                        $sql .= ")";
                        if ($i != $rows) $sql .= ",";
                    }
                }
                $sqltruncate = "TRUNCATE TABLE {$table}";
                $sql = trim($sql, ',');
                $sqllist = array(
                    0 => $sqltruncate,
                    1 => $sql,
                );
                //exit($sql);
                $conn = $this->db_base->transaction($sqllist);
                if ($conn == 1) {
                    alert('导入成功');
                } else {
                    alert('导入失败');
                }
            } else {
                echo $_FILES["file"]["type"];
                echo "Invalid file";
            }
        }

    }

    public function export_data()
    {
        ini_set('memory_limit', '500M');
        set_time_limit(1200);
        $type = Jec::getInt('type');
        $actid = Jec::getInt('act_id');
        $actid = $actid == 0 ? 0 : $actid;
        $sql = "select * from activity where type =$type";
        $typeinfo = $this->db->getRow($sql);
        if($typeinfo['type']>0){
            $table = $typeinfo['name'];
        }else{
            alert('活动类型错误，查看失败');
            return;
        }
        $sql = "SELECT * FROM activity_data where type = $type and act_id = $actid";
        $rows = $this->db->getRow($sql);
        $list = array();

        $content = $rows['content'] == "" ? array() : explode("\n",$rows['content']);
        foreach ($content as $key => $val) {
            $val = $val == "" ? array() : explode("|",$val);
            foreach ($val as $k => $v) {
                $list[$k][] = stripslashes($v);
            }
        }

        $frs = $typeinfo['title'] == "" ? array() : explode("|",$typeinfo['title']);
        $fieldarr = array();
        foreach ($frs as $fk => $fv) {
            $fieldarr[$fk] = $fv;
        }
        $objPHPExcel = new PHPExcel();
        $i = 0;
        $j = 2;
        //表为空也导出字段名称
        if (count($list) == 0) {
            foreach ($fieldarr as $fname) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i, 1, $fname);
                $i++;
            }

        }
        foreach ($list as $key => $val) {
            $fields = isset($fieldarr[$key]) ? $fieldarr[$key] : $key;
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i, 1, $fields);
            foreach ($val as $v) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i, $j++, trim($v));
            }
            $i++;
            $j = 2;
        }
        $objPHPExcel->getActiveSheet()->setTitle($table);
        $excelName = $table . '_' . date("YmdHis") . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header("Content-Disposition: attachment; filename=" . $excelName);
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter->save('php://output');
    }
}