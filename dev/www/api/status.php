<?php
/**
 * Created by PhpStorm.
 * User: fancy
 * Date: 15/3/26
 * Time: 上午10:56
 */

require '../../Jec/booter.php';
header('Access-Control-Allow-Origin:*');

function monitorChat(){
    $db = DB::getInstance('db_game');
    $online = (int) $db->getOne("select num from online order by time desc limit 1");
    $time = time() - 10;
    $sql = "select * from log_chat where time >= $time";
    $data = $db->getAll($sql);
    return json_encode(array('online'=>$online,'chat'=>$data));
}

function serverStatus(){
    $ret = Net::rpc_game_server('gm', 'update_notice', array());
    return $ret;
}

function kfStatus(){
    $ret = Net::rpc_game_server('gm', 'get_kf_state', array());
    return $ret;
}

function sp_unbanchat()
{
    $pkey = Jec::getVar('pkey');
    Helper::setPlayerBanStatusCache('unban',['pkey'=>$pkey,'type'=>'2']);
    return Net::rpc_game_server(gm, lim_chat_sp, array('pkey' => $pkey, 'chat_state' => 0));
}


function unbanaccount()
{
    $pkey = Jec::getVar('pkey');
    $db = DB::getInstance('db_game');
    $res = $db->update('player_login', array('status' => 0), array('pkey' => $pkey));

    return $res;

}


function banChat(){
    $pkey = Jec::getVar('pkey');
    $ret = Net::rpc_game_server('gm','lim_chat',array('pkey'=>$pkey,'hour'=>1));
    Helper::setPlayerBanStatusCache('ban',['pkey'=>$pkey,'type'=>'1','hour'=>1]);
    return $ret;
}

function spbanChat(){
    $pkey = Jec::getVar('pkey');
    $time = Jec::getVar('time');
    $ret = Net::rpc_game_server('gm','lim_chat_sp',array('pkey'=>$pkey,'chat_state'=>$time));
    switch ($time) {
        case '1':
            $hour = 24;
            break;
         case '2':
            $hour = 48;
            break;
         case '3':
            $hour = 20*360*24; //以2018年计算2038年的时间长度
            break;
    }
    Helper::setPlayerBanStatusCache('ban',['pkey'=>$pkey,'type'=>'2','hour'=>$hour]);
    return $ret;
}


function activityState(){
    $stime = Jec::getVar('stime');
    $etime = Jec::getVar('etime');
    $ret = Net::rpc_game_server('gm','get_work_list_by_time_all',array('stime'=>$stime,'etime'=>$etime));
    return $ret;
}
function banAccount(){
    $pkey = Jec::getVar('pkey');
    $db = DB::getInstance('db_game');
    $res = $db->update('player_login', array('status' => 1), array('pkey' => $pkey));
    $ret = Net::rpc_game_server(gm, kick_off, array('pkey' => $pkey));
    return $ret;
}

function getIP(){
    $pkey = Jec::getInt('pkey');
    if(!$pkey) return json_encode(array('status'=>0,'msg'=>'参数不全...'));
    $db = DB::getInstance('db_game');
    $ip = $db->getOne("select last_login_ip from player_login where pkey = ".$pkey);
    return json_encode(array('status'=>1,'msg'=>$ip));
}

function getPlayerChat()
{
    $pkey = Jec::getInt('pkey');
    if(!$pkey) return json_encode(array('status'=>0,'msg'=>'参数不全...'));
    $time = time() - 3600;
    $db = DB::getInstance('db_game');
    $data = $db->getAll("select * from log_chat where time > $time and pkey = ".$pkey);
    $returnStr = '<div style="width:350px;color:white;background-color: #222222;border:1px solid;">';
    foreach($data as $v)
    {
        $type = $v['type'] == '4' ? '[公会]' : ($v['type'] == '3' ? '[私聊]' : '[世界]');
        $nickname = $v['nickname'] == '' ? '空昵称('.$v['pkey'].')' : $v['nickname'];
        $toname   = $v['toname']   == '' ? '空昵称('.$v['pkey'].')' : $v['toname'];
        $returnStr .= '<p>'.$type.'[vip'.$v['vip'].'][lv'.$v['lv'].']'.$nickname;
        if ($v['type'] == '3') {
            $returnStr .= ' ⇨ [vip'.$v['tovip'].'][lv'.$v['tolv'].']'.$toname;
        }
        $returnStr .= ' : '.$v['content'].'</p>';
    }
    $returnStr .= '</div>';
    return json_encode(array('status'=>1,'msg'=>$returnStr));
}

/**
 * 无记录充值接口
 */
function gameRechargeNoRecord () {
    $data = $_POST;
    $validate_res = Helper::recv_data_simple_validate($data,'do_game_recharge_with_record_operation_key');
     if ($validate_res['result'] == 'error')
        return "-3";
    else if($validate_res['result'] == 'success') {
        $params = $validate_res['data'];
        Net::rpc_game_server(charge, gm_recharge, ['pname'=>$params['pname'],'charge_val'=>$params['charge_val']]);
        return "1";
    }
}

/**
 * 更新消费表
 */
function getConsumeType () {
    $data = $_POST;
    $validate_res = Helper::recv_data_simple_validate($data);
     if ($validate_res['result'] == 'error')
        return json_encode(['state'=>'-1','msg'=>'签名有误.']);
    else if($validate_res['result'] == 'success') {
        $params = $validate_res['data'];
        $db = DB::getInstance('db_game');
        $count = $db->getOne("select count(*) from consume_type");
        if ($count > $params['total_num']) {
            $ret_data = [];
            $where = $params['data'] ? ' where id not in ('.$params['data'].') ' : '';
            $ret_data = $db->getAll("select * from consume_type $where");
            return json_encode(['state'=>1,'data'=>$ret_data]);
        }else
            return json_encode(['state'=>0,'msg'=>'Is The Newest Version.']);
    }
}

