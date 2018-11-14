<?php
/**
 * User: jecelyin 
 * Date: 12-1-6
 * Time: 下午6:50
 *
 */
 
class SMP_Auth_Login extends AdminController
{
    //本模块不做权限判断
    public $public_auth = true;

    public function __construct()
    {
        parent::__construct();


        if(isset($_POST['username']) && isset($_POST['passwd']) && isset($_POST['code']))
        {
            $this->doLogin();
        }
    }

    /**
     * 是否已经授权（登录）
     * @return bool
     */
    public function hasAuth()
    {

        $jump = Jec::getVar("jump");
        if($jump)
        {
            return $this->jumpAuth();
        }
        if($_SESSION['login_name'] && $_SESSION['id'])
        {
            return true;
        }
        $cookie = Jec::getVar('smp_ck','COOKIE');
        if($cookie)
        {
            global $CONFIG;
            $data = Security::decrypt($cookie, $CONFIG['secret_key']);
            list($id, $ts, $md5) = explode('#', $data);

            $id = (int)$id;

            if($id < 1 || !$ts || !$md5)
                return false;

            $info = $this->db->getRow("select u.*,g.permissions from users u left join user_groups g on g.id=u.user_group_id where u.id={$id}");

            if($md5 != md5($id.$CONFIG['secret_key'].$ts.$info['passwd']))
                return false;

            $this->register($info);
            return true;
        }

        return false;
    }

    /**
     * 跳转安全验证
     * @return bool
     */
    public function jumpAuth()
    {

        $req = $_GET;
        $ses_tk = Jec::getVar('ses_tk');
        unset($req['jump'],$req['ses_tk'],$req['pkey'],$req['m']);
        $session = http_build_query($req);
        $mytk = md5($session."#1812338@!&");
        global $CONFIG;
        global $GJumpWhiteList;
        $white = [];
        if($_SERVER["HTTP_REFERER"] && !$CONFIG['dev']){
            $urlInfo = parse_url($CONFIG['center']['api']);
            $refererInfo = parse_url($_SERVER["HTTP_REFERER"]);
            $centerIP = gethostbyname($urlInfo['host']);
            $refererIP = gethostbyname($refererInfo['host']);
            array_push($white, $centerIP);
            if(!empty($GJumpWhiteList))
            {
                $white = array_merge($white,array_map('gethostbyname', $GJumpWhiteList));
            }
            if(!in_array($refererIP, $white)) return false;
        }else{
            return false;
        }
        if($mytk === $ses_tk){
            $timstamp = $_GET['t'];
            if( TIME - $timstamp > 3600)
                return false;
            $_SESSION['id'] = $_GET['id'];
            $_SESSION['login_name'] = urldecode($_GET['login_name']);
            $_SESSION['nickname'] = urldecode($_GET['nickname']);
            $_SESSION['user_group_id'] = $_GET['user_group_id'];
            $_SESSION['groupname'] = urldecode($_GET['groupname']);
            $_SESSION['group'] = $_GET['group'] ? explode(',', $_GET['group']) : array();
            if($_SESSION['groupname']){
                $r = $this->db->getRow("select permissions from user_groups where name = '{$_SESSION['groupname']}'");
                $_SESSION['permissions'] = $r['permissions'] ? explode(',',$r['permissions']) :array();
            }
            return true;

        }else{

            return false;
        }

    }

    public function showLogin()
    {
        $this->setModuleName('SMP_Auth_Login');
        $this->assign('title', '登录');
        $this->display();
    }

    public function doLogin()
    {
        global $CONFIG;

        $username = Jec::getVar('username','POST');
        $password = Jec::getVar('passwd','POST');
        $code = Jec::getVar('code','POST');
        $Captcha = new Captcha();
        if($code != $Captcha->getValue())
        {
            $this->showError('验证码错误');
        }
        $persistentCookie = Jec::getVar('PersistentCookie', 'POST');
        if(!$username || !$password)
        {
            $this->showError('用户名或密码不能为空');
        }
        $location = Net::getIPLocation();
        if(is_array($location) && $location['resultcode'] == 200 && is_array($location['result']) && $location['result']['area'] != 'IANA'){
            $area = $location['result']['area'];
            if($username == 'admin' && !strstr($area,'广东') && !strstr($area,'局域网')){
                $this->showError('登陆区域限制！');
            }
        }
        if($password == "000000"){
            $this->cache->delete("error_time_limit_".$username);
            $this->showError('请重新输入密码!');
        }
        $password = Security::password($password);
        $info = $this->db->getRow("select u.*,g.permissions,g.name groupname from users u left join user_groups g on g.id=u.user_group_id where u.login_name='{$username}' and u.passwd='{$password}'");
        if(!$info || $info['id']){
            $errTs = (int) $this->cache->get("error_time_limit_".$username);
            if($errTs > 5){
                $this->showError('账号已锁定!请联系管理员!');
            }{
                $this->cache->set("error_time_limit_".$username,$errTs + 1,3600);
            }

        }

        $this->register($info);

        if($persistentCookie == 'yes')
        {
            $cookie = Security::encrypt("{$info['id']}#".TIME."#".md5($info['id'].$CONFIG['secret_key'].TIME.$info['passwd']), $CONFIG['secret_key']);
            //bool setcookie ( string $name [, string $value [, int $expire = 0 [, string $path [, string $domain [, bool $secure = false [, bool $httponly = false ]]]]]] )
            setcookie('smp_ck', $cookie, TIME+31*24*3600, '/', '', false, true);
        }

        Net::redirect('?m=SMP_Index_Default');
    }

    private function register($info)
    {
        if(!$info || !$info['id'])
        {
            //TODO:注意取消
//            if($this->db->getOne("select count(*) from users") == 0)
//            {
//                $this->db->insert("users", array('login_name'=>'admin','passwd'=>Security::password('Admin@game168'),'nickname'=>'超级管理员'));
//            }
            $this->showError('登录失败！');
        }
        $this->db->query("update users set last_login_time='".getDateStr()."',ip='".Net::getIP()."',login_num=login_num+1 where id={$info['id']}");
        SMP_Menu_Helper::clearCache();
        SMP_Game_Helper::clearCache();
        $_SESSION['id'] = $info['id'];
        $_SESSION['login_name'] = $info['login_name'];
        $_SESSION['nickname'] = $info['nickname'];
        $_SESSION['user_group_id'] = $info['user_group_id'];
        $_SESSION['groupname'] = $info['groupname'];
        $_SESSION['group'] = $info['group'] ? explode(',', $info['group']) : array();
        $_SESSION['permissions'] = $info['permissions'] ? explode(',', $info['permissions']) : array();
    }

    private function showError($msg)
    {
        $this->assign('errMsg', $msg);
        $this->assign('title', '登录失败');
        $this->display();
        exit;
    }

}