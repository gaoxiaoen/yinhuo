<?php
/**
 * User: jecelyin 
 * Date: 12-1-6
 * Time: 下午5:55
 *
 */
 
class AdminController extends View
{
    /**
     * 全局可见（可在非登录时访问），不做权限认证
     * @var bool
     */
    public $public_auth = false;
    /**
     * 内部可见（登录后才算内部），不做权限认证
     * @var bool
     */
    public $private_auth = false;

    public $db = null;
    public $db_game = null;
    public $cache = null;
    public $gameHelper = null;
    public $widget = null;
    //常用搜索字段
    private $st = '';
    private $et = '';
    private $timeFormat = '-7 day';
    public $gp_id = 0;
    public $gs_id = 0;
    public $consume_type = array();
    public function __construct()
    {
        //解决一些浏览器不能识别编码的问题
        header("Content-Type:text/html; charset=utf-8");
        $mod = $this->getModuleName();
        if(!$this->can($mod) && !$this->can(getSubMenu($mod)))
        {
            throw new JecException("没有访问权限");
        }
        Pager::setDefaultStyleFile(MOD_PATH.'/Views/Page.php');

        //初始化一些常用的类
        $this->db = DB::getInstance('db_admin');
        $this->db_game = DB::getInstance('db_game');
        $this->cache = Cache::getInstance();

        $this->gameHelper = new SMP_Game_Helper();
        $this->widget = new Widget($this);
        //避免每个模块都去设置变量
        $this->gp_id = Jec::getInt('gp_id');
        $this->gs_id = Jec::getInt('gs_id');

        //缓存消费类型
        $consumeType = $this->cache->get('consume_type');
        if (is_array($consumeType) && count($consumeType) > 0) {
            $this->consume_type = $consumeType;
        } else {
            $consumeTypeData = $this->db_game->getAll("select * from consume_type");
            foreach ($consumeTypeData as $val) {
                $this->consume_type[0] = '系统';
                $this->consume_type[$val['id']] = $val['name'];
            }
            $this->cache->set('sonsume_type', $consumeType, 3600);
        }
        //简单验证下访问权限
        if($this->gp_id > 0 && $_SESSION['group'] && !in_array($this->gp_id, $_SESSION['group']))
            throw new JecException('你没有权限访问此渠道分组');
    }

    public function display($tplFile='')
    {
        $this->initHeader();

        if($this->getAssignVar('title') === false)
            throw new JecException('缺少模板标题！');

        parent::display($tplFile);

    }

    /**
     * render Views/Header.html
     */
    private function initHeader()
    {
        $header_servers_urls = array();
        $CustomGroups = array();
        $this->assign('newServers', array());
        $this->assign('header_servers_urls', $header_servers_urls);
        $this->assign('header_servers_plts', $CustomGroups);
        $this->parse_session();

        $select = Jec::getInt('jump') > 0 ? Jec::getInt('jump') : $_SESSION['serverid'];
        $_SESSION['serverid'] = $select;
        $this->assign('select',$select);
        $this->assign('MODULE_NAME', Controller::getModuleName());
        $this->assign('menus', SMP_Menu_Helper::getMenus());
    }

    /**
     * 拆分session到字符串
     */
    public function parse_session()
    {
        $session_arr['login_name'] = urlencode($_SESSION['login_name']);
        $session_arr['id'] = $_SESSION['id'];
        $session_arr['nickname'] = urlencode($_SESSION['nickname']);
        $session_arr['user_group_id'] = $_SESSION['user_group_id'];
        $session_arr['groupname'] = urlencode($_SESSION['groupname']);
        $_SESSION['group'] ? $session_arr['group'] = implode(',',$_SESSION['group']) : $session_arr ;
        $_SESSION['permissions'] ? $session_arr['permissions'] = implode(',',$_SESSION['permissions']) : $session_arr;
        $session_arr['t']=TIME;
        $session = http_build_query($session_arr);
        $ses_tk = md5($session."cl168");
        $this->assign('session',$session);
        $this->assign('ses_tk',$ses_tk);

    }

    /**
     * 权限控制
     * @param $mod
     * @return bool
     */
    public function can($mod)
    {
        if(!$mod)
            return true;
        //超级管理员
        if(!$_SESSION['permissions'] && !$_SESSION['user_group_id'])
            return true;
        //全局可见
        if($this->public_auth)
            return true;

        //内部可见
        if($_SESSION['id']>0 && $this->private_auth)
            return true;

        //检查功能权限
        if(in_array($mod, $_SESSION['permissions']))
            return true;

        return false;
    }


