<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Reg_Stat extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '登录注册统计');

        $this->display();
    }



}