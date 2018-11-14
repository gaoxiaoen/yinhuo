<?php
/**
 * User: jecelyin
 * Date: 12-3-9
 * Time: 下午2:08
 * 一些公共接口
 */
class Helper
{


    public static function getAuthUrl($usr)
    {
        $ts=time();
        if($usr == 'CRON')
        {
            $pwd=$usr.$ts;
        }else{
            $pwd=$_SERVER['HTTP_USER_AGENT'].$ts;
        }

        $auth=md5($usr.$usr.$pwd.$ts);
        return "usr={$usr}&ts={$ts}&auth={$auth}";
    }

    /**
     * @param action:操作 ban表示执行禁止操作，unban表示执行解除禁止操作
     * @param params:包含玩家pkey、禁止类型type 和 禁止小时hour 参数
     */
    public static function setPlayerBanStatusCache($action,$params) {
        if(!$action) return;
        $cache = new Cache_File('player_ban_status_cache');
        $key = 'player_ban_status_cache_key';
        $g_expire_time = TIME + 360*86400;
        $data = $cache->get($key);
        if($action == 'ban') {
            if(!$params['pkey'] || !$params['type'] || !isset($params['hour'])) return;
            $expire_time = $params['hour'] ? TIME + $params['hour'] * 3600 : 0;
            $data[$params['pkey']][$params['type']] = $expire_time;
        }else{
            if(!$params['pkey'] || !$params['type']) return;
            unset($data[$params['pkey']][$params['type']]);
        }
        $cache->set($key,$data,$g_expire_time);

    }

    /**
     * @param action getall表示获取缓存中有禁止状态的所有玩家禁止信息,getmany表示获取指定玩家禁止状态信息(0:正常,1:普通禁言,2:特殊禁言)
     * @param 指定玩家的pkey,格式为 : [1,2,3,4,5,],注意单个玩家也需要以此数组格式作为参数。
     * @notice 禁止状态优先级：1 普通禁言为1级，2 特殊禁言为2级。
     */
    public static function getPlayerBanStatusCache($action,$pkeys='') {
        $cache = new Cache_File('player_ban_status_cache');
        $key = 'player_ban_status_cache_key';
        $res = $cache->get($key);
        if(!empty($res)) {
            switch ($action) {
                case 'getall':
                    foreach($res as $pkey=>$item) {
                        if(!empty($item)) {
                            if(isset($item[1]) && $item[1] >= time()) {
                                $result[$pkey] = ['type'=>1,'expire_time'=>date('Y-m-d H:i:s',$item[1])];
                                continue;
                            }
                            if(isset($item[2]) && $item[2] >= time()) {
                                $result[$pkey] = ['type'=>2,'expire_time'=>date('Y-m-d H:i:s',$item[2])];
                                continue;
                            }
                        }else{
                            continue;
                        }
                    }
                    break;
                
                case 'getmany':
                    if(!is_array($pkeys) || empty($pkeys)) return;
                    foreach($pkeys as $k) {
                        if(isset($res[$k]) && !empty($res[$k])) {
                            $item = $res[$k];
                            if(isset($item[1]) && $item[1] >= time()) {
                                $result[$k] = ['type'=>1,'expire_time'=>date('Y-m-d H:i:s',$item[1])];
                                continue;
                            }
                            if(isset($item[2]) && $item[2] >= time()) {
                                $result[$k] = ['type'=>2,'expire_time'=>date('Y-m-d H:i:s',$item[2])];
                                continue;
                            }
                            $result[$k] = ['type'=>0];
                        }else{
                            $result[$k] = ['type'=>0];
                        }
                    }
                    break;
            }
        }else{
            $result = [];
        }
        return $result;
    }

    public static function csv_download($data,$filename) {
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download($filename);
    }

    /**
     *  简单的发送中央服请求token的添加
     *  @param token_key 加密时计算用到的key， 单服和中央服需一致，有修改时需要同步好
     *  @param data 数组格式
     *  @return []
     */
    public static function send_data_simple_format ($data, $token_key = 'center_game_server_token_key') {

        $time = time();

        $sign = md5( md5( $time . $token_key ) . $time );

        $tmp_data = [
            'data'  =>  json_encode($data),
            'time'  =>  $time,
            'sign'  =>  $sign
        ];

        return $tmp_data;
    }

    /**
     *  简单的接收中央服请求数据token的验证
     *  @param token_key 加密时计算用到的key， 单服和中央服需一致，有修改时需要同步好
     *  @param data json格式发送过来的请求数据，包含 通讯数据 + sign签名 + time
     *  @param $is_json 1 json | 0 array
     *  @return array['result','msg'];
     */
    public static function recv_data_simple_validate ($data, $token_key = 'center_game_server_token_key') {

        if (!is_array($data)) return ['result'=>'error','msg'=>'Wrong Data Format, Need To Be Array Or Json.'];

        #   缺少签名的情况
        if(!$data['sign']) return ['result'=>'error','msg'=>'Param Sign Is Must.'];

        #   时间戳,最好是请求一方提供，防止服务器之间时间不一致导致的验证错误
        $now = time();

        $time = $data['time'] ? $data['time'] : $now;

        #   超过30s则签名过期，防止签名被重复使用
        if(abs($time - $now) > 30) return ['result'=>'error','msg'=>'Bad Sign.'];

        #   签名加密方式
        $mysign = md5( md5( $time . $token_key ) . $time );

        if ($mysign == $data['sign'])

            return ['result'=>'success','data'=>g(json_decode($data['data'],true))];

        else

            return ['result'=>'error','msg'=>'The Signature Verification Error. '];
    }
}
