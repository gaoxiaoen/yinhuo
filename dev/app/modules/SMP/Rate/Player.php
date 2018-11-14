<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Rate_Player extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '玩家流失率');

        $this->show();
    }

    private function show(){
        $time = $this->getWhereTime('date','0 day',true,'all');
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from cron_rate_player where $time "));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        #获取参考数据
        $data = $this->db_game->getAll("select * from cron_rate_player");
        #获取展示数据
        $showData = $this->db_game->getAll("select * from cron_rate_player where $time order by date asc limit $offset,$limit");
        $formatData = $retData = [];
        #格式化参考数据
        foreach($data as $v)
        {
            $formatData[$v['date']] = $v;
        }
        unset($data);
        #梳理展示数据
        foreach($showData as $v)
        {
            $retData[$v['date']]['date'] = $v['date'];
            $retData[$v['date']]['d1']  = isset($formatData[$v['date']+86400*1])  ? $formatData[$v['date']+86400*1]['d1'] : '';
            $retData[$v['date']]['d2']  = isset($formatData[$v['date']+86400*2])  ? $formatData[$v['date']+86400*2]['d2'] : '';
            $retData[$v['date']]['d3']  = isset($formatData[$v['date']+86400*3])  ? $formatData[$v['date']+86400*3]['d3'] : '';
            $retData[$v['date']]['d4']  = isset($formatData[$v['date']+86400*4])  ? $formatData[$v['date']+86400*4]['d4'] : '';
            $retData[$v['date']]['d5']  = isset($formatData[$v['date']+86400*5])  ? $formatData[$v['date']+86400*5]['d5'] : '';
            $retData[$v['date']]['d6']  = isset($formatData[$v['date']+86400*6])  ? $formatData[$v['date']+86400*6]['d6'] : '';
            $retData[$v['date']]['d7']  = isset($formatData[$v['date']+86400*10])  ? $formatData[$v['date']+86400*10]['d7'] : '';
            $retData[$v['date']]['d15'] = isset($formatData[$v['date']+86400*15]) ? $formatData[$v['date']+86400*15]['d15'] : '';
            $retData[$v['date']]['d30'] = isset($formatData[$v['date']+86400*30]) ? $formatData[$v['date']+86400*30]['d30'] : '';
        }
        unset($showData,$formatData);
        $this->assign('page', $pager->render());
        $this->assign('data',$retData);
        $this->display();
    }

}