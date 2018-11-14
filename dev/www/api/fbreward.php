<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-8-17
 * Time: 20:25
 */


require '../../Jec/booter.php';

class FB_REWARD
{
    public $db_game = null;

    public function __construct()
    {
        header("Content-Type:text/html; charset=utf-8");
        $this->db_game = DB::getInstance('db_game');
    }

    public function Reward()
    {
        $user_id = (int)$_GET['user_id'];
        $activity_id = $_GET['activity_id'];
        $goods = $_GET['goods'];

        $role_exists = $this->_getrole($user_id);
        if (empty($role_exists))
            exit("0");

        $param = array(
            'pkey' => $user_id,
            "act_id" => $activity_id,
            "goods" => $goods,
        );
        Net::rpc_game_server(gm, fb_act, $param);
        exit("1");
    }


    private function _getrole($pkey)
    {
        return $this->db_game->getRow("select pkey  from player_state where pkey = {$pkey}");
    }
}

$fb_act = new FB_REWARD();
$fb_act->Reward();