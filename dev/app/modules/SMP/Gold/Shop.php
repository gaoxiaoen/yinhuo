<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Gold_Shop extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '商城产出统计');

        $this->show();
    }
    
    private function makeTab()
    {
        $tabs = array(
            'goodStatistics' => array('name' => '商城物品消费统计', 'checked' => true),
            'goldStatistics' => array('name' => '商城元宝消耗统计')
        );
        $this->assign('tabs', $tabs);
    }
    
    private function show(){
        
        $this->makeTab();
        
        $pager = new Pager();
        $pager->setTotalRows($this->db->getOne("select count(*) from user_groups"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        
        $this->assign('page', $pager->render());
        $this->display();
    }



}