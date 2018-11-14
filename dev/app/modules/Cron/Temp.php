<?php
/**
 *
 * 临时跑一些数据
 */
 
class Cron_Temp
{
    private $data = array();

    public function __construct($data)
    {

        $method = $data['method'];
        $this->$method();

    }

    private function run()
    {
        $time = time();
        file_put_contents(VAR_PATH."/log/$time.log","run");
    }



}