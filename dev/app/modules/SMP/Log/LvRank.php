<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/14
 * Time: 下午6:06
 */

class SMP_Log_LvRank extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '等级榜日志');

        $this->show();
    }

    private function show(){
        $where = '1';
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where .= " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and nickname ='{$kw_name}'";

        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_exp_rank where $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select * from log_exp_rank  where $where order by time desc limit $offset,$limit ");
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->display();
    }



}