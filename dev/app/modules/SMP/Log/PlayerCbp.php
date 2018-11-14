<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-12
 * Time: 13:45
 */

class SMP_Log_PlayerCbp  extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '玩家战力日志表');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and nickname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_player_cbp_1 where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from log_player_cbp_1 where $time $where order by time desc,id desc limit $offset,$limit");
        $data = array_reverse($data);
        $restr_list = array(
            'base,' => '基础:',
            "base_add," => "基础加成:",
            "lv_add," => "等级属性:",
            "equip," => "装备:",
            "cloth," => "时装:",
            "mount," => "坐骑:",
            "fuwen," => "符文:",
            "pet," => "宠物:",
            "wing," => "翅膀:",
            "guild_build," => "帮派建筑:",
            "meridian," => "经络:",
            "swordpool," => "剑池:",
            "light_weapon," => "光武:",
            "magic_weapon," => "法宝:",
            "god_weapon," => "神兵:",
            "stage," => "境界:",
            "mount_img," => "坐骑化形:",
            "pet_img," => "宠物化形:",
            "wing_img," => "光翼化形:",
            "douqi_img," => "斗气化形:",
            "light_weapon_img," => "光武化形:",
            "magic_weapon_img," => "法宝化形:",
            "mon_photo," => "怪物图鉴:",
            "smelt," => "熔炼:",
            "skill," => "技能:",
            "designation," => "称号:",
            "guild_skill," => "帮派技能:",
            "task_career," => "转职任务:",
            "xingyun," => "星蕴:",
            "junhun," => "聚魂:",
            "dogz," => "神兽:",
            "suit," => "套装:",
            "douqi," => "斗气:",
            "pet_fly," => "飞行器:",
            "skill_add_cbp," => "外观技能加战力:",
            "marry," => "结婚:",
            "sum_add," => "总加成:",
            "ms," => "命锁:",
            "bs_suit," => "变身套装:",
        );
        foreach($data as &$d){
            $d['change_cbp'] = $d['new_cbp'] - $d['old_cbp'];
            $d['attrs'] = strtr($d['attrs'], $restr_list);
            $d['change_attr'] = strtr($d['change_attr'], $restr_list);
        }
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select * from log_player_cbp_1  where $time $where order by time "));
        $data = array_reverse($data);
        $this->assign('data',$data);
        $this->assign('page', $pager->render());
        $this->display();
    }

    /*
     * @param array $data 需要导出的数据data
     */
    private function csv_download($data)
    {
        $csv = new CSV();
        $csv->setEncoding('gb2312');
        $csv->addAll($data);
        $csv->download('log_equip_wash.csv');
    }

}

