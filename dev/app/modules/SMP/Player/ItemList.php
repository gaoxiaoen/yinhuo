<?php
/**
 * User:
 * Date: 12-8-17
 *
 */
 
class SMP_Player_ItemList extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '角色物品查看');
        $act = Jec::getVar('act');
        if($act == 'del')
            $this->del();

        $this->show();
    }

    private function del(){
        $gkey = Jec::getVar('gkey');
        $this->db_game->delete('goods_item',array('ikey'=>$gkey));
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
            $where .= " and item_id={$params['gkey']}";
        }
        $params['pos'] = Jec::getInt('kw_itempos');
        if($params['nickname'] && $params['pos']){
            $where .= " and location = {$params['pos']}";
        }

        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(ikey) from goods_item where $where and lossflag=0"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select * from goods_item where $where and lossflag=0 order by create_time desc limit $offset,$limit");
        global $Ggoods;
        $consumeTypeData = $this->db_game->getAll("select * from consume_type");
        $consume_type1 = array();
        foreach ($consumeTypeData as $val) {
            $consume_type1[0] = '系统';
            $consume_type1[$val['id']] = $val['name'];
        }
        foreach($data as &$val){
            $val['nickname'] = get_player_name($val['pkey']);
            $val['goodsname'] = $Ggoods[$val['item_id']];
            $val['res'] = $consume_type1[$val['res']];
            $val['create_time'] = getDateStr($val['create_time']);
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