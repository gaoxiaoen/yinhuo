<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_GM_addplayerMail extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '发送邮件');

        $this->show();
    }
    
     private function show()
    {
        $this->display();   
    }



}