<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_Guild_Member extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '<<紙醉金迷>> 帮会成员列表');

        $this->display();
    }



}