/**
 * @desc 防沉迷-缓存存储
 * @params opt get / set
 * @params data 
 * @return [] | bool
 */
function _addiction_cache_Opt($opt,$data=[])
{
    $cache = Cache::getInstance();
    $cache_key = "addiction_setting_cache_key";
    $cache_expire_time = TIME + 86400*365*20;
    switch ($opt) 
    {
        case 'get':
            $res = $cache->get($cache_key);
            if ($res == '') 
            {
                global $CONFIG;
                $sid = $CONFIG['game']['sn'];
                $api = $CONFIG['center']['api'];
                unset($CONFIG);
                $url = $api.'/status.php?act=getAddictionSetting';
                $result = json_decode(postData($url,Helper::send_data_simple_format(['sid'=>$sid])),1);
                if($result['state'] === 0)
                {
                    $param = $result['data'];
                    $request = [
                        'is_addiction'              =>  $param['is_addiction'] == ''    ? 0 : $param['is_addiction'],
                        'warn_frequency'            =>  $param['warn_frequency'] == ''  ? '[]' : $param['warn_frequency'],
                        'is_mail'                   =>  $param['is_mail'] == ''         ? 0 : $param['is_mail'],
                        'mail_online_time'          =>  $param['mail_online_time'] == ''? 0 : $param['mail_online_time'],
                        'mail_title'                =>  $param['mail_title'] == ''      ? '' : $param['mail_title'],
                        'mail_content'              =>  $param['mail_content'] == ''    ? '' : $param['mail_content'],
                        'is_lower_income'           =>  $param['is_lower_income'] == '' ? 0 : $param['is_lower_income'],
                        'is_certificate'            =>  $param['is_certificate'] == ''  ? 0 : $param['is_certificate'],
                        'is_charge_uncertificate'   =>  $param['is_charge_uncertificate'] == '' ? 0 : $param['is_charge_uncertificate'],
                        'memo'                      =>  $param['memo'] == '' ? '' : $param['memo']
                    ];
                    $data = json_encode($request);
                    _addiction_cache_Opt('set',$data);
                    $res = $data;
                }else{
                    _log($res,'get_center_addiction.error');
                    $res = [];
                }
            }
            break;
        case 'set':
            $res = $cache->set($cache_key,$data,$cache_expire_time);
            break;
        default:
            $res = $cache->get($cache_key);
            break;
    }
    return $res;
}

/**
 *  @desc 防沉迷-设置数据更新 并通知游戏服更新设置数据
 *  @param time
 *  @param sign
 *  @param data
 *  @return json[state,msg]
 */
function addictionUpdate() 
{
    $data = $_POST;
    $validate_res = Helper::recv_data_simple_validate($data);
    if ($validate_res['result'] == 'error')
    {
        return json_encode(['state'=>'-1','msg'=>'bad signature.']);
    }else{
        $param = $validate_res['data'];
        $request = [
            'is_addiction'              =>  $param['is_addiction'] == ''    ? 0 : $param['is_addiction'],
            'warn_frequency'            =>  $param['warn_frequency'] == ''  ? '[]' : $param['warn_frequency'],
            'is_mail'                   =>  $param['is_mail'] == ''         ? 0 : $param['is_mail'],
            'mail_online_time'          =>  $param['mail_online_time'] == ''? 0 : $param['mail_online_time'],
            'mail_title'                =>  $param['mail_title'] == ''      ? '' : $param['mail_title'],
            'mail_content'              =>  $param['mail_content'] == ''    ? '' : $param['mail_content'],
            'is_lower_income'           =>  $param['is_lower_income'] == '' ? 0 : $param['is_lower_income'],
            'is_certificate'            =>  $param['is_certificate'] == ''  ? 0 : $param['is_certificate'],
            'is_charge_uncertificate'   =>  $param['is_charge_uncertificate'] == '' ? 0 : $param['is_charge_uncertificate'],
            'memo'                      =>  $param['memo'] == '' ? '' : $param['memo']
        ];
        _addiction_cache_Opt('set',json_encode($request));
        Net::rpc_game_server('gm','set_anti_addiction',['is_addiction'=>1]);
        return json_encode(['state'=>1,'msg'=>'Success !']);
    }
}

/**
 * @desc 防沉迷-设置数据获取接口
 * @return array
 */
function getAddictionSetting()
{
    header("Content-Type:text/html; charset=utf-8");
    return _addiction_cache_Opt('get');
}

$ac = Jec::getVar('ac');

switch($ac){
    case 'chat':
        $ret = monitorChat();break;
    case 'banchat':
        $ret = banChat();break;
    case 'spbanchat':
        $ret = spbanChat();break;
    case 'activityState':
        $ret = activityState();break;
    case 'banaccount':
        $ret = banAccount();break;
    case 'getip':
        $ret = getIP();break;
    case 'getplayerchat':
        $ret = getPlayerChat();break;
    case 'gameRechargeNoRecord':
        $ret = gameRechargeNoRecord();break;
    case 'getConsumeType':
        $ret = getConsumeType();break;
    case 'addictionUpdate':
        $ret = addictionUpdate();break;
    case 'getAddictionSetting':
        $ret = getAddictionSetting();break;
    case 'ubanaccount':
        $ret = unbanaccount();break;
    case 'ubanchat':
        $ret = sp_unbanchat();break;
    case 'kf_status':
        $ret = kfStatus();break;
    default :
        $ret = serverStatus();
}
exit($ret);