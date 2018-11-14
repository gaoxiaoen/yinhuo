<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Rate_Task extends AdminController
{
    private $tasktype = array('1'=>'主线','2'=>'支线');
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '任务流失率');

        $this->show();
    }
    
    private function show()
    {
        $where = '1 = 1 ';
        $params['taskid'] = Jec::getInt('taskid') == '' ?  '' : Jec::getInt('taskid');
        if($params['taskid']) $where .= "and task_id = {$params['taskid']} ";
        $params['tasktype'] = Jec::getInt('kw_task_type');
        if($params['tasktype']) $where .= "and type = {$params['tasktype']} ";
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from cron_task where $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from cron_task where $where order by task_id asc limit $offset,$limit");
        $this->assign('data',$data);
        $this->assign('tasktype',$this->tasktype);
        $this->assign('page', $pager->render());
        $this->assign('params',$params);
        $this->display();
    }


}