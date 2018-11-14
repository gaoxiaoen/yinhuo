<?php
/**
 * Created by PhpStorm.
 * User: fancy
 * Date: 15/3/26
 * Time: 上午10:56
 */

require '../../Jec/booter.php';
class PAY
{
    public $db_game = null;
    public $key = "CL128SAFHALFKSOWJERLWJLET";

    public function __construct()
    {
        header("Content-Type:text/html; charset=utf-8");
        $this->db_game = DB::getInstance('db_game');
    }

    public function Pay()
    {

        $channel_id = $_GET['channel_id'];
        $game_channel_id = $_GET['game_channel_id'];
        $total_fee = $_GET['total_fee'];
        $original_total_fee = $_GET['original_total_fee'];
        $currency_code = $_GET['currency_code'];
        $user_id = $_GET['user_id'];
        $jh_order_id = $_GET['jh_order_id'];
        $app_role_id = $_GET['app_role_id'];
        $server_id = $_GET['server_id'];
        $pay_result = $_GET['pay_result'];
        $product_id = (int)$_GET['product_id'];
        $sign = $_GET['sign'];
        $app_order_id = $_GET['app_order_id'];
        $mysign = md5($this->key.$app_role_id);
        if($sign != $mysign)
            exit("-1");

        $order_exists = $this->_getorder($jh_order_id);
        if(!empty($order_exists)){
            Net::rpc_game_server(charge,notice,array("pid"=>$app_role_id));
            exit("1");
        }

        $role_exists = $this->_getrole($app_role_id);
        if(empty($role_exists))
            exit("0");

        $time = time();
        $charge_data = array(
            'jh_order_id' => $jh_order_id,
            'app_order_id' => $app_order_id,
            'app_role_id' => $app_role_id,
            'user_id' => $user_id,
            'channel_id' => $channel_id,
            'game_channel_id' => $game_channel_id,
            'server_id' => $server_id,
            'time' => $time,
            'total_fee' => $total_fee,
            'original_total_fee' => $original_total_fee,
            'currency_code' => $currency_code,
            'product_id' => $product_id,
            'total_gold' => 0,
            'pay_result' => $pay_result,
            'lv' => $role_exists['lv'],
            'career' => $role_exists['career'],
            'nickname' => $role_exists['nickname'],
            'state' => 1
        );
        $this->db_game->insert("recharge",$charge_data);
        Net::rpc_game_server(charge,notice,array("pid"=>$app_role_id));
        exit("1");
    }

    private function _getorder($jhorderid){
        return $this->db_game->getRow("select id from recharge where jh_order_id = '{$jhorderid}' ");
    }

    private function _getrole($pkey){
        return $this->db_game->getRow("select pkey , sn , pf ,nickname ,lv ,career from player_state where pkey = {$pkey}");
    }
}
$pay = new PAY();
$pay->Pay();