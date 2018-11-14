<?php
/**
 * Created by PhpStorm.
 * User: fengzhenlin
 * Date: 16/1/5
 * Time: 下午6:26
 */
include_once 'Activity/util.php';
include_once 'Activity/act_config.php';
include_once 'Activity/taobao_rank.php';
include_once 'Activity/invest_act.php';
include_once 'Activity/marry_activity.php';
include_once 'Activity/charge_turntable.php';
include_once 'Activity/back_gold_turntable.php';
include_once 'Activity/Import.php';
include_once 'Activity/group_shop.php';
include_once 'Activity/charge_find_back.php';
include_once 'Activity/limit_buy.php';
include_once 'Activity/cross_flower.php';
include_once 'Activity/login_online.php';
include_once 'Activity/hundred_return.php';
include_once 'Activity/new_exchange.php';
include_once 'Activity/act_equip_sell.php';
include_once 'Activity/stone_ident.php';
include_once 'Activity/collect_exchange.php';
include_once 'Activity/acc_charge_d.php';
include_once 'Activity/act_consume_back_charge.php';
include_once 'Activity/consume_rank.php';
include_once 'Activity/recharge_rank.php';
include_once 'Activity/cross_consume_rank.php';
include_once 'Activity/cross_recharge_rank.php';
include_once 'Activity/flower_rank.php';
include_once 'Activity/act_con_charge.php';
include_once 'Activity/xj_map.php';
include_once 'Activity/open_back_buy.php';
include_once 'Activity/gold_silver_tower.php';
include_once 'Activity/red_goods_exchange.php';
include_once 'Activity/debris_exchange.php';
include_once 'Activity/marry_rank.php';
include_once 'Activity/excel_handle.php';
//骑战活动引用
include_once 'Activity/acc_charge.php';
include_once 'Activity/acc_consume.php';
include_once 'Activity/one_charge.php';
include_once 'Activity/online_gift.php';
include_once 'Activity/online_time_gift.php';
include_once 'Activity/daily_acc_charge.php';
include_once 'Activity/ad.php';
include_once 'Activity/act_monopoly.php';
include_once 'Activity/open_all_rank.php';
include_once 'Activity/act_convoy.php';
include_once 'Activity/fuwen_map.php';
include_once 'Activity/act_acc_char.php';
include_once 'Activity/con_acc_charge.php';
include_once 'Activity/taobao_act.php';
include_once 'Activity/taobao_lt_act.php';
include_once 'Activity/act_luck_yg.php';
include_once 'Activity/act_kzlp.php';
include_once 'Activity/act_gbw.php';
include_once 'Activity/act_login_gift.php';
include_once 'Activity/act_acc_charge.php';
include_once 'Activity/act_extra_exp.php';
include_once 'Activity/act_exchange.php';
include_once 'Activity/act_lucky_treasure.php';
include_once 'Activity/act_hi_fan_tian.php';
include_once 'Activity/act_double_drop.php';
include_once 'Activity/act_festive_boss.php';
include_once 'Activity/marry_rank.php';
include_once 'Activity/cross_vitality.php';
include_once 'Activity/act_gold_rank.php';
include_once 'Activity/act_charge_gift.php';
include_once 'Activity/fireworks.php';
include_once 'Activity/df_xunbao_act.php';
include_once 'Activity/df_xunbao_lt_act.php';
include_once 'Activity/free_gift.php';
include_once 'Activity/act_cross_tg.php';
include_once 'Activity/act_pray.php';
include_once 'Activity/act_cross_ms.php';
include_once 'Activity/act_ms.php';
include_once 'Activity/act_smash_egg.php';
include_once 'Activity/act_cross_week_boss.php';
include_once 'Activity/act_xzxb.php';
include_once 'Activity/act_continue_charge.php';
include_once 'Activity/act_boss_hunter.php';
include_once 'Activity/act_merge_hi_fan_tian.php';
include_once 'Activity/act_local_lucky_turn.php';
include_once 'Activity/act_tqxz.php';
include_once 'Activity/con_acc_consume.php';
include_once 'Activity/daily_acc_consume.php';
include_once 'Activity/discount_gift.php';
include_once 'Activity/act_acc_consume.php';
include_once 'Activity/act_world_cup.php';
include_once 'Activity/zj_xunbao_act.php';
include_once 'Activity/zj_xunbao_lt_act.php';
include_once 'Activity/gold_tower.php';
include_once 'Activity/today_hot_sale.php';
include_once 'Activity/act_flower_give_reward.php';
include_once 'Activity/act_9377_vip_service.php';
include_once 'Activity/cross_yg.php';
include_once 'Activity/acc_daily_charge1st.php';
include_once 'Activity/act_cross_star.php';
include_once 'Activity/act_cross_lucky_turn.php';

