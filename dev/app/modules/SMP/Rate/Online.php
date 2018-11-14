<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Rate_Online extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '在线时长比率');
        $time = $this->getWhereTime('date','0 day',true);
        $rate_online = $this->db_game->getRow("select * from cron_rate_online where $time order by date desc limit 1");
        if($rate_online['date']){
            $data = unserialize($rate_online['online_data']);
            $config = array();
            $config['legend']['data'] = array('人数','比率');
            foreach($data as $key => $v){;
                $config['xaxis']['data'][]= $key;
                $config['series'][1]['data'][] = $v['num'];
                $config['series'][2]['data'][] = $v['pec'];
            }
            $config['xaxis']['type']='category';
            $config['series'][1]['type'] = 'bar';
            $config['series'][1]['name'] = '人数';
            $config['series'][2]['type'] = 'line';
            $config['series'][2]['name'] = '比率';

            $this->assign('chart', Echarts::create($config));
        }
        $this->display();
    }



}