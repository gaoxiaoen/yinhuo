<?php
/**
 * User: jecelyin 
 * Date: 12-2-9
 * Time: 下午5:21
 *
 */
 
class SMP_Index_Default extends AdminController
{
    //登录后所有人都可以访问
    public $private_auth = true;

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '基本信息');
        SMP_Menu_Helper::clearCache();
        SMP_Game_Helper::clearCache();
        $act = Jec::getVar('act');
        if($act == 'setPageNum')
        {
            $this->setUserPageNum();
        }else{
            $this->display();
        }
    }

    public function setUserPageNum()
    {
        $pageNum = Jec::getInt('pageNum');
        if(!$pageNum) exit(json_encode('错误的输入...'));
        $cache = Cache::getInstance();
        $cacheArr = $cache->get('user_set_page_num_cache');
        $cacheArr[$_SESSION['id']] = $pageNum;
        $cache->set('user_set_page_num_cache',$cacheArr,86400*360);
        $res = $cache->get('user_set_page_num_cache');
        exit(json_encode('分页数量设置为: '.$res[$_SESSION['id']]));
    }

}