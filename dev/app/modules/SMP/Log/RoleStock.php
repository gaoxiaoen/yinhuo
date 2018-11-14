<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_Log_RoleStock extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '仓库操作日志');

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