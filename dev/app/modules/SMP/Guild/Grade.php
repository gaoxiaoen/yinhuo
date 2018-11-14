<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_Guild_Grade extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '帮会升级列表');

        $this->display();
    }



}