<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-7-13
 * Time: 14:43
 */


class SMP_Log_Marry extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '结婚日志');

        $this->show();
    }

    private function show(){
        
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_marry where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from log_marry  where $time $where order by time desc limit $offset,$limit");
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select * from log_marry  where $time $where order by time "));
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
        $csv->download('log_marry.csv');
    }

}

