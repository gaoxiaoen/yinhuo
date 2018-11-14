<?php

/**
 * Created by PhpStorm.
 * User: fancy
 * Date: 15/3/19
 * Time: 下午11:21
 */
class Cron_Log
{

    public $db = null;

    public function __construct($data)
    {

        set_time_limit(60);
        $this->db = DB::getInstance('db_game');
        $method = $data['method'];
        $this->$method();

    }

    /**
     * 删除数据
     */
    public function del1()
    {
        $day = 90;
        $delLog = array(
            1 => array('table'=>'log_acc','day'=>$day,'row' => 'time'),
            2 => array('table'=>'log_acc_char','day'=>$day,'row' => 'time'),
            3 => array('table'=>'log_acc_charge','day'=>$day,'row' => 'time'),
            4 => array('table'=>'log_acc_charge_turntable','day'=>$day,'row' => 'time'),
            5 => array('table'=>'log_acc_eat','day'=>$day,'row' => 'time'),
            6 => array('table'=>'log_achieve','day'=>$day,'row' => 'time'),
            7 => array('table'=>'log_act_acc_charge','day'=>$day,'row' => 'time'),
            8 => array('table'=>'log_act_acc_consume','day'=>$day,'row' => 'time'),
            9 => array('table'=>'log_act_charge_gift','day'=>$day,'row' => 'time'),
            10 => array('table'=>'log_act_continue_charge','day'=>$day,'row' => 'time'),
            11 => array('table'=>'log_act_convoy','day'=>$day,'row' => 'time'),
            12 => array('table'=>'log_act_exchange','day'=>$day,'row' => 'time'),
            13 => array('table'=>'log_act_festive_boss','day'=>$day,'row' => 'time'),
            14 => array('table'=>'log_act_gbw','day'=>$day,'row' => 'time'),
            15 => array('table'=>'log_act_gold_rank','day'=>$day,'row' => 'time'),
            16 => array('table'=>'log_act_gold_rank_reward','day'=>$day,'row' => 'time'),
            17 => array('table'=>'log_act_hi_fan_point','day'=>$day,'row' => 'time'),
            18 => array('table'=>'log_act_hi_fan_tian','day'=>$day,'row' => 'time'),
            19 => array('table'=>'log_act_login_gift','day'=>$day,'row' => 'time'),
            20 => array('table'=>'log_act_lucky_treasure','day'=>$day,'row' => 'time'),
            21 => array('table'=>'log_act_lucky_turn','day'=>$day,'row' => 'time'),
            21 => array('table'=>'log_act_cross_lucky_turn','day'=>$day,'row' => 'time'),
            22 => array('table'=>'log_act_ms_buy','day'=>$day,'row' => 'time'),
            23 => array('table'=>'log_act_pray','day'=>$day,'row' => 'time'),
            24 => array('table'=>'log_act_smash_egg','day'=>$day,'row' => 'time'),
            25 => array('table'=>'log_act_tqxz_buy','day'=>$day,'row' => 'time'),
            26 => array('table'=>'log_act_up_target','day'=>$day,'row' => 'time'),
            27 => array('table'=>'log_all_rank','day'=>$day,'row' => 'time'),
            28 => array('table'=>'log_answer_rank','day'=>$day,'row' => 'time'),
            29 => array('table'=>'log_arena','day'=>$day,'row' => 'time'),
            30 => array('table'=>'log_arena_rank','day'=>$day,'row' => 'time'),
            31 => array('table'=>'log_battlefield','day'=>$day,'row' => 'time'),
            32 => array('table'=>'log_bless','day'=>$day,'row' => 'time'),
            33 => array('table'=>'log_boss_hunter_rank_reward','day'=>$day,'row' => 'time'),
            34 => array('table'=>'log_boss_hunter_score_reward','day'=>$day,'row' => 'time'),
            35 => array('table'=>'log_boss_invest','day'=>$day,'row' => 'time'),
            36 => array('table'=>'log_boss_join','day'=>$day,'row' => 'time'),
            37 => array('table'=>'log_bs_suit_lv_or_act','day'=>$day,'row' => 'time'),
            38 => array('table'=>'log_bs_suit_lvup','day'=>$day,'row' => 'time'),
            39 => array('table'=>'log_bs_suit_rebuild','day'=>$day,'row' => 'time'),
            40 => array('table'=>'log_bs_suit_skill','day'=>$day,'row' => 'time'),
            41 => array('table'=>'log_change_name','day'=>$day,'row' => 'time'),
            42 => array('table'=>'log_change_sex','day'=>$day,'row' => 'time'),
            43 => array('table'=>'log_charge_gift','day'=>$day,'row' => 'time'),
            44 => array('table'=>'log_charm_rank','day'=>$day,'row' => 'time'),
            45 => array('table'=>'log_chat','day'=>$day,'row' => 'time'),
            46 => array('table'=>'log_chat_ad','day'=>$day,'row' => 'time'),
            47 => array('table'=>'log_clothes_active','day'=>$day,'row' => 'time'),
            48 => array('table'=>'log_clothes_expire_time','day'=>$day,'row' => 'time'),
            49 => array('table'=>'log_clothes_lv','day'=>$day,'row' => 'time'),
            50 => array('table'=>'log_coin','day'=>$day,'row' => 'time'),
            51 => array('table'=>'log_combatpower_rank','day'=>$day,'row' => 'time'),
            52 => array('table'=>'log_con_acc_charge','day'=>$day,'row' => 'time'),
            53 => array('table'=>'log_con_acc_consume','day'=>$day,'row' => 'time'),
            54 => array('table'=>'log_convoy','day'=>$day,'row' => 'time'),
            55 => array('table'=>'log_cross_1v1','day'=>$day,'row' => 'time'),
            56 => array('table'=>'log_cross_battlefield','day'=>$day,'row' => 'time'),
            57 => array('table'=>'log_cross_battlefield_reward','day'=>$day,'row' => 'time'),
            58 => array('table'=>'log_cross_boss','day'=>$day,'row' => 'time'),
            59 => array('table'=>'log_cross_dun','day'=>$day,'row' => 'time'),
            60 => array('table'=>'log_cross_elite','day'=>$day,'row' => 'time'),
            61 => array('table'=>'log_cross_flower_rank','day'=>$day,'row' => 'time'),
            62 => array('table'=>'log_cross_luck_yg_buy','day'=>$day,'row' => 'time'),
            63 => array('table'=>'log_cross_luck_yg_open','day'=>$day,'row' => 'time'),
            64 => array('table'=>'log_cross_ms_buy','day'=>$day,'row' => 'time'),
            65 => array('table'=>'log_cross_six_dragon','day'=>$day,'row' => 'time'),
            66 => array('table'=>'log_cross_six_dragon_one_fight','day'=>$day,'row' => 'time'),
            67 => array('table'=>'log_cross_sjzc_open','day'=>$day,'row' => 'time'),
            68 => array('table'=>'log_cross_sjzc_rank','day'=>$day,'row' => 'time'),
            69 => array('table'=>'log_cross_sm_res','day'=>$day,'row' => 'time'),
            70 => array('table'=>'log_cross_smzc_rank','day'=>$day,'row' => 'time'),
            71 => array('table'=>'log_cross_tg_buy','day'=>$day,'row' => 'time'),
            72 => array('table'=>'log_cross_tg_reward','day'=>$day,'row' => 'time'),
            73 => array('table'=>'log_cross_vitality','day'=>$day,'row' => 'time'),
            74 => array('table'=>'log_cross_war','day'=>$day,'row' => 'time'),
            75 => array('table'=>'log_cross_yg_buy','day'=>$day,'row' => 'time'),
            76 => array('table'=>'log_cwtx_help_reward','day'=>$day,'row' => 'time'),
            77 => array('table'=>'log_cwtx_task_reward','day'=>$day,'row' => 'time'),
            78 => array('table'=>'log_daily_acc_consume','day'=>$day,'row' => 'time'),
            79 => array('table'=>'log_daily_acc_recharge','day'=>$day,'row' => 'time'),
            80 => array('table'=>'log_daily_charge','day'=>$day,'row' => 'time'),
            81 => array('table'=>'log_day7login','day'=>$day,'row' => 'time'),
            82 => array('table'=>'log_designation','day'=>$day,'row' => 'time'),
            83 => array('table'=>'log_destiny_war_boss_die','day'=>$day,'row' => 'time'),
            84 => array('table'=>'log_destiny_war_boss_drop','day'=>$day,'row' => 'time'),
            85 => array('table'=>'log_df_xunbao','day'=>$day,'row' => 'time'),
            86 => array('table'=>'log_douqi_dan','day'=>$day,'row' => 'time'),
            87 => array('table'=>'log_douqi_lv','day'=>$day,'row' => 'time'),
            88 => array('table'=>'log_dun_marry','day'=>$day,'row' => 'time'),
            89 => array('table'=>'log_dungeon','day'=>$day,'row' => 'time'),
            90 => array('table'=>'log_equip','day'=>$day,'row' => 'time'),
            91 => array('table'=>'log_equip_gem','day'=>$day,'row' => 'time'),
            92 => array('table'=>'log_equip_inlay','day'=>$day,'row' => 'time'),
            93 => array('table'=>'log_equip_putoff','day'=>$day,'row' => 'time'),
            94 => array('table'=>'log_equip_puton','day'=>$day,'row' => 'time'),
            95 => array('table'=>'log_equip_refine','day'=>$day,'row' => 'time'),
            96 => array('table'=>'log_equip_stren','day'=>$day,'row' => 'time'),
            97 => array('table'=>'log_equip_taobao','day'=>$day,'row' => 'time'),
            98 => array('table'=>'log_equip_upgrade','day'=>$day,'row' => 'time'),
            99 => array('table'=>'log_equip_wash','day'=>$day,'row' => 'time'),
            100 => array('table'=>'log_equip_zhulin','day'=>$day,'row' => 'time'),
            101 => array('table'=>'log_exp_rank','day'=>$day,'row' => 'time'),
            102 => array('table'=>'log_field_boss','day'=>$day,'row' => 'time'),
            103 => array('table'=>'log_field_boss_rank','day'=>$day,'row' => 'time'),
            104 => array('table'=>'log_findback_act_time','day'=>$day,'row' => 'time'),
            105 => array('table'=>'log_flower_give_reward','day'=>$day,'row' => 'time'),
            106 => array('table'=>'log_flower_rank','day'=>$day,'row' => 'time'),
            107 => array('table'=>'log_free_gift','day'=>$day,'row' => 'time'),
            108 => array('table'=>'log_fuwen_change','day'=>$day,'row' => 'time'),
            109 => array('table'=>'log_fuwen_compound_back','day'=>$day,'row' => 'time'),
            110 => array('table'=>'log_fuwen_map','day'=>$day,'row' => 'time'),
            111 => array('table'=>'log_fuwen_op','day'=>$day,'row' => 'time'),
            112 => array('table'=>'log_fuwen_pos','day'=>$day,'row' => 'time'),
            113 => array('table'=>'log_fuwen_puton','day'=>$day,'row' => 'time'),
            114 => array('table'=>'log_gbw_fg','day'=>$day,'row' => 'time'),
            115 => array('table'=>'log_gbw_s_win_reward','day'=>$day,'row' => 'time'),
            116 => array('table'=>'log_gem_change','day'=>$day,'row' => 'time'),
            117 => array('table'=>'log_gift','day'=>$day,'row' => 'time'),
            118 => array('table'=>'log_god_dan','day'=>$day,'row' => 'time'),
            119 => array('table'=>'log_god_lv','day'=>$day,'row' => 'time'),
            120 => array('table'=>'log_god_star','day'=>$day,'row' => 'time'),
            121 => array('table'=>'log_gold','day'=>$day,'row' => 'time'),
            122 => array('table'=>'log_gold_tower','day'=>$day,'row' => 'time'),
            123 => array('table'=>'log_goods_create','day'=>$day,'row' => 'time'),
            124 => array('table'=>'log_goods_use','day'=>$day,'row' => 'time'),
            125 => array('table'=>'log_guild','day'=>$day,'row' => 'time'),
            126 => array('table'=>'log_guild_dedicate','day'=>$day,'row' => 'time'),
            127 => array('table'=>'log_guild_demon','day'=>$day,'row' => 'time'),
            128 => array('table'=>'log_guild_dinner','day'=>$day,'row' => 'time'),
            129 => array('table'=>'log_guild_exp','day'=>$day,'row' => 'time'),
            130 => array('table'=>'log_guild_mb','day'=>$day,'row' => 'time'),
            131 => array('table'=>'log_guild_name','day'=>$day,'row' => 'time'),
            132 => array('table'=>'log_guild_new_demon_reward','day'=>$day,'row' => 'time'),
            133 => array('table'=>'log_guild_rank','day'=>$day,'row' => 'time'),
            134 => array('table'=>'log_guild_secret','day'=>$day,'row' => 'time'),
            135 => array('table'=>'log_guild_skill','day'=>$day,'row' => 'time'),
            136 => array('table'=>'log_guild_war','day'=>$day,'row' => 'time'),
            137 => array('table'=>'log_guild_warehouse','day'=>$day,'row' => 'time'),
            138 => array('table'=>'log_guild_warehouse_op','day'=>$day,'row' => 'time'),
            139 => array('table'=>'log_hunt_reward','day'=>$day,'row' => 'time'),
            140 => array('table'=>'log_invest','day'=>$day,'row' => 'time'),
            141 => array('table'=>'log_juhun_fix','day'=>$day,'row' => 'time'),
            142 => array('table'=>'log_korea_token','day'=>$day,'row' => 'time'),
            143 => array('table'=>'log_kzlp','day'=>$day,'row' => 'time'),
            144 => array('table'=>'log_light_weapon_dan','day'=>$day,'row' => 'time'),
            145 => array('table'=>'log_light_weapon_lv','day'=>$day,'row' => 'time'),
            146 => array('table'=>'log_lim_buy','day'=>$day,'row' => 'time'),
            147 => array('table'=>'log_login','day'=>$day,'row' => 'time'),
            148 => array('table'=>'log_luck_yg_buy','day'=>$day,'row' => 'time'),
            149 => array('table'=>'log_luck_yg_open','day'=>$day,'row' => 'time'),
            150 => array('table'=>'log_lucky_pool','day'=>$day,'row' => 'time'),
            151 => array('table'=>'log_lv','day'=>$day,'row' => 'time'),
            152 => array('table'=>'log_lv_gift','day'=>$day,'row' => 'time'),
            153 => array('table'=>'log_magic_weapon_dan','day'=>$day,'row' => 'time'),
            154 => array('table'=>'log_magic_weapon_lv','day'=>$day,'row' => 'time'),
            155 => array('table'=>'log_mail_draw','day'=>$day,'row' => 'time'),
            156 => array('table'=>'log_market','day'=>$day,'row' => 'time'),
            157 => array('table'=>'log_marry','day'=>$day,'row' => 'time'),
            158 => array('table'=>'log_marry_cruise','day'=>$day,'row' => 'time'),
            159 => array('table'=>'log_marry_cruise_buy','day'=>$day,'row' => 'time'),
            160 => array('table'=>'log_marry_heart','day'=>$day,'row' => 'time'),
            161 => array('table'=>'log_marry_ring','day'=>$day,'row' => 'time'),
            162 => array('table'=>'log_marry_tree','day'=>$day,'row' => 'time'),
            163 => array('table'=>'log_marry_tree_reward','day'=>$day,'row' => 'time'),
            164 => array('table'=>'log_merge_act_hi_fan_point','day'=>$day,'row' => 'time'),
            165 => array('table'=>'log_merge_act_hi_fan_tian','day'=>$day,'row' => 'time'),
            166 => array('table'=>'log_meridian','day'=>$day,'row' => 'time'),
            167 => array('table'=>'log_mon_photo','day'=>$day,'row' => 'time'),
            168 => array('table'=>'log_month_card','day'=>$day,'row' => 'time'),
            169 => array('table'=>'log_mount_dan','day'=>$day,'row' => 'time'),
            170 => array('table'=>'log_mount_lv','day'=>$day,'row' => 'time'),
            171 => array('table'=>'log_mount_star','day'=>$day,'row' => 'time'),
            172 => array('table'=>'log_on_hook','day'=>$day,'row' => 'time'),
            173 => array('table'=>'log_open_recharge','day'=>$day,'row' => 'time'),
            174 => array('table'=>'log_out','day'=>$day,'row' => 'time'),
            175 => array('table'=>'log_party','day'=>$day,'row' => 'time'),
            176 => array('table'=>'log_party_reward','day'=>$day,'row' => 'time'),
            177 => array('table'=>'log_pet_dan','day'=>$day,'row' => 'time'),
            178 => array('table'=>'log_pet_fly','day'=>$day,'row' => 'time'),
            179 => array('table'=>'log_pet_lv','day'=>$day,'row' => 'time'),
            180 => array('table'=>'log_pet_star','day'=>$day,'row' => 'time'),
            181 => array('table'=>'log_pet_weapon_dan','day'=>$day,'row' => 'time'),
            182 => array('table'=>'log_pet_weapon_lv','day'=>$day,'row' => 'time'),
            183 => array('table'=>'log_pet_weapon_star','day'=>$day,'row' => 'time'),
            184 => array('table'=>'log_pet_wing_dan','day'=>$day,'row' => 'time'),
            185 => array('table'=>'log_pet_wing_lv','day'=>$day,'row' => 'time'),
            186 => array('table'=>'log_pet_wing_star','day'=>$day,'row' => 'time'),
            187 => array('table'=>'log_player_cbp','day'=>$day,'row' => 'time'),
            188 => array('table'=>'log_player_cbp_1','day'=>$day,'row' => 'time'),
            189 => array('table'=>'log_player_chaijie','day'=>$day,'row' => 'time'),
            190 => array('table'=>'log_player_dogz_asist','day'=>$day,'row' => 'time'),
            191 => array('table'=>'log_player_dogz_equip','day'=>$day,'row' => 'time'),
            192 => array('table'=>'log_player_fireworks','day'=>$day,'row' => 'time'),
            193 => array('table'=>'log_player_jinjie','day'=>$day,'row' => 'time'),
            194 => array('table'=>'log_player_juhun_fix_cbp','day'=>$day,'row' => 'time'),
            195 => array('table'=>'log_player_mys_shop','day'=>$day,'row' => 'time'),
            196 => array('table'=>'log_player_refine','day'=>$day,'row' => 'time'),
            197 => array('table'=>'log_player_scene_enter','day'=>$day,'row' => 'time'),
            198 => array('table'=>'log_player_state','day'=>$day,'row' => 'time'),
            199 => array('table'=>'log_random_market','day'=>$day,'row' => 'time'),
            200 => array('table'=>'log_rank','day'=>$day,'row' => 'time'),
            201 => array('table'=>'log_red_bag_notice','day'=>$day,'row' => 'time'),
            202 => array('table'=>'log_scene_enter','day'=>$day,'row' => 'time'),
            203 => array('table'=>'log_sign_in','day'=>$day,'row' => 'time'),
            204 => array('table'=>'log_smelt','day'=>$day,'row' => 'time'),
            205 => array('table'=>'log_sprite','day'=>$day,'row' => 'time'),
            206 => array('table'=>'log_sprite_star','day'=>$day,'row' => 'time'),
            207 => array('table'=>'log_star_luck','day'=>$day,'row' => 'time'),
            208 => array('table'=>'log_suit_active','day'=>$day,'row' => 'time'),
            209 => array('table'=>'log_sword_pool','day'=>$day,'row' => 'time'),
            210 => array('table'=>'log_sword_pool_daily','day'=>$day,'row' => 'time'),
            211 => array('table'=>'log_top_invest','day'=>$day,'row' => 'time'),
            212 => array('table'=>'log_treasure_hunt','day'=>$day,'row' => 'time'),
            213 => array('table'=>'log_vip_change','day'=>$day,'row' => 'time'),
            214 => array('table'=>'log_vip_up_value','day'=>$day,'row' => 'time'),
            215 => array('table'=>'log_war_boss','day'=>$day,'row' => 'time'),
            216 => array('table'=>'log_warehouse','day'=>$day,'row' => 'time'),
            217 => array('table'=>'log_warehouse_open_cell','day'=>$day,'row' => 'time'),
            218 => array('table'=>'log_wing_dan','day'=>$day,'row' => 'time'),
            219 => array('table'=>'log_wing_lv','day'=>$day,'row' => 'time'),
            220 => array('table'=>'log_world_boss_drop','day'=>$day,'row' => 'time'),
            221 => array('table'=>'log_world_boss_home_kill','day'=>$day,'row' => 'time'),
            222 => array('table'=>'log_world_boss_icon','day'=>$day,'row' => 'time'),
            223 => array('table'=>'log_world_boss_kill','day'=>$day,'row' => 'time'),
            224 => array('table'=>'log_world_boss_mh_kill','day'=>$day,'row' => 'time'),
            225 => array('table'=>'log_world_boss_min_gift','day'=>$day,'row' => 'time'),
            226 => array('table'=>'log_world_boss_sl_kill','day'=>$day,'row' => 'time'),
            227 => array('table'=>'log_world_boss_xy_kill','day'=>$day,'row' => 'time'),
            228 => array('table'=>'log_xzxb','day'=>$day,'row' => 'time'),
            229 => array('table'=>'log_zj_xunbao','day'=>$day,'row' => 'time'),
        );
        $this->clean($delLog);
    }

    public function del2()
    {
        $delLog = array(
            1 => array('table' => 'log_coin', 'day' => 30, 'row' => 'time'),
            2 => array('table' => 'log_gold', 'day' => 30, 'row' => 'time'),
        );
        $this->clean($delLog);
    }

    public function del3()
    {
        $delLog = array(
            1 => array('table' => 'log_goods_use', 'day' => 30, 'row' => 'time'),
            2 => array('table' => 'log_goods_create', 'day' => 30, 'row' => 'time'),
        );
        $this->clean($delLog);
    }


    /**
     * @param
     */
    public function clean($delLog)
    {
        $now = time();
        foreach ($delLog as $key => $d) {
            $deltime = $now - 86400 * $d['day'];
            $this->db->query("delete from {$d['table']} where `{$d['row']}` < $deltime limit 20000");
        }
    }

    /*
     * 清空表
     */
    public function dle_goods_create_log()
    {
//        $now = time();
//        $day = date('d',$now);
//        if($day % $day == 0) {
//            $this->db->query("truncate table log_goods_create");
//        }
    }


}