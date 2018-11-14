<?php
/**
 * Date: 12-3-13
 * Time: 上午11:22
 * 跳转到单服
 */

class SMP_Game_Goto extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->redirect();
    }

    private function redirect()
    {
        if($this->gs_id < 1)
            throw new JecException('no gs_id');

        $srv = $this->gameHelper->getServerInfo($this->gs_id);
        if(!$srv)throw new JecException('没有该服务器！');

        $url = $srv['url']."dc_api/dc_csv.php?m=Auth_Login&".Helper::getAuthUrl($_SESSION['login_name']);
        //d($url);
        $newurl = Net::fetch($url, array(CURLOPT_USERAGENT=>$_SERVER['HTTP_USER_AGENT']));
        if(strpos($newurl, '?') !== 0 || !strpos($newurl,'&') || !strpos($newurl, '='))
            exit('服务器返回错误信息：'.htmlspecialchars($newurl));
        Net::redirect($srv['url']."dc_api/dc_csv.php".$newurl);
    }

}