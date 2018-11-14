<?php
/**
 * User: jecelyin 
 * Date: 12-2-24
 * Time: 下午2:57
 * 定时任务
 */

require dirname(__FILE__).'/../../Jec/booter.php';

#*/5 * * * * /usr/bin/php app/bin/cron.php SMP_ACD

$module = $argv[1];
$method = $argv[2];
$args = array();
if($argc > 2)
{
    $args = explode("#",$argv[3]);
}

$array = array(
    'method'=>$method,
    'args'=>$args
);

if(!$module)
    throw new JecException('Usage: php '.__FILE__.' MODULE_NAME --gs_id=xx');
//限制调用模块
if(strpos($module, 'Cron_') !== 0)
    throw new JecException('You can not run a non-crontab module!');

new $module($array);

//exec("screen -S $module -X quit");

$lockFile = "../../var/cache/phpcron_{$module}.lock";
if(is_file($lockFile))
    unlink($lockFile);