<?php
/**
 * User:
 * Date: 12-8-17
 *
 */
 
class SMP_Player_LightW extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '角色光武查看');
        $act = Jec::getVar('act');
        if($act == 'update')
            $this->update();

        $this->show();
    }

    private function update(){
        $pkey = Jec::getVar('pkey');
        $light_weapon_id = Jec::getVar('light_weapon_id');
        $where = "pkey =$pkey and light_weapon_id = $light_weapon_id";
        $file = Jec::getVar('file');
        $value = Jec::getVar('value');
        Log::info('SMP_Player_LightW',"修改[{$pkey}][$file=$value]");
        $this->db_game->update('light_weapon',array($file=>$value),$where);
    }

    private function show(){
        $where = ' 1';
        $nickname = Jec::getVar('kw_rname');
        if($nickname){
            $pkey = $this->_get_player_key($nickname);
            $where .= " and pkey='$pkey'";
        }
        $pkey = Jec::getVar('kw_pkey');
        if($pkey){
            $where .= " and pkey='$pkey'";
        }


        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from light_weapon where $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select * from light_weapon where $where limit $offset,$limit");

        foreach($data as &$val){
            $val['nickname'] = get_player_name($val['pkey']);
        }
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->display();
    }

    private function _get_player_key($nickname){
        $pkey = $this->db_game->getOne("select pkey from player_state where nickname = '$nickname'");
        if($pkey){
            return $pkey;
        }
        return 0;
    }


    

}