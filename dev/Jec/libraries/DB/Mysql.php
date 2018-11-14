<?php
/**
 *@copyright Jec
 *@package Jec框架
 *@link 
 *@author jecelyin peng
 *@license 转载或修改请保留版权信息
 * Mysql数据库连接驱动
 */
class DB_Mysql
{
    //当前连接ID
    public $link_id = null;

    //最后插入ID
    public $insert_id = 0;
    //查询次数
    public $count = 0;

    public function __construct($cfg,$dbtype = null)
    {
        $this->link_id = new mysqli($cfg['host'],$cfg['user'],$cfg['pwd'],$cfg[$dbtype]);
        if(!$this->link_id)
            $this->_halt('无法打开数据库'.$cfg[$dbtype]);
        $cfg['charset'] = preg_replace('/utf\-(\d+)/i', 'utf\1', $cfg['charset']);
        $this->link_id->query("SET NAMES {$cfg['charset']}");
    }


    /**
     * 执行一个查询
     * @param string $sql
     * @return int|resource 返回删除与更新语句将返回影响行数
     *      插入与替换语句将返回最后插入id
     *      或resource For SELECT, SHOW, DESCRIBE, EXPLAIN and other statements returning resultset, mysql_query
     */
    public function query($sql)
    {
        $query_id = $this->link_id->query($sql);
        ! $query_id && $this->_halt('QUERY STRING: ' . str_replace(array("\n", "\r"), '', $sql));
        $this->count ++;
        $sql = trim($sql);
        //删除或更新时返回影响行数
        if (preg_match("/^(delete|update) /i", $sql))
            return mysqli_affected_rows($this->link_id);
            //插入或替换时返回最后影响的ID
        if (preg_match("/^(insert|replace) /i", $sql))
        {
            $this->insert_id = mysqli_insert_id($this->link_id);
            return $this->insert_id;
        }
        return $query_id;
    }

    /**
     * 获取一个查询结果数组
     * @param resource $result resource For SELECT, SHOW, DESCRIBE, EXPLAIN and other statements returning resultset, mysql_query
     * @param int $result_type 类型: MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
     * @return array
     */
    public function fetchArray($result, $result_type = MYSQL_ASSOC)
    {
        $array = mysqli_fetch_array($result, $result_type);
        //is_array($result) && $result = array_map('trim', $result);
        //mysql_free_result($queryId);
        return $array;
    }

    /**
     * 获得查询结果的第一行数组
     * @param string $query resource For SELECT, SHOW, DESCRIBE, EXPLAIN and other statements returning resultset, mysql_query
     * @param int $result_type 类型: MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
     * @return array
     */
    public function getRow($query, $result_type = MYSQL_ASSOC)
    {
        return $this->fetchArray($this->query($query), $result_type);
    }
    
    /**
     * 获取查询结果中的第一条第几列
     * @param string $query 查询语句
     * @param int $offset 第几列
     * @return bool|string
     */
    public function getOne($query, $offset = 0)
    {
        $result = mysqli_fetch_row($this->query($query));
        return $result === false ? false : $result[$offset];
    }

    /**
     * 返回所有查询结果集
     * @param string $query 查询语句
     * @param int $result_type 返回结果方式 MYSQL_ASSOC, MYSQL_NUM
     * @return array
     */
    public function getAll($query, $result_type = MYSQL_ASSOC)
    {
        $query_id = $this->query($query);
        $cacheArray = array();
        while ($result = $this->fetchArray($query_id, $result_type))
        {
            $cacheArray[] = $result; //trim($result);
        }
        return $cacheArray;
    }

    /**
     * @param $query
     * @param $data_type
     *        type = '' ,       use 2d array and the key is auto_increment number.
     *        type = 'key' ,    use 2d array and the key is the field which need to be Unique in the sql search resule or the former will be replace by the latter.
     *        type = 'value',   use 1d array to return the field.
     * @param $column_field
     * @param int $result_type
     */
    public function fetchAll($query, $data_type = '', $column_field = '', $result_type = MYSQL_ASSOC)
    {
        $query_id = $this->query($query);
        $cacheArray = array();
        switch ($data_type)
        {
            case 'key':
                if(is_array($column_field))
                {
                    $count = count($column_field);
                    if(!$count) new JecException('bad param: column_field');
                    switch ($count)
                    {
                        case '1':
                            while ($result = $this->fetchArray($query_id, $result_type))
                            {
                                $cacheArray[$result[$column_field[0]]] = $result; //trim($result);
                            }
                            break;
                        case '2':
                            while ($result = $this->fetchArray($query_id, $result_type))
                            {
                                $cacheArray[$result[$column_field[0]]][$result[$column_field[1]]] = $result; //trim($result);
                            }
                            break;
                        case '3':
                            while ($result = $this->fetchArray($query_id, $result_type))
                            {
                                $cacheArray[$result[$column_field[0]]][$result[$column_field[1]]][$result[$column_field[2]]] = $result; //trim($result);
                            }
                            break;
                    }
                }else{
                    while ($result = $this->fetchArray($query_id, $result_type))
                    {
                        $cacheArray[$result[$column_field]] = $result; //trim($result);
                    }
                }
                break;
            case 'value':
                 while ($result = $this->fetchArray($query_id, $result_type))
                 {
                     $cacheArray[] = $result[$column_field]; //trim($result);
                 }
                 break;
             default:
                while ($result = $this->fetchArray($query_id, $result_type))
                {
                    $cacheArray[] = $result; //trim($result);
                }
        }
        return $cacheArray;
    }

