<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Rate_Time extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '时间段流失率');
        $time = $this->getWhereTime('date','-2 day',true,'oneday');
        $rate_time = $this->db_game->getRow("select * from cron_rate_time where $time order by date desc limit 1");
        if($rate_time['date']){
            $data = unserialize($rate_time['ratetime']);
            $config = array();
            $config['legend']['data'] = array('流失人数','流失比率');
            foreach($data as $key => $v){;
                $config['xaxis']['data'][]= $key;
                $config['series'][1]['data'][] = $v['num'];
                $config['series'][2]['data'][] = $v['pec'];
            }
            $config['xaxis']['type']='category';
            $config['series'][1]['type'] = 'bar';
            $config['series'][1]['name'] = '流失人数';
            $config['series'][2]['type'] = 'line';
            $config['series'][2]['name'] = '流失比率';

            $this->assign('chart', Echarts::create($config));
        }


        $this->display();
    }



}