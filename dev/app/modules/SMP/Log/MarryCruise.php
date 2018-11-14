<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-8-28
 * Time: 15:07
 */

class SMP_Log_MarryCruise extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '婚宴预约日志');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('b_key'));
        if($kw_key) $where = " and key_boy={$kw_key}";
        $kw_name = Jec::getVar('b_name');
        if($kw_name) $where .= " and nickname_boy ='{$kw_name}'";
        $kw_key = g(Jec::getVar('g_key'));
        if($kw_key) $where = " and key_girl={$kw_key}";
        $kw_name = Jec::getVar('g_name');
        if($kw_name) $where .= " and nickname_girl ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_marry_cruise where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from log_marry_cruise  where $time $where order by time desc limit $offset,$limit");
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select * from log_marry_cruise  where $time $where order by time "));
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
        $csv->download('log_marry_cruise.csv');
    }

}