    /**
     * @param string $fieldName 数据库字段名称
     * @param string $defFormat 时间范围格式,空则不默认一个搜索范围，如：'-7 day', '-1 month'
     * @param bool $is_unixtime $fieldName 是否unixtime时间戳，默认false
     * @param string $daytime 日期类型 默认每天
     * @param int $maxday 最长时间跨度
     * @return string 返回一个SQL查询where条件的部分语句，可能返回1
     */
    public function getWhereTime($fieldName='ctime', $defFormat='-7 day',$is_unixtime = false,$daytype = 'daily',$maxday = 31)
    {
        $st0 = Jec::getDate('st');
        $et0 = Jec::getDate('et');
        //只选择开始日期，则认定是要查看当天的数据
        if($st0 && !$et0)
        {
            $st = $st0;
            $et = getEndTimeOfDay($st0);
        }elseif(!$st0 || !$et0){
            $midnight = strtotime('midnight');
            $st = getDateStr(strtotime($defFormat, $midnight));
            if($daytype == 'oneday'){
                $et = getEndTimeOfDay($st);
            }else
                $et = getEndTimeOfDay(TIME);
        }else{
            $st = $st0;
            $et = $et0;
        }
        //自然月
        if(!$st0 && !$et0 && $daytype == 'month')
        {
            $st = getDateStr(date('Y-m',TIME));
            $et = getDateStr(strtotime('+1 month',strtotime($st))-1);
        }
        $st_u = strtotime($st);
        $et_u = strtotime($et);
        if(($et_u - $st_u) > 86400 * $maxday)
            throw new JecException("时间限制${maxday}天");
        $this->st = $st;
        $this->et = $et;
        $this->timeFormat = $defFormat;

        if($is_unixtime){
            $st = $st_u;
            $et = $et_u;
        }
        if(!$st0 && !$et0 && $daytype == 'all') {
                $this->st = '';
                $this->et = '';
                return "1";
        }
        //这里不能加`号，因为有前缀时，就是bug
        $sql = "{$fieldName}>='{$st}' AND {$fieldName}<='{$et}'";
        return $sql;
    }

    /**
     * 根据当前时间段获取开始时间的偏移
     * @param int $index 暂时只支持-1, 0, 1
     * @return string
     */
    public function getStartTime($index=0)
    {
        if($index == 0)
        {
            return $this->st;
        }elseif($index < 0){
            if(!$this->st)
            {
                return getStartTimeOfDay(strtotime($this->timeFormat));
            }else{
                $st = strtotime($this->st);
                $et = strtotime($this->et);
                //向前偏移一个时间段
                $ret = $st - ($et - $st) - 1;
            }
        }else{
            $et = $this->et ? strtotime($this->et) : TIME;
            $ret = ++$et;
        }
        return getDateStr($ret);
    }

    /**
     * 根据当前时间段获取结束时间的偏移
     * @param int $index 暂时只支持-1, 0, 1
     * @return string
     */
    public function getEndTime($index=0)
    {
        if($index == 0)
        {
            return $this->et;
        }elseif($index < 0){
            if(!$this->et)
            {
                return getEndTimeOfDay(TIME);
            }else{
                $st = strtotime($this->st);
                //上个时间段开始处向左移一秒钟即得到结果
                $ret = --$st;
            }
        }else{
            $et = $this->et ? strtotime($this->et) : getEndTimeOfDay(TIME);
            $st = $this->st ? strtotime($this->st) : strtotime($this->timeFormat);
            $ret = $et + ($et - $st) + 1;
        }
        return getDateStr($ret);
    }

    public function getDateType()
    {
        $dt = $_REQUEST['dateType'];
        if(!in_array($dt, array('month','week','daily')))
            $dt = 'daily';
        return $dt;
    }

    public function getWeekName($week)
    {
        return $week[0].'~'.$week[1];
    }

    public function getMonthRange($month)
    {
        $st = strtotime("{$month}01");
        $et = strtotime("+1 month", $st)-1;

        return array(date('Y-m-d',$st), date('Y-m-d',$et));
    }

    public function getARPU($money, $role_num)
    {
        if($role_num <= 0)return 0;
        return formatNumber(round($money/$role_num));
    }

}