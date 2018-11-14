<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 0:18
 */
class SMP_Log_ActWlv extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '世界等级日志');
        $this->show();
    }

    private function show(){
        $where = "1";
//        $kw_key = g(Jec::getVar('kw_key'));
//        if($kw_key) $where = " and pkey={$kw_key}";
//        $kw_name = Jec::getVar('kw_name');
//        if($kw_name) $where .= " and pname ='{$kw_name}'";
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from act_wlv_log where  $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from act_wlv_log  where  $where  limit $offset,$limit");
        foreach($data as $key => $val){
            if($val['key1'] > 1483200000){
                $data[$key]['key1'] =  getDateStr($val['key1'],'Y-m-d');
            }
        }
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select * from act_wlv_log  where  $where"));
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
        $csv->download('act_wlv_log.csv');
    }

}

