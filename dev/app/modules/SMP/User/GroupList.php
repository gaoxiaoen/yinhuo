<?php
/**
 * Date: 12-3-14
 * Time: 上午9:40
 * 用户组管理
 */

class SMP_User_GroupList extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '用户组列表');

        $this->show();
    }

    private function show()
    {
        $pager = new Pager();
        $pager->setTotalRows($this->db->getOne("select count(*) from user_groups"));
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();

        $list = $this->db->getAll("select * from user_groups limit $offset,$limit");

        $menusM = SMP_Menu_Helper::getMenusByMod();

        foreach($list as &$Pval)
        {
            if($Pval['permissions'])
            {
                $perms = array();
                $exp = explode(',',$Pval['permissions']);
                foreach($exp as $mod)
                {
                    $mod = trim($mod);
                    $perms[$mod] = $menusM[$mod]['name'];
                }
                $Pval['permissions'] = implode('， ',$perms);
            }
        }

        $this->assign('list', $list);
        $this->assign('page', $pager->render());
        $this->display();
    }

}