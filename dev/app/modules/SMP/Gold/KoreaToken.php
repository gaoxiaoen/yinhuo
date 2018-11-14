<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */

class SMP_Gold_KoreaToken extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '韩国代币使用日志');

        $this->show();
    }

    private function show()
    {
        #$this->makeTab();

        $pager = new Pager();
        $pager->setTotalRows($this->db->getOne("select count(*) from user_groups"));
        $this->assign('page', $pager->render());

        $this->getGoldLog();
        $this->display();
    }

    public function getGoldLog()
    {
        $where = "1 ";
        $roleId = Jec::getString('role_id');
        if($roleId > 0){
            $where .= "and a.pkey = $roleId ";
        }
        $useType2 = Jec::getVar('use_type2') === '' ? '' : Jec::getInt('use_type2');
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
        $where .= " and $time";
        $total_rows = $this->db_game->getOne("select count(*) from log_korea_token a where ".$where);
        $pager = new Pager();
        $pager->setTotalRows($total_rows);
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $columns = ['a.pkey','b.nickname','a.old_korea_token','a.new_korea_token','a.add_korea_token','a.addreason','a.time'];
        $sql1 = "select ".implode(',', $columns)." from log_korea_token a LEFT JOIN player_state b on a.pkey=b.pkey where ".$where." order by time desc,id asc";
        $sql2 = $sql1 . " limit $offset ,$limit";
        if(Jec::getVar('download')) $this->csv_download($this->db_game->getAll($sql1));
        $rows = $this->db_game->getAll($sql2);
        $this->assign('page', $pager->render());
        $this->assign('usetype',$useType);
        $this->assign('goldlog',$rows);
        $this->assign('req_params',['pkey'=>$roleId,'name'=>$roleName,'useType'=>$useType,'useType2'=>$useType2]);

    }
    private function csv_download($data){
        foreach ($data as &$d)
        {
            $d['memo'] = $this->consume_type[$d['addreason']];
            $d['time'] = date('Y-m-d H:i:s',$d['time']);
        }
        array_unshift($data,['pkey','nickname','old_korea_token','new_korea_token','add_korea_token', 'memo','time']);
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('gold_consume.csv');
    }

}