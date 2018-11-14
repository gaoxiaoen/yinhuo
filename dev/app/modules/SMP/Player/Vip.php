<?php
/**
 * User:
 * Date: 12-8-17
 *
 */

class SMP_Player_Vip extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '角色VIP查看');
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
        Log::info('SMP_Player_Vip',"修改[{$pkey}][$file=$value]");
        $this->db_game->update('player_vip',array($file=>$value),$where);
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
        $pager->setTotalRows($this->db_game->getOne("select count(*) from player_vip where $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select pv.*,ps.nickname from player_vip as pv left join player_state as ps on pv.pkey = ps.pkey where $where limit $offset,$limit");

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