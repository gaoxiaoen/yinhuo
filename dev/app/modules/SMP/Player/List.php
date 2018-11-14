<?php

/**
 * User: qinglin QQ:474778220
 * Date: 12-8-17
 *
 */
class SMP_Player_List extends AdminController
{
    public $cache = [];
    public $columnKey = '';

    public function __construct()
    {
        $this->cache = Cache::getInstance();
        $this->columnKey = 'RoleListColumnArr_cache';
        parent::__construct();
        $this->assign('title', '角色列表');
        $do = Jec::getVar('do');
        switch ($do) {
            case 'banchat' :
                $this->banchat();
                break;
            case 'sp_banchat' :
                $this->sp_banchat();
                break;
            case 'unbanchat' :
                $this->unbanchat();
                break;
            case 'sp_unbanchat' :
                $this->sp_unbanchat();
                break;
            case 'ban' :
                $this->ban();
                break;
            case 'unban' :
                $this->unban();
                break;
            case 'kickoff':
                $this->kickoff();
                break;
            case 'set_gm':
                $this->set_gm(1);
                break;
            case 'unset_gm':
                $this->set_gm(0);
                break;
             case 'updatecheck':
                $this->update_check();
                break;
        }

        $this->show($do);
    }

    /**
     * 封号
     */
    private function ban()
    {
        $pkeys = Jec::getVar('id');

        if (!$pkeys) {
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg', $msg);
            return;
        }

        //$u = array();
        foreach ($pkeys as $pkey) {
            $this->db_game->update('player_login', array('status' => 1), array('pkey' => $pkey));
            Net::rpc_game_server(gm, kick_off, array('pkey' => $pkey));
        }
        $msg['msg'] = '封号成功！';
        $this->assign('msg', $msg);

    }

    /**
     * 解封
     */
    private function unban()
    {
        $pkeys = Jec::getVar('id');

        if (!$pkeys) {
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg', $msg);
            return;
        }

        //$u = array();
        foreach ($pkeys as $pkey) {
            $this->db_game->update('player_login', array('status' => 0), array('pkey' => $pkey));
        }
        $msg['msg'] = '解封成功！';
        $this->assign('msg', $msg);

    }

    /**
     * 禁言
     */
    private function banchat()
    {
        $pkeys = Jec::getVar('id');

        if (!$pkeys) {
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg', $msg);
            return;
        }
        foreach ($pkeys as $pkey) {
            Helper::setPlayerBanStatusCache('ban',['pkey'=>$pkey,'type'=>'1','hour'=>12]);
            Net::rpc_game_server(gm, lim_chat, array('pkey' => $pkey, 'hour' => 12));
        }
        $msg['msg'] = '禁言成功！';
        $this->assign('msg', $msg);
    }

    private function unbanchat()
    {
        $pkeys = Jec::getVar('id');

        if (!$pkeys) {
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg', $msg);
            return;
        }
        foreach ($pkeys as $pkey) {
            Helper::setPlayerBanStatusCache('unban',['pkey'=>$pkey,'type'=>'1']);
            Net::rpc_game_server(gm, lim_chat, array('pkey' => $pkey, 'hour' => 0));
        }
        $msg['msg'] = '解除禁言成功！';
        $this->assign('msg', $msg);
    }

    /**
     * 禁言
     */
    private function sp_banchat()
    {
        $pkeys = Jec::getVar('id');

        if (!$pkeys) {
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg', $msg);
            return;
        }
        foreach ($pkeys as $pkey) {
            Helper::setPlayerBanStatusCache('ban',['pkey'=>$pkey,'type'=>'2','hour'=>24]);
            Net::rpc_game_server(gm, lim_chat_sp, array('pkey' => $pkey, 'chat_state' => 1));
        }
        $msg['msg'] = '禁言成功！';
        $this->assign('msg', $msg);
    }

