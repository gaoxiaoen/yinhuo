<?php
class SMP_Log_ShopStatistics extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		$this->show();
	}

	public function show()
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
		$where = $this->getWhereTime('time','-30 day',true);
		//$where = ' 1=1 ';
		$total = $this->db_game->getRow('select count(*) as num,sum(total) as total from (select sum(cost_all_money) as total from cron_new_shop_sell where '.$where.' group by concat(shop_type,goods_id)) as t');
		$page = new Pager();
		$page->setTotalRows($total['num']);
		$offset = $page->getOffset();
		$limit  = $page->getLimit();
		$data   = $this->db_game->getAll('select sum(goods_num) as gnum,sum(cost_all_money) as total,one_money,shop_type,goods_id,money_type from cron_new_shop_sell where '.$where. 'group by concat(shop_type,goods_id) order by total desc limit '.$offset.', '.$limit);
		$this->assign('total',$total);
		$this->assign('goods',$goods);
		$this->assign('shop_type',$shop_type);
		$this->assign('money_type',$money_type);
		$this->assign('data',$data);
		$this->assign('page',$page->render());
		$this->assign('title','商店购买统计');
		$this->display();
	}
}