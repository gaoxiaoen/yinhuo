<?php
/**
 * User: qinglin QQ:474778220
 * Date: 12-8-20
 * Time: 下午7:00
 *
 */
 
class SMP_User_PasswdEdit extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->assign('title', '修改密码');
        
   		$do = Jec::getVar('do');
        switch($do)
        {
            case 'edit_password':
                $this->passwdedit();
                break;
                
            case 'check_old_password':
                $this->oldpasswdcheck();
                break;
        }
        $this->show();

    }

    private function show()
    {
        //附加信息
        //var_dump($_SESSION);
        $this->display();
    }

    private function passwdedit()
    {
        $id = $_SESSION['id'];
        $req = Jec::getVar('u');
        
        $old_password = Security::password($req['old_passwd']);
        $new_password = $req['passwd'];
        $new_password2 = $req['passwd2'];
        
        $new_password_len = strlen($new_password);
        
        if($id < 1 || empty($new_password_len))
            throw new JecException('密码为空！');

        if($new_password_len < 8)
            throw new JecException('新密码长度必须为8位或以上！');    
            
        if($new_password !== $new_password2)
            throw new JecException('请认真确认新密码！');
        
        $res = $this->db->getOne("select passwd from users where id = {$id}");
        
        if($old_password !== $res){
            throw new JecException('旧密码错误！');
        }else{
        	$u= array();
        	$u['passwd'] = Security::password($new_password);
        	if($this->db->update('users', $u, array('id'=>$id))){
        		//alert('请重新登录'); 
        		Net::redirect('?m=SMP_Auth_Logout');
        	}else{
        		$msg  = array();
        		$msg['error'] = 1;
                $msg['msg'] = '修改密码不成功！';
                $this->assign('msg', $msg);
        	}
        }
    }
    
    private function oldpasswdcheck()
    {
    	$req = Jec::getVar('old_pw');
    	$old_password = Security::password($req);
    	$id = $_SESSION['id'];
    	
    	$res = $this->db->getOne("select count(id) from users where id = {$id} and passwd = '{$old_password}' ");
    	
    	if($res){
    		$arr = array(
    			'status' => 1,
    			'data' => '旧密码检验正确！'
    		);
    	}else{
    		$arr = array(
    			'status' => 0,
    			'data' => '旧密码检验错误！'
    		);
    	}
    	
    	echo $json_string = json_encode($arr);
    	exit();
    }
}