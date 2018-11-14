<?php
/**
 * User:
 * Date: 12-8-17
 *
 */
 
class SMP_Player_HeroList extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '角色装备查看');
        $act = Jec::getVar('act');
        if($act == 'del')
            $this->del();

        $this->show();
    }

    private function del(){
        $gkey = Jec::getVar('gkey');
        $this->db_game->delete('goods_equip',array('ekey'=>$gkey));
    }
    
    private function show(){
        global $GPosName;
        $where = ' 1';
        $params['nickname'] = Jec::getVar('kw_rname');
        if($params['nickname']){
            $pkey = $this->_get_player_key($params['nickname']);
            $where .= " and pkey=$pkey";
        }
        $params['gkey'] = Jec::getVar('kw_gkey');
        if($params['gkey']){
            $where .= " and hero_id={$params['gkey']}";
        }
        $params['pos'] = Jec::getInt('kw_itempos');
        if($params['nickname'] && $params['pos']){
            $where .= " and location = {$params['pos']}";
        }

        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(hkey) from player_hero where $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select * from player_hero where $where order by time desc limit $offset,$limit");
        global $Ggoods;
        foreach($data as &$val){
            $val['nickname'] = get_player_name($val['pkey']);
            $val['res'] = $this->consume_type[$val['res']];
            $val['time'] = getDateStr($val['time']);
        }
        $this->assign('locatename',$GPosName);
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->assign('params',$params);
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