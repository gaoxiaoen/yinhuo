<?php
/**
 *@copyright Jec
 *@package Jec框架
 *@link 
 *@author jecelyin peng
 *@license 转载或修改请保留版权信息
 * HTTP处理类
 */

class Net
{
    const HTTP_AGENT = 'JecSpider Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)';

    /**
     * @static
     * 获取一个URL地址返回的内容
     * @param string $url
     * @param array $other_curl_opt 设置CURL选项
     * @return mixed 成功则返回string，否则返回false
     */
    public static function fetch($url, $other_curl_opt = array())
    {
        $curl_opt = array(
            CURLOPT_URL => $url,
            CURLOPT_AUTOREFERER => true, //自动添加referer链接
            CURLOPT_RETURNTRANSFER => true, //true: curl_exec赋值方式，false：curl_exec直接输出结果
            CURLOPT_FOLLOWLOCATION => false, //自动跟踪301,302跳转
            //CURLOPT_HTTPGET => TRUE, //默认为GET，无需设置
            //CURLOPT_POST => TRUE,
            //CURLOPT_POSTFIELDS => 'username=abc&passwd=bcd',//也可以为数组array('username'=>'abc','passwd'=>'bcd')
            CURLOPT_CONNECTTIMEOUT => 15, //秒
            CURLOPT_USERAGENT => self::HTTP_AGENT,
            //CURLOPT_COOKIE => '',
        );
        
        if($other_curl_opt)
        foreach ($other_curl_opt as $key => $val)
            $curl_opt[$key] = $val;
        
        $ch = curl_init();
        curl_setopt_array($ch, $curl_opt);
        $contents = curl_exec($ch);
		curl_close($ch);
        return $contents;
    }

