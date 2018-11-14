<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/14
 * Time: 下午4:23
 */

class SMP_Log_Playergold extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '玩家当天钻石获取日志');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and a.pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and b.nickname ='{$kw_name}'";
        $time = $this->getWhereTime('a.time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from cron_player_money a where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select *,b.nickname from cron_player_money a LEFT JOIN player_state b on a.pkey=b.pkey where $time $where order by sum_gold desc limit $offset,$limit ");
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->display();
    }



}