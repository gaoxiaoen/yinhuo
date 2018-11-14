<?php
/**
 * User: jecelyin 
 * Date: 12-2-20
 * Time: 下午5:35
 *
 */
 
class SMP_User_GroupEdit extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if(isset($_GET['show_id']))
        {
            $this->show((int)Jec::getInt('show_id'));
        }elseif($_GET['del_id']){
            $this->delete();
        }elseif(isset($_POST['edit_id'])){
            $this->edit();
        }

    }

    private function show($id)
    {
        $this->assign('title', $id > 0 ? '修改用户组' : '新增用户组');
        if($id > 0)
        {
            $group = $this->db->getRow("select * from user_groups where id={$id}");
            $group['permissions'] = $group['permissions'] ? explode(',', $group['permissions']) : array();
        }else{
            $group = array();
            $group['permissions'] = array();
        }

        $menusP = SMP_Menu_Helper::getAllModule();

        $this->assign(array(
            'group' => $group,
            'menusP' => $menusP,
            'show_id' => $id,
        ));

        $this->display();
    }

    private function delete()
    {
        $id = Jec::getInt('del_id');
        if($id < 1)
            throw new JecException('非法提交');
        $this->db->delete('user_groups', array('id'=>$id));
        Net::redirect('?m=SMP_User_GroupList');
    }

    private function edit()
    {
        $id = Jec::getInt('edit_id');
        $perms = Jec::getVar('perms');
        if(!is_array($perms))$perms=array();
        foreach($perms as $mod)
        {
            if(!preg_match('#^SMP_[A-Z][\w]+_[A-Z][\w]+$#', $mod))
            {
                throw new JecException('非法的：'.$mod);
            }
        }

        $u = array();
        $u['name'] = Jec::getVar('name');
        $u['permissions'] = implode(',',$perms);
        $u['public'] = Jec::getInt('public') ? 1 : 0;

        if(!$u['name'])
            throw new JecException('非法提交');

        $msg  = array();
        if($id > 0)
        {
            if($this->db->getOne("select id from user_groups where id!=$id and `name`='{$u['name']}'"))
            {
                $msg['error'] = 1;
                $msg['msg'] = '已经存在相同的组名称';
            }else{
                if($this->db->update('user_groups', $u, array('id'=>$id)))
                    $msg['msg'] = '修改成功!';
                else
                    $msg['msg'] = '没有任何改动!';
            }

        }else{
            if($this->db->getOne("select id from user_groups where `name`='{$u['name']}'"))
            {
                $msg['error'] = 1;
                $msg['msg'] = '已经存在相同的登录名称';
            }else{
                if($this->db->insert('user_groups', $u))
                    $msg['msg'] = '添加成功！';
                else{
                    $msg['error'] = 1;
                    $msg['msg'] = '貌似没添加成功！';
                }
            }

        }

        $this->assign('msg', $msg);
        $this->show($id);
    }


}