<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_Guild_Conevent extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '<<紙醉金迷>> 帮会贡献事件');

        $this->display();
    }



}