class SMP_Act_Activity extends AdminController
{

    public static $key = 'activity_cache';
    public static $server_key = 'server_key';

    public function __construct()
    {
        parent::__construct();
        $do = Jec::getVar('do');

        $type = Jec::getInt('type');
        $actid = Jec::getInt('act_id');
        $type = $type == 0 ? 1 : $type;
        $actid = $actid == 0 ? 0 : $actid;

        switch ($do) {
            //子活动处理
            case 'Activity':
                $this->activity_info("SMP/Act/Views/Activity.html", $type, $actid);
                break;
            case 'editActivity' :
                $this->showEdit("SMP/Act/Views/ActivityEdit.html");
                break;
            case 'edit' :
                $this->edit("SMP/Act/Views/ActivityEdit.html");
                break;
            case 'delActivity':
                $this->del("SMP/Act/Views/Activity.html");
                break;
            case 'addActivity':
                $this->add_activity("SMP/Act/Views/ActivityEdit.html", $type);
                break;
            case 'ShowExcel':
                new SMP_Data_Import();
                break;
            //活动类型处理
            case 'allActivityType':
                $this->allActivityType("SMP/Act/Views/ActivityType.html");
                break;
            case 'addActivityType':
                $this->addActivityType("SMP/Act/Views/ActivityTypeEdit.html");
                break;
            case 'editActivityType':
                $this->editActivityType("SMP/Act/Views/ActivityTypeEdit.html", $type);
                break;
            case 'edit_activity_finish':
                $this->editActivityTypeFinish("SMP/Act/Views/ActivityTypeEdit.html");
                break;
            //重载活动
            case 'reloadActivity':
                $this->reloadActivity($type);
                break;
            //同步外服服务器列表
            case 'sync_platform':
                $this->allActivityType("SMP/Act/Views/ActivityType.html");
                $this->sync_platform();
                break;
            case 'mtupActivity':
                $this->allActivityType("SMP/Act/Views/ActivityType.html");
                $this->mtupActivity();
                break;
            case 'reload_all':
                $this->reload_all();
                break;
            case 'reload_select':
                $this->reload_select($type);
                break;
            case 'getPreviewContent':
                $this->getPregGoodsPreview();
                break;
            default :
                $this->allActivityType("SMP/Act/Views/ActivityType.html");
                break;
        }
    }

    //活动类型处理
    public function allActivityType($html = '')
    {
        $excel = new Excelhandle();
        $acttype = $excel->get_rows("base_activity", array());
        foreach ($acttype as &$act) {
            if ($act['state'] == 1)
                $act['state'] = "启用";
            else
                $act['state'] = "停用";
        }
        $this->assign('act_type', $acttype);
        $this->show($html);
    }

    public function addActivityType($html = '')
    {
        //先更新svn
        SMP_Act_Activity::update_svn();
        $excel = new Excelhandle();
        $actdata = $excel->get_rows("base_activity", array());
        $maxtype = 0;
        foreach ($actdata as $data) {
            if ($data['type'] > $maxtype)
                $maxtype = $data['type'];
        }
        $act = array();
        $act['type'] = $maxtype + 1;
        $this->assign('act', $act);
        $this->show($html);
    }

    public function editActivityType($html = '', $type)
    {
        //先更新svn
        SMP_Act_Activity::update_svn();
        $excel = new Excelhandle();
        $acttype1 = $excel->get_rows("base_activity", array('type' => $type));
        $act = $acttype1[0];
        $this->assign('act', $act);
        $this->show($html);
    }

