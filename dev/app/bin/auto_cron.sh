#!/bin/sh
##author fancy
##计划任务脚本

export PATH="/usr/kerberos/sbin:/usr/kerberos/bin:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin"

## 注意任务总长度不大于60
function run
{
	cd  `dirname $0`
	# 每n分钟执行一次run.php(0-59)
    run_by_min 2 Cron_Data global_mail          #检查全服邮件
    # 每n小时执行一次
    run_by_min 30 Cron_Data rate_player         #留存30
    run_by_min 15 Cron_Data reate_time          #时间流失率1
    run_by_min 10 Cron_Data rate_ltv            #7日ltv
    run_by_min 5  Cron_Data calc_summary        #注册登录统计
    run_by_min 5  Cron_Sensitive main      		#敏感词统计
    run_by_min 20 Cron_Data rate_online         #在线时长统计
    run_by_min 59 Cron_Coin gold_log         	#同步元宝流水变动数据
    run_by_min 60 Cron_Consume cal_consume_type #统计消费点元宝铜币消费数据
	## 定时执行
	run_at_time 5 5 Cron_Log del1               #删除日志
	run_at_time 5 15 Cron_Log del2              #删除日志
	run_at_time 5 25 Cron_Log del3              #删除日志
	run_at_time 0 5 Cron_Charge charge_calc 	#统计昨天充值
	run_at_time 23 59 Cron_Data calc_summary 	#统计今天注册登陆
    #run_by_min 1 Cron_Temp run
}



# 每x分钟执行一次
function run_by_min
{
	# 当前分钟数
	min=`date +%M`
	# 传入的变量 file为要执行的控制器和方法 t为时间

	local t=${1}
	local module=${2}
	local method=${3}
    local args=${4}
	# 每t分钟执行一次
	local b=$(( 10#$min % 10#$t ))
	if [ ${b} -eq 0 ] ; then
		   /bin/bash cron $module $method $args
	fi
}

# 每x小时执行一次
function run_by_hour
{
	# 当前小时数
	hour=`date +%H`
    min=`date +%M`
	# 传入的变量 file为要执行的控制器和方法 t为时间

	local t=${1}
	local module=${2}
    local method=${3}
    local args=${4}
	# 每t分钟执行一次
	local a=$(( 10#$min % 60 ))
	local b=$(( 10#$hour % 10#$t ))
	if [ ${a} -eq 0 -a ${b} -eq 0 ] ; then
		   /bin/bash cron $module $method $args
	fi
}

# 定点定时执行
function run_at_time
{
	# 当前小时数
	hour=`date +%H`
	# 当前分钟数
	min=`date +%M`

	# 传入的变量 file为要执行的控制器和方法 h 为小时 m 分钟
	local h=${1}
	local m=${2}
	local module=${3}
	local method=${4}
	local args=${5}

	if [ ${hour} -eq ${h} -a ${min} -eq ${m} ] ; then
		   /bin/bash cron $module $method $args
	fi
}

run