    /*
    *事务
    * @param array $sqllist
    */
    public function transaction($SqlList) {
        if(is_array($SqlList)){
            $res = 1;
            $this->link_id->query("BEGIN");
            foreach($SqlList as $sql){
                $result = $this->link_id->query($sql);
                if($result === FALSE) {
                    $res = "SQL:{$sql} ".mysql_error();
                    //exit($sql);
                    break;
                }
           }
           if($res == 1) $this->link_id->query("COMMIT");
           else $this->link_id->query("ROLLBACK");
           return $res;
        }
        else die("操作错误");
    }

    /**
     * 返回最后插入ID
     * @return int
     */
    public function getInsertId()
    {
        if ($this->insert_id)
        {
            return $this->insert_id;
        } else
        {
            return mysqli_insert_id($this->link_id);
        }
    }

    /**
     * 根据数组组织成一条查询语句
     * @param string $action 操作动作名称：insert,replace,update
     * @param string $table 表名
     * @param array $data 数据内容 array(字段名=>值)
     * @param array $where 条件
     * @return string
     */
    public function getSql($action, $table, $data, $where = array())
    {
        switch (strtolower($action))
        {
            case 'insert':
            case 'replace':
                $fields = array_keys($data);
                return strtoupper($action) . " INTO `$table` (`" . implode('`,`', $fields) . "`) VALUES ('" . implode("','", $data) . "')";
            case 'update':
            case 'delete':
                $sp = $set = $w = '';
                if($data)
                {
                    foreach ($data as $k => $v)
                    {
                        $set .= $sp . "`$k` = '{$v}'";
                        $sp = ', ';
                    }
                }

                if ($where)
                {
                    $sp = '';
                    if (is_array($where))
                    {
                        foreach ($where as $k => $v)
                        {
                            $w .= $sp . (is_array($v) ? "`$k` IN('".implode("','", $v)."')" : "`$k` = '$v'");
                            $sp = ' AND ';
                        }
                    }else{
                        $w = $where;
                    }
                }
                if($action == 'update')
                {
                    return strtoupper($action) . " `{$table}` SET $set WHERE $w";
                }else{
                    return strtoupper($action) . " FROM `{$table}` WHERE $w";
                }
        }
        return false;
    }

    /**
     * 插入一条数据
     * @param string $table 表名
     * @param array $data 数据内容 array(字段名=>值)
     * @return int 最后插入ID
     */
    public function insert($table, $data)
    {
        return $this->query($this->getSql('insert', $table, $data));
    }

    /**
     * 替换一条数据
     * @param string $table 表名
     * @param array 数据内容 array(字段名=>值)
     * @return int 最后插入ID
     */
    public function replace($table, $data)
    {
        return $this->query($this->getSql('replace', $table, $data));
    }

    /**
     * 更新数据
     * @param string $table 要更新的表名
     * @param string $data 数据内容 array(字段名=>值)
     * @param array $where 更新对象的数组或字符串
     * @return int 影响行数
     */
    public function update($table, $data, $where)
    {
        return $this->query($this->getSql('update', $table, $data, $where));
    }

    /**
     * 删除数据
     * @param string $table 表名
     * @param array $where Where条件，可以是数组或字符串
     * @return int 影响行数
     */
    public function delete($table, $where)
    {
        return $this->query($this->getSql('delete', $table, array(), $where));
    }

    /**
     * 返回上条语句执行影响行数
     * @return int
     */
    public function getAffectedRows()
    {
        return mysqli_affected_rows($this->link_id);
    }

    /**
     * 返回查询行数
     * @param string $query 查询语句
     * @return int
     */
    public function getRowsNum($query)
    {
        return mysqli_num_rows($this->query($query));
    }

    private function _halt($msg)
    {
        $error = mysqli_error($this->link_id);
        $errno = mysqli_errno($this->link_id);
        throw new JecException("MySQL Error($msg):\n [{$errno}] {$error} \n", 999);
    }
}
