<?php
/**
 * User:
 * Date: 12-8-17
 *
 */

class SMP_Player_MagicWeapon extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '角色翅膀查看');
        $act = Jec::getVar('act');
        if($act == 'update')
            $this->update();
        $this->show();
    }

    private function update(){
        $pkey = Jec::getVar('pkey');
        $where = "pkey =$pkey";
        $file = Jec::getVar('file');
        $value = Jec::getVar('value');
        Log::info('SMP_Player_magic_weapon',"修改[{$pkey}][$file=$value]");
        $this->db_game->update('player_magic_weapon',array($file=>$value),$where);
    }

    private function show(){
        $where = ' 1';
        $nickname = Jec::getVar('kw_rname','POST');
        if($nickname){
            $pkey = $this->_get_player_key($nickname);
            $where .= " and pkey='$pkey'";
        }
        $pkey = Jec::getVar('kw_gkey','POST');
        if($pkey){
            $where .= " and pkey='$pkey'";
        }

        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from player_magic_weapon where $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from player_magic_weapon where $where limit $offset,$limit");

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