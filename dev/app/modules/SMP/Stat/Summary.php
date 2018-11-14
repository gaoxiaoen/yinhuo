<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_Stat_Summary extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '每天数据汇总');
        $this->show();
    }
    
    private function show(){
        $tabs = array(
            'currentChart' => array('name'=>'每天数据汇总','checked'=>true),
        );
        $this->assign('tabs',$tabs);
        $time = $this->getWhereTime('time','0 day',true,'month');
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from cron_daily where $time "));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select * from cron_daily where $time order by time desc limit $offset,$limit");
        foreach($data as &$d){
            $reg_time_data = unserialize($d['reg_time_data']);
            for($i = 0;$i <= 23;$i ++){
                $d['time_data'][$i] = isset($reg_time_data[$i]) ? $reg_time_data[$i] : 0;
            }
        }
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        sort($data);
        $config['legend']['data'] = array('注册人数','活跃人数');
        foreach($data as $v){
            $dt = date('d',$v['time']);
            $config['xaxis']['data'][]=$dt;
            $config['series'][1]['data'][] =(int) $v['reg_num'];
            $config['series'][2]['data'][] =(int) $v['login_num'];
        }
        $config['xaxis']['type']='category';
        $config['series'][1]['type'] = 'line';
        $config['series'][1]['name'] = '注册人数';
        $config['series'][2]['type'] = 'line';
        $config['series'][2]['name'] = '活跃人数';

        $this->assign('chart_data', Echarts::create($config));

        $this->display();
    }

}