<?php
/**
 * User: jecelyin 
 * Date: 12-1-7
 * Time: 上午10:59
 *
 */

class SMP_Auth_Logout extends AdminController
{
    //本模块不做权限判断
    public $public_auth = true;

    public function __construct()
    {
        parent::__construct();

        setcookie('smp_ck', '', TIME - 99999, '/', '', false, true);
        Session::destroy();
        Net::redirect('/');
    }
}