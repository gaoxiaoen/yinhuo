<?php
/**
 * User:
 * Date: 12-8-17
 *
 */
 
class SMP_Player_MailList extends AdminController
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
        $mkey = Jec::getVar('mkey');
        $this->db_game->delete('mail',array('mkey'=>$mkey));
    }
    
    private function show(){
        $where = ' 1';
        $nickname = Jec::getVar('kw_rname');
        if($nickname){
            $pkey = $this->_get_player_key($nickname);
            $where .= " and pkey=$pkey";
        }
        $pkey = Jec::getVar('kw_pkey');
        if($pkey){
            $pkey = $pkey;
            $where .= " and pkey=$pkey";
        }
		$status = Jec::getVar('kw_status');		

		if(is_numeric($status)==FALSE or $status==99) 
		{
			$where .= " and state not in (3,4)";
			$status = 99;
		}
		else
		{
			$where .= " and state = $status";
		}

        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(mkey) from mail where $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select * from mail where $where order by time desc limit $offset,$limit");
        global $GMailState;
        foreach($data as &$d){
            $d['nickname'] = get_player_name($d['pkey']);
            $d['goodslist'] = format_goods_list($d['goodslist']);
			$d['state'] = $GMailState[$d['state']];	
        }
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
		$this->assign('kw_status',$status);
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