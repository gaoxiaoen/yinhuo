<?php

/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-12
 * Time: 13:45
 */
class SMP_Log_CW001 extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '程远统计表001');

        $this->show();
    }

    private function show()
    {
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from player_state"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $is_download = Jec::getVar('download');
        $limit = $is_download ? "" : "limit $offset,$limit";
        $st = strtotime(date('Ymd',time()));
        $et = $st + 86400;
        $equip_data = [];
        $equip = $this->db_game->getAll("select pkey,subtype,class,color,star from (select * from log_equip_puton where time < $et order by time desc) t group by concat(pkey,subtype)");
        foreach ($equip as $item) 
        {
            $equip_data[$item['pkey']][$item['subtype']] = '{'.$item['class'].','.$item['color'].','.$item['star'].'}';
        }
        unset($equip);
        $sql = "select 
                ps.pkey,
                pl.total_online_time,
                ps.nickname as pname,
                ps.sn,
                ps.pf,
                pl.reg_time,
                ps.lv,
                ps.combat_power as cbp,
                ps.vip_lv,
                (select sum(total_fee) from recharge where app_role_id = ps.pkey) as total_fee, 
                (select count(*) from log_world_boss_kill where pkey = ps.pkey) as wb,
                (select count(*) from log_world_boss_home_kill where pkey = ps.pkey) as wbh
                from player_state ps 
                left join player_login pl on pl.pkey=ps.pkey 
                group by ps.pkey 
                $limit";
        $data = $this->db_game->getAll($sql);
        foreach ($data as &$d) 
        {
            $d['reg_time'] = $d['reg_time'] ? date('Y-m-d H:i:s',$d['reg_time']) : '';
            $d['total_fee'] = $d['total_fee'] / 100;
            $d['boss_num']  = $d['wb'] + $d['wbh'];
            $d['total_online_time'] = $d['total_online_time'] ? round($d['total_online_time']/ 60,2) : 0;
            for($i=1; $i<11; $i++)
            {
                $eq = $equip_data[$d['pkey']][$i];
                if(isset($eq))
                {
                    $d[$i.'_equip'] = $eq;
                }else{
                    $d[$i.'_equip'] = '{0,0,0}';
                }
            }
        }
        unset($equip_data);
        if($is_download) $this->csv_download($data);
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->display();
    }

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data)
    {
        array_unshift($data,['PKEY','在线时长(分钟)','玩家名','服区','渠道','注册时间','等级','战力','VIP等级','累计充值','世界BOSS','BOSS之家','世界BOSS+BOSS之家','装备1信息','装备2信息','装备3信息','装备4信息','装备5信息','装备6信息','装备7信息','装备8信息','装备9信息','装备10信息']);
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('log_cw001.csv');
    }

}

