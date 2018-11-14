<?php
/**
 * User: liuxiaoqing 
 * Mail: liuxiaoqing437@gmail.com 
 * Time: 下午2:41
 * 后台操作日志
 */
 
class SMP_Admin_Log extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '后台操作日志');
        $this->show();
    }

    public function show()
    {
        $where = $this->getWhereTime('ctime','0 day',true,'daily');
        $where .= Jec::getVar('nickname') ? " and nickname='".Jec::getVar('nickname')."'" : '';
        $where .= Jec::getVar('ip') ? " and ip='".Jec::getVar('ip')."'" : '';
        //echo $where;
        
        $total_rows = $this->db->getOne("select count(*) from logs where $where");

        $pager = new Pager();
        $pager->setTotalRows($total_rows);
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        
        $logs = $this->db->getAll("select * from logs where $where order by ctime desc limit $offset,$limit");
        loadPlugins('IP');
        //$Ip = new IP(APP_PATH . DS . 'assets' . DS . 'QQWry.dat');
        
        foreach ($logs as $key => $value) {
        	$logs[$key]['ipaddress'] = $value['ip'];
        }
        //d($logs);
        $this->assign('data', $logs);
        $this->assign('page', $pager->render());
        $this->display();
    }
}