    private function sp_unbanchat()
    {
        $pkeys = Jec::getVar('id');

        if (!$pkeys) {
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg', $msg);
            return;
        }
        foreach ($pkeys as $pkey) {
            Helper::setPlayerBanStatusCache('unban',['pkey'=>$pkey,'type'=>'2']);
            Net::rpc_game_server(gm, lim_chat_sp, array('pkey' => $pkey, 'chat_state' => 0));
        }
        $msg['msg'] = '解除禁言成功！';
        $this->assign('msg', $msg);
    }

    /*
 * 函数lot_kick, 实现踢除玩家操作
 */
    private function kickoff()
    {
        //需要用接口与relang相接
        $pkeys = Jec::getVar('id');

        if (!$pkeys) {
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg', $msg);
            return;
        }
        foreach ($pkeys as $pkey) {
            Net::rpc_game_server(gm, kick_off, array('pkey' => $pkey));
        }
        $msg['msg'] = '剔除成功！';
        $this->assign('msg', $msg);
    }

    /*
     * 设置新手指导
     */
    private function set_gm($gm)
    {
        $pkeys = Jec::getVar('id');

        if (!$pkeys) {
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg', $msg);
            return;
        }
        foreach ($pkeys as $pkey) {
            $res = Net::rpc_game_server(gm, set_gm, array('pkey' => $pkey, 'gm' => $gm));
        }
        if ($res == 2) {
            $msg['msg'] = '该服的新手指导员人数已达上限!(最多5人)';
        } else {
            if ($gm == 1) {
                $msg['msg'] = '设置新手指导员成功！';
            } else {
                $msg['msg'] = '解除新手指导员成功！';
            }
        }
        $this->assign('msg', $msg);
    }

