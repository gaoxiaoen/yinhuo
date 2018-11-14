<?php
/**
 * User: jecelyin 
 * Date: 12-2-24
 * Time: 下午6:56
 * 专门为Widget组件提供数据服务，避免AdminController方法过多
 */
 
class Widget
{
    /**
     * @class AdminController 管理员控制器
     */
    private $parent = null;
    //是否仅显示游戏名称列表
    private $gameOnly = false;
    /**
     * 服务器列表多选
     * @var int 0默认，1多选有链接，2多选无链接
     */
    private $serverCheckbox = 0;
    //需要移除的服务器列表链接参数
    private $removeQueryArr = array();

    public function __construct($controller)
    {
        $this->parent = $controller;
    }

    public function setGameOnly($boolean)
    {
        $this->gameOnly = $boolean;
        if($boolean && !$this->parent->game_id)
        {
            $games = $this->parent->gameHelper->getGameList();
            $game = end($games);
            $_GET['game_id'] = $this->parent->game_id = (int)$game['game_id'];
            unset($games);
        }
    }

    public function setServerAsCheckbox($style)
    {
        $this->serverCheckbox = $style;
    }

    public function addRemoveQuery($key)
    {
        $this->removeQueryArr[] = $key;
    }

    public function getSelectServerList()
    {
        return array();
    }

    public function getGroupServerList(){
        return array();
    }

    //活动服务器列表用
    public function getSelectedGroup()
    {
        //服务器组
        $gpid = 'gp';
        $g_sel = $_POST[$gpid];
        $g_sel = $g_sel == NULL ? array() : $g_sel;
        return $g_sel;
    }
    //活动服务器列表用
    public function getSelectedServer()
    {
        $g_sel = $this->getSelectedGroup();
        //指定服id
        $GS = SMP_Act_Activity::get_server_list();
        $GGroup = $GS['group'];
        $s_sel = array();
        foreach($GGroup as $key=>$g){
            $exist = false;
            foreach($g_sel as $gl){
                if($key==(int)$gl)
                    $exist = true;
            }
            if($exist == false){
                $gsid = 'gs'.$key;
                $sarr = $_POST[$gsid];
                $sarr = $sarr == NULL ? array() : $sarr;
                $s_sel = array_merge($s_sel,$sarr);
            }
        }
        return $s_sel;

    }
    //活动服务器列表用
    public function getIgnoreSelectedServer()
    {
        $ignore = $_POST['no_gs'];
        return $ignore;
    }

    public function getGameServerList()
    {
        $gs_id = $this->parent->gs_id;
        $gp_id = $this->parent->gp_id;
        $req = $_GET;
        foreach($_POST as $key => $val)
            $req[$key] = $val;

        foreach($this->removeQueryArr as $key)
            unset($req[$key]);
        unset($req['gs_id'], $req['gp_id'], $req['game_id'], $req['page']);
        $queryStr = http_build_query($req);
        $url = '?'.$queryStr;

        $blocks = array(
            'display' => array(),
            'hidden' => array(),
        );

        $platforms = $this->parent->gameHelper->getPlatformLists();
        if(!$platforms)return $blocks;

        $blocks['display']['gp_id'][0] = array(
            'name' => '所有平台',
            'checked' => $gp_id == 0,
            'href' => $url."&gp_id=0"
        );
        $blocks['display']['gp_id'][-1] = array(
            'name' => '国内',
            'checked' => $gp_id == -1,
            'href' => $url."&gp_id=-1"
        );
        $blocks['display']['gp_id'][-2] = array(
            'name' => '海外',
            'checked' => $gp_id == -2,
            'href' => $url."&gp_id=-2"
        );
        foreach($platforms as $pl)
        {
            if (empty($pl['name']))continue;
            $blocks['display']['gp_id'][$pl['gp_id']] = array(
                'name' => $pl['name'],
                'checked' => $gp_id == $pl['gp_id'],
                'href' => $url."&game_id={$pl['game_id']}&gp_id={$pl['gp_id']}"
            );
        }
        if(!$gp_id || $this->gameOnly)return $blocks;

        $servers = $this->parent->gameHelper->getServerList($gp_id);
        if(!$servers)return $blocks;

        $name = $this->serverCheckbox > 0 ? 'gs_id[]' : 'gs_id';
        //兼容checkbox的情况
        if(!is_array($gs_id))
            $gs_id = $gs_id ? array($gs_id) : array();

        $blocks['display'][$name][0] = array(
            'name' => '所有服',
            'checked' => count($gs_id) == 0 && $this->serverCheckbox == 0,
            'checkbox' => $this->serverCheckbox,
            'href' => $url."&gp_id={$gp_id}&gs_id=0",
            'extra' => 'onchange="game_list_chk(this);"',
        );

        foreach($servers as $srv)
        {
            $blocks['display'][$name][$srv['gs_id']] = array(
                'name' => $srv['name'],
                'checkbox' => $this->serverCheckbox,
                'checked' => in_array($srv['gs_id'], $gs_id),
                'href' => $url."&gp_id={$srv['gp_id']}&gs_id={$srv['gs_id']}"
            );
        }
        return $blocks;
    }
}