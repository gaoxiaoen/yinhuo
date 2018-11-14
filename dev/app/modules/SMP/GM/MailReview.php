<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_GM_MailReview extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '邮件审核');
        $act = Jec::getVar('act');
        $id = Jec::getInt('id');

        if($act == "send"){
            $this->send($id);
        }elseif($act == "del"){
            $this->del($id);
        }
        
        $this->show();
    }
    
    private function show($html = '') {

        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from mail_adm where state = 0 "));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $this->assign('page', $pager->render());

        $data = $this->db_game->getAll("select * from mail_adm where state = 0 order by id desc limit $offset ,$limit");
        foreach($data as &$row){
            $row['goodslist'] = format_goods_list($row['goodslist']);
        }
        $this->assign('data',$data);

        $this->display($html);
    }

    /**
     *  单服邮件审核-拒绝选项
     *  注意：此接口修改需要考虑同步到api中的game_mail对应的do_mail_reject接口
     */
    private function del($sid)
    {
        $signal_res = $this->_send_signal_to_center('delGameMail', ['id'=>$sid]);
        if ($signal_res != '1') $this->_fail_to_send_signal(['act'=>'delGameMail','data'=>['id'=>$sid],'res'=>$signal_res]);
        $res = $this->db_game->delete('mail_adm',array("id"=>$sid));
        if ($res) {
            $msg['msg']="删除成功！";
            Log::info('SMP_GM_MailReview',"删除审核邮件ID[$sid]");
        }else
            $msg = ['msg'=>'删除失败! ','error'=>1];
        $this->assign('msg',$msg);
    }

    /**
     *  单服邮件审核-通过选项
     *  注意：此接口修改需要考虑同步到api中的game_mail对应的do_mail_verify接口
     */
    private function send($id)
    {
        $row = $this->db_game->getRow("select * from mail_adm where id = $id and state = 0");
        if($row['id'] > 0)
        {
            $msg = array();
            $now = time();
            $count = 0;
            $time = $row['time'];
            $goodslist = $row['goodslist'];
            $title = $row['title'];
            $content = $row['content'];
            $type = $row['type'];
            Log::info('SMP_GM_MailReview',"审核发送邮件[$title][$goodslist]");
            #   type=0指定玩家发送
            if($type == 0){
                $players = $row['players'];
                $roles = explode("\r\n",$players);
                _log("roles:".$roles);
                if(count($roles) > 50) new JecException('玩家数量不能大于50!');
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
                        $msg['msg'] .= $role . " 不存在 ";
                    }

                }
                if ($count > 0) {
                    $msg['msg'] .= "发送完毕";
                }else
                    $msg['msg'] .= "没有合法玩家, 无发送任务.";
                $this->db_game->update('mail_adm',array('state'=>2),array("id"=>$row['id']));
                $signal_res = $this->_send_signal_to_center('updateGameMail', ['id'=>$id, 'state'=>2]);
                if ($signal_res != '1') $this->_fail_to_send_signal(['act'=>'updateGameMail','data'=>['id'=>$id,'state'=>2],'res'=>$signal_res]);

            }

            #   type=1:全服发送,type=2:安搜索条件发送;
            if(in_array($type, [1,2])){
                $queue = $this->_get_send_mail_queue();
                if($queue) new JecException('邮件任务正在发送中，请稍后。');
                $signal_res = $this->_send_signal_to_center('updateGameMail', ['id'=>$id, 'state'=>1, 'send_time'=>$now]);
                if ($signal_res != '1') $this->_fail_to_send_signal(['act'=>'updateGameMail','data'=>['id'=>$id,'state'=>1],'res'=>$signal_res]);
                $msg['msg'] = "邮件已进入发送队列.";
                $this->db_game->update('mail_adm',array('state'=>1, 'send_time'=>$now),array('id'=>$row['id']));
                Net::rpc_game_server(gm,send_mail,array("id"=>$id));

            }
            alert($msg['msg'],'href',"?m=SMP_GM_MailReview");
        }

    }

    private function _insert_mail($type,$pkey,$title,$content,$goodslist,$time){
        $mkey = unique_key();
        $overtime = time() + 86400 * 7;
        $sql = "insert into mail set mkey = $mkey,pkey = $pkey ,type = $type,title = '$title',content = '$content',goodslist = '$goodslist',time = $time,overtime = $overtime ";
        $this->db_game->query($sql);
    }

  /**
   * 用户pek | nickname 验证
   */
    private function _validate_player_key ($key, $val) 
    {
        return $this->db_game->getOne("select pkey from player_state where $key = '{$val}'");
    }

    private function _get_send_mail_queue(){
        $queue = $this->db_game->getRow("select id,time,send_time from mail_adm where state = 1");
        $now = time();
        if($queue['id'] > 0 && $now - $queue['send_time'] > 600) {
            $this->db_game->delete('mail_adm', array('id' => $queue['id']));
            return false;
        }
        return $queue['id'] ? true :false;
    }

  /**
   *  @param operate 操作 delGameMail + updateGameMail
   *  @param params delGameMail : 需要 sid 和 id 
   *                updateGameMail : 需要 sid 、id 和 state
   *                 addGameMail  :  需要 mailinfo[]
   *  @return 1 成功，0 失败， -1 参数或签名出错
   */
    private function _send_signal_to_center ($operate, $params) {

        global $CONFIG;

        $center_api = $CONFIG['center']['api'];

        $sid = $CONFIG['game']['sn'];

        unset($CONFIG);

        $params['sid'] = $sid;

        $reqest_data = Helper::send_data_simple_format($params);

        $res = json_decode(postData($center_api."/mail.php?act=$operate", $reqest_data), true);

        return $res;
    }

    private function _fail_to_send_signal ($params) {
        $date = date('Y-m-d H:i:s', time());
        if (is_array($params)) {
            $params['date'] = $date;
            $content = print_r($params, 1);
        }else
            $content = $date.' : '.$params;

        _log($content, 'mail_sync_fail.log');
    }

}