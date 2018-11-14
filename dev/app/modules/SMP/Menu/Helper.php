 <?php
/**
 * User: jecelyin 
 * Date: 12-2-13
 * Time: 下午5:10
 *
 */
 
class SMP_Menu_Helper
{
    public static $key = 'SMP_MENU_LIST';
    private static $menu_by_mods = array();

    public static function clearCache()
    {
        Cache::getInstance()->delete(self::$key);
        self::$menu_by_mods = array();
    }

    public static function getMenus($default=false)
    {
        global $CONFIG;

        $cache = Cache::getInstance();
        $data = $cache->get(self::$key);
        if($data === false){
            $data = DB::getInstance()->getAll("select * from menus order by pid asc,sort desc");
            $cache->set(self::$key, $data);
        }
        $list = array(); 
        $data = DB::getInstance()->getAll("select * from menus order by pid asc,sort desc");
        foreach($data as $val)
        {
            if($val['pid'] == 0)
            {
                $val['sub'] = array();
                $list[$val['id']] = $val;
            }else{
                if($_SESSION['permissions'] && !in_array($val['module'], $_SESSION['permissions']))
                    continue;
                $list[$val['pid']]['sub'][$val['module']] = $val;
            }
        }

        foreach($list as $pid => $subs)
        {
            if(count($subs) == 0)
                unset($list[$pid]);
            if($subs['name'] == 'GAMEDATA'){
                if($CONFIG['dev'] != true && $CONFIG['game']['sn'] < 40000)
                    unset($list[$pid]);
            }

        }
        return $list;
    }

    public static function getMenusByMod()
    {
        if(self::$menu_by_mods)
            return self::$menu_by_mods;
        self::$menu_by_mods = array();
        $list = self::getAllModule();
        foreach($list as $subs)
        {
            foreach($subs['sub'] as $sub)
            {
                self::$menu_by_mods[$sub['module']] = $sub;
            }
        }

        return self::$menu_by_mods;
    }


    public static function getAllModule()
    {
        $list = self::getMenus();
        return $list;
    }

    /**
     * 单服菜单列表，不考虑权限
     * @return array
     */
    public static function getMenusSync()
    {
        $data = DB::getInstance()->getAll("select * from menus_sync order by pid asc,sort desc");
        $list = array();
        foreach($data as $val)
        {
            if($val['pid'] == 0)
            {
                $val['sub'] = array();
                $list[$val['id']] = $val;
            }else{
                $list[$val['pid']]['sub'][$val['module']] = $val;
            }
        }

        foreach($list as $pid => $subs)
        {
            if(count($subs) == 0)
                unset($list[$pid]);
        }
        return $list;
    }


}