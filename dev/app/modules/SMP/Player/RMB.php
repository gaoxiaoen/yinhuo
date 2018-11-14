<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
 
class SMP_Player_RMB extends AdminController
{


    public function __construct()
    {
        parent::__construct();
        $this->assign('title', 'RMB玩家管理');
        $this->cache = Cache::getInstance();
        $this->show();


    }

    private function show()
    {
        global $GCareer;
        $params['online_ts'] = Jec::getVar('online_ts') ? strtotime(Jec::getVar('online_ts')) : strtotime(date('Y-m-d',time()));
        $params['pkey'] = Jec::getVar('kw_pkey');
        $params['nickname'] = Jec::getVar('kw_name');
        $is_time = $this->getWhereTime('time','0 day',true,'all');
        $where = '1 = 1 ';
        if($params['pkey']) $where .= " and app_role_id = '{$params['pkey']}' ";
        if($params['nickname']) $where .= "and r.nickname = '{$params['nickname']}' ";
        if($is_time) $where .= " and $is_time ";
        $pager = new Pager();
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $sql = "select sum(total_fee) as totalcharge ,channel_id as channelid ,p.lv as lv_state ,max(r.lv) as lv ,max(time) as lasttime,min(time) as firsttime ,
                r.career,r.nickname,sex,user_id as userid,count(id) as ctime, app_role_id as pkey,server_id as sn ,channel_id as pf,p.sex from recharge r left join player_state p on r.app_role_id = p.pkey where $where group by app_role_id order by totalcharge desc limit $offset, $limit";
        $total_user = $this->db_game->getOne("select count(*) as num from (select id from recharge r where $where group by app_role_id) as roles");
        $pager->setTotalRows($total_user);
        $data = $this->db_game->getAll($sql);
        if($cacheRmb = $this->cache->get("rmb")){
            $c1num = $cacheRmb['c1num'];
            $c1money = $cacheRmb['c1money'];
//            $c2num = $cacheRmb['c2num'];
//            $c2money = $cacheRmb['c2money'];
//            $c3num = $cacheRmb['c3num'];
//            $c3money = $cacheRmb['c3money'];
//            $c4num = $cacheRmb['c4num'];
//            $c4money = $cacheRmb['c4money'];
            $nocharge = $cacheRmb['nocharge'];
        }else{
            $c1num = $this->db_game->getOne("select count(*) as num from (select id from recharge where career = 1 group by app_role_id) as roles");
            $c1money = $this->db_game->getOne("select sum(total_fee) from recharge where career = 1");
//            $c2num = $this->db_game->getOne("select count(*) as num from (select id from recharge where career = 2 group by app_role_id) as roles");
//            $c2money = $this->db_game->getOne("select sum(total_fee) from recharge where career = 2");
//            $c3num = $this->db_game->getOne("select count(*) as num from (select id from recharge where career = 3 group by app_role_id) as roles");
//            $c3money = $this->db_game->getOne("select sum(total_fee) from recharge where career = 3");
//            $c4num = $this->db_game->getOne("select count(*) as num from (select id from recharge where career = 4 group by app_role_id) as roles");
//            $c4money = $this->db_game->getOne("select sum(total_fee) from recharge where career = 4");
            $nochargetime = strtotime(getStartTimeOfDay(time())) - 86400 * 7;
            $nocharge = $this->db_game->getOne("select count(*) as num from (select id from recharge where time < $nochargetime group by app_role_id) as roles");
            $this->cache->set("rmb",array(
                'c1num'=>$c1num,
                'c1money'=>$c1money,
//                'c2num'=>$c2num,
//                'c2money'=>$c2money,
//                'c3num'=>$c3num,
//                'c3money'=>$c3money,
//                'c4num'=>$c4num,
//                'c4money'=>$c4money,
                'nocharge'=>$nocharge
            ),3600);

        }
        $online_data = $this->getOnlineTime($params['online_ts']);
        $now = time();
        foreach($data as &$user){
            $userinfo = $this->db_game->getRow("select pl.pkey ,ps.gold,ps.coin,pl.reg_time,pl.last_login_time from player_login pl left join player_state ps on pl.pkey = ps.pkey where ps.pkey = {$user['pkey']}");
            //d($user,0);
            $user['totalcharge'] = $user['totalcharge'] / 100;
            $user['pkey'] = $userinfo['pkey'];
            $user['gold'] = $userinfo['gold'];
            $user['coin'] = $userinfo['coin'];
            $user['reg_time'] = date('Y-m-d H:i:s',$userinfo['reg_time']);
            $user['last_login_time'] = date('Y-m-d H:i:s',$userinfo['last_login_time']);
            $user['stopcharge'] = round(($now - $user['lasttime'])/86400);
            $user['nologin'] = round(($now - $userinfo['last_login_time'])/86400);
            $user['firsttime'] = date('Y-m-d H:i:s',$user['firsttime']);
            $user['lasttime'] = date('Y-m-d H:i:s',$user['lasttime']);
            $user['online_time'] = $online_data[$user['pkey']] ? $online_data[$user['pkey']] : 0;

        }
        unset($online_data);
        //d($page_user);
        $this->assign('c1num',$c1num);
        $this->assign('c1money',$c1money / 100);
//        $this->assign('c2num',$c2num);
//        $this->assign('c2money',$c2money / 100);
//        $this->assign('c3num',$c3num);
//        $this->assign('c3money',$c3money / 100);
//        $this->assign('c4num',$c4num);
//        $this->assign('c4money',$c4money / 100);
        $this->assign('nocharge',$nocharge);
        $this->assign('totaluser',$total_user);
        $this->assign('roles',$data);
        $this->assign('career',$GCareer);
        $this->assign('params',$params);
        $this->assign('page', $pager->render());

        $this->display();
    }

    public function getOnlineTime($st)
    {
        $et = $st + 86400;;
        $res = [];
        $data = $this->db_game->getAll("select pkey,data from log_login where time >= $st and time < $et");
        foreach ($data as $item) 
        {
            $item['data'] = str_replace('"', '', $item['data']);
            $tmp = explode('},{', substr($item['data'], 1,-1));
            $online_time = 0;
            foreach ($tmp as $v) 
            {
                $online_str = explode(',', $v);
                $online_time += abs(strtotime($online_str[1]) - strtotime($online_str[0]));
            }
            $hour = floor($online_time / 3600);
            $minute = floor(($online_time - 3600 * $hour)/60);
            $res[$item['pkey']] = $hour.' h.'.$minute.' m';
        }

        return $res;
    }
    

}
function mysort($a,$b){
    if($a['totalcharge'] == $b['totalcharge']) return 0;
    return $a['totalcharge'] > $b['totalcharge'] ? -1 : 1;
}