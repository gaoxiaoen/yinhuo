<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Coin_Consume extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '金币使用日志');

        $this->show();
    }
    
    private function show(){
        $pager = new Pager();
        $pager->setTotalRows($this->db->getOne("select count(*) from user_groups"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        
        $this->assign('page', $pager->render());
        $this->getCoinLog();
        $this->display();
    }

    public function getCoinLog()
    {
        $where = "";
        $roleId = Jec::getString('role_id');
        if($roleId > 0){
            $where .= "and a.pkey = $roleId ";
        }
        $useType2 = Jec::getInt('use_type2');
        if($useType2 > 0){
            $where .= "and addreason = $useType2 ";
        }else{
            $useType = Jec::getVar('use_type');
            $useType === false ? $useType = -1 :$useType = (int) $useType;
            if($useType > 0){
                $where .= "and addreason = $useType ";
            }
        }
        $roleName = Jec::getVar('role_name');
        if(!empty($roleName)){
            $where .= "and b.nickname = '{$roleName}' ";
        }
        $time = $this->getWhereTime('time','0 day',true);
        $total_rows = $this->db_game->getOne("select count(*) from log_coin a LEFT JOIN player_state b on a.pkey=b.pkey where  $time ".$where);
        $pager = new Pager(array('pageRows'=> 20));
        $pager->setTotalRows($total_rows);
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $sql1 = "select a.*,b.nickname,b.sn,b.pf from log_coin a LEFT JOIN player_state b on a.pkey=b.pkey where $time ".$where." order by id desc";
        $sql2 = $sql1. " limit $offset ,$limit";
         if(Jec::getVar('download')) $this->csv_download($this->db_game->getAll($sql1));
        $rows = $this->db_game->getAll($sql2);
        $this->assign('page', $pager->render());
        $this->assign('usetype',$useType);
        $this->assign('coinlog',$rows);

    }

    private function csv_download($data){
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('coin_consume.csv');
    }

}