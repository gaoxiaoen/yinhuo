<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 0:18
 */
class SMP_Log_Chat extends AdminController
{

    public function __construct()
    {
    	$chat_type[$d['type']];
        parent::__construct();
        $this->assign('title', '聊天日志');
		$confList = array("频道"=>"type","玩家key"=>"pkey","昵称"=>"nickname","等级"=>"lv","VIP"=> "vip","内容"=>"content",
							"私聊玩家key"=>"tokey","私聊玩家名称"=>'toname',"时间"=>"time");
		$this->assign('confList',$confList);
        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and nickname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_chat where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from log_chat  where $time $where order by time desc limit $offset,$limit");
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select * from log_chat  where $time $where order by time "));
        
        global $GChatType;
   
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
		$this->assign('chat_type',$GChatType);
        $this->display();
    }
	
	
	

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data)
    {
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('log_chatcsv');
    }

}

