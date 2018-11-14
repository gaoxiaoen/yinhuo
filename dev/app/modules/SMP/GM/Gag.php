<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_GM_Gag extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '封号 / 禁言');
        
        $this->show();
    }
    
    private function makeTab()
    {
        $tabs = array(
            'listGag' => array('name' => '封号/禁言列表', 'checked' => true),
            'manageGag' => array('name' => '封号/禁言管理')
        );
        $this->assign('tabs', $tabs);
    }
    
    private function show($html = ''){
        
        $this->makeTab();
        
        $pager = new Pager();
        $pager->setTotalRows($this->db->getOne("select count(*) from user_groups"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        
        $this->assign('page', $pager->render());
        
        $this->display();
    }



}