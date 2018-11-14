<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Page_Stat extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '页面流失率');

        $this->display();
    }



}