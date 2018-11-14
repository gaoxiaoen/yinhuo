<?php
/**
* 单个玩家信息汇总类 
*/
class SMP_Player_Info extends AdminController
{
	
	public function __construct()
	{
		parent::__construct();
		$this->assign('title','玩家信息查询');
		$act = Jec::getVar('act');
		if ($act == 'update') {
			$this->update();
		}else if($act == 'change_account'){
			$this->change_account();
		}else{
			$this->show();
		}
	}

	public function show()
	{
		global $GCareer;
		$pkey = Jec::getInt('pkey');
		$where0 = ' where '.$this->getWhereTime('time','-7 day',true,'daily');
		$where = ' where '.$this->getWhereTime('time','-7 day',true,'daily');
		$tabs = [
			0=>['type'=>1,'name'=>'玩家信息'],
			1=>['type'=>2,'name'=>'登录日志'],
			2=>['type'=>3,'name'=>'物品日志'],
			3=>['type'=>4,'name'=>'货币日志'],
//			4=>['type'=>5,'name'=>'充值日志'],
//			5=>['type'=>6,'name'=>'交易所日志'],
			6=>['type'=>7,'name'=>'修改操作']
		];
		$sideColumn = [
			0=>['type'=>1,'name'=>'玩家个人信息'],
//			1=>['type'=>2,'name'=>'查看宠物信息'],
//			2=>['type'=>3,'name'=>'查看技能信息'],
//			3=>['type'=>4,'name'=>'查看时装信息'],
			4=>['type'=>5,'name'=>'查看坐骑信息'],
//			5=>['type'=>6,'name'=>'查看称号信息'],
			6=>['type'=>7,'name'=>'查看玩家物品'],
			7=>['type'=>8,'name'=>'查看装备信息'],
			8=>['type'=>9,'name'=>'查看英雄信息'],
		];
		$params['tab_type'] = Jec::getInt('tab_type');
		if(!$params['tab_type']) $params['tab_type'] = 1;
		$params['side_type'] = Jec::getInt('side_type');
		if(!$params['side_type']) $params['side_type'] = 1;
		$params['pkey'] = $pkey ?  $pkey : '';
		$page = new Pager();
		$limit = $page->getLimit();
		$offset = $page->getOffset();
		if($pkey) $where .= ' and pkey = '.$pkey;
		switch ($params['tab_type']) {
			case '1':
				if(!$pkey) new JecException('缺少参数...');
				$where = 'where pkey = '.$pkey;
				switch ($params['side_type']) {
					case '1':
						$player_column = ['pkey'=>'玩家ID','nickname'=>'昵称','accname'=>'账号','online'=>'是否在线','guild_name'=>'公会',
											'lv'=>'等级','realm'=>"魅力值",'career'=>'职业','status'=>'封号状态','cbp'=>'战斗力','max_cbp'=>'历史最高战力',
											'silent'=>'禁言状态','exp'=>'经验','qtimes'=>'快速作战次数','likes'=>'点赞数','petid'=>'坐骑id',	
												#0-14
							'reg_time'=>'注册时间','reg_ip'=>'注册IP','last_login_time'=>'上次登录时间','logout_time'=>'上次下线时间',
									'last_login_ip'=>'上次登录IP','location'=>'IP所在地',	#29-34
							'gold'=>'水晶','coin'=>'金币','explore'=>'探索值',	#35-38
							'frip'=> '友情点','total_online_time'=>'总在线时长(小时)','login_days'=>'登录天数'
								  ];

						$sqlparams = "ps.pkey,ps.nickname,ps.lv,ps.career,ps.sex,ps.realm,ps.gold,ps.coin,
							ps.exp,ps.explore,ps.scene,ps.point_list,ps.cbp,ps.max_cbp,
							ps.frip,ps.guild_name,ps.likes,ps.qtimes,ps.petid,					
							pl.sn,pl.accname,
							pl.reg_time,pl.reg_ip,pl.last_login_time,pl.last_login_ip,
							pl.location,pl.logout_time,pl.total_online_time,pl.status,
							pl.game_channel_id,pl.login_days,pl.silent,pl.online";

						$info = $this->db_game->getRow("select $sqlparams 
							from player_login pl left join player_state ps on pl.pkey = ps.pkey where pl.pkey = $pkey");

						#	获取充值金额
//						$charge = (int) $this->db_game->getOne("select sum(total_fee) from recharge where app_role_id = $pkey");
//						$charge = 0;
//						$info['total_charge_money'] = $charge > 0 ? $charge / 100 : 0 ;
						break;
//					case '2':
//						$second_column = [0=>['type'=>1,'name'=>'宠物信息'],1=>['type'=>'2','name'=>'宠物增删日志'],2=>['type'=>'3','name'=>'妖灵信息']];
//						$player_column[1] = ['pkey','pet_key','昵称','阶数','星级','星级经验','状态','战力','创建时间'];
//						$player_column_val[1] = ['pkey','pet_key','name','stage','star','star_exp','state','cbp','time'];
//						$info[1] = $this->db_game->getAll("select pkey,pet_key,name,stage,star,star_exp,state,cbp,time from pet $where");
//						$player_column[2] = ['记录ID','pkey','昵称','类型','petkey','宠物名称','时间'];
//						$player_column_val[2] = ['id','pkey','nickname','type','petkey','pname','time'];
//						$info[2] = $this->db_game->getAll("select id,pkey,nickname,type,petkey,pname,time from log_pet $where");
//						foreach($info[2] as &$v){
//							$v['type'] = $v['type'] == 1 ? '增加' : '删除';
//						}
//						$player_column[3] = ['pkey','weapon_id','阶数','经验','祝福CD','战力','装备列表','属性列表','灵脉列表'];
//						$player_column_val[3] = ['pkey','weapon_id','stage','exp','bless_cd','cbp','equip_list','attribute'];
//						$info[3] = $this->db_game->getAll("select pkey,weapon_id,stage,exp,bless_cd,cbp,equip_list,attribute from pet_weapon $where");
						break;
					case '3':
						$second_column = [0=>['type'=>1,'name'=>'技能'],1=>['type'=>'2','name'=>'公会技能']];
						$player_column[1] = ['pkey','技能列表','被动技能列表'];
						$player_column_val[1] = ['pkey','skill_battle_list','skill_passive_list'];
						$info[1] = $this->db_game->getAll("select * from player_skill $where");
						$player_column[2] = ['pkey','技能列表'];
						$player_column_val[2] = ['pkey','skill_list'];
						$info[2] = $this->db_game->getAll("select * from guild_skill $where");
						break;
//					case '4':
//						$second_column = [0=>['type'=>'1','name'=>'时装信息'],1=>['type'=>'2','name'=>'收到/送出']];
//						$player_column[1] = ['pkey','时装列表'];
//						$player_column_val[1] = ['pkey','fashion_list'];
//						$info[1] = $this->db_game->getAll("select * from fashion $where");
//						$player_column[2] = ['记录ID','赠送者','收到者','时装名称','赠送时间'];
//						$player_column_val[2] = ['id','nickname1','nickname2','fashion_name','present_time'];
////						$info[2] = $this->db_game->getAll("select id,nickname1,nickname2,fashion_name,present_time from log_present_fashion where pkey1 = $pkey or pkey2 = $pkey");
////						foreach ($info[2] as &$value) {
////							$value['present_time'] = date('Y-m-d H:i:s',$value['present_time']);
////						}
//						break;
					case '5':
						$player_column[1] = ['玩家ID','宠物key','宠物ID','星级','强化等级','骑乘状态','战力','技能列表','属性列表','生成时间','来源'];	
						$player_column_val[1] = ['pkey','petkey','petid','star','stren','state','cbp','skill_list','attribute','time','res'];
						$info[1] = $this->db_game->getAll("select * from player_pet $where");
						foreach($info[1] as &$e)
						{
							if($e['state'] ==1)
							{
								$e['state'] = '已骑';
							}
							else
							{
								$e['state'] = '未骑';
							}
							$e['res'] = $this->consume_type[$e['res']];						
						}
						break;
//					case '6':
//						$second_column = [0=>['type'=>'1','name'=>'称号信息'],1=>['type'=>'2','name'=>'结婚称号']];
//						$player_column[1] = ['pkey','称号列表'];
//						$player_column_val[1] = ['pkey','designation_list'];
//						$info[1] = $this->db_game->getAll("select * from designation $where");
//						$player_column[2] = ['pkey','已获取称号列表'];
//						$player_column_val[2] = ['pkey','designation'];
//						//$info[2] = $this->db_game->getAll("select * from player_marry_designation $where");
//						break;
					case '7':
						global $Ggoods;global $GPosName;
						$player_column[1] = ['ikey','物品id','名称','数量','位置','创建时间','失效时间','宝石镶嵌装备key','符文装备英雄key','符文强化等级','随机属性'];	
						$player_column_val[1] = ['ikey','item_id','item_name','item_num','location','create_time','expire_time','gemequipkey','sealherokey','seallv','rattr_list'];
						$page->setTotalRows($this->db_game->getOne("select count(*) from goods_item where lossflag = 0  and pkey = $pkey"));
						$info[1] = $this->db_game->getAll("select ikey,item_id,item_num,location,create_time,expire_time,gemequipkey,sealherokey,seallv,rattr_list 
									from goods_item where lossflag = 0 and pkey = $pkey limit $offset,$limit");
						foreach ($info[1] as &$v) {
							 $v['location'] = $GPosName[$v['location']];
						     $v['item_name'] = $Ggoods[$v['item_id']] ? $Ggoods[$v['item_id']] : '未知';
						     $v['create_time'] = $v['create_time'] !=0 ?date('Y-m-d H:i:s',$v['create_time']):0;
							 $v['expire_time'] = $v['expire_time'] !=0 ?date('Y-m-d H:i:s',$v['expire_time']):'不限期';
						}
						break;
					case '8':
						global $Ggoods;
						$player_column[1] = ['ekey','装备id','装备名','装备英雄key','强化等级'];	
						$player_column_val[1] = ['ekey','equip_id','name','wearherokey','equip_lv'];
						$info[1] = $this->db_game->getAll("select ekey,equip_id,wearherokey,equip_lv from goods_equip where lossflag = 0 and pkey = $pkey");
						foreach ($info[1] as &$v) {
						     $v['name'] = $Ggoods[$v['equip_id']] ? $Ggoods[$v['equip_id']] : '未知';
						}
						break;
					case '9':
						$player_column[1] = ['hkey','英雄id','等级','经验','星级','强化等级','战力','技能点','装备信息','符文信息','技能列表','生成时间','来源'];	
						$player_column_val[1] = ['hkey','heroid','lv','exp','star','stren','cbp','spoint','equipinfo_list','sealinfo_list','skill_list','time','res'];
						$info[1] = $this->db_game->getAll("select * from player_hero where pkey = $pkey");
						foreach ($info[1] as &$v) {
							$v['res'] = $this->consume_type[$v['res']];	
						}
						break;
				}
				$this->assign('second_column',$second_column);
				$this->assign('player_column_val',$player_column_val);
				break;

			case '2':
						$player_column = ['记录ID','pkey','昵称','日期','登录次数','登录时长(秒)'];
						$page->setTotalRows($this->db_game->getOne('select count(*) from log_login '.$where));
						$info = $this->db_game->getAll('select * from log_login '.$where.' limit '.$offset.', '.$limit);
						break;
			case '3':
						$goods_id = Jec::getVar('goods_id') ? Jec::getInt('goods_id') : '';
						if($goods_id) {
							$where0 .= ' and goods_id = '.$goods_id;
							$where  .= ' and goods_id = '.$goods_id;
							$params['goods_id'] = $goods_id;
						}
						global $GGoodUseReason;
						$player_column = ['记录ID','pkey','昵称','物品ID','物品名称','操作类型','消费点','数量','时间'];
						$arr = [];
						$arr = $this->db_game->getAll("select 1 as type,g.id, g.pkey,ps.nickname,g.goods_id,g.num,g.res,g.time from log_goods_add g left join player_state ps on g.pkey = ps.pkey $where0 and g.pkey = $pkey ");
						$arr = array_merge($arr,$this->db_game->getAll('select * from log_goods_subtract '.$where));
						$page->setTotalRows(count($arr));
						$info = array_slice($arr, $offset,$limit);
						$this->assign('GGoodUseReason',$GGoodUseReason);
						break;
			case '4':
						$player_column = ['记录ID','pkey','昵称','类型','消费点','原金额','变动','余额','时间'];
						$arr = [];
//						$arr = $this->db_game->getAll('select if(newgold-oldgold > 0,0,1) as type,id,pkey,nickname,addreason,`desc`,oldgold,newgold,oldbgold,newbgold,addgold as `change`,time from log_gold '.$where);
						$arr = $this->db_game->getAll('select 0 as type,id,pkey,nickname,addreason,`desc`,oldgold,newgold,oldbgold,newbgold,addgold as `change`,time from log_gold '.$where);
						
						$arr = array_merge($arr,$this->db_game->getAll('select 2 as type,id,pkey,nickname,addreason,`desc`,oldcoin,newcoin,addcoin as `change`,time from log_coin '.$where));
						$page->setTotalRows(count($arr));
						$info = array_slice($arr, $offset,$limit);
						$this->assign('currency_type',[0=>'水晶',1=>'水晶',2=>'金币']);
						break;
			case '5':
						if($pos = strpos($where,'pkey')){
							$where = substr_replace($where, 'app_role_id', $pos,strlen('pkey'));
						}
						$player_column = ['记录ID','渠道','游戏包','服区','pkey','昵称','君海订单','CP订单','充值金额','物品类型','获得钻石','时间'];
						$page->setTotalRows($this->db_game->getOne("select count(*) from recharge $where"));
						$info = $this->db_game->getAll("select * from recharge $where limit $offset,$limit");
						break;

//			case '6':
//						global $Ggoods;
//						$player_column = ['记录ID','pkey','昵称','类型','商品','数量','价格','时间'];
//						$page->setTotalRows($this->db_game->getOne('select count(*) from log_market '.$where));
//						$info = $this->db_game->getAll('select * from log_market '.$where.' limit '.$offset.', '.$limit);
//						$this->assign('Ggoods',$Ggoods);
//						break;
			case '7':
						if ($pkey) {
							$player = $this->db_game->getRow("select * from player_state where pkey = $pkey");
					        $login = $this->db_game->getRow("select * from player_login where pkey = $pkey");
					        $this->assign('login',$login);
					        $this->assign('player',$player);
						}
						
		}
		$this->assign('page',$page->render());
		$this->assign('career',$GCareer);
		$this->assign('player_column',$player_column);
		$this->assign('info',$info);
		$this->assign('params',$params);
		$this->assign('sideColumn',$sideColumn);
		$this->assign('tabs',$tabs);
		$this->assign('gc_info',getGameChannelIdInfo());
		$this->display();
	}

	/**
	 *	异步更新player_state表的玩家信息
	 */
	private function update () {
	 	$pkey = Jec::getVar('pkey');
        $val = Jec::getVar('val');
        $key = Jec::getVar('key');
        if (!$key || !$pkey || !$val)  exit(json_encode(['state'=>'0','msg'=>'缺少参数...']));
        if($key != 'nickname'){
            $val = abs($val);
            $OldVal = $this->db_game->getOne("select $key from player_state where pkey = $pkey");
            if($val > $OldVal && !isAdmin())
               exit(json_encode(['state'=>'0','msg'=>'没有操作权限...']));
        }
        Log::info('SMP_Player_Detail',"修改[{$pkey}][$key=$val]");
        Net::rpc_game_server(gm,kick_off,array('pkey'=>$pkey));
        $this->db_game->update('player_state',array($key=>$val),array('pkey'=>$pkey));
       exit(json_encode(['state'=>'1','msg'=>'修改成功 !']));
	}

	/**
	 *	异步更新player_login表的账号信息
	 */
	private function change_account () {
		$key = Jec::getVar('key');
		$pkey = Jec::getVar('pkey');
        $account = Jec::getVar('val');
        if (!$key || !$pkey || !$account) exit(json_encode(['state'=>'0','msg'=>'缺少参数!'])); 
        $exists = $this->db_game->getRow("select pkey from player_login where $key = '$account'");
        if($exists['pkey']){
            exit(json_encode(['state'=>'0','msg'=>'账号已存在，请使用唯一账号!']));
        }else {
            $this->db_game->update('player_login', array('accname' => $account), array('pkey' => $pkey));
            Log::info('SMP_Player_Detail',"修改账号[{$pkey}][$account]");
            exit(json_encode(['state'=>'1','msg'=>'修改成功 !']));
        }
	}
}