    public function editActivityTypeFinish($html = '')
    {
        $items = Jec::getVar('items');
        $act = array();
        $act['type'] = (int)$items['type'];
        if ($act['type'] == 0) {
            return;
        }
        $act['name'] = $items['name'];
        $act['state'] = 1; //$items['state'];
        $act['title'] = $items['title'];
        $excel = new Excelhandle();
        $excel->add('base_activity', $act);
        //提交svn
        SMP_Act_Activity::commit_svn();
        $this->editActivityType($html, $act['type']);
    }

    //子活动处理
    public function del($html = '')
    {
        $type = Jec::getVar('type');
        $act_id = Jec::getVar('act_id');
        if ($type > 0 and $act_id > 0) {
            $excel = new Excelhandle();
            $excel->del('base_activity_data_' . $type, array("type" => $type, "act_id" => $act_id));
        }
        //提交svn
        SMP_Act_Activity::commit_svn();
        $this->activity_info($html, $type, 0);
    }

    public function add_activity($html = '', $type)
    {
        $excel = new Excelhandle();
        $actdata = $excel->get_rows("base_activity_data_$type", array());
        $maxactid = 0;
        foreach ($actdata as $data) {
            if ($data['act_id'] > $maxactid)
                $maxactid = $data['act_id'];
        }
        $acttype1 = $excel->get_rows("base_activity", array('type' => $type));
        $acttype = $acttype1[0];
        $act = array();
        $act['data'] = array();
        $act['data']['act_id'] = (int)$maxactid + 1;
        $act['name'] = $acttype['name'];
        $act['title'] = $acttype['title'];
        $this->assign('act_data', $act);
        $this->assign('gp_id', array());
        $this->assign('gs_id', array());
        $this->assign('type', $type);
        $this->show($html);
    }

    public function edit($html = '')
    {
        $g_sel = $this->widget->getSelectedGroup(); //被选择了的服务器组
        $s_sel = $this->widget->getSelectedServer(); //被单独选择了的服务器id
        $ignore_sel = $this->widget->getIgnoreSelectedServer();//忽略的服务器id
        $items = Jec::getVar('items');
        $act = array();
        $act['act_id'] = (int)$items['id'];
        if ($act['act_id'] == 0) {
            return;
        }
        $act['id_desc'] = $items['id_desc'];
        $act['type'] = Jec::getInt('type');
        $act['gp_id'] = implode('|', $g_sel);
        $act['gs_id'] = implode('|', $s_sel);
        $act['ignore_gs'] = $ignore_sel;
        $act['content'] = $items['content'];
        $act['priority'] = $items['priority'];
        $act['after_open_day'] = $items['after_open_day'];
        $act['show_pos_day'] = $items['show_pos_day'];
        $act['open_day'] = $items['open_days'];
        $act['end_day'] = $items['end_days'];
        $act['merge_st_day'] = $items['merge_st_day'];
        $act['merge_et_day'] = $items['merge_et_day'];
//        $act['start_time'] = $items['start_time']==0 || $items['start_time']=="" ? 0 : $items['start_time'];
//        $act['end_time'] = $items['end_time']==0 || $items['end_time']=="" ? 0 : $items['end_time'];
        $act['start_time'] = $items['start_time'] == 0 || $items['start_time'] == "" ? 0 : date('Y/n/j G:i:s', strtotime($items['start_time']));
        $act['end_time'] = $items['end_time'] == 0 || $items['end_time'] == "" ? 0 : date('Y/n/j G:i:s', strtotime($items['end_time']));
        $act['act_name'] = htmlspecialchars_decode($items['act_name'], ENT_QUOTES);
        $act['act_desc'] = htmlspecialchars_decode($items['act_desc'], ENT_QUOTES);
        $act['show_goods_list'] = $items['show_goods_list'];
        $act['icon'] = $items['icon'];
        $act['ad_pic'] = $items['ad_pic'];
        $act['kf_state'] = $items['kf_state'] == "" ? 0 : $items['kf_state'];
        $act['merge_times_list'] = $items['merge_times_list'];
        $act['conflict_list'] = $items['conflict_list'];
        $excel = new Excelhandle();
        $excel->add('base_activity_data_' . $act['type'], $act);
        //提交svn
        SMP_Act_Activity::commit_svn();
        $msg['msg'] = "更新成功";
        $this->assign('msg', $msg);
//        $this->showEdit($html);
        $this->activity_info("SMP/Act/Views/Activity.html", $act['type'], 0);
    }

