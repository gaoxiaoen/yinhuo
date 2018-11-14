<?php
/**
 * Created by PhpStorm.
 * User: hxming
 * Date: 17-5-12
 * Time: 13:45
 */

class SMP_Log_EquipTaobao  extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '装备寻宝日志表');

        $this->show();
    }

    private function show(){
        $kw_key = g(Jec::getVar('kw_key'));
        if($kw_key) $where = " and pkey={$kw_key}";
        $kw_name = Jec::getVar('kw_name');
        if($kw_name) $where .= " and nickname ='{$kw_name}'";
        $time = $this->getWhereTime('time','0 day',true);
        $pager = new Pager();
        $pager->setTotalRows($this->db_game->getOne("select count(*) from log_equip_taobao where $time $where"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $data = $this->db_game->getAll("select * from log_equip_taobao where $time $where order by time desc limit $offset,$limit");
        global $Ggoods;
        foreach($data as &$d){
            $goodsstr = "";
            $d['goods_list'] = trim($d['goods_list'], '[');
            $d['goods_list'] = trim($d['goods_list'], ']');
            $a = explode('}', $d['goods_list']);
            foreach($a as $aa){
                $aa = trim($aa, ',');
                $aa = trim($aa, '{');
                $aa = trim($aa, '}');
                $b = explode(',', $aa);
                $gid = $b[0];
                $gnum = $b[1];
                $gname = $Ggoods[$gid];
                if($gid){
                    $goodsstr .= "{{$gid},{$gname},{$gnum}},";
                }
            }
            $goodsstr = trim($goodsstr, ",");
            $d['goods_list'] = $goodsstr;
        }
        if (Jec::getVar('download')) $this->csv_download($this->db_game->getAll("select * from log_equip_taobao  where $time $where order by time "));
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
        $csv->download('log_equip_wash.csv');
    }

}

