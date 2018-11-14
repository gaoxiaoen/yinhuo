<?php
/**
 * User: jecelyin 
 * Date: 12-8-17
 *
 */
 
class SMP_GM_Mail extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '发送邮件');
        $act = Jec::getVar("act");
        if($act == "send")
        {
            $this->send_mail();
        }elseif($act == "del"){
            $mid = Jec::getInt('mid');
            $this->del_mail_log($mid);
        }elseif($act == "search"){
            $this->search();
        }
        $this->show();
    }

    /**
     * @param $goodslist
     * @return bool 检查物品格式是否正确
     */
    public static  function checkMailGoods($goodslist)
    {
        preg_match('/^\[\{\d+,\d+\}(,\{\d+,\d+\})*]$/',$goodslist,$match);
        return $goodslist == $match[0];
    }

    /**
     * 查找物品id或名称
     */
    public function search()
    {
        $search = Jec::getVar('search');
        global $Ggoods;
        if((int) $search > 0){
            $name = $Ggoods[$search];
            exit($name);
        }else{
            $find = "";
            foreach($Ggoods as $k => $g){
                if(strstr($g,$search)){
                    $find .= " $g($k)";
                }
            }
            exit($find);
        }

    }
    
    private function makeTab()
    {
        $tabs = array(
            'FSendMail' => array('name' => '发邮件', 'checked' => true),
            'PlayerMail' => array('name' => '发送邮件记录')
        );
        $this->assign('tabs', $tabs);
    }
    
     private function show()
    {
        $this->makeTab();
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from mail_adm"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $list = $this->db_game->getAll("select * from mail_adm order by id desc limit $offset,$limit");
        foreach($list as &$row){
            $row['goodslist'] = format_goods_list($row['goodslist']);
        }
        $this->assign("list",$list);
        $this->assign('page', $pager->render());
        $this->display();
    }

    private function del_mail_log($mid)
    {
        $state = $this->db_game->getOne("select state from mail_adm where id = $mid");
        if($state != '0' && $_SESSION['login_name'] != 'admin')
            new JecException("权限不足，无法删除!");
        $signal_res = $this->_send_signal_to_center('delGameMail', ['id'=>$mid]);
        if ($signal_res != '1') $this->_fail_to_send_signal(['act'=>'delGameMail', 'data'=>['id'=>$mid], 'res'=>$signal_res]);
         $r = $this->db_game->delete("mail_adm",array("id"=>$mid));
         if($r > 0)  
            $msg['msg'] = "删除成功！";
         else {
            $msg['error'] = 1;
            $msg['msg'] = "删除失败!";
         }
        $this->assign("msg",$msg);
    }


    private function send_mail()
    {
        $items = Jec::getVar('items');
        $type = parseInt($items['type']);
        $title = g($items['title']);
        $content = g($items['text']);
        $lv_s = (int)$items['lv_s'];
        $lv_e = (int)$items['lv_e'];
        $reg_time_s = $items['reg_time_s'] ? strtotime($items['reg_time_s']) : 0 ;
        $reg_time_e = $items['reg_time_e'] ? strtotime($items['reg_time_e']) : 0 ;
        $login_time_s = $items['login_time_s'] ? strtotime($items['login_time_s']) : 0;
        $login_time_e = $items['login_time_e'] ? strtotime($items['login_time_e']) : 0;
        $game_channel_id = (int) $items['game_channel_id'];
        $goods = Jec::getVar('goods');
        $nums = Jec::getVar('nums');
        $reason = Jec::getVar('reason');
        $listStr = "";
        if(is_array($goods)){
            foreach($goods as $k => $g){
                $n = $nums[$k];
                $listStr .= '{'.$g.','.$n.'},';
            }
            $listStr = trim($listStr,',');
        }
        $goodslist = "[$listStr]";
        if(empty($content)||empty($title)) new JecException("邮件内容不能为空!");

        $real_name = $_SESSION['nickname'];

        $resourec = false;
        if(!empty($listStr)) $resourec = true;

        $roleName = trim($items['role_name']);
        $time = time();
        $mailinfo = array(
            'type' => $type,
            'players' => $roleName,
            'title' => $title,
            'content' => $content,
            'reason' => $reason,
            'goodslist' => $goodslist,
            'time' => $time,
            'send_time'=>0,
            'user' => $real_name,
            'lv_s' =>$lv_s,
            'lv_e' =>$lv_e,
            'reg_time_s' =>$reg_time_s,
            'reg_time_e' =>$reg_time_e,
            'login_time_s' =>$login_time_s,
            'login_time_e' =>$login_time_e,
            'game_channel_id'=>$game_channel_id,
            'state' => 0
        );

        $msg = array();
        $count = 0;
        $time = time();
        if($goodslist != "[]"){
            //d($goodslist);
            $checkformat = SMP_GM_Mail::checkMailGoods($goodslist);
            if(!$checkformat) new JecException('物品格式错误!');
        }
        if($type == 0) {
            if (empty($roleName)) new JecException('玩家名字不能为空!');
            $roles = explode("\r\n",$roleName);
            if(count($roles) > 50) new JecException('玩家数量不能大于50!');
            if(!$resourec) {
                foreach ($roles as $role) {
                    if((int) $role > 0) {
                        $validate_pkey = $this->_validate_player_key('pkey', $role);
                    }else{
                        $validate_pkey = $this->_validate_player_key('nickname', $role);
                    }
                    if ($validate_pkey) {
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
                    $msg['msg'] .= "发送完毕 ";
                }else
                    $msg['msg'] .= "没有合法玩家, 无发送任务.";
                $mailinfo['state'] = 2;
            }else{
                $msg['msg'] = "邮件已提交审核!";
            }
        }

        if($type == 1 || $type == 2){
            $queue = $this->_get_send_mail_queue();
            if($queue) new JecException('邮件任务正在发送中，请稍后。');
            if(!$resourec){
                $mailinfo['state'] = 1;
                $mailinfo['send_time'] = $time;
                $msg['msg'] = "邮件已进入发送队列.";
                Log::info('SMP_GM_Mail',"发送无资源邮件[$title][$goodslist]");
                $this->db_game->insert("mail_adm",$mailinfo);
                $id = $this->db_game->getInsertId();
                Net::rpc_game_server(gm,send_mail,array("id"=>$id));
            }else{
                $this->db_game->insert("mail_adm",$mailinfo);
                $id = $this->db_game->getInsertId();
                $msg['msg'] = "邮件已提交审核!";
            }
        }else{
            $mailinfo['send_time'] = $time;
            $this->db_game->insert("mail_adm",$mailinfo);
            $id = $this->db_game->getInsertId();
        }
        #   对state = 0 待审核的邮件通知中心服添加待审核邮件
        if ($mailinfo['state'] == '0') {
            $mailinfo['id'] = $id;
            $signal_res = $this->_send_signal_to_center('addGameMail', $mailinfo);
            if ($signal_res != '1') $this->_fail_to_send_signal(['act'=>'addGameMail', 'data'=>$mailinfo, 'res'=>$signal_res]);
        }
        alert($msg['msg'],'href',"?m=SMP_GM_Mail");

    }

    private function _insert_mail($type,$pkey,$title,$content,$goodslist,$time){
        $mkey = unique_key();
        $overtime = time() + 86400 * 7;
        $sql = "insert into mail set mkey = $mkey,pkey = $pkey ,type = $type,title = '$title',content = '$content',goodslist = '$goodslist',time = $time,overtime = $overtime ";
        $this->db_game->query($sql);
    }

    private function _validate_player_key($key, $val){
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

    /**
     *  @param 需要记录的内容
     *  @return void
     *  中央服信号接收失败的日志
     *  日志中的res -1 验证失败， 0 数据库操作失败， 空 通讯失败。
     */
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