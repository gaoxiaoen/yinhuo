<?php

/**
 *
 */
class SMP_Charge_List extends AdminController
{
    public $cache = null;

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '充值明细');

        $this->cache = Cache::getInstance();
        $this->getTotalCharge();
        $this->getChargeCalc();
        $this->getCharge();
        $this->display();
    }

    /**
     * 充值日志
     */
    public function getCharge()
    {

        $where = "";
        $params['game_channel_id'] = Jec::getInt('game_channel_id') == '' ? '' : Jec::getInt('game_channel_id');
        if ($params['game_channel_id'] > 0) {
            $where .= "and game_channel_id = {$params['game_channel_id']} ";
        }
        $params['roleId'] = Jec::getInt('role_id') == '' ? '' : Jec::getInt('role_id');
        if ($params['roleId'] > 0) {
            $where .= "and app_role_id = {$params['roleId']} ";
        }
        $params['roleName'] = Jec::getVar('role_name');
        if (!empty($params['roleName'])) {
            $where .= "and nickname = '{$params['roleName']}' ";
        }
        $params['accName'] = Jec::getVar('account_name');
        if (!empty($params['accName'])) {
            $where .= "and user_id = '{$params['accName']}' ";
        }
        $params['order'] = Jec::getVar('odd');
        if (!empty($params['order'])) {
            $where .= "and app_order_id = '{$params['order']}' ";
        }

        $time = $this->getWhereTime('time','0 day',true,'daily');

        $total_rows = $this->db_game->getOne("select count(*) from recharge where $time $where");
        #$pager = new Pager(array('pageRows' => 20));
        $pager = new Pager();
        $pager->setTotalRows($total_rows);
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $sql = "select * from recharge where  $time $where order by time desc limit $offset ,$limit";
        //d($sql);
        $rows = $this->db_game->getAll($sql);
        $detail = Jec::getVar('detail') > 0 ? true:false;
        if(!$detail && $pager->page <= 1){
            $tabs = array('charge'=>array('name'=>'充值统计','checked'=>true),'charge_detail'=>array('name'=>'充值详细报表'));
            $this->assign('disp1','block');
            $this->assign('disp2','none');
        }else{
            $tabs = array('charge'=>array('name'=>'充值统计'),'charge_detail'=>array('name'=>'充值详细报表','checked'=>true));
            $this->assign('disp1','none');
            $this->assign('disp2','block');
        }
        #图表数据
        $sql = "select total_fee, count(*) c from recharge where $time group by total_fee";
        $chart_rows = $this->db_game->getAll($sql);
        $config = array();
        $config['title'] = '充值分布';
        foreach($chart_rows as $v){;
            $config['xaxis']['data'][]=(int)$v['total_fee']/100;
            $config['series'][1]['data'][] =(int) $v['c'];
        }
        $config['xaxis']['type']='category';
        $config['xaxis']['axisLabel']=['interval'=>0];
        $config['series'][1]['type'] = 'line';
        $config['series'][1]['name'] = '金额人数';
        $this->assign('charts_charge',Echarts::create($config,'1200px','300px'));
        $this->assign('tabs',$tabs);
        $this->assign('params',$params);
        $this->assign('page', $pager->render());
        $this->assign('chargelog', $rows);

    }

    /**
     * 充值统计
     */
    public function getChargeCalc()
    {
        $st = Jec::getVar('st');
        if ($st) {
            $monthTime = strtotime(date('Y-m',strtotime($st)));
        } else {
            $monthTime = strtotime(date("Y-m", time()));
        }
        $et = Jec::getVar('et');
        if ($et) {
            $nextmonthTime = strtotime('+1 month',strtotime(date('Y-m',strtotime($et))));
        }else {
            $nextmonthTime = strtotime(date("Y-m", strtotime('+1 month')));
        }

        $sql = "select * from cron_recharge where time >= $monthTime and time < $nextmonthTime order by time desc";
        $rows = $this->db_game->getAll($sql);

        $totalfdee = 0;
        $totalusers = 0;
        $totaltimes = 0;
        if (count($rows) > 0) {
            foreach ($rows as $val) {
                $totalfdee += $val['total_fee'];
                $totalusers += $val['charge_users'];
                $totaltimes += $val['charge_times'];
            }
        }
        $todayData = $this->getTodayCalc();
        $this->assign('calctodaydata', $todayData);
        $this->assign('calctotalfee', $totalfdee);
        $this->assign('calctotalusers', $totalusers);
        $this->assign('calctotaltimes', $totaltimes);
        $this->assign('chargecalc', $rows);

    }

    /*
     * 获取今天到当前的充值统计
     */
    public function getTodayCalc()
    {

        $todayStr = date("Y-m-d", time());
        $st = strtotime($todayStr);
        $et = time();
        $sql = "select re.* ,pl.reg_time  from recharge re left join player_login pl on pl.pkey = re.app_role_id where re.time >= $st and re.time < $et";
        $rows = $this->db_game->getAll($sql);
        $totalFee = $totalGold = 0;
        $user_old = array();
        $user = array();
        foreach ($rows as $d) {
            $user[$d['app_role_id']][] = 1;
            $totalFee += $d['total_fee'];
            $totalGold += $d['total_gold'];
            if($d['reg_time'] < $st){
                $user_old[$d['app_role_id']][] = 1;
            }
        }
        $userNum = count($user);
        $oldUserNum = count($user_old);
        $newUserNum = $userNum - $oldUserNum;
        $todayTimes = count($rows);
        $daily = $this->db_game->getRow("select login_num,reg_num from cron_daily where time = $st");
        $chargeNewRate = $chargeActRate = 0;
        if(isset($daily['login_num'])){
            $oldUserTotal = $daily['login_num'] - $daily['reg_num'];
            $newUserTotal = $daily['reg_num'];
            $chargeNewRate = $newUserTotal > 0 ? round($newUserNum / $newUserTotal,4) : 0;
            $chargeActRate = $oldUserTotal > 0 ? round($oldUserNum / $oldUserTotal,4) : 0 ;
        }
        return array(
            'total_fee' => $totalFee,
            'total_gold' => $totalGold,
            'users' => $userNum,
            'times' => $todayTimes,
            'chargeNewUsers'=>$newUserNum,
            'chargeNewRate'=>$chargeNewRate,
            'chargeActUsers'=>$oldUserNum,
            'chargeActRate'=>$chargeActRate
        );

    }

    /*
     * 单服总计算
     *
     */
    public function getTotalCharge()
    {
        $key = "gettotalcharge";
        $data = $this->cache->get($key);

        if ($data == false) {
            $sql = "select sum(total_fee) as total_fee,sum(charge_times) as total_times,sum(charge_users) as total_users from cron_recharge";
            $data = $this->db_game->getRow($sql);
            $this->cache->set($key, $data, 300);
        }

        $this->assign('totalfee', $data['total_fee']);
        $this->assign('totaltimes', $data['total_times']);
        $this->assign('totalusers', $data['total_users']);
    }



}