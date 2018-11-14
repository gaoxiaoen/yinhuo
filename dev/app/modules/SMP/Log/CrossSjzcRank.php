<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-9
 * Time: 15:42
 */

class SMP_Log_CrossSjzcRank extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '三界战场结算日志');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key != '' && !is_numeric($kw_key)) throw new JecException("玩家PKEY必须数字");
        if($kw_key) $where = " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and pname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_cross_sjzc_rank where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from log_cross_sjzc_rank  where $time $where order by time desc limit $offset,$limit");
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select * from log_cross_sjzc_rank  where $time $where order by time "));
        $this->assign('data',$data);
        $this->assign('params',['kw_key'=>$kw_key,'kw_name'=>$kw_name]);
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
        $csv->download('log_cross_sjzc_rank.csv');
    }

}

