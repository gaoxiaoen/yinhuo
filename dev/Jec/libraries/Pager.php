<?php
/**
 *@copyright Jec
 *@link 
 *@author jecelyin peng
 *@license 转载或修改请保留版权信息
 */
class Pager
{
    /**
     * $query 查询语句
     * $pageRows 每页显示行数
     * $url 地址: list-{page}.html
     * @uses $page = new Pager(array(
     * 'url' => '',
     * 'query' => '',,
     * ));
     * example:
     * Template:{echo $page->display()}
     *
     **/
    public $pageRows = 30; //每页显示行数
    public $url = '';
    public $page = 0; //指定当前页，不指定将取提交的page参数
    private $totalPage = 0; //总页数
    public $totalRows = 0; //记录总数
    public $style = 'i'; //风格 default
    public $left = 2; //左边显示个数
    public $right = 7; //显示右边列表的页的个数，如 1..3 4 5
    private static $styleFile = ''; //为当前程序默认一个全局默认的样式

    public function __construct ($param=array())
    {
        $cachePageNum = Cache::getInstance()->get('user_set_page_num_cache');
        $this->pageRows = $cachePageNum[$_SESSION['id']] ? $cachePageNum[$_SESSION['id']] : 30;
        if (isset($param['page']))
            $page = (int) $param['page'];
        else
            $this->page ? $page = $this->page : $page = Jec::getInt('page');

        //处理参数
        foreach ($param as $key => $val)
            $this->$key = $val;
        unset($param);
        $this->page = $page;
        if(! is_numeric($this->page))$this->page = 1;
        if($this->page < 1)$this->page = 1;

        $req = $_GET;
        foreach($_POST as $key => $val)
            $req[$key] = $val;
        unset($req['page']);
        if(! $this->url) $this->url = '?' . http_build_query($req);

        if($this->pageRows < 1)
            return;
    }

    /**
     * 设置总行数
     * @param $num
     */
    public function setTotalRows($num)
    {

        $this->totalRows = $num;
        $totalPage = max(1,ceil($this->totalRows / $this->pageRows));
        if($this->page > $totalPage)
            $this->page = $totalPage;
    }

    /**
     * 提供数据库分页偏移，limit offset, pageRows
     * @return int
     */
    public function getOffset()
    {
        return ($this->page - 1) * $this->pageRows;
    }

    /**
     * 返回每页行数
     * @return int
     */
    public function getLimit()
    {
        return (int)$this->pageRows;
    }

    private function getUrl ($page)
    {
        $url = $this->url;
        //如果有{page}则为静态处理
        if (strpos($url, '{p}') !== false)
            return str_replace('{p}', $page, $url);
        strpos($url, '?') === false ? $url .= "?page=$page" : $url .= "&amp;page=$page";
        return $url;
    }

    /**
     * 获取 1 2 3 4 5 6 7 8 9 10 ..16 这样的列表的左页数，右页数
     * @return array (curPage, left, right)
     */
    private function get_page_list ()
    {
        $curPage = $this->page; //当前页
        $totalRows = $this->totalRows; //总行数
        $totalPage = $this->totalPage; //总页数
        if ($curPage > $totalPage)
        {
            $curPage = $totalPage;
        }
        $show_right_num = $this->right;
        $show_left_num = $this->left; //左边页的个数
        $show_total_num = $show_left_num + $show_right_num + 1;
        if ($show_total_num > $totalPage)
        {
            $left = 1;
            $right = $totalPage;
        } else
        {
            if ($curPage - $show_left_num > 1)
            {
                $left = $curPage - $show_left_num;
            } else
            {
                $left = 1;
                $show_right_num = $show_left_num - $curPage + $show_right_num +
                 1;
            }
            $right = $curPage + $show_right_num;
            if ($right >= $totalPage)
            {
                //如果右页数大于总页数，右页数肯定为总页数
                $right = $totalPage;
                //保证要显示的页的个数
                $left = $totalPage - $show_total_num + 1 >
                 1 ? $totalPage - $show_total_num + 1 : 1;
            }
        } //end if
        return array('curPage' => $curPage, 'left' => $left, 
        'right' => $right);
    }

    /**
     * @static
     * 设置全局样式
     * @param $file 样式文件完整路径
     */
    public static function setDefaultStyleFile($file)
    {
        self::$styleFile = $file;
    }

    /**
     * @return string 返回分页html内容
     */
    public function render()
    {
        $totalPage = max(1,ceil($this->totalRows / $this->pageRows));
        if ($this->totalPage && $totalPage > $this->totalPage)
            $totalPage = $this->totalPage;
        $this->totalPage = $totalPage;

        $stylefile = self::$styleFile ? self::$styleFile : LIB_PATH . '/Pager/styles/' . $this->style . '.php';

        return require($stylefile);
    }
    //end class
}
