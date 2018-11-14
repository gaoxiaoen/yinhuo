<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-12
 * Time: 13:45
 */

class SMP_Log_Onhook  extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '离线挂机日志表');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and nickname ='{$kw_name}'";
        $time = $this->getWhereTime('start_time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_on_hook where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from log_on_hook  where $time $where order by start_time desc limit $offset,$limit");
        foreach($data as &$d){
            if($d['state']==1){
                $d['state']= '开始挂机';
            }else{
                $d['state']= '结束挂机';
            }
        }
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select * from log_on_hook  where $time $where order by start_time "));
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->display();
    }

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data)
    {
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('log_equip_wash.csv');
    }

}

