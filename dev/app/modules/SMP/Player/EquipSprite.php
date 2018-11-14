<?php
/**
 * User:
 * Date: 12-8-17
 *
 */
 
class SMP_Player_EquipSprite extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '坐骑装备查看');
        $act = Jec::getVar('act');
        if($act == 'update')
            $this->update();

        $this->show();
    }

    private function update(){
        $gkey = Jec::getVar('gkey');
        $where = "gkey ='$gkey'";
        $file = Jec::getVar('file');
        $value = Jec::getVar('value');
        Log::info('SMP_Player_EquipSprite',"修改[{$gkey}][$file=$value]");
        $this->db_game->update('sprite_goods',array($file=>$value),$where);
    }

    private function show(){
        $where = ' 1';
        $nickname = Jec::getVar('kw_rname');
        if($nickname){
            $pkey = $this->_get_player_key($nickname);
            $where .= " and pkey=$pkey";
        }else{
             $pkey = Jec::getVar('kw_pkey');
             if($pkey){
                $where .= " and pkey=$pkey";
        }}
        $gkey = Jec::getVar('kw_gkey');
        if($gkey){
            $where .= " and gkey=$gkey";
        }

        $goods_id = Jec::getVar('kw_goods_id');
        if($goods_id){
            $where .= " and goods_id=$goods_id";
        }
        
       $pos = Jec::getInt('kw_itempos');
        if($pos&&$pos == 1){
            $where .= " and location = 1";
        }



        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(gkey) from sprite_goods where $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select * from sprite_goods where $where order by createtime desc limit $offset,$limit");
        global $Ggoods;
        $consumeTypeData = $this->db_game->getAll("select * from consume_type");
        $consume_type1 = array();
        foreach ($consumeTypeData as $val) {
            $consume_type1[0] = '系统';
            $consume_type1[$val['id']] = $val['name'];
        }
        foreach($data as &$val){
            $val['nickname'] = get_player_name($val['pkey']);
            $val['goodsname'] = $Ggoods[$val['goods_id']];

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