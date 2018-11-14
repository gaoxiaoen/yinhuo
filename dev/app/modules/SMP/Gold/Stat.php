<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Gold_Stat extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '元宝消耗图表');

        $this->show();
    }
    
    private function show()
    {
        $data = array(
            '0' => array(
                '0' => '商城',
                '1' => '38344679' 
            ),
            '1' => array(
                '0' => '寻宝',
                '1' => '6125925'
            ),
            '2' => array(
                '0' => '市场消费',
                '1' => '1829171'
            ),
            '3' => array(
                '0' => '元神',
                '1' => '1697583'
            ),
            '4' => array(
                '0' => '刷宠物蛋',
                '1' => '1648597'
            ),
            '5' => array(
                '0' => '帮会捐献',
                '1' => '1615789'
            ),
            '6' => array(
                '0' => '角色培养',
                '1' => '739560'
            ),
            '7' => array(
                '0' => '神秘商店',
                '1' => '631227'
            ),
            '8' => array(
                '0' => '购买熟练度',
                '1' => '366871'
            ),
            '9' => array(
                '0' => '仙园',
                '1' => '221162'
            ),
            '10' => array(
                '0' => '刷美女',
                '1' => '141642'
            ),
            '11' => array(
                '0' => '结婚消费',
                '1' => '77347'
            ),
            '12' => array(
                '0' => '领取离线经验',
                '1' => '68680'
            ),
            '13' => array(
                '0' => '宠物消费',
                '1' => '60060'
            ),
            '14' => array(
                '0' => '复活',
                '1' => '56385'
            ),
            '15' => array(
                '0' => '购买挂机',
                '1' => '47640'
            ),
            
        );
        
        $this->makeChart('chart','元宝消耗直方图', '消耗类型', '消耗量', $data);
        $this->display();
    }

    private function makeChart($name,$caption, $xAxisName, $yAxisName, $dataset)
    {
        $this->assign($name, Charts::renderColumn2D($caption, $xAxisName, $yAxisName, $dataset,'100%'));
    }

}