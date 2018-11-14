<?php
/**
 * author fancy
 * 2016.06.07
 */

class PhpRun
{
    public $cmd ;
    public $log ;
    private $run ;
    private $buffsize;

    public function __construct($cmd,$log,$run = null){
        if($run == null){
            $this->run = APP_PATH."/bin/run";
        }else
            $this->run = $run;
        $this->buffsize = 1024 * 2048;
        $this->cmd = $cmd;
        $this->log = $log;
        if(!is_file($cmd))
            throw new JecException("file $cmd not exists!");
        if(!is_file($this->run))
            throw new JecException("run file not exists!");
    }

    public function run()
    {
        set_time_limit(60);
        echo "<pre>";
        system("$this->run $this->cmd $this->log");
        echo "</pre>";
//        $this->clean_buffers();
//        $handle = fopen($this->log, "rb");
//        if ($handle) {
//            do{
//                if($buffer = fgets($handle, 4096)){
//                    echo $buffer . "<br/>";
//                    $this->flush_buffers();
//                }else
//                usleep(10000);
//            }while($buffer != ':)' && $buffer != '');
//            fclose($handle);
//        }else{
//            echo "read err";
//        }

    }
    public function clean_buffers()
    {
        ob_end_clean ();
        ob_start(array('PhpRun','ob_callback'));
    }

    public function flush_buffers()
    {
        ob_flush();
        flush();
    }

    public function ob_callback($buffer)
    {
        return str_pad ( $buffer, $this->buffsize);
    }

}