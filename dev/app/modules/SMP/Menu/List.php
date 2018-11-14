<?php
/**
 * User: jecelyin 
 * Date: 12-2-13
 * Time: 下午7:39
 *
 */
 
class SMP_Menu_List extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '菜单管理');

        if(Jec::getInt('del_id')>0) $this->delete();
        elseif(Jec::getInt('hide_id')>0) $this->hide();
        elseif(Jec::getInt('show_id')>0) $this->show();
        elseif(Jec::getVar('url')) $this->import();
        else $this->edit();
        $this->view();
    }

    private function delete()
    {
        $id = Jec::getInt('del_id');
        if($id < 1)
            return false;
        $this->db->delete("menus", "id={$id} OR pid={$id}");
        SMP_Menu_Helper::clearCache();
        return true;
    }

    private function hide()
    {
        $id = Jec::getInt('hide_id');
        if($id < 1)
            return false;
        $this->db->update("menus",array("hide"=>1),array("id"=>$id));
        SMP_Menu_Helper::clearCache();
        return true;
    }
    
    private function show()
    {
        $id = Jec::getInt('show_id');
        if($id < 1)
            return false;
        $this->db->update("menus",array("hide"=>0),array("id"=>$id));
        SMP_Menu_Helper::clearCache();
        return true;
    }

    private function validModule($name)
    {
        if($this->isValidModuleName($name))
            return $name;

        throw new JecException('无效的Module名称：'.$name);
    }

    private function edit()
    {
        if(!$_POST)
            return;
        $msg = array();
        $parent = Jec::getVar('parent');
        if($parent['name'])
        {
            $ins = array();
            $ins['pid'] = 0; //父级菜单
            $ins['module'] = '';
            $ins['name'] = $parent['name'];
            $ins['sort'] = (int)$parent['sort'];
            $this->db->insert('menus', $ins);
            $msg['msg'][0] = '添加父级菜单成功！';
        }
        //添加子菜单
        $sub = Jec::getVar('submenu');
        if($sub)
        {
            foreach($sub as $pid => $m)
            {
                if(!$m['name'] || !$pid || !$m['module'])continue;
                $ins = array();
                $ins['pid'] = $pid;
                $ins['name'] = $m['name'];
                $ins['module'] = $this->validModule($m['module']);
                $ins['sort'] = (int)$m['sort'];
                $this->db->insert('menus', $ins);
                $msg['msg'][1] = '添加子菜单成功！';
            }
        }
        //清理缓存
        SMP_Menu_Helper::clearCache();
        //修改菜单
        $menu = Jec::getVar('menu');
        if($menu)
        {
            foreach($menu as $id => $m)
            {
                $u = array();
                $u['pid'] = (int)$m['pid'];
                $u['name'] = $m['name'];
                $u['module'] = $this->validModule($m['module']);
                $u['sort'] = (int)$m['sort'];
                $this->db->update('menus', $u, array('id'=>$id));
                $msg['msg'][2] = '更新菜单成功！';
            }

        }
        $this->assign('msg', $msg);
    }

    public function view()
    {
        $this->display();
    }

}