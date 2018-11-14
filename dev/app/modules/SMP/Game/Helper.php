<?php
/**
 * User: jecelyin 
 * Date: 12-2-20
 * Time: 下午7:26
 *
 */

class SMP_Game_Helper
{
    private $db;
    private $cache;
    public static $key = 'SMP_SERVER_LIST';
    public static $mix_level = array(0 => '无', 1 => '主混服', 2 => '被混服');
    private static $cache_platforms = array();
    private static $cache_groups = array();
    private static $cache_custom_groups = array();

    public function __construct()
    {
        $this->db = DB::getInstance();
        $this->cache = Cache::getInstance();
    }

    public static function clearCache()
    {
        Cache::getInstance()->delete(self::$key);
    }

    /**
     * @return array
     * 获取自定义分组列表
     */
    public function getCustomGroups($all = false)
    {
        $custom_groups = self::$cache_custom_groups;
        ksort($custom_groups);
        return $custom_groups;
    }

    /**
     * @return array
     * 获取平台列表
     */
    public function getPlatformLists()
    {
        if (!self::$cache_platforms) {
            $data = $this->db->getAll("select gp_id ,name from game_platforms order by gp_id asc");

            self::$cache_platforms = array();
            foreach ($data as $v) {
                self::$cache_platforms[$v['gp_id']] = $v;
            }
        }
        $platformlist = self::$cache_platforms;
        ksort($platformlist);
        return $platformlist;
    }

    /**
     * @return array
     * 获取渠道列表
     */
    public function getGroupList()
    {
        if(!self::$cache_groups){
            $data = $this->db->getAll("select gg_id ,name from game_groups order by gg_id asc");
            foreach($data as $v){
                self::$cache_groups[$v['gg_id']] = $v;
            }
        }
        return self::$cache_groups;
    }

    //获取活动自定义分组
    public function getActPlatformLists($all = false)
    {
        $customGroups = $this->getCustomGroups($all);
        return $customGroups;
    }


    /**
     * 验证传递的gs_id是否有权限访问
     * @param int $gs_id
     * @return bool
     * @throws JecException
     */
    public function validServer($gs_id)
    {
        return count($this->filter(array($gs_id))) > 0;
    }

    /**
     * @param $array
     * @param int $status
     * @return $array
     */
    private function filter($servers)
    {
        $validServers = array();
        $customGroups = $this->getCustomGroups();
        foreach($servers as $sid){
            foreach($customGroups as $group){
                if($group['st'] <= $sid && $sid <= $group['et'])
                    $validServers[] = $sid;
            }
        }
        return $validServers;
    }

    /**
     * 获取服务器列表
     * @return array
     */
    public function getServersURL()
    {
        global $CONFIG;
        if($CONFIG['dev'])
            return array('1001'=>'http://127.0.0.1:8013/','2001'=>'http://127.0.0.1:8023/');
//        $cache = $this->cache->get(self::$key);
//        if($cache)
//            return $cache;
        $serverData = Net::fetch($CONFIG['center']['api']."/server_list.php?type=2");
        $servers = (array)json_decode($serverData);
        //d($servers);
//        $this->cache->set(self::$key,$servers,3600);
        return $servers;
    }

    /**
     * 获取单服服务器url 用于api
     * @param $gsid
     * @return array
     */
    public static function getServerUrl($gsid)
    {
        if($gsid > 0){
            $server = DB::getInstance()->getRow("select gs.gs_id ,s.url from game_servers gs left join servers s on gs.sid = s.sid where gs.gs_id = {$gsid}");
            return $server;
        }else
            return array();

    }

