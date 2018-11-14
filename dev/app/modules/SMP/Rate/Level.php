<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Rate_Level extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '等级流失率');

        $this->lv_data();
        $this->display();
    }

    public function lv_data()
    {
        $total = $this->db_game->getOne("select count(pkey) as total from player_state");
        $data = $this->db_game->getAll("select lv ,count(pkey) as num from player_state group by lv order by lv asc ");
        if($total > 0){
            foreach($data as $key=>$lvdata){
                $data[$key]['rate'] = round($lvdata['num'] / $total,4)*100;
            }
        }
        $tabs = array(
            'currentChart' => array('name'=>'等级人数图表','checked'=>true),
        );
        $this->assign('tabs',$tabs);

        $config = array();
        $config['legend']['data'] = array('等级人数');
        foreach($data as $v){;
            $config['xaxis']['data'][]=$v['lv'];
            $config['series'][1]['data'][] =(int) $v['num'];
        }
        $config['xaxis']['type']='category';
        $config['xaxis']['axisLabel']=['interval'=>0];
        $config['series'][1]['type'] = 'line';
        $config['series'][1]['name'] = '等级人数';

        $this->assign('chart_lv', Echarts::create($config));

        //d($data);
        $this->assign('data',$data);
    }



}