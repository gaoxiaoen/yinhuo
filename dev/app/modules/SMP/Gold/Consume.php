<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Gold_Consume extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '钻石使用日志');

        $this->show();
    }

    private function makeTab()
    {
        $tabs = array(
            'gold_log' => array('name' => '钻石获得及使用记录', 'checked' => true),
        );
        $this->assign('tabs', $tabs);
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
        if($roleId) {
            if(strpos($roleId,",") > 0){
                $where .= " and a.pkey in ({$roleId})";
            }else
                if($roleId > 0){
                    $where .= "and a.pkey = $roleId ";
                }
        };
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
            $where .= "and a.nickname = '{$roleName}' ";
        }
        $time = $this->getWhereTime('time','0 day',true);
        $where .= " and $time";
        $total_rows = $this->db_game->getOne("select count(*) from log_gold a where ".$where);
        $pager = new Pager();
        $pager->setTotalRows($total_rows);
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $columns = ['a.pkey','a.nickname','a.sn','b.pf','a.addgold','a.oldbgold','a.newbgold','a.oldgold','a.newgold','a.addreason','a.time'];
        $sql1 = "select ".implode(',', $columns)." from log_gold a LEFT JOIN player_state b on a.pkey=b.pkey where ".$where." order by time desc,id asc";
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
        array_unshift($data,['PKEY','nickname','server','platform','gold_change','bgold_before_change','bgold_after_change','gold_before_change','gold_after_change','type','time','memo']);
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('gold_consume.csv');
    }

}