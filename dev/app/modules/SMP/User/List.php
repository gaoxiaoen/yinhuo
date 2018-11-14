<?php
/**
 * User: jecelyin 
 * Date: 12-2-17
 * Time: 下午2:41
 *
 */
 
class SMP_User_List extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('title', '用户列表');
        $this->show();
    }

    public function show()
    {
        $where = $this->getWhereTime('last_login_time','0 day',false,'all');
        if($_SESSION['user_group_id'])
        {
            $where .= " and creater_login_name='{$_SESSION['login_name']}'";
        }
        $total_rows = $this->db->getOne("select count(*) from users where $where");

        $pager = new Pager();
        $pager->setTotalRows($total_rows);
        $offset = $pager->getOffset();
        $limit = $pager->getLimit();
        $groupList = $this->gameHelper->getCustomGroups();

        $users = $this->db->getAll("select * from users where $where order by last_login_time desc limit $offset,$limit");
        foreach($users as &$P)
        {
            if($P['user_group_id'])
            {
                $P['user_group_name'] = $this->db->getOne("select name from user_groups where id = {$P['user_group_id']}");
            }else{
                $P['user_group_name'] = 'Root';
            }

            if($P['group'])
            {
                $groups = explode(',', $P['group']);
                foreach($groups as $k=>$groupId)
                {
                    if($groupId < 1)continue;

                    if(!isset($groupList[$groupId]))
                    {
                        $groups[$k] = '分组错误';
                    }else
                    $groups[$k] = $groupList[$groupId]['name'];
                }
                $P['group'] = implode(', ', $groups);
            }else{
                $P['group'] = '-';
            }

        }

        $this->assign('users', $users);
        $this->assign('page', $pager->render());
        $this->display();
    }
}