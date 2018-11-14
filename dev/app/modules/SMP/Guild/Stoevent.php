<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_Guild_Stoevent extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '<<紙醉金迷>> 帮会仓库事件');
        $this->display();
    }
}