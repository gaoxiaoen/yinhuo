<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Online_Chart extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '在线图表');

        $this->showOnline();
    }

    /** 实时在线人数图表 **/
    private function showOnline()
    {
        $tabs = array(
            'currentChart' => array('name'=>'在线实时人数图表','checked'=>true),
        );
        $this->assign('tabs',$tabs);
        $time = $this->getWhereTime('time','0 day',true);
        $data = $this->db_game->getAll("select * from online where $time order by time asc limit 10000");
        $config = array();
        $config['legend']['data'] = array('在线数');
        foreach($data as $v){
            $time = date("Y-m-d H:i",$v['time']);
            $config['xaxis']['data'][]=$time;
            $config['series'][1]['data'][] =(int) $v['num'];
        }
        $config['xaxis']['type']='category';
        $config['series'][1]['type'] = 'line';
        $config['series'][1]['name'] = '在线';
        $this->assign('chart_now', Echarts::create($config));
        $this->display();

    }

    /** 以下参考无用 **/

    private function makeTab()
    {
        $tabs = array(
            'currentChart' => array('name' => '在线实时人数图表', 'checked' => true),
            'statisticsChart' => array('name' => '在线峰值统计图表')
        );
        $this->assign('tabs', $tabs);
    }
    
     private function show()
    {
        $this->makeTab();
        
        $data = array(
            '0' => array(
                'time' => '00:00',
                'top_num' => '10',
                'avg_num' => '18'
            ),
            '1' => array(
                'time' => '01:00',
                'top_num' => '5',
                'avg_num' => '25'
            ),
            '2' => array(
                'time' => '02:00',
                'top_num' => '8',
                'avg_num' => '28'
            ),
            '3' => array(
                'time' => '03:00',
                'top_num' => '10',
                'avg_num' => '15'
            ),
            '4' => array(
                'time' => '04:00',
                'top_num' => '12',
                'avg_num' => '10'
            ),
            '5' => array(
                'time' => '05:00',
                'top_num' => '16',
                'avg_num' => '0'
            ),
            '6' => array(
                'time' => '06:00',
                'top_num' => '18',
                'avg_num' => '5'
            ),
        );
        $this->makeChart($data,'chart_now');
        $this->makeChart($data,'chart_past',1);
        $this->display();   
    }
    
    private function makeChart($data,$name,$avg = 0)
    {
        $categories = array();//分类(横轴)
        $content = array();//内容
        if($avg == 1){
            foreach($data as $v){
                $categories[$v['time']] = $v['time'];
                $content['在线峰值'][$v['time']] = $v['top_num'];
                $content['平均在线峰值'][$v['time']] = $v['avg_num'];
            }
        }else{
            foreach($data as $v){
                $categories[$v['time']] = $v['time'];
                $content['在线峰值'][$v['time']] = $v['top_num'];
            }
        }
        echo "<pre>";
        print_r($categories);
        echo "</pre>";  
        $this->assign($name, Charts::renderZoomLine($categories,$content));
    }

}