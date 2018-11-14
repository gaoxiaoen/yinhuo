<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Player_Staff extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '内部人员监控');

        $this->show();
    }
    
    private function show(){
        $pager = new Pager();
        $pager->setTotalRows($this->db->getOne("select count(*) from user_groups"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        
        $this->assign('page', $pager->render());
        $this->display();
    }
    

}