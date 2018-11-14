<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-9
 * Time: 15:42
 */

class SMP_Log_GoodsCreate extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '物品创建日志');

        $this->show();
    }

    private function show()
    {
        global $Ggoods;
        $goods = $Ggoods;
        unset($Ggoods);
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and m.pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and r.nickname ='{$kw_name}'";
        $kw_goods_id = g(Jec::getVar('kw_goods_id'));
        if($kw_goods_id)
            $where .= " and m.goods_id='{$kw_goods_id}'";

        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_goods_create as m left join player_state as r on m.pkey = r.pkey where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select m.*,r.nickname as nickname from log_goods_create as m left join player_state as r on m.pkey = r.pkey where $time $where order by time desc limit $offset,$limit");
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select m.*,r.nickname as nickname from log_goods_create as m left join player_state as r on m.pkey = r.pkey where $time $where  order by time "),$goods);
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->assign('req_params',['kw_name'=>$kw_name,'kw_key'=>$kw_key,'kw_goods_id'=>$kw_goods_id]);
        $this->assign('goods',$goods);
        $this->display();
    }

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data,$goods)
    {
        foreach ($data as &$d) 
        {
            unset($d['id']);
            $d['goods_name'] = $goods[$d['goods_id']];
            $d['from_memo']  = $this->consume_type[$d['from']];
            $d['time']       = getDateStr($d['time']);
        }
        array_unshift($data, ['pkey','goods_id','num','source_id','time','nickname','goods_name','source_memo']);
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('log_goods_create.csv');
    }

}

