<?php
/**
 * Created by PhpStorm.
 * User: whx
 * Date: 2017/9/28
 * Time: 23:57
 */
class SMP_Log_GemStrength extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '宝石强化');
        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and (pkey={$kw_key})";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and (nickname ='{$kw_name}' )";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_gem_strength where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from log_gem_strength  where $time $where order by time desc limit $offset,$limit");
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select * from log_gem_strength  where $time $where order by time "));
        
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
        $csv->download('log_gem_strength.csv');
    }

}