    public function showEdit($html = '')
    {
        $type = Jec::getInt('type');
        $actid = Jec::getInt('act_id');
        $excel = new Excelhandle();
        $acttype1 = $excel->get_rows("base_activity", array('type' => $type));
        $acttype = $acttype1[0];
        $actdata = $excel->get_rows("base_activity_data_" . $type, array('type' => $type, 'act_id' => $actid));
        $act = array();
        $act['type'] = $acttype['type'];
        $act['name'] = $acttype['name'];
        $act['state'] = $acttype['state'];
        $act['title'] = $acttype['title'];
        $data = $this->get_act_content($actdata, $acttype);
        $act['data'] = $data[0];

        $this->assign('act_data', $act);
        $this->assign('type', $type);
        $this->assign('gp_id', $act['data']['gp_id']);
        $this->assign('gs_id', $act['data']['gs_id']);
        $this->assign('ignore_gs', $act['data']['ignore_gs']);
        $this->show($html);
    }

    //显示活动列表
    public function activity_info($html = '', $type = 0, $actid = 0)
    {
        if ($actid == 0) {
            $where1 = array('type' => $type);
        } else {
            $where1 = array('type' => $type, 'act_id' => $actid);
        }
        $excel = new Excelhandle();
        $acttype1 = $excel->get_rows("base_activity", array('type' => $type));
        $acttype = $acttype1[0];
        $actdata = $excel->get_rows("base_activity_data_$type", $where1);
        $act = array();
        $act['type'] = $acttype['type'];
        $act['name'] = $acttype['name'];
        $act['state'] = $acttype['state'];
        $act['title'] = $acttype['title'];
        $act['data'] = array();
        $act['data'] = $this->get_act_content($actdata, $acttype);

        $allactid = array();
        $this->assign('selecttype', $act['type']);
        $this->assign('selectname', $act['name']);
        $this->assign('selectactid', $actid);
        $actidname = $actid == 0 ? "全部" : $actid;
        $this->assign('selectactidname', $actidname);
        $this->assign('allactid', $allactid);
        $this->assign('act_data', $act);
        $this->show($html);

    }

    //获取子活动数据
    private function get_act_content($Content, $acttype)
    {
        $Info = array();
        foreach ($Content as $data) {
            $act['id'] = $data['id'];
            $act['act_id'] = $data['act_id'];
            $act['type'] = $acttype['type'];
            $act['name'] = $acttype['name'];
            $act['id_desc'] = $data['id_desc'];
            $act['gp_id'] = $data['gp_id'] == "" ? array() : explode("|", $data['gp_id']);
            $act['gs_id'] = $data['gs_id'] == "" ? array() : explode("|", $data['gs_id']);
            $act['ignore_gs'] = $data['ignore_gs'];
            $act['content'] = $data['content'];
            $act['priority'] = $data['priority'];
            $act['after_open_day'] = $data['after_open_day'];
            $act['show_pos_day'] = $data['show_pos_day'];
            $act['open_day'] = $data['open_day'];
            $act['end_day'] = $data['end_day'];
            $act['merge_st_day'] = $data['merge_st_day'];
            $act['merge_et_day'] = $data['merge_et_day'];
            $act['start_time'] = $data['start_time'];
            $act['end_time'] = $data['end_time'];
            $act['act_name'] = $data['act_name'];
            $act['act_desc'] = $data['act_desc'];
            $act['show_goods_list'] = $data['show_goods_list'];
            $act['icon'] = $data['icon'];
            $act['ad_pic'] = $data['ad_pic'];
            $act['kf_state'] = $data['kf_state'];
            $act['merge_times_list'] = $data['merge_times_list'];
            $act['conflict_list'] = $data['conflict_list'];
//            if($act['start_time']>0){
//                $act['start_time'] = date('Y-m-d H:i:s', $act['start_time']);
//            }else{
//                $act['start_time'] = 0;
//            }
//            if($act['end_time']>0){
//                $act['end_time'] = date('Y-m-d H:i:s',$act['end_time']);
//            }else{
//                $act['end_time'] = 0;
//            }
            $Info[] = $act;
        }
        return $Info;
    }

