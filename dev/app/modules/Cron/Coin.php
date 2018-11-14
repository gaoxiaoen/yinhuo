<?php
class Cron_Coin {
	public $db = null;

    public function __construct($data)
    {
        $this->db = DB::getInstance('db_game');
        $method = $data['method'];
        $this->$method();
    }

    public function gold_log()
    {
        #更新当前时间前1小时的数据
        $hour = strtotime(date('Y-m-d H',time()).':00:00');
        $st = $hour - 3600;
        $et = $hour;
        $snlimit = 40000;
        $sql = "select * from log_gold where time >= $st and time < $et and game_id != 0 and sn < $snlimit and oldgold != newgold";
        $rows = $this->db->getAll($sql);
        if(count($rows) > 0)
        {
            $path = VAR_PATH.'/sync/gold_log/'.date('Ymd',$hour);
            if (!is_dir($path)){
                mkdir($path,0777,true);
            }
            $serverList = array();
            foreach($rows as $row)
            {
                $serverList[] = $row['sn'];
            }
            $serverList = array_unique($serverList);
            $tmpArr = array();
            global $Ggoods;
            foreach($serverList as $ser)
            {
                 $file = $path.'/'.$ser.'_'.date('Ymd',$hour).'_CoinLog.log';
                 if(!file_exists($file)){
                    if(!$fs = fopen($file,'w+'))
                    {
                        $this->errorLog($hour,$msg = "Fail to open $file");
                        continue;
                    }
                }else{
                    if(!$fs = fopen($file,'a+'))
                    {
                        $this->errorLog($hour,$msg = "Fail to open $file");
                        continue;
                    }
                }    
                foreach($rows as $row)
                {
                    if($row['sn'] == $ser)
                    {
                    	$newGold = (int)$row['newgold'];
						$oldGold = (int)$row['oldgold'];
                        $tmpStr = '';
                        #$tmpStr .=  $row['id']."\t";
                        $tmpStr .=  $row['game_id']."\t";
                        $tmpStr .=  $row['channel_id'] ? $row['channel_id']."\t" : "未知\t";
                        $tmpStr .=  $row['game_channel_id'] ? $row['game_channel_id']."\t" : "未知\t";
                        $tmpStr .=  $row['acc_name'] ? $row['acc_name']."\t" : "未知\t";
                        $tmpStr .=  $row['pkey']."\t";
                        $tmpStr .=  $row['nickname'] ? preg_replace("/[\t\r\n]/", '', $row['nickname'])."\t" : "未知\t";
                        $tmpStr .=  $row['sn']."\t";
						// 本游戏除了充值获取水晶外还有任务等原因可以获得水晶，根据接口文档，除了充值外，其他的都定义为5类型的消耗
						if($newGold>$oldGold)
						{
							$tmpStr .=  $row['addreason'] == 121 ? "0\t" : "5\t";
						}
						else
						{
							$tmpStr .=  "4\t";
						}
                        $tmpStr .=  ($newGold-$oldGold)."\t";
                        $tmpStr .=  $row['newgold']."\t";
                        $tmpStr .=  $row['goods_id'] == 0 ? "未知\t" : ($Ggoods[$row['goods_id']] ? $Ggoods[$row['goods_id']]."\t" : "未知商品(GID:".$row['goods_id'].")\t");
                        $tmpStr .=  $row['goods_num']."\t";
                        $tmpStr .=  $row['desc'] ? $row['desc']."\t" : "未知\t";
                        $tmpStr .=  $row['time']."\t";
                        $tmpStr .="\n";
                        if(!fwrite ($fs,print_r($tmpStr,1)))
                        {
                            $this->errorLog($hour,$msg = "Fail to write $file\n",$tmpStr);
                            continue;
                        }
                     }
                }
                fclose($fs);
            }
        }
    }

    /**
     *  记录错误日日志
     */
    public function errorLog($hour,$msg,$data='')
    {
        $path = VAR_PATH.'/sync/gold_log/'.date('Ymd',$hour);
        if (!is_dir($path)){
            mkdir($path,0777,true);
        }
        $file_name = $path.'/error.log';
        if(!file_exists($file_name))
        {
            $fs = fopen($file_name,'w+');
        }else{  
            $fs = fopen($file_name,'a+');
        }
        if($data){ $content = $msg."\n".print_r($data,1)."\n"; }else{ $content = $msg."\n"; }
        fwrite($fs, $content);
        fclose($fs);
}
}