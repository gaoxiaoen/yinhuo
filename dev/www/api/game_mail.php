<?php

/**
 *  单服邮件接口
 */

require '../../Jec/booter.php';

#   接收信息
class MailReviewApi
{
    private static $token_key = null;

    public function __construct ()
    {
         $this->db_game = DB::getInstance('db_game');
         self::$token_key = "game_mail_verify_reject_token_key";
    }

    #   数据验证
    public function data_verify ($request_params)
    {
        $res = [];

        if (!$request_params) return $res;

        $res = Helper::recv_data_simple_validate($request_params, self::$token_key);

        return $res;
    }

    #   拒绝
    public function do_mail_reject ($id)
    {       
        global $CONFIG;

        $server_id = $CONFIG['game']['sn'];

        unset($CONFIG);

        $msg['msg']="删除成功！";
        //Log::info('SMP_GM_MailReview',"删除审核邮件ID[$sid]");  --在中央服打日志
        $db_res = $this->db_game->delete('mail_adm',array("id"=>$id));
        if($db_res)
            return ['result'=>'success','msg'=>$server_id." : 成功删除 ! '"];
        else{
            $db_id = $this->db_game->getOne("select id from mail_adm where id = $id");
            if (!$db_id)
                return ['result'=>'error','msg'=>$server_id." : Id = '{$id}' 不存在. "];
            else
                return ['result'=>'error','msg'=>$server_id." : 删除失败,请重试  ! "];
        }
    }

    #   通过
    public function do_mail_verify($id)
    {
        global $CONFIG;

        $server_id = $CONFIG['game']['sn'];

        unset($CONFIG);

        $row = $this->db_game->getRow("select * from mail_adm where id = $id and state = 0");

        if(!$row) return ['result'=>'error','msg'=>$server_id." : Id = '{$id}' And State = 0 Is Not Exist."];

        $msg = array();
        $now = time();
        $count = 0;
        $time = $row['time'];
        $goodslist = $row['goodslist'];
        $title = $row['title'];
        $content = $row['content'];
        $type = $row['type'];
        //Log::info('SMP_GM_MailReview',"审核发送邮件[$title][$goodslist]");  -- 在中央服打日志

        #   type=0指定玩家发送
        if ($type == 0)
        {
            $players = $row['players'];
            $roles = explode("\r\n",$players);
            _log("roles:".$roles);
            if(count($roles) > 50) return ['result'=>'error','msg'=>$server_id." : The Number Of Player Is More Then 50 ! "];
            foreach ($roles as $role) {
                _log("role:".$role."int bool:".(int) $role);
                if ((int) $role > 0) 
                {
                        $validate_pkey = $this->_validate_player_key('pkey',$role);
                }else{
                        $validate_pkey = $this->_validate_player_key('nickname',$role);
                }
                if ($validate_pkey) 
                {
                    sleep(1);

                    $this->_insert_mail($type,$validate_pkey,$title,$content,$goodslist,$time);

                    Net::rpc_game_server(gm, update_online_mail, array('pkey' => $validate_pkey));
                    
                    $count += 1;
                } else {
                    $msg['error'] = 1;
                    $msg['msg'] .= "玩家 " . $role . " 不存在 ";
                }

            }
            if ($count > 0) 
            {
                $msg['msg'] .= "发送完毕. ";
            }else
                $msg['msg'] .= "没有合法玩家, 无发送任务.";

            $db_res = $this->db_game->update('mail_adm',array('state'=>2),array("id"=>$row['id']));

            return ['result'=>'success','mail_state_reset'=>'2', 'msg'=>$server_id." : ".$msg['msg']];

        }

        #   type=1:全服发送,type=2:安搜索条件发送;
        if (in_array($type, [1,2])) 
        {
            $queue = $this->_get_send_mail_queue();

            if($queue) return ['result'=>'error','msg'=>$server_id." : 邮件任务正在发送中, 请稍后 ! "];

            $msg['msg'] = $server_id."邮件已进入发送队列.";

            $db_res = $this->db_game->update('mail_adm',array('state'=>1, 'send_time'=>$now),array('id'=>$row['id']));

            Net::rpc_game_server(gm,send_mail,array("id"=>$id));

            return ['result'=>'success','mail_state_reset'=>'1','send_time'=>$now, 'msg'=>$msg['msg']];

        }

        return ['result'=>'error','msg'=>$server_id." : Type = '{$type}' 不存在."];

    }

    /**
     *  插入mail表
     */
    private function _insert_mail ($type,$pkey,$title,$content,$goodslist,$time)
    {
        $mkey = unique_key();
        $overtime = time() + 86400 * 7;
        $sql = "insert into mail set mkey = $mkey,pkey = $pkey ,type = $type,title = '$title',content = '$content',goodslist = '$goodslist',time = $time,overtime = $overtime ";
        $this->db_game->query($sql);
    }

    /**
     * 判断是否存在发送中的邮件
     */
    private function _get_send_mail_queue ()
    {
        $queue = $this->db_game->getRow("select id,time,send_time from mail_adm where state = 1");
        $now = time();
        if($queue['id'] > 0 && $now - $queue['send_time'] > 600) {
            $this->db_game->delete('mail_adm', array('id' => $queue['id']));
            return false;
        }
        return $queue['id'] ? true :false;

    }

  /**
   * 用户pek | nickname 验证
   */
    private function _validate_player_key ($key, $val) 
    {
        return $this->db_game->getOne("select pkey from player_state where $key = '{$val}'");
    }
}

$act = Jec::getVar("act");

if ($act == 'mail_review')
{   
    $res = [];

    $sending_data = $_POST;

    $obj = new MailReviewApi();

    $validate_res = $obj->data_verify($sending_data);

    if ($validate_res['result'] == 'error')

        exit(json_encode($validate_res['msg'],JSON_UNESCAPED_UNICODE));

    else if($validate_res['result'] == 'success') {

        $params = $validate_res['data'];

        switch ($params['action']) {

            case 'do_mail_verify':

                $res = $obj->do_mail_verify($params['id']);

                break;
            
            case 'do_mail_reject':

                $res = $obj->do_mail_reject($params['id']);

                break;
        }

        exit(json_encode($res,JSON_UNESCAPED_UNICODE));
    }
}