    private function show($html = '')
    {
        $excel = new Excelhandle();
        $acttype = $excel->get_rows("base_activity", array());
        $this->assign('alltype', $acttype);
        $platforms = $this->gameHelper->getActPlatformLists(true);
        $this->assign('group', $platforms);
        $this->assign('title', "活动系统");
        $this->display($html);
    }

    public function reloadActivity($type)
    {
        //先更新svn
        SMP_Act_Activity::update_svn();
        $res = SMP_Act_Activity::do_reload($type, $this->db);
        $this->activity_info("SMP/Act/Views/Activity.html", $type, 0);
        if ($res) {
            SMP_Act_Activity::del_cache($type);
            SMP_Act_Activity::commit_svn();
            echo "<script>alert('重载成功！注意：需要重启服务器才能生效!')</script>";
        } else {
            echo "<script>alert('重载失败！')</script>";
        }
    }

    public function reload_all()
    {
        //先更新svn
        SMP_Act_Activity::update_svn();
        $excel = new Excelhandle();
        $typedata = $excel->get_rows("base_activity", array());
        $reloadtypestr = "";
        $excel = new Excelhandle();
        foreach ($typedata as $data) {
            $actdata = $excel->get_rows("base_activity_data_" . $data['type'], array());
            $dd = array();
            foreach ($actdata as $d) {
                if ($d['type'] == $data['type']) {
                    $dd[] = $d;
                }
            }
            if ($data['type'] > 0) {
                $res = SMP_Act_Activity::do_reload($data['type'], $this->db, $dd);
            }
            if ($res) {
                SMP_Act_Activity::del_cache($data['type']);
            }
        }
        SMP_Act_Activity::commit_svn();
        $this->allActivityType("SMP/Act/Views/ActivityType.html");
        echo "<script>alert('重载成功！')</script>";

    }

    /**
     * 异步选中重载接口
     * @param $type
     * @return json
     */
    public function reload_select($type)
    {
        //先更新svn
        SMP_Act_Activity::update_svn();
        $success = $fail = [];
        if (!is_array($type)) $type = [$type];
        foreach ($type as $type_v) {
            $res = SMP_Act_Activity::do_reload($type_v, $this->db);
            if ($res) {
                $success[] = $type_v;
                SMP_Act_Activity::del_cache($t);
            } else
                $fail[] = $type_v;
        }
        if (!empty($fail))
            exit(json_encode(['state' => 0, 'data' => $fail]));
        else {
            SMP_Act_Activity::commit_svn();
            exit(json_encode(['state' => 1, 'data' => $success]));
        }
    }

