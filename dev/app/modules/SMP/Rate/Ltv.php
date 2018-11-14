<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Rate_Ltv extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', 'LTV');

        $this->show();
    }
    
    private function show(){
        $time = $this->getWhereTime('date','0 day',true,'all');
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from cron_rate_ltv where $time "));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $limit2 = $limit + 30;
        $data = $this->db_game->getAll("select * from cron_rate_ltv order by date limit $offset,$limit2");
        $showData = $this->db_game->getAll("select * from cron_rate_ltv where $time order by date asc limit $offset,$limit");
        $formData = $retData = [];
        #格式化参考数据并统计每一天的ltv
        foreach($data as $v)
        {
            $formData[$v['date']] = $v;
        }
        unset($data);
        foreach ($showData as $v) {
            $retData[$v['date']]['date'] = $v['date'];
            $retData[$v['date']]['d1'] = $v['d1'];
            $retData[$v['date']]['d2']  = isset($formData[$v['date']+86400*1])  ? $formData[$v['date']+86400*1]['d2']   : '-';
            $retData[$v['date']]['d3']  = isset($formData[$v['date']+86400*2])  ? $formData[$v['date']+86400*2]['d3']   : '-';
            $retData[$v['date']]['d4']  = isset($formData[$v['date']+86400*3])  ? $formData[$v['date']+86400*3]['d4']   : '-';
            $retData[$v['date']]['d5']  = isset($formData[$v['date']+86400*4])  ? $formData[$v['date']+86400*4]['d5']   : '-';
            $retData[$v['date']]['d6']  = isset($formData[$v['date']+86400*5])  ? $formData[$v['date']+86400*5]['d6']   : '-';
            $retData[$v['date']]['d7']  = isset($formData[$v['date']+86400*6])  ? $formData[$v['date']+86400*6]['d7']   : '-';
            $retData[$v['date']]['d10'] = isset($formData[$v['date']+86400*9])  ? $formData[$v['date']+86400*9]['d10']  : '-';
            $retData[$v['date']]['d15'] = isset($formData[$v['date']+86400*14]) ? $formData[$v['date']+86400*14]['d15'] : '-';
            $retData[$v['date']]['d30'] = isset($formData[$v['date']+86400*29]) ? $formData[$v['date']+86400*29]['d30'] : '-';
        }
        unset($formData,$showData);
        $this->assign('page', $pager->render());
        $this->assign('data',$retData);
        $this->display();
    }

}