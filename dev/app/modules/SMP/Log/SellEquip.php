<?php
/**
 * Created by PhpStorm.
 * User: li
 * Date: 2017/6/2
 * Time: 17:24
 */

class SMP_Log_SellEquip extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '特权炫装产出');
        $this->show();
        $this->display();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and nickname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_sell_equip where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select * from log_sell_equip  where $time $where order by time desc limit $offset,$limit ");
        $sql = "select * from log_sell_equip ";
        if(Jec::getVar('download')) $this->csv_download($this->db_game->getAll($sql));
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
    }

    private function csv_download($data){
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('sell_equip.csv');
    }
}