    public static function do_reload($type, $db, $data = "")
    {
        if ($data == "") {
            $excel = new Excelhandle();
            $data = $excel->get_rows("base_activity_data_" . $type, array());
        }
        switch ($type) {
            case 1 : //累冲
                $act = new acc_charge();
                $res = $act->make($data);
                break;
            case 2 : //累计消费
                $act = new acc_consume();
                $res = $act->make($data);
                break;
            case 3 : //单笔充值
                $act = new one_charge();
                $res = $act->make($data);
                break;
            case 4 : //在线奖励
                $act = new online_gift();
                $res = $act->make($data);
                break;
            case 5 : //在线时长奖励
                $act = new online_time_gift();
                $res = $act->make($data);
                break;
            case 6 : //每日累充
                $act = new daily_acc_charge();
                $res = $act->make($data);
                break;
            case 7 : //开服广告
                $act = new ad();
                $res = $act->make($data);
                break;
            case 8 : //大富翁
                $act = new act_monopoly();
                $res = $act->make($data);
                break;
            case 9 : // 开服冲榜
                $act = new open_all_rank();
                $res = $act->make($data);
                break;
            case 10 : //护送活动
                $act = new act_convoy();
                $res = $act->make($data);
                break;
            case 11 : // 符文寻宝
                $act = new fuwen_map();
                $res = $act->make($data);
                break;
            case 12 : // 集字活动
                $act = new act_acc_char();
                $res = $act->make($data);
                break;
            case 13 : //连续累冲
                $act = new con_acc_charge();
                $res = $act->make($data);
                break;
            case 14 : //寻宝活动
                $act = new taobao_act();
                $res = $act->make($data);
                break;
            case 15 : //限时寻宝活动
                $act = new taobao_lt_act();
                $res = $act->make($data);
                break;
            case 16 : //幸运云购
                $act = new act_luck_yg();
                $res = $act->make($data);
                break;
            case 17 : //开宗立派
                $act = new act_kzlp();
                $res = $act->make($data);
                break;
            case 18 : //帮派争霸活动
                $act = new act_gbw();
                $res = $act->make($data);
                break;
            case 19 : //登陆有礼
                $act = new act_login_gift();
                $res = $act->make($data);
                break;
            case 20 : //累冲骑战52
                $act = new act_acc_charge();
                $res = $act->make($data);
                break;
            case 21 : //多倍经验
                $act = new act_extra_exp();
                $res = $act->make($data);
                break;
            case 22 : //兑换活动
                $act = new act_exchange();
                $res = $act->make($data);
                break;
            case 23 : //幸运鉴宝
                $act = new act_lucky_treasure();
                $res = $act->make($data);
                break;
            case 24 : //hi翻天
                $act = new act_hi_fan_tian();
                $res = $act->make($data);
                break;
            case 25 : //副本掉落双倍
                $act = new act_double_drop();
                $res = $act->make($data);
                break;
            case 26 : //首领活动
                $act = new act_festive_boss();
                $res = $act->make($data);
                break;
            case 27 : // 结婚排行榜
                $act = new marry_rank();
                $res = $act->make($data);
                break;
            case 28 : // 跨服活跃
                $act = new cross_vitality();
                $res = $act->make($data);
                break;
            case 29 : // 元宝周常排行
                $act = new act_gold_rank();
                $res = $act->make($data);
                break;
            case 30 : // 周常充值活动
                $act = new act_charge_gift();
                $res = $act->make($data);
                break;
            case 31 : // 烟花盛典
                $act = new fireworks();
                $res = $act->make($data);
                break;
            case 32 : //巅峰寻宝活动
                $act = new df_xunbao_act();
                $res = $act->make($data);
                break;
            case 33 : //巅峰限时寻宝活动
                $act = new df_xunbao_lt_act();
                $res = $act->make($data);
                break;
            case 34 : //0元礼包
                $act = new free_gift();
                $res = $act->make($data);
                break;
            case 36 : //跨服团购
                $act = new act_cross_tg();
                $res = $act->make($data);
                break;
            case 37 : //跨服鲜花榜
                $act = new cross_flower();
                $res = $act->make($data);
                break;
            case 38: //上上签
                $act = new act_pray();
                $res = $act->make($data);
                break;
            case 39: //跨服秒杀
                $act = new act_cross_ms();
                $res = $act->make($data);
                break;
            case 40: //砸蛋协议
                $act = new act_smash_egg();
                $res = $act->make($data);
                break;
            case 41: //跨服周常活动
                $act = new act_cross_week_boss();
                $res = $act->make($data);
                break;
            case 42: //雪踪寻宝
                $act = new act_xzxb();
                $res = $act->make($data);
                break;
            case 43: //连续豪冲
                $act = new act_continue_charge();
                $res = $act->make($data);
                break;
            case 44: //单服秒杀
                $act = new act_ms();
                $res = $act->make($data);
                break;
            case 45: //boss猎人
                $act = new act_boss_hunter();
                $res = $act->make($data);
                break;
            case 46:
                $act = new act_merge_hi_fan_tian();
                $res = $act->make($data);
                break;
            case 47:
                $act = new act_local_lucky_turn();
                $res = $act->make($data);
                break;
            case 48:
                $act = new act_tqxz();
                $res = $act->make($data);
                break;
            case 49:
                $act = new con_acc_consume();
                $res = $act->make($data);
                break;
            case 50:
                $act = new daily_acc_consume();
                $res = $act->make($data);
                break;
            case 51:
                $act = new discount_gift();
                $res = $act->make($data);
                break;
            case 52:
                $act = new act_acc_consume();
                $res = $act->make($data);
                break;
            case 53:
                $act = new act_world_cup();
                $res = $act->make($data);
                break;
            case 54: //至尊寻宝活动
                $act = new zj_xunbao_act();
                $res = $act->make($data);
                break;
            case 55: //至尊限时寻宝活动
                $act = new zj_xunbao_lt_act();
                $res = $act->make($data);
                break;
            case 56: //金银塔
                $act = new gold_tower();
                $res = $act->make($data);
                break;
            case 57 : //今日热卖
                $act = new today_hot_sale();
                $res = $act->make($data);
                break;
            case 58 : //手有余香
                $act = new act_flower_give_reward();
                $res = $act->make($data);
                break;
            case 59 :
                $act = new act_9377_vip_service();
                $res = $act->make($data);
                break;
            case 60 : //跨服云购
                $act = new cross_yg();
                $res = $act->make($data);
                break;
            case 61 : //首充双倍元宝
                $act = new acc_daily_charge1st();
                $res = $act->make($data);
                break;
            case 62 :
                $act = new act_cross_star();
                $res = $act->make($data);
                break;
            case 63 :
                $act = new act_cross_lucky_turn();
                $res = $act->make($data);
                break;
            default :
                $res = false;
                break;
        }
        return $res;
    }

