<?php
/**
 * User:
 * Date: 12-8-17
 *
 */
 
class SMP_Player_Fashion extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '角色时装查看');
        $act = Jec::getVar('act');
        if($act == 'update')
            $this->update();

        $this->show();
    }

    private function update(){
        $pkey = Jec::getVar('pkey');
		$fashion_id = Jec::getVar('fashion_id');
        $where = "pkey =$pkey and fashion_id = $fashion_id";
        $file = Jec::getVar('file');
        $value = Jec::getVar('value');
        Log::info('SMP_Player_Fashion',"修改[{$pkey}][$file=$value]");
        $this->db_game->update('fashion',array($file=>$value),$where);
    }
	

	private function name_trans_1($data){
        $res = array();
        $datas = array(
       41100 => " 战士经典装",
       41200 => " 法师经典装",
       41300 => " 枪手经典装",
       41400 => " 萝莉经典装",
       41101 => " 战士龙装",
       41201 => " 法师龙装",
       41301 => " 枪手龙装",
       41401 => " 萝莉龙装",
       42001 => " 冰足迹",
       42002 => " 火足迹",
       42003 => " 水足迹",
       42004 => " 龙足迹",
       42005 => " 蝙蝠足迹",
       42006 => " 步步为荧",
       42007 => " 步步生财",
       42008 => " 丛林冒险",
       42009 => " 海底世界",
       42010 => " 派对舞步",
       42011 => " 爱的足迹",
       42012 => " 祈福莲花灯",
       42013 => " 幸运五角星",
       42014 => " 调皮小幽灵",
       42015 => " 喜迎2017",
       42016 => " 彩龙足迹",
       40000 => " 小波利",
       43001 => " 扁腹侠",
       43002 => " 小钢铁侠",
       43003 => " 萌萌龙",
       43004 => " 红火鸟",
       43005 => " 雪拉比",
       43006 => " 伊芙琳",
       43007 => " 淑月兔",
       43008 => " 基洛夫",
       43009 => " 南瓜灯",
       43010 => " 呆萌小幽灵",
       43011 => " 海盗章鱼",
       43012 => " 小雪人",
       43013 => " 招财猫",
       44000 => " 经典默认",
       44001 => " 绿龙",
       44002 => " 萌萌猪",
       44003 => " 夜空",
       44004 => " 羞涩君",
       44005 => " 恶魔",
       44006 => " 天使",
       44007 => " 这就是爱情",
       44008 => " 威廉古堡",
       44009 => " 甜甜的",
       44010 => " 我要雪糕",
       44011 => " 我要西瓜",
       44012 => " 最爱冰皮",
       44013 => " 月是故乡明",
       44014 => " 红旗招展",
       44015 => " 搞怪万圣节",
       44016 => " 狂欢万圣节",
       44017 => " 剁手喵",
       44018 => " 纪念版气泡",
       44019 => " 2017冬雪",
       44020 => " 利利是是",
       44021 => " 新春大吉",
       46001 => " 乔巴巴",
       46002 => " 皮丘丘",
       46003 => " 悟空空",
       46004 => " 魔王格鲁多",
       46005 => " 钢铁侠",
       46006 => " 美国队长",
       46007 => " 蝙蝠侠",
       46008 => " 学徒狮",
       46009 => " 祭祀狮",
       46010 => " 法老狮",
       46011 => " 猫头鹰",
       46012 => " 侦探鹰",
       46013 => " 柯南鹰",
       46014 => " 仙人掌",
       46015 => " 仙人花",
       46016 => " 仙人球",
       46017 => " 水母龙",
       46018 => " 琉璃龙",
       46019 => " 海怒龙",
       46021 => " 小鲨鱼",
       46022 => " 暴力鲨",
       46023 => " 小人鱼",
       46024 => " 美人鱼",
       46025 => " 娜迦",
       46026 => " 小球龙",
       46027 => " 宝石龙",
       46028 => " 璀璨龙",
       46029 => " 小火鸟",
       46030 => " 赤炎鸟",
       46031 => " 火烈鸟",
       46032 => " 怪物小猎人",
       46033 => " 疯狂猎人",
       46034 => " 专家猎人",
       46037 => " 小恶魔",
       46038 => " 恶魔",
       46039 => " 死神",
       46041 => " 天使",
       46042 => " 神圣天使",
       46046 => " 恶魔猎手",
       46064 => " 火恐龙",
       46065 => " 大脸猫",
       46066 => " 独角兽",
       46067 => " 猪侠客",
       46068 => " 火焰兽",
       46069 => " 哈士奇",
       46070 => " 苹果机器人",
       46071 => " 觅心女王",
       46072 => " 笨笨熊",
       46073 => " DJ琴女",
       46074 => " 海盗鹦鹉",
       46075 => " 月祭祀",
       46076 => " 烈焰恶魔",
       46077 => " 捣蛋麻瓜",
       46078 => " 剑无名",
       46079 => " 岚蝶狐",
       46080 => " 深海公主",
       46081 => " 钢达00",
       46082 => " 花芽精灵",
       46083 => " 圣诞雪人",
       46084 => " 火恐龙",
       41102 => " 战士熊猫",
       41202 => " 法师熊猫",
       41302 => " 枪手熊猫",
       41402 => " 萝莉熊猫",
       48001 => " 凤凰之魂",
       48002 => " 魔焰之魂",
       48003 => " 迷梦之魂",
       48004 => " 恶魔之魂",
       48005 => " 自然之魂",
       48006 => " 精灵之球",
       48007 => " 魔术卡牌",
       48008 => " 音符之魂",
       48009 => " 基洛夫飞艇",
       48010 => " 捣蛋小幽灵",
       48011 => " 雪花之舞",
       48012 => " 红龙之魂",
       48013 => " 黄龙之魂",
       48014 => " 绿龙之魂",
       48015 => " 蓝龙之魂",
       48016 => " 紫龙之魂",
       48017 => " 彩龙之魂",
       41103 => " 泳池派对",
       41203 => " 泳池派对",
       41303 => " 泳池派对",
       41403 => " 泳池派对",
       41104 => " 真爱永恒",
       41204 => " 真爱永恒",
       41304 => " 真爱永恒",
       41404 => " 真爱永恒",
       41105 => " 霸气学长",
       41205 => " 高冷学姐",
       41305 => " 正太学弟",
       41405 => " 软萌学妹",
       41106 => " 圣诞时装",
       41206 => " 圣诞时装",
       41306 => " 圣诞时装",
       41406 => " 圣诞时装",
       42101 => " 炮车",
       42102 => " 守方控制BOSS",
       42103 => " 守方辅助BOSS",
       42104 => " 攻方控制BOSS",
       42105 => " 攻方辅助BOSS",
		);
        foreach($data as $row) {
            $pname = get_player_name($row['pkey']);
            $fashion_id = $row['fashion_id'];
            $tem = array(
				"nickname" => $row['nickname'],
				"pkey" => $row['pkey'],
				"fashion_id" => $row['fashion_id'],
				"expiration_time" => $row['expiration_time'],
				"star" => $row['star'],
                "fashion_name" => $datas[$fashion_id],
            );
            $res[] = $tem;
        }
        return $res;
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
        $pager->setTotalRows($this->db_game->getOne("select count(*) from fashion where $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $data = $this->db_game->getAll("select * from fashion where $where limit $offset,$limit");

        foreach($data as &$val){
            $val['nickname'] = get_player_name($val['pkey']);
        }
		
        $this->assign('data',$this->name_trans_1($data));
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