    private function show($do)
    {
        $kw = Jec::getVar('kw');
        $kw['online_ts'] = $kw['online_ts'] ? strtotime($kw['online_ts']) : strtotime(date('Y-m-d',time()));
        $sort_type = ' desc';
        $sort_column = 'pl.reg_time';
        $where = '1';
        if ($kw['stime'] == 1) {
            $where .= ' and '.$this->getWhereTime('reg_time', '0 day',true,'all',365);
        }
        if ($kw['stime'] == 2) {
            $where .= $this->getWhereTime('last_login_time', '0 day',true,'all',365);
        }
        if (!empty($kw['pf'])) {
            $where .= " and pl.pf = " . $kw['pf'];
        }
        if (!empty($kw['id'])) {
            $where .= " and pl.pkey = {$kw['id']}";
        }
        if (!empty($kw['name'])) {
            $where .= " and ps.nickname like '%" . $kw['name'] . "%'";
        }
        if (!empty($kw['account'])) {
            $where .= " and pl.accname like '%" . $kw['account'] . "%'";
        }
        if($kw['order_sort'] && $kw['order_sort'] !=1) {
            $sort_type = ' asc';
        }
        if($kw['order_column'] && $kw['order_column'] !=1) {
            switch ($kw['order_column']) {
               case '2':
                    $sort_column = 'ps.lv';
                    break;
               case '3':
                    $sort_column = 'ps.vip_lv';
                    break;
               case '4':
                    $sort_column = 'ps.gold';
                    break;
               case '5':
                    $sort_column = 'ps.coin';
                    break;
               case '6':
                    $sort_column = 'pl.total_online_time';
                    break;
               case '7':
                    $sort_column = 'pl.last_login_time';
                    break; 
            }
        }
        $order = 'order by '.$sort_column.$sort_type;
        if ($kw['status']) {
            if ($kw['status'] == 2) {
                $now = time();
                $where .= " and pl.silent > $now ";
            } else
                $where .= " and pl.status = " . $kw['status'];
        }
        #组装sql字段
        $columnsArr = [
                0   =>  ['id'=>0,'name'=>'角色名称','column'=>'ps.nickname'],
                1   =>  ['id'=>1,'name'=>'游戏渠道','column'=>'pl.game_channel_id'],
                2   =>  ['id'=>2,'name'=>'是否在线','column'=>'pl.online'],
                3   =>  ['id'=>3,'name'=>'服务器编号','column'=>'ps.sn'],
//              4   =>  ['id'=>4,'name'=>'平台编号','column'=>'ps.pf'],
                5   =>  ['id'=>5,'name'=>'帐号名称','column'=>'pl.accname'],
                6   =>  ['id'=>6,'name'=>'阵营','column'=>'ps.realm'],
//              7   =>  ['id'=>7,'name'=>'性别','column'=>'ps.sex'],
                8   =>  ['id'=>8,'name'=>'职业','column'=>'ps.career'],
                9   =>  ['id'=>9,'name'=>'等级','column'=>'ps.lv'],
                10  =>  ['id'=>10,'name'=>'封禁','column'=>'pl.status'],
                11  =>  ['id'=>11,'name'=>'禁言','column'=>'pl.silent'],
                12  =>  ['id'=>12,'name'=>'gm类型','column'=>'ps.gm'],
                13  =>  ['id'=>13,'name'=>'钻石','column'=>'ps.gold'],
                14  =>  ['id'=>14,'name'=>'金币','column'=>'ps.coin'],
//              15  =>  ['id'=>15,'name'=>'vip等级','column'=>'ps.vip_lv'],
                16  =>  ['id'=>16,'name'=>'总在线时长(小时)','column'=>'pl.total_online_time'],
                17  =>  ['id'=>17,'name'=>'日在线时长'],
        ];
        $isCache = $this->cache->get($this->columnKey);
        if(!$checkColumn = $isCache[$_SESSION['id']])
        {
            $checkColumn = [0,1,2,3,4,5,6,8,9,10,11,12,13,14,15,16,17];
            $this->cache->set($this->columnKey,[$_SESSION['id']=>$checkColumn],360*24*3600);
        }
        foreach($columnsArr as $v)
        {
            if( in_array($v['id'],$checkColumn) )
            {
                $columnInfoArr['id'][] = $v['id'];
                $columnInfoArr['name'][] = $v['name'];
                if(isset($v['column'])) $columnInfoArr['column'][] = $v['column'];
            }
        }
        $sqlParams = implode(',',$columnInfoArr['column']);
        #通过缓存 或者 接口 获取gamechannelInfo信息
        $gcInfo = getGameChannelIdInfo();
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from player_login as pl , player_state as ps where $where and pl.pkey = ps.pkey"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $query1 = "select $sqlParams, FROM_UNIXTIME(pl.reg_time, '%Y-%m-%d %H:%i:%S') as reg_time ,FROM_UNIXTIME(pl.last_login_time, '%Y-%m-%d %H:%i:%S') as last_login_time ,ps.pkey,pl.game_channel_id from player_login as pl left join player_state as ps on pl.pkey = ps.pkey where " . $where;
        $query2 = $query1 . " $order limit $offset,$limit";
        $list = $this->db_game->getAll($query2);
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll($query1));
        $pf = getCenterPlatFormInfo();
        if($pf) {
            foreach ($pf as $v) {
                $pf_gpid[] = $v['gp_id'];
                $pf_name[] = $v['name'];
            }
            $this->assign('pf',[0=>implode(',',$pf_gpid),1=>implode(',',$pf_name)]);
        }
        $player_ban_status = Helper::getPlayerBanStatusCache('getall');
        $this->assign('online_data',$this->getOnlineTime($kw['online_ts']));
        $this->assign('player_ban_status',$player_ban_status);
        $this->assign('columnsArr',$columnsArr);
        $this->assign('columnInfoArr',$columnInfoArr);
        $this->assign('gcInfo',$gcInfo);
        $this->assign('kw', $kw);
        $this->assign('list', $list);
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

    /**
     * 更新字段筛选结果缓存
     */
    private function update_check()
    {
        $columns = Jec::getVar('params');
        if(empty($columns)) exit(json_encode(['status'=>0,'msg'=>'未能成功生成,缺少字段']));
        $cacheColumnNew = [];
        foreach($columns as $v)
        {   
            $cacheColumnNew[] = $v;
        }
        $this->cache->set($this->columnKey,[$_SESSION['id']=>$cacheColumnNew],360*24*3600);
        exit(json_encode(['status'=>1,'msg'=>'成功生成! ']));
                
    }

    /*
     * 函数excel_download, 实现角色数据导出操作
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data)
    {
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('role_list.csv');
    }


}