    /**
     * @param $app_role_id 玩家key
     * @param $server_id  服务器号
     * @param $channel_id 渠道号
     * @param $total_fee 充值金额（分）
     * @param $user_id 账号
     * @param $jh_order_id 订单号
     * @param $pay_result 支付结果
     * @param $product_id 产品号
     * @param $cp_order_id 客户端生成订单
     * @return int|string
     */
    public static function local_pay($app_role_id,$server_id,$channel_id,$total_fee,$user_id,$jh_order_id,$pay_result,$product_id,$cp_order_id)
    {
        $key = "CL128SAFHALFKSOWJERLWJLET";
        if(isset($app_role_id) && isset($server_id) && isset($channel_id) && isset($total_fee) && isset($user_id) && isset($jh_order_id)  && isset($pay_result)){
            $sign = md5($key.$app_role_id);
            $param = array(
                'app_role_id' => $app_role_id,
                'server_id' =>$server_id,
                'channel_id' =>$channel_id,
                'total_fee' =>$total_fee,
                'user_id' =>$user_id,
                'jh_order_id' =>$jh_order_id,
                'pay_result' =>$pay_result,
                'product_id' =>$product_id,
                'sign' => $sign,
                'app_order_id' => $cp_order_id
            );
            $query = http_build_query($param);
            $server = SMP_Game_Helper::getServerUrl($server_id);
            if(isset($server['url'])){
                $url = $server['url'].'/api/pay.php?&'.$query;
                _log($url,'charge.log');
                $ret = file_get_contents($url);
            }else{
                $ret = -1;
            }
            if($ret == "1")
                self::log_pay($app_role_id,$server_id,$channel_id,$total_fee,$user_id,$jh_order_id,$pay_result,$product_id,$cp_order_id,time(),"");
            return $ret;
        }else
            return -2;

    }

    /**
     * @param $app_role_id
     * @param $server_id
     * @param $channel_id
     * @param $total_fee
     * @param $user_id
     * @param $jh_order_id
     * @param $pay_result
     * @param $product_id
     * @param $cp_order_id
     * @param $time 充值时间
     * @extra $extra 额外记录信息
     */
    public static function log_pay($app_role_id,$server_id,$channel_id,$total_fee,$user_id,$jh_order_id,$pay_result,$product_id,$cp_order_id,$time,$extra)
    {
        if(isset($app_role_id) && isset($server_id) && isset($channel_id) && isset($total_fee) && isset($user_id) && isset($jh_order_id)  && isset($pay_result))
        {
            $db = DB::getInstance();
            $exists = $db->getRow("select id from cc_recharge where jh_order_id = '$jh_order_id'");
            if(!$exists['id']){
                $data = array(
                    'jh_order_id' => $jh_order_id,
                    'app_order_id' => $cp_order_id,
                    'app_role_id' => $app_role_id,
                    'user_id' => $user_id,
                    'channel_id' => $channel_id,
                    'server_id' => $server_id,
                    'time' => $time,
                    'total_fee' => $total_fee,
                    'pay_result' => $pay_result,
                    'product_id' => $product_id,
                    'extra' => $extra
                );
                $db->insert('cc_recharge',$data);
            }
        }
    }

    /**
     * @param $appkey
     * @return string
     */
    public static function get_app_secret($appkey)
    {
        switch($appkey){
            case 100000045 : return "24bcf52f69d45d8db29ef2a3630637e2";
            case 100000004 : return "01be675cad6b36dd8a00a98579d58528";
            case 100000100 : return "981eed2cecce5b0ea27c54813f63c158";
            case 100000101 : return "6dd383b32fa81567e6a6a42bc076661f";
            case 100000006 : return "97d99c7858225a8922ca21d662563895";
            case 100000114 : return "ec98db12b5e5c75df9b4525ca4ed1802";
            case 100000007 : return "31985ab23b5a7b362c3c81de76ae9650";
            case 100000008 : return "b6eeef94c80ab0322be85671f1254552";
            case 100000009 : return "4906c82bda19a95223f2d786be6d89a1";
            case 100000014 : return "3db14a2fc6ba4e8dde9b8f81de8ddff4";
            case 100000012 : return "e9c813ffa9bd2c39027c7606bdb7b6f5";
            default : return "000000";
        }
    }

}