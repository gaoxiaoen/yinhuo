<?php
/**
 * Created by PhpStorm.
 * User: li
 * Date: 2017/6/2
 * Time: 17:24
 */

class SMP_Log_MarryTreeReward extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '姻缘树种子领取奖励');
        $this->show();
        $this->display();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pkey={$kw_key}";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_marry_tree_reward where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from log_marry_tree_reward  where $time $where order by time desc limit $offset,$limit ");
        foreach($data as &$val){
            $val['nickname'] = get_player_name($val['pkey']);
        }
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
    }
}