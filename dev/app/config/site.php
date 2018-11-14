<?php
//游戏内自定义全局变量

//配置文件路径
$GCfgFile ='/data/ctl/init.sh';

//自定义分组
$GCustomGroup = array(
    1 => array('st'=>1000,'et'=>1999,'name'=>'大混服'),
);

//位置
$GPosName = array(1=>'普通道具背包',2=>'装备背包',3=>'宝石背包' ,4=>'符文背包');
//装备颜色
$GColor = array(0=>'白',1=>'绿',2=>'蓝',3=>'紫',4=>'橙',5=>'神');
//游戏状态
$GState = array(0=>'正常',1=>'推荐',2=>'新服',3=>'维护',4=>'火爆',5=>'即将开启',6=>'测试中',7=>'停服',8=>'计划开服',9=>'禁止创角');

$GViewStatus = array(
    0 => '<span style="color:#f00000">主服</span>',
    1 => '<span style="color:#009966">混服</span>',
    2 => '<span style="color:#333333">屏蔽服</span>',
    3 => '<span style="color:#999999;">已合服</span>',
);
$GViewYesno = array(
    1 => '<span style="color:#009966">是</span>',
    0 => '<span style="color:#999999;">否</span>',
);
$GViewEnable = array(
    1 => '<span style="color:#009966">可见</span>',
    0 => '<span style="color:#999999;">不可见</span>',
);
$GViewIPType = array(
    0 => '<span style="color:#009966">白名单</span>',
    1 => '<span style="color:#999999;">黑名单</span>',
);
$GViewState = array(
    0 => '<span style="color:#390">正常</span>',
    1 => '<span style="color:#f00000">推荐</span>',
    2 => '<span style="color:#009966">新服</span>',
    3 => '<span style="color:#999999">维护</span>',
    4 => '<span style="color:#f00000">火爆</span>',
    5 => '<span style="color:#003399">即将开启</span>',
    6 => '<span style="color:#333333;">测试中</span>',
    7 => '<span style="color:#cccccc">停服</span>',
    8 => '<span style="color:#009966">计划开服</span>',
    9 => '<span style="color:#f00000">禁止创角</span>'
);

$GType_name = array(
    '0'=>"通用"
);
//职业
$GCareer = array(
    '1' => '男性职业',
    '2' => '女性职业'
);
//渠道号
$Gqdname = array(
    '2001' => 'XY',
    '2002' => '爱思',
    '2003' => '海马',
    '2004' => 'i苹果',
    '2005' => 'pp',
    '2006' => 'itools',
    '2007' => '91',
    '2008' => '快用',
    '2009' => '同步推'
);

//
$GShopType = array(
    1   =>      '每周限购',
    2   =>      '常用道具',
    3   =>      '成长道具',
    4   =>      '绑定元宝商店',
    5   =>      '荣誉商店',
    6   =>      '声望商店',
    7   =>      '功勋商店',
    8   =>      '历练商店',
);

$GMoneyType = array(
    0   =>      '错误',
    1   =>      '经验',
    2   =>      '金币',
    3   =>      '情报值',
    4   =>      '水晶',
    5   =>      '补给值',
    6   =>      '声望',
    7   =>      '速通代币'
);

$GChatType = array(
    1   =>  '世界',
    2   =>  '公会',
	3   =>  '私聊',
//  4   =>  '公会',
//  6   =>  '跨服',
//  7   =>  '神魔世界'
);

// 商店类型
$GShopType = array(
	0 => '神秘商店',
	10001 => '商店'
);

$GJumpWhiteList = array(
    
);

// 任务状态
$GTaskState = array(
	0 => '未接取',
	1 => '已接未获得',
	2 => '奖励可领取',
	3 => '已完成',
	4 => '完成失败'
);

//邮件状态
$GMailState = array(
	0 => "未读",
	1 => "已读",
	2 => "已提取",
	3 => "已删除",
	4 => "已失效"
);
