<?php
/**
 * User: jecelyin
 * Date: 12-8-17
 *
 */

class SMP_Log_Rank extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '排行榜快照');

        $this->show();
    }

    private function makeTab()
    {
        $tabs = array(
            'rankcbp' => array('name' => '战力排行榜', 'checked' => true),
            'ranklv' => array('name' => '等级排行榜'),
            'rankmount' => array('name' => '坐骑排行榜'),
            'rankwing' => array('name' => '光翼排行榜'),
            'rankfb' => array('name' => '法宝排行榜'),
            'ranksb' => array('name' => '神兵排行榜'),
            'rankpet' => array('name' => '宠物排行榜'),
            'rankguild' => array('name' => '帮派排行榜'),
            'rankfwtower' => array('name' => '符文塔排行榜'),
//            'rankmstar' => array('name' => '坐骑星级排行榜'),
//            'rankpstar' => array('name' => '宠物星级排行榜'),
//            'rankgem' => array('name' => '宝石总等级排行榜'),
//            'rankdailycharge' => array('name' => '每日充值排行榜'),
        );
        $this->assign('tabs', $tabs);
    }

    private function getRankData($table,$where)
    {
        $time = $this->getWhereTime('time','0 day',true);
        return $this->db_game->getAll("select * from $table where 1 $where and $time order by time , rank desc");
    }
    private function show(){
        $this->makeTab();
        $rank_cbp= $this->getRankData('log_rank','and type = 1 ');
        $rank_lv = $this->getRankData('log_rank','and type = 2');
        $rank_mount = $this->getRankData('log_rank','and type = 4');
        $rank_wing = $this->getRankData('log_rank','and type = 5');
        $rank_fb = $this->getRankData('log_rank','and type = 6');
        $rank_sb = $this->getRankData('log_rank','and type = 7');
        $rank_pet = $this->getRankData('log_rank','and type = 9');
        $rank_guild = $this->getRankData('log_rank','and type = 10');
        $rank_fwtower = $this->getRankData('log_rank','and type = 11');
//        $rank_mstar = $this->getRankData('log_rank','and type = 12');
//        $rank_pstar = $this->getRankData('log_rank','and type = 13');
//        $rank_gem = $this->getRankData('log_rank','and type = 14');
//        $rank_dailycharge = $this->getRankData('log_rank','and type = 15');
        $this->assign(array(
            'rankcbp'=>$rank_cbp,
            'ranklv'=>$rank_lv,
            'rankmount'=>$rank_mount,
            'rankwing'=>$rank_wing,
            'rankfb'=>$rank_fb,
            'ranksb'=>$rank_sb,
            'rankpet'=>$rank_pet,
            'rankguild'=>$rank_guild,
            'rankfwtower'=>$rank_fwtower,
//            'rankmstar'=>$rank_mstar,
//            'rankpstar'=>$rank_pstar,
//            'rankgem'=>$rank_gem,
//            'rankdailycharge'=>$rank_dailycharge
        ));
        $this->display();
    }



}