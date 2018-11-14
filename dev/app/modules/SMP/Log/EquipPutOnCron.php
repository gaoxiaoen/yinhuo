<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-12
 * Time: 13:45
 */

class SMP_Log_EquipPutOnCron extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '装备穿戴日志');

        $this->show();
    }

    private function show(){
        ini_set('memory_limit', '512M');
        $where = " 1=1 ";
        $params['kw_pkey'] = Jec::getVar('kw_pkey');
        if($params['kw_pkey']) $where .= " and lps.pkey = '".$params['kw_pkey']."' ";
        $params['kw_name'] = Jec::getVar('kw_name');
        if($params['kw_name']) $where .= " and lps.pname = '".$params['kw_name']."' ";
        $time = ' and '.$this->getWhereTime('time','0 day',true);
        $is_download = Jec::getVar("download");
        $player_state_data = $this->db_game->getAll("select date_format(from_unixtime(lps.time),'%Y%m%d') as date,lps.pkey,lps.pname,lps.sn,lps.pf,lps.reg_time,lps.lv,lps.cbp,lps.vip_lv,pr.total_fee from log_player_state lps left join player_recharge pr on lps.pkey=pr.pkey where $where $time order by lps.time desc");
        $show_data = [];
        $st = strtotime(explode(' ',$this->getStartTime())[0]);
        $et = strtotime(explode(' ',$this->getEndTime())[0]);
        $et = $et > strtotime(date('Ymd',time())) ?  strtotime(date('Ymd',time())) : $et;
        $player_state_limit = $this->db_game->getAll("select * from (select date_format(from_unixtime(lps.time),'%Y%m%d') as date,lps.pkey,lps.pname,lps.sn,lps.pf,lps.reg_time,lps.lv,lps.cbp,lps.vip_lv,pr.total_fee from log_player_state lps left join player_recharge pr on lps.pkey=pr.pkey where $where and time < $st order by lps.time desc) t group by pkey");
        $player_state_data = array_merge($player_state_data,$player_state_limit);
        unset($player_state_limit);
        if ($player_state_data) {
                $equip_data = [];
                $equip_db = $this->db_game->getAll("select pkey,subtype,class,star,color,date_format(from_unixtime(time),'%Y%m%d') as date from (select pkey,subtype,class,star,color,time from log_equip_puton lps where $where $time order by time desc) t  group by concat(pkey,date,subtype)");
                $equip_db_limit = $this->db_game->getAll("select pkey,subtype,class,star,color,date_format(from_unixtime(time),'%Y%m%d') as date from (select pkey,subtype,class,star,color,time from log_equip_puton lps where $where and time < $st order by time desc) t  group by concat(pkey,subtype)");
                $equip_db = array_merge($equip_db,$equip_db_limit);
                unset($equip_db_limit);
                foreach ($equip_db as $item)
                {
                    $equip_data[$item['pkey']][$item['date']][$item['subtype']] = ['class'=>$item['class'],'star'=>$item['star'],'color'=>$item['color']];
                }
                unset($equip_db);
                foreach ($player_state_data as &$psd)
                {
                    $pkeys[$psd['pkey']] = strtotime(date('Ymd',$psd['reg_time']));
                    $tmp = $psd;
                    $tmp['total_fee'] = $psd['total_fee'] > 0 ? $psd['total_fee'] / 100 : 0;
                    $tmp['reg_time']  = date('Ymd',$psd['reg_time']);
                    for($i=1;$i<=10;$i++)
                    {
                        $subtype_data = ['class'=>0,'star'=>0,'color'=>0];
                        if($equip_data[$psd['pkey']])
                        {
                            if($equip_data[$psd['pkey']][$psd['date']][$i])
                            {
                                $subtype_data = $equip_data[$psd['pkey']][$psd['date']][$i];
                            }else{
                                $roles_arr = $equip_data[$psd['pkey']];
                                end($roles_arr);    //equip_data数组中的数据是按日期由小到大排序的,所以先把指针指向最后一个,确保重大到小进行获取合适的装备日志数据
                                while (!isset(current($roles_arr)[$i]) || key($roles_arr) > $psd['date']) 
                                {
                                    if (!prev($roles_arr)) break;
                                }
                                $sdata = current($roles_arr)[$i];
                                if($sdata)
                                {
                                    $subtype_data = ['class'=>$sdata['class'],'star'=>$sdata['star'],'color'=>$sdata['color']];
                                }
                            }
                        }
                        $tmp['class'.$i] = $subtype_data['class'];
                        $tmp['star'.$i]  = $subtype_data['star'];
                        $tmp['color'.$i] = $subtype_data['color'];
                    }
                    $player_state[$psd['pkey']][$psd['date']] = $tmp;
                }
                unset($equip_data,$player_state_data);
                //按搜索日期 对log_player_state表中记录的玩家进行信息汇总
                for ($et; $et >= $st; $et-=86400)
                { 
                   foreach ($pkeys as $pkey=>$regtime)
                   {
                       if($regtime <= $et)
                       {
                           $date = date('Ymd',$et);
                           $role_row = $player_state[$pkey];
                            if($role_row[$date]){
                                $show_data[] = $player_state[$pkey][$date];
                           }else{
                                $role_row = $player_state[$pkey];
                                if(!$role_row) continue;
                                while (key($role_row) > $date) 
                                {
                                    if(!next($role_row)) break;
                                }
                                $sdata = current($role_row);
                                if($sdata)
                                {
                                    $sdata['date'] = $date;
                                    $show_data[] = $sdata;
                                }
                           }
                           
                       }
                   }
                }
                unset($player_state);
        }
        if($is_download) $this->csv_download($show_data);
        $page = new Pager();
        $offset = $page->getOffset();
        $limit = $page->getLimit();
        $page->setTotalRows(count($show_data));
        $page_data = $show_data ? array_slice($show_data,$offset,$limit) : [];
        unset($show_data);
        $this->assign('data',$page_data);
        $this->assign('req_params',$params);
        $this->assign('page', $page->render());
        $this->display();
    }

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data)
    {
        array_unshift($data,['日期','PKEY','玩家名','服区','注册渠道','注册时间','等级','战力','当前VIP等级','当日为止的累计充值','装备1_阶级','装备1_星级','装备1_颜色','装备2_阶级','装备2_星级','装备2_颜色','装备3_阶级','装备3_星级','装备3_颜色','装备4_阶级','装备4_星级','装备4_颜色','装备5_阶级','装备5_星级','装备5_颜色','装备6_阶级','装备6_星级','装备6_颜色','装备7_阶级','装备7_星级','装备7_颜色','装备8_阶级','装备8_星级','装备8_颜色','装备9_阶级','装备9_星级','装备9_颜色','装备10_阶级','装备10_星级','装备10_颜色']);
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('log_equip_puton_cron.csv');
        unset($data);
    }


}