    /**
     * @static
     * 获取一个URL返回的头部内容
     * @param string $url
     * @return array
     */
    public static function getHeaders($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ));
        $r = curl_exec($ch);
        $r = explode("\r\n", $r);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $header = array();
        foreach($r as $line)
        {
            if(empty($line))
                continue;
            
            list($k, $v) = explode(': ', $line);
            if($v === null)
                $header[] = $k;
            else
                $header[$k] = $v;
        }
        $header['http_code'] = $http_code;
        
        return $header;
    }

    /**
     * 告诉浏览器对当前页面不做缓存
     */
    public static function sendNoCache()
    {
        header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
        header('Last-Modified: ' . date(DATE_RFC1123));
        header("Pragma: no-cache");
        //header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
        header("Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0");
    }

    /**
     * 给浏览器发送下载头部消息
     * @static
     * @param string $filename
     * @param string $mimetype
     * @param int $length
     * @param bool $no_cache
     */
    public static function sendDownloadHeader($filename, $mimetype, $length = 0, $no_cache = true)
    {
        if($no_cache)
            self::sendNoCache();

        if (!empty($filename)) {
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
        }
        header('Content-Type: ' . $mimetype);
        header('Content-Transfer-Encoding: binary');
        if ($length > 0) {
            header('Content-Length: ' . $length);
        }
    }
    
    /**
     * 重定向当前窗口网址
     * @param string $URL 网址
     * @param boolean $meta 是否使用meta标签刷新
     * @return null 直接退出PHP
     */
    public static function redirect($URL='', $meta = false)
    {
        ob_end_clean();
        if(!$URL)
            $URL = $_SERVER['HTTP_REFERER'];
        if(!$URL)
            $URL = '/';
        if (!$meta) {
            header("Location: $URL");
            exit;
        } else {
            echo "<meta http-equiv='refresh' content='0;url=$URL'>";
            exit;
        }
    }

    /**
     * 获取客户端真实IP
     * @return IP or '0.0.0.0'
     */
    public static function getIP()
    {
        $clientIP = '0.0.0.0';
        if ($_SERVER['HTTP_CLIENT_IP'] && strcasecmp($_SERVER['HTTP_CLIENT_IP'], 'unknown'))
            $clientIP = $_SERVER['HTTP_CLIENT_IP'];
        elseif ($_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], 'unknown'))
            $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        //web在内网，即web在vpn里的情况
        elseif (isset($_SERVER['HTTP_CDN_SRC_IP']) && $_SERVER['HTTP_CDN_SRC_IP'])
            $clientIP = $_SERVER['HTTP_CDN_SRC_IP'];
        elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
            $clientIP = $_SERVER['REMOTE_ADDR'];
        
        preg_match('/[\d\.]{7,15}/', $clientIP, $clientIPmatches);
        $clientIP = $clientIPmatches[0] ? $clientIPmatches[0] : '0.0.0.0';
        unset($clientIPmatches);
        
        return $clientIP;
    }

    /**
     * 获取ip位置信息
     * @param $ip
     * @return mixed
     */
    public static function getIPLocation($ip=false)
    {
        if(!$ip) $ip = self::getIP();
        $ch = curl_init();
        $url = "http://apis.juhe.cn/ip/ip2addr?ip=$ip&key=5dfe1177f72f6b4274a4f2e96279ea07";
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        return json_decode($res,true);
    }

    /**
     * 是否来自搜索引擎
     * @return boolean
     */
    public static function isRobot()
    {
        $kw_spiders = 'Google|baidu|Bot|Crawl|Spider|Slurp|sohu|Twiceler|lycos|robozilla|msn|yahoo|sogou';

        if (preg_match("/($kw_spiders)/i", $_SERVER['HTTP_USER_AGENT']))
            return true;

        return false;

    }

    /**
     * @static
     * 是否为移动设备
     * @return bool
     */
    public static function isMobile()
    {
        if(isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']))
            return true;

        if(strpos(strtolower($_SERVER['HTTP_VIA']), 'wap') !== false)
            return true;

        if(strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'vnd.wap') !== false)
            return true;

        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $isMobile =
                //机器特征部分
                strpos($ua, 'j2me') !== false
                || strpos($ua, 'epoc') !== false
                || strpos($ua, 'midp-') !== false
                || strpos($ua, 'cldc-') !== false
                || strpos($ua, 'wap1.') !== false
                || strpos($ua, 'wap2.') !== false
                //手机型号或浏览器名称部分
                || strpos($ua, 'sony') !== false
                || strpos($ua, 'symbian') !== false
                || strpos($ua, 'nokia') !== false
                || strpos($ua, 'iphone') !== false
                || strpos($ua, 'android') !== false
                || strpos($ua, 'philips') !== false
                || strpos($ua, 'samsung') !== false
                || strpos($ua, 'mobile') !== false
                || strpos($ua, 'windows ce') !== false
                || strpos($ua, 'opera mini') !== false
                || strpos($ua, 'nitro') !== false
                || strpos($ua, 'netfront') !== false
                || strpos($ua, 'mot') !== false //motorola
                || strpos($ua, 'up.browser') !== false
                || strpos($ua, 'up.link') !== false
                || strpos($ua, 'audiovox') !== false
                || strpos($ua, 'blackberry') !== false
                || strpos($ua, 'ericsson,') !== false
                || strpos($ua, 'panasonic') !== false
                || strpos($ua, 'philips') !== false
                || strpos($ua, 'sanyo') !== false
                || strpos($ua, 'sharp') !== false
                || strpos($ua, 'sie-') !== false
                || strpos($ua, 'portalmmm') !== false
                || strpos($ua, 'blazer') !== false
                || strpos($ua, 'avantgo') !== false
                || strpos($ua, 'danger') !== false
                || strpos($ua, 'palm') !== false
                || strpos($ua, 'series60') !== false
                || strpos($ua, 'series70') !== false
                || strpos($ua, 'series80') !== false
                || strpos($ua, 'series90') !== false
                || strpos($ua, 'palmsource') !== false
                || strpos($ua, 'pocketpc') !== false
                || strpos($ua, 'smartphone') !== false
                || strpos($ua, 'rover') !== false
                || strpos($ua, 'ipad') !== false
                || strpos($ua, 'au-mic,') !== false
                || strpos($ua, 'alcatel') !== false
                || strpos($ua, 'ericy') !== false
                || strpos($ua, 'up.link') !== false
                || strpos($ua, 'vodafone/') !== false
                || strpos($ua, 'maemo') !== false
                || strpos($ua, 'iemobile') !== false
                || strpos($ua, 'windows phone os 7') !== false
                //分辨率部分
                || strpos($ua, '320x320') !== false
                || strpos($ua, '240x320') !== false
                || strpos($ua, '176x220') !== false;

        return $isMobile;
    }


    /**
     * 发送HTTP状态
     * @param int $code 状态码，如404,502
     */
    public static function sendHttpStatus($code)
    {
        static $_status = array(
        // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols', 
        // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content', 
        // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ', // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy', 
        // 306 is deprecated but reserved
            307 => 'Temporary Redirect', 
        // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed', 
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed', 
        // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );
        
        if (array_key_exists($code, $_status)) {
            //ob_end_clean();
            header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
        }
    }

    /**
     * 游戏服务器通信接口
     * @param $Module erlang模块
     * @param $Method 模块方法
     * @param $args 方法参数数组
     */
    public static function rpc_game_server($Module, $Method, $args = array())
    {
        global $CONFIG;
        if(isset($CONFIG['game'])){
            //$HOST = $CONFIG['game']['ip'];
            $PORT = $CONFIG['game']['port'];
            $key = $CONFIG['game']['tick'];
            $qid = "cl168";
            $ts = time();
            $auth = md5($ts . $key);
            $query = http_build_query($args); 
            $URL = "http://127.0.0.1:${PORT}/gm?auth=$auth&ts=$ts&m=$Module&f=$Method&$query";
            $ret = self::fetch($URL);
            return $ret;
        }else{
            return -1;
        }

    }

    public static function rpc_game_server2($IP,$PORT,$Module,$Method,$args = array())
    {
        global $CONFIG;
        $key = $CONFIG['game']['tick'];
        $qid = "cl168";
        $ts = time();
        $auth = md5($qid . $ts . $key);
        $query = http_build_query($args);
        $URL = "http://{$IP}:{$PORT}/{$Module}/{$Method}?timestamp=$ts&auth=$auth&qid=$qid&" . $query;
        $ret = self::fetch($URL);
        return $ret;
    }

    public static function rpc_game_by_url($url,$Module,$Method,$args = array())
    {
        global $CONFIG;
        $key = $CONFIG['game']['tick'];
        $qid = "cl168";
        $ts = time();
        $auth = md5($qid . $ts . $key);
        $query = http_build_query($args);
        preg_match('/http:\/\/(.*?):(\d+)/', $url, $find);
        if (isset($find[1]) && isset($find[2])) {
            $IP = $find[1];
            $PORT = $find[2] - 1;
            $URL = "http://{$IP}:{$PORT}/{$Module}/{$Method}?timestamp=$ts&auth=$auth&qid=$qid&" . $query;
            $ret = self::fetch($URL);
        return $ret;
        }else
            return 0;
    }


} // end class
