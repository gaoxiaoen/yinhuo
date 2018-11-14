<?php

/**
 * @param $module 目标菜单
 * @return mixed 继承的权限菜单
 */
function getSubMenu($module){
    $menus = array(
        'SMP_GM_ReplyFeedback' => 'SMP_GM_Feedback',
        'SMP_Game_SrvEdit' => 'SMP_Game_SrvList',
        'SMP_Game_Edit' => 'SMP_Game_List',
        'SMP_Center_GsEdit' => 'SMP_Center_GsList',
        'SMP_GM_GlobalMailEdit' => 'SMP_GM_GlobalMail',
        'SMP_Center_NoticeEdit' => 'SMP_Center_Notice',
        'SMP_Center_CardsEdit' => 'SMP_Center_Cards',
        'SMP_Center_IPSEdit' => 'SMP_Center_IPS',
        'SMP_Game_ListChange' => 'SMP_Game_List',
        'SMP_Center_BroadcastEdit' => 'SMP_Center_Broadcast',
        'SMP_Center_PlatformEdit' =>'SMP_Center_Platform',
        'SMP_Center_ChannelEdit' => 'SMP_Center_Channel',
        'SMP_Center_ChannelGroupEdit' => 'SMP_Center_ChannelGroup',
        'SMP_Center_VersionGameEdit' => 'SMP_Center_VersionGame',
        'SMP_Center_VersionResEdit' => 'SMP_Center_VersionRes',
        'SMP_Center_VersionCustomEdit' => 'SMP_Center_VersionCustom',
        'SMP_Player_Detail' => 'SMP_Player_List',
        'SMP_Player_Info' => 'SMP_Player_List'
    );
    return isset($menus[$module]) ? $menus[$module] : $module;

}