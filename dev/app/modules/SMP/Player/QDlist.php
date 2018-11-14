<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Player_QDlist extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '渠道新增列表');

        $this->show();
    }


    private function show(){
        global $Gqdname;
        $kw = Jec::getVar('kw');
        
        #$where = $this->getWhereTime('reg_time','');
        $st = Jec::getVar('st') ? strtotime(Jec::getVar('st')) :strtotime(date('Y-m-d'));
        $et = Jec::getVar('et') ? strtotime(Jec::getVar('et')) :strtotime(date('Y-m-d'))+86400;

        //d("select count(id) as num ,pf from player_login where $where group by pf",0);
        $qdinfo = array();
        foreach($Gqdname as $qid => $name){
            $qdinfo[$qid]['pf'] = $qid;
            $qdinfo[$qid]['num'] = $this->db_game->getOne("select count(pkey) as num from player_login where reg_time > $st and reg_time < $et and pf = $qid");
            $qdtotal = $this->db_game->getOne("select sum(total_fee) from recharge where time > $st and time < $et and channel_id = $qid");
            $qdinfo[$qid]['charge'] = $qdtotal > 0 ? $qdtotal/100 : 0;
        }
        $this->assign('qdname',$Gqdname);
        $this->assign('kw',$kw);
        $this->assign('qdinfo',$qdinfo);
        $this->display();
    }
    

    /*
     * 函数lot_kick, 实现踢除玩家操作
     */
    private function lot_kick(){
        //需要用接口与relang相接
    }
    
    /*
     * 函数excel_download, 实现角色数据导出操作
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data){
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('role_list.csv');
    }
    

}