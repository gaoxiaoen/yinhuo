<?php

/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-9-18
 * Time: 18:09
 */
class SMP_Player_Cbp extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '近三天活跃玩家');
        $act = Jec::getVar('act');

        $this->show();
    }


    private function show()
    {

        $pager = new Pager();

        $time = strtotime(date('Ymd')) - 86400;
        $all = $this->db_game->getAll("select ps.pkey,ps.nickname,ps.vip_lv,ps.lv,ps.combat_power,pl.last_login_time from player_state as ps left join player_login as pl on pl.pkey=ps.pkey where pl.last_login_time > $time and ps.lv > 50 order by  ps.combat_power desc ");
        $pager->setTotalRows(count($all));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();


        $data = $this->db_game->getAll("select ps.pkey,ps.nickname,ps.vip_lv,ps.lv,ps.combat_power,pl.last_login_time from player_state as ps left join player_login as pl on pl.pkey=ps.pkey where pl.last_login_time > $time and ps.lv > 50 order by  ps.combat_power desc limit $offset,$limit");
        if (Jec::getVar('download')) $this->csv_download($all);
        $this->assign('data', $data);

        $count_total = 0;
        $cbp_total = 0;
        $lv_total = 0;

        foreach ($all as $val) {
            $count_total = $count_total + 1;
            $lv_total = $lv_total + $val['lv'];
            $cbp_total = $cbp_total + $val['combat_power'];
        }
        $this->assign('count_total', $count_total);
        $this->assign('cbp_total', $cbp_total);
        $cbp_per = $cbp_total / ($count_total + 1);
        $lv_per = $lv_total / ($count_total + 1);
        $this->assign('cbp_per', $cbp_per);
        $this->assign('lv_per', $lv_per);


        $this->assign('page', $pager->render());
        $this->display();
    }

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data)
    {
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('log_cbp.csv');
    }
}