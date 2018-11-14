<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 0:18
 */
class SMP_Log_CronNewShop extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '商城购买统计');
        $this->show();
    }

    private function show()
    {
        global $GMoneyType;
        $money_type = $GMoneyType;
        unset($GMoneyType);
        global $GShopType;
        $shop_type = $GShopType;
        unset($GShopType);
        global $Ggoods;
        $goods = $Ggoods;
        unset($Ggoods);
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and pname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from cron_new_shop_sell where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $columns = ['pkey,pname','shop_type','money_type','goods_id','goods_num','one_money','cost_all_money','discount','time'];
        $data = $this->db_game->getAll("select ".implode(',', $columns)." from cron_new_shop_sell  where $time $where order by time desc limit $offset,$limit");
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select ".implode(',', $columns)." from cron_new_shop_sell  where $time $where order by time "),$money_type,$shop_type,$goods);
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->assign('goods',$goods);
        $this->assign('shop_type',$shop_type);
        $this->assign('money_type',$money_type);
        $this->assign('req_params',['kw_key'=>$kw_key,'kw_name'=>$kw_name]);
        $this->display();
    }

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data,$money_type,$shop_type,$goods)
    {
        foreach ($data as &$d) 
        {
            $d['money_type'] = $money_type[$d['money_type']];
            $d['shop_type']  = $shop_type[$d['shop_type']];
            $d['goods_name'] = $goods[$d['goods_id']];
            $d['discount']   = $d['discount'].'%';
            $d['time']       = getDateStr($d['time']);
        }
        array_unshift($data, ['pkey','nickname','shop_type','money_type','goods_id','goods_num','price','total_cost','discount','time','goods_name']);
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('cron_new_shop_sell.csv');
    }

}

