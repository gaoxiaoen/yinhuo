<?php
/**
 * User: jecelyin 
 * Date: 12-2-24
 * Time: 下午5:17
 *
 */

class SMP_Charge_Tpl extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '充值列表模板');
        $this->show();
    }

    private function makeTab()
    {
        $tabs = array(
            'data' => array('name' => '充值列表', 'checked' => true),
            'chart' => array('name' => '充值图表')
        );
        $this->assign('tabs', $tabs);
    }

    private function show()
    {
        $this->makeTab();
        //$whereLimit = $this->gameHelper->getServersLimit();
        $timeLimit = $this->getWhereTime('ts', '0 day',false,'month');

        $total = array();
        //$total['srv_num'] = $this->gameHelper->getServerNum();
        $total['charge'] = 345222;
        $total['charge'] = formatNumber($total['charge'], -1);

        $dateType = $this->getDateType();
        $table = 'charge_' . $dateType;
        if ($dateType == 'week') {
            $extraField = ',week_num';
            $groupby = 'ts,week_num';
        } else {
            $extraField = '';
            $groupby = 'ts';
        }

        //$groupby = $this->gameHelper->getGroupBy($groupby);
        $data = array(
            0 =>
            array(
                'role_num' => '164',
                'c_num' => '256',
                'new_role_num' => '19',
                'new_c_num' => '20',
                'new_gold' => '1788',
                'new_rmb' => '178',
                'rmb' => '16351.00',
                'cny' => '16351.00',
                'hkd' => '0.00',
                'twd' => '3900.00',
                'vnd' => '0.00',
                'myr' => '0.00',
                'krw' => '0.00',
                'thb' => '0.00',
                'gold' => '163252',
                'ts' => '2012-07-22',
                'srv_num' =>
                array(
                    'sum' => 3416,
                    'count' => 1169,
                ),
            ),
            1 =>
            array(
                'role_num' => '149',
                'c_num' => '218',
                'new_role_num' => '34',
                'new_c_num' => '34',
                'new_gold' => '10319',
                'new_rmb' => '1037',
                'rmb' => '13987.00',
                'cny' => '13987.00',
                'hkd' => '0.00',
                'twd' => '580.00',
                'vnd' => '0.00',
                'myr' => '0.00',
                'krw' => '0.00',
                'thb' => '0.00',
                'gold' => '140023',
                'ts' => '2012-07-23',
                'srv_num' =>
                array(
                    'sum' => 3431,
                    'count' => 1184,
                ),
            ),
            2 =>
            array(
                'role_num' => '251',
                'c_num' => '359',
                'new_role_num' => '130',
                'new_c_num' => '130',
                'new_gold' => '29807',
                'new_rmb' => '2980',
                'rmb' => '23704.00',
                'cny' => '23704.00',
                'hkd' => '0.00',
                'twd' => '12270.00',
                'vnd' => '0.00',
                'myr' => '0.00',
                'krw' => '0.00',
                'thb' => '0.00',
                'gold' => '237220',
                'ts' => '2012-07-24',
                'srv_num' =>
                array(
                    'sum' => 3449,
                    'count' => 1202,
                ),
            ),
            3 =>
            array(
                'role_num' => '152',
                'c_num' => '264',
                'new_role_num' => '39',
                'new_c_num' => '48',
                'new_gold' => '24234',
                'new_rmb' => '2444',
                'rmb' => '23165.00',
                'cny' => '23165.00',
                'hkd' => '0.00',
                'twd' => '5300.00',
                'vnd' => '0.00',
                'myr' => '0.00',
                'krw' => '0.00',
                'thb' => '0.00',
                'gold' => '231674',
                'ts' => '2012-07-25',
                'srv_num' =>
                array(
                    'sum' => 3464,
                    'count' => 1217,
                ),
            ),
            4 =>
            array(
                'role_num' => '141',
                'c_num' => '273',
                'new_role_num' => '39',
                'new_c_num' => '41',
                'new_gold' => '19985',
                'new_rmb' => '1996',
                'rmb' => '20115.00',
                'cny' => '20115.00',
                'hkd' => '0.00',
                'twd' => '11100.00',
                'vnd' => '0.00',
                'myr' => '0.00',
                'krw' => '0.00',
                'thb' => '0.00',
                'gold' => '201263',
                'ts' => '2012-07-26',
                'srv_num' =>
                array(
                    'sum' => 3482,
                    'count' => 1235,
                ),
            ),
            5 =>
            array(
                'role_num' => '73',
                'c_num' => '122',
                'new_role_num' => '8',
                'new_c_num' => '10',
                'new_gold' => '2830',
                'new_rmb' => '293',
                'rmb' => '7835.00',
                'cny' => '7835.00',
                'hkd' => '0.00',
                'twd' => '0.00',
                'vnd' => '0.00',
                'myr' => '0.00',
                'krw' => '0.00',
                'thb' => '0.00',
                'gold' => '78372',
                'ts' => '2012-07-27',
                'srv_num' =>
                array(
                    'sum' => 3499,
                    'count' => 1252,
                ),
            ),
        );

        $this->makeChart($data);

        $this->assign('data', $data);
        $this->assign('total', $total);
        $this->display();

    }

    private function makeChart($data)
    {
        $categories = array();
        $dataset_rmb = array();
        $dataset_c_num = array();
        $dataset_role_num = array();
        foreach ($data as $val) {
            $categories[$val['ts']] = $val['ts'];
            $dataset_rmb['充值人民币'][$val['ts']] = $val['rmb'];
            $dataset_rmb['新增RMB玩家充值人民币'][$val['ts']] = $val['new_rmb'];
            $dataset_c_num['充值次数'][$val['ts']] = $val['c_num'];
            $dataset_c_num['新增RMB玩家充值次数'][$val['ts']] = $val['new_c_num'];
            $dataset_role_num['充值人数'][$val['ts']] = $val['role_num'];
            $dataset_role_num['新增RMB玩家人数'][$val['ts']] = $val['new_role_num'];
        }
        /*ksort($categories);
        ksort($dataset_rmb['充值人民币']);
        ksort($dataset_rmb['新增RMB玩家充值人民币']);
        ksort($dataset_c_num['充值次数']);
        ksort($dataset_c_num['新增RMB玩家充值次数']);
        ksort($dataset_role_num['充值人数']);
        ksort($dataset_role_num['新增RMB玩家人数']);*/
        $this->assign('chart1', Charts::renderZoomLine($categories, $dataset_rmb));
        $this->assign('chart2', Charts::renderZoomLine($categories, $dataset_c_num));
        $this->assign('chart3', Charts::renderZoomLine($categories, $dataset_role_num));
    }
}