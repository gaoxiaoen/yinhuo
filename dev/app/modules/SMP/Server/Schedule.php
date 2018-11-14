<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-29
 *
 */
 
class SMP_Server_Schedule extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        parent::assign('title', '开服申请');
        
        $do = Jec::getVar('do');
        $id = Jec::getVar('id');
        
        switch($do)
        {
            case 'audit':
                $this->audit($id);
                break;
                
            case 'reject':
                $this->reject($id);
                break;
                
            case 'add':
                $this->add();
                break;
                
            case 'delete':
                $this->delete($id);
                break;
                
            case 'showedit':
                $this->showEdit($id);
                return;
               
            case 'edit':
                $this->edit($id);
                break;
        }
        
        $this->show();
    }
    
    private function makeTab()
    {
        $tabs = array(
            'listSchedule' => array('name' => '开服申请列表', 'checked' => true),
            'addSchedule' => array('name' => '添加新开服申请')
        );
        $this->assign('tabs', $tabs);
    }
    
    private function show(){
        
        $this->makeTab();
        $where = $this->getWhereTime('open_time','');
        
        $kw = Jec::getVar('kw');
        $gs_id = Jec::getVar('gs_id');
        $gp_id = Jec::getVar('gp_id');
        
        if(!empty($gp_id)){
           $gp_name = $this->db->getOne("select name from game_platforms where gp_id = ".$gp_id);
            $where .= " and platform = ".$gp_name;
        }
        
        if(!empty($gs_id)){
           $platform_srv_cn = $this->db->getOne("select name from game_servers where gs_id = ".$gs_id);
           $where .= " and platform_srv_cn = '".$platform_srv_cn."'";
        }
        
        if(!empty($kw['platform_srv_cn'])){
           $where .= " and platform_srv_cn like '%".$kw['platform_srv_cn']."%'";
        }
        
        if(!empty($kw['status'])){
           $where .= " and status = ".$kw['status'];
        }
        
        //判断操作类型 超级管理员(审批,拒绝,编辑);用户(编辑,删除)
        if(!$this->is_admin()){
            $platform_limit_name = $this->db->getOne("select name from game_platforms where gp_id = ".$_SESSION['platform_limit'][0]);
            $where .= " and platform = ".$platform_limit_name;
            $this->assign('permission',1);
        }
        
        
        
        $pager = new Pager();
        $pager->setTotalRows($this->db->getOne("select count(*) from server_schedule where ".$where));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        
        $list = $this->db->getAll("select * from server_schedule where ".$where." order by id desc limit $offset,$limit");
        
        $this->assign('kw',$kw);
        $this->assign('list',$list);
        $this->assign('page', $pager->render());
        $this->display();
    }
    
    /**
     * 函数audit,实现审批开服区申请操作
     * @param int $id 被审批开服区申请id
     */
    private function audit($id){
              
        if(!$this->is_admin() || empty($id)){
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg',$msg);
            $this->assign('tpye',1);
            return ;
        }
        
        $u= array();
        $u['status'] = 2;
        $msg  = array();
        $get_addToServerList = $kw = Jec::getVar('addToServerList');
        $get_status = $kw = Jec::getVar('status');
        
        if($this->db->update('server_schedule', $u, array('id'=>$id))){
            //判断是否需要插入game_servers表
            if($get_addToServerList == 1 && isset($get_status)){
                $servers = $this->db->getAll("select a.platform_srv_cn,a.open_time,b.gp_id from server_schedule as a , game_platforms as b where a.platform = b.name and a.id = {$id}");
                $game_servers_data = array();
                $game_servers_data['gp_id'] = $servers[0]['gp_id'];
                $game_servers_data['name'] = $servers[0]['platform_srv_cn'];
                $game_servers_data['open_time'] = $servers[0]['open_time'];
                $game_servers_data['status'] = $_GET['status'];
                
                //插入game_servers表
                if(($this->db->insert('game_servers', $game_servers_data)) > 0)
                {
                    $msg['msg'] = '审批成功！';
                }else{
                    $msg['error'] = 1;
                    $msg['msg'] = '审批成功，但插入服务器列表失败！';
                }
            }
        }else{
            $msg['error'] = 1;
            $msg['msg'] = '审批不成功！';
        }
        
        
        $this->assign('msg',$msg);
        $this->assign('tpye',1);
    }
    
    /**
     * 函数reject,实现拒绝开服区申请操作
     * @param int $id 被拒绝开服区申请id
     */
    private function reject($id){
        
        if(!$this->is_admin() || empty($id)){
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg',$msg);
            $this->assign('tpye',1);
            return ;
        }
        //判断是否已拒绝
        if($this->db->getOne("select status from server_schedule where id = ".$id) == 3){
            $msg['error'] = 1;
            $msg['msg'] = '拒绝不成功！';
            $this->assign('msg',$msg);
            $this->assign('tpye',1);
            return;
        }
        
        //获取拒绝理由
        $memo = Jec::getVar('memo');
        
        $u= array();
        $u['status'] = 3;
        $u['memo'] = $memo;
        
        $msg  = array();
        if($this->db->update('server_schedule', $u, array('id'=>$id))){
            $msg['msg'] = '拒绝成功！';
        }else{
            $msg['error'] = 1;
            $msg['msg'] = '拒绝不成功！';
        }
        
        $this->assign('msg',$msg);
        $this->assign('tpye',1);
    }
    
    //添加操作
    /**
    * 函数add,实现添加开服区申请操作
    */
    private function add(){
        //检查数据格式
        $fields = array(
                'platform_num' => 'int',
                'platform' => '',
                'st' => 'date',
                'name' => '',
            );
        $rep = Jec::getMap('items', $fields);
        
        $u = array();
        $msg = array();
        
        if(!$rep['platform_num'] || !$rep['st'] || !$rep['name']){
            $msg['error'] = 1;
            $msg['msg'] = '提交失败，请填写好每一项！';
            $this->assign('msg',$msg);
            $this->assign('tpye',2);
            return ;
        }
        
        //判断是否存在选择平台标识
        if($rep['platform']>0 || !empty($_SESSION['platform_limit'][0])){
            if(!$this->is_admin()){
                $platform_limit_name = $this->db->getOne("select name from game_platforms where gp_id = ".$_SESSION['platform_limit'][0]);
                $u['platform'] = $platform_limit_name;
                $u['platform_srv_cn'] = $u['platform'].'_'.$rep['platform_num'];
                $u['srv_id'] = 'S' . $rep['platform_num'];
            }else{
                $platform_limit_name = $this->db->getOne("select name from game_platforms where gp_id = ".$rep['platform']);
                $u['platform'] = $platform_limit_name;
                $u['platform_srv_cn'] = $u['platform'].'_'.$rep['platform_num'];
                $u['srv_id'] = 'S' . $rep['platform_num'];
            }
            
            $u['open_time'] = $rep['st'];
            $u['open_time_test'] = date('Y-m-d H:i:s');
            $u['name'] = $rep['name'];
            $u['game_name'] = "梦幻修仙2";
            
            //判断是否已存在开服区
            if($this->db->getOne("select count(platform_srv_cn) from server_schedule where platform_srv_cn = '".$u['platform_srv_cn']."'") > 0 ){
                $msg['error'] = 1;
                $msg['msg'] = '此开服区已申请，请耐心等候结果！';
            }else{
                if($this->db->insert('server_schedule', $u))
                    $msg['msg'] = '提交成功！';
                else{
                    $msg['error'] = 1;
                    $msg['msg'] = '提交不成功！';
                }
            }
        }else{
            $msg['error'] = 1;
            $msg['msg'] = '请先选择平台！';
        }
        
        $this->assign('msg',$msg);
        $this->assign('tpye',2);
    }

    /**
    * 函数delete,实现删除开服区申请操作
    * @param int $id 被删除开服区申请id
    */
    private function delete($id){
        
        if($this->is_admin() || empty($id)){
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg',$msg);
            $this->assign('tpye',1);
            return;
        }
        
        $msg  = array();
        
        if($this->db->delete('server_schedule', array('id'=>$id))){
            $msg['msg'] = '删除成功！';
        }else{
            $msg['error'] = 1;
            $msg['msg'] = '删除不成功！';
        }
            
        $this->assign('msg',$msg);
        $this->assign('tpye',1);
    }
    
    /**
    * 函数edit,实现编辑开服区申请操作
    * @param int $id 被编辑开服区申请id
    */
    private function edit($id){
        
        if (isset($_POST['submit']) && !empty($id)){
            
            $fields = array();
            $u = array();
            $msg  = array();
            
            //检查数据格式
            $fields = array(
                'platform_num' => 'int',
                'platform' => '',
                'st' => 'date',
                'name' => '',
            );
            
            $rep = Jec::getMap('items', $fields);
            
            if(!$rep['platform_num'] || !$rep['st'] || !$rep['name']){
                $msg['error'] = 1;
                $msg['msg'] = '编辑失败，请填写好每一项！';
                $this->assign('msg',$msg);
                $this->assign('tpye',1);
                return;
            }
            
            $u['srv_id'] = 'S'.$rep['platform_num'];
            $u['open_time'] = $rep['st'];
            $u['name'] = $rep['name'];
            
            if($this->db->update('server_schedule', $u, array('id'=>$id))){
                $msg['msg'] = '编辑成功！';
            }else{
                $msg['error'] = 1;
                $msg['msg'] = '编辑不成功！';
            }
            
        }else{
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
        }
        
        $this->assign('msg',$msg);
        $this->assign('tpye',1);
    }
    
    /**
    * 函数showEdit,显示编辑开服区申请页面
    * @param int $id 被显示需要编辑开服区申请id
    */
    private function showEdit($id){
        
        if(empty($id)){
            $msg['error'] = 1;
            $msg['msg'] = '操作错误！';
            $this->assign('msg',$msg);
            $this->assign('tpye',1);
            return;
        }
            
        $plan_list = $this->db->getAll("select * from server_schedule where id = ".$id);

        if(!empty($plan_list)){
            $plan_list[0]['srv_id'] = ltrim($plan_list[0]['srv_id'],'S');
            $this->assign('plan_list',$plan_list);
            $this->display('SMP/Server/Views/editSchedule.html');
        }else{
            $msg['error'] = 1;
            $msg['msg'] = '所选编辑对象有误！';
            
            $this->assign('msg',$msg);
            $this->assign('tpye',1);
            
        }
    }
    
    /**
    * 函数is_admin,判断是否超级管理员
    * @return bool
    */
    private function is_admin(){
        return $this->can('SMP_Server_IsAdmin');
    }


}