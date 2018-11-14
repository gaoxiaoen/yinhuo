<?php
class Cron_Sensitive{
	public $db = null;
	public $circle_time = 0;

    public function __construct($data)
    {
    	$this->cache = Cache::getInstance();
        $this->db = DB::getInstance('db_game');
        $method = $data['method'];
        $this->data = $data;
        $this->$method();
    }

    /**
     *  可手动更新某个时间前5分钟内聊天记录是否含有敏感词
     *  手动调用格式 : [  php cron.php Cron_Sensitive main 20170505  ] (注:第三个时间参数是通过strtotime转化为时间戳)
     */
    public function main(){
    	$time = $this->data['args'][0] ? strtotime($this->data['args'][0]) : time();
    	$disableword = $this->db->getOne('select word_list from sensitive_word order by time desc');
    	$key = 'post_sensitive_word_data_to_center_key';
    	global $CONFIG;
		$nowtime = time();
		$params['time'] = $nowtime;
		$params ['sign']= md5($key.$nowtime);
		# 如果关键词表为空，则可能是新开服等情况，需主动向中心服拉取数据
    	if(!$disableword){
    		$res = postData($CONFIG['center']['api'].'/Sensitive.php?act=pullData',$params);
    		if($disableword = json_decode($res,1)){
    			$this->db->insert('sensitive_word',['word_list'=>$disableword,'time'=>time()]);
    		}else{
    			exit();
    		}
    	}
		$sqltime = $time - 5*60;		#获取5分钟内的聊天内容(ps:5min为定时更新的时间间隔)
		$content = $this->db->getAll('select pkey,nickname,type,content,time,lv,vip from log_chat where time >='.$sqltime .' limit 0,2000');
		if(empty($content)) exit(); 	#时间段内不存在连天记录则退出
		$match_arr = [];
		$key_arr = explode(',', str_replace('，', ',', $disableword)); #如果中文逗号则替换为英文逗号后按英文逗号拆分敏感词列表为数组
		foreach($content as $c){
			foreach($key_arr as $k){
				if(!empty($k) && strpos($c['content'],$k) !== false){
					$match_arr[] = $c;
					break;
				}
			}
		}
		if(empty($match_arr)){
			exit();
		}else{
			$params['sn'] = $CONFIG['game']['sn'];
			$params['data'] = $match_arr;
			$this->pushData($CONFIG['center']['api'].'/Sensitive.php?act=pushData',json_encode($params));
		}

    }

    private function pushData($url,$params){
    	$res = postData($url,$params,'json');
    	if($res == '0' && $this->circle_time <=3){
    		sleep(rand(1,5));
    		$this->circle_time += 1;
    		$this->pushData($url,$params);
    	}
    }
}