    public static function get_file($type)
    {
        return false;
    }

    public static function set_cache($cache)
    {
        Cache::getInstance()->set(self::$key, $cache, 86400);
    }

    public static function get_cache()
    {
        return Cache::getInstance()->get(self::$key);
    }

    public static function del_cache($type)
    {
        $cache = SMP_Act_Activity::get_cache();
        $cache[$type] = false;
        SMP_Act_Activity::set_cache($cache);
    }

    public static function update_svn()
    {
        $url = UPSVN_URL;
        $html = file_get_contents($url);
        return $html;
    }

    public static function commit_svn()
    {
        $url = CMSVN_URL;
        $html = file_get_contents($url);
        return $html;
    }

    public function sync_platform()
    {
        $url = SYNC_PLATFORM_URL;
        file_get_contents($url);
        echo "<script>alert('同步成功！')</script>";
    }

    public function mtupActivity()
    {
        $url = UPSVN_URL;
        file_get_contents($url);
        echo "<script>alert('同步成功！')</script>";
    }

    public static function get_server_list()
    {
        $cache = Cache::getInstance()->get(self::$server_key);
        if ($cache == false) {
            $ret = Net::fetch(CENTER_URL);
            $result = (array)json_decode($ret, true);
            if (!$result || !is_array($result) || !$result['ret']) {
                return array(
                    'group' => array(),
                    'servers' => array()
                );
            } else {
                Cache::getInstance()->set(self::$server_key, $result);
                return $result;
            }
        } else {
            return $cache;
        }
    }

    public static function get_platform_list()
    {
        return array();
    }

    /**
     *  获取配置内容预览数据
     */
    static private function getPregGoodsPreview()
    {
        $content = Jec::getVar('content');
        if (!$content) exit('内容为空.');
        function matchCallbackFunc($matches)
        {
            global $Ggoods;
            return isset($Ggoods[$matches[0]]) ? '<span style="color:green;">' . $Ggoods[$matches[0]] . '</span>' : '<span style="color:red;">' . $matches[0] . '</span>';
        }

        exit(preg_replace_callback('/\d{5,}/', 'matchCallbackFunc', $content));
    }
}
