<?php
/**
 * User: jecelyin 
 * Date: 12-2-20
 * Time: 下午5:35
 *
 */
 
class SMP_User_Edit extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if(isset($_GET['show_id']))
        {
            $this->show(Jec::getInt('show_id'));
        }elseif($_GET['del_id']){
            $this->delete();
        }elseif(isset($_POST['edit_id'])){
            $this->edit();
        }

    }

    private function show($id)
    {
        $this->assign('title', $id > 0 ? '修改用户信息' : '新增用户');
        if($id > 0)
        {
            $user = $this->db->getRow("select * from users where id={$id}");
            $user['group'] = $user['group'] ? explode(',', $user['group']) : array();
        }else{
            $user = array();
            $user['group'] = array();
        }

        $group_where = $_SESSION['user_group_id'] ? "id='{$_SESSION['user_group_id']}'" : '1';
        $user_groups = $this->db->getAll("select id,name from user_groups where $group_where OR `public`=1");
        $groupList = $this->gameHelper->getCustomGroups();
        $this->assign(array(
            'groupList' => $groupList,
            'show_id' => $id,
        ));
        $this->assign('user_groups', $user_groups);
        $this->assign('user', $user);
        $this->display();
    }

    private function delete()
    {
        $id = Jec::getInt('del_id');
        if($id < 1)
            throw new JecException('非法提交');
        $this->db->delete('users', array('id'=>$id));
        Net::redirect('?m=SMP_User_List');
    }

    private function edit()
    {
        $id = Jec::getInt('edit_id');

        $req = Jec::getVar('u');
        
        $u = array();
        $u['login_name'] = $req['login_name'];
        $u['nickname'] = $req['nickname'];
        $u['passwd'] = $req['passwd'];
        $u['user_group_id'] = (int)$req['user_group_id'];
        if(!$u['login_name'] || !$u['nickname'])
            throw new JecException('非法提交！');

        if($_SESSION['user_group_id'] && $u['user_group_id']!=$_SESSION['user_group_id'] && !$this->db->getOne("select id from user_groups where id={$u['user_group_id']} and `public`=1"))
            throw new JecException('非法更改用户组！');


        if($id < 1 && !$u['passwd'])
            throw new JecException('密码为空！');
        //加密密码
        if($u['passwd'])
        {
            $u['passwd'] = Security::password($u['passwd']);
        }else{
            unset($u['passwd']);
        }

//        $u['group'] = $req['group'] ? implode(',',$req['group']):'';
//        if($_SESSION['group'] && $u['group'] != $_SESSION['group'] && !$this->db->getOne("select gp_id from group_custom where gp_id={$u['group']}"))
//            throw new JecException("非法更改权限组");

        $msg  = array();
        if($id > 0)
        {
            if($this->db->getOne("select id from users where id!=$id and login_name='{$u['login_name']}'"))
            {
                $msg['error'] = 1;
                $msg['msg'] = '已经存在相同的登录名称';
            }else{
                if($this->db->update('users', $u, array('id'=>$id)))
                    $msg['msg'] = '修改成功!';
                else
                    $msg['msg'] = '没有任何改动!';
            }

        }else{
            if($this->db->getOne("select id from users where login_name='{$u['login_name']}'"))
            {
                $msg['error'] = 1;
                $msg['msg'] = '已经存在相同的登录名称';
            }else{
                $u['creater_login_name'] = $_SESSION['login_name'];
                $u['login_num'] = 0;
                if($this->db->insert('users', $u))
                    $msg['msg'] = '添加成功！';
                else{
                    $msg['error'] = 1;
                    $msg['msg'] = '貌似添加不成功！';
                }
            }

        }

        $this->assign('msg', $msg);
        $this->show($id);
    }


}