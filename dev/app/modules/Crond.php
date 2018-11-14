<?php
/**
 * User: jecelyin 
 * Date: 12-2-24
 * Time: 下午3:23
 * 定时任务处理基础类
 */
 
abstract class Crond
{
    protected $db;
    protected $baseUrl = '';
    protected $error = array();
    private $dataSql = array();
    private $dataSqlKeys = array();

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    protected function setBaseUrl($srv)
    {
        //验证参数
        $authUrl = Helper::getAuthUrl('CRON');
        $url = "{$srv['url']}/api/csv.php?{$authUrl}";
        $this->baseUrl = $url;
    }

    /**
     * 获取一个远程请求返回的结果
     * 要求远程API必须返回JSON：{"ver":"版本号","data":"内容"}
     * @param $url 地址
     * @param $data 表单参数，可以是key=value&key=value的字符串形式，也可以为数组，建议使用数组
     * @return bool|array|string
     */
    public function fetchUrl($url, $data)
    {
        $opts = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        );
        $eurl = $url.'&'.(is_array($data) ? http_build_query($data) : $data);
        echo "##[FETCH]: $eurl\n";
        $text = Net::fetch($url, $opts);
        try{
            $result = json_decode($text, 1);
        }catch(Exception $e){
            $result = $e->getMessage();
        }
        $this->error = array();
        if(!$result || !is_array($result) || !$result['ver'] || !isset($result['data']))
        {
            echo "##[ERROR]:\n{$text}\n";
            $this->error['text'] = htmlspecialchars($text); //防止电信XXOO
            $headers = Net::getHeaders($url);
            $this->error['code'] = $headers['http_code'];
            return false;
        }
        if(isset($result['error']))
        {
            echo "##[ERROR]:\n{$result['error']}\n";
            return false;
        }
        echo "##[SUCCESS]: API Ver is:{$result['ver']}\n\n";

        return $result['data'];
    }

    /**
     * @abstract
     * 请求成功后返回数据
     * @param array $srv 服务器信息
     * @param array $result 返回结果
     * @return void
     */
    abstract protected function onSuccess($srv, $result);

    /**
     * @abstract
     * 所有任务跑完时触发
     */
    abstract protected function onFinish();

    protected function lock($module)
    {
        //尝试锁定当前模块
        $info = $this->getLockInfo($module);
        if($info)
        {
            //限制2小时内不能同时进行并发2个进程
            if(time()-$info['ctime'] < 2 * 3600 && $info['pid']>0 && posix_getsid($info['pid']))
                exit('已经有一个进程正在运行，开始时间：'.getDateStr($info['ctime']));
            //不能容忍一个脚本跑的时间太长了，直接干掉它
            $this->kill($info['pid'],$module);
        }
        file_put_contents(VAR_PATH.'/log/cron.'.$module.'.lock', time().'|'.getmypid());
    }

    public function kill($pid,$module)
    {
        if(!$pid)return;
        if(!posix_kill($pid, SIGKILL))
        {
            exec("kill -9 {$pid}");
        }
        $this->unlock($module);
    }

    public function isLocked($module)
    {
        return is_file(VAR_PATH.'/log/cron.'.$module.'.lock');
    }

    public function getLockInfo($module)
    {
        $file = VAR_PATH.'/log/cron.'.$module.'.lock';
        if(is_file($file))
        {
            list($ctime,$pid) = explode('|', file_get_contents($file));
            return array(
                'ctime' => (int)$ctime,
                'pid' => $pid
            );
        }
        return false;
    }

    protected function unlock($module)
    {
        $file = VAR_PATH.'/log/cron.'.$module.'.lock';
        if(!unlink($file))
        {
            file_put_contents("", $file);
            exec("rm -rf $file");
        }
    }

    public function resetSql()
    {
        $this->dataSql = array();
        $this->dataSqlKeys = array();
    }

    public function addDataToSql($row)
    {
        $this->dataSqlKeys = $row;
        $this->dataSql[] = "('".implode("','",$row)."')";
    }

    /**
     * TODO:注意一下大数据插入，有限制的问题
     * @param $table
     * @return string
     */
    public function getInsertSql($table)
    {
        if(!$this->dataSql)return "";
        $sql = "INSERT INTO `{$table}` ";
        $sql .= " (`".implode("`,`", array_keys($this->dataSqlKeys))."`) ";
        $sql .= " VALUES".implode(", ", $this->dataSql);
        return $sql;
    }

    public function log($msg)
    {
        echo date('Y-m-d H:i:s').": $msg\n";
    }

}