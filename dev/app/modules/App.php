<?php
/**
 * User: jecelyin 
 * Date: 12-2-2
 * Time: 下午3:09
 *
 */
 
class App extends Controller
{
    public function run()
    {
        //开启session使用
        Session::start();

        $login = new SMP_Auth_Login();
        $hasAuth = $login->hasAuth();
        if(!$hasAuth && $_GET['m'] != 'SMP_Auth_Login')
        {
            $login->showLogin();
        }elseif($hasAuth){ //只能登录后才能执行其它模块，不然会有安全问题
            if(isset($_GET['m'])){
                $module = Controller::getModuleName();
                if(!$module)
                {
                    throw new JecException("非法访问");
                }
            }else {
                $module = 'SMP_Index_Default';
                Controller::setModuleName($module);
            }
            //记录每一个操作，不能在开启module后记录，因为可能被exit
            $m = $_SESSION['module'];
            if($m != $module) {
                Log::info($module, Jec::getVar('do'));
                $_SESSION['module'] = $module;
            }
            $this->startModule();

        }
    }
}