<?php
/**
 *@copyright Jec
 *@package Jec框架
 *@link 
 *@author jecelyin peng
 *@license 转载或修改请保留版权信息
 * 文件缓存操作驱动类
 */

class Cache_File
{
    private $_expire = 0;
    private $_type = 0;
    private $_path = '';
    
    public function __construct($type='common')
    {
        global $CONFIG;
        $this -> _expire = $CONFIG['cache_expire'];
        $this -> _type = $type;
        $this ->_path = VAR_PATH . '/cache/'.$this->_type.'/';
        if(!is_dir($this->_path))
            mkdir($this->_path);
    }
    
    private function _getCacheFile($key)
    {
        $file = $this->_path . md5($key) . '.php';
        return $file;
    }
    
    public function get($key)
    {
        $cache_file = $this->_getCacheFile($key);
        
        //clearstatcache($cache_file);
        
        if (! is_file($cache_file))
            return false;
        
        $cache = unserialize(file_get_contents($cache_file));
        if($cache['expire'] < TIME)
        {
            return false;
        }
        return $cache['data'];
    }

    public function set($key, $val, $timeout = 0)
    {
        if(!$timeout)
            $timeout = $this -> _expire;

        return file_put_contents($this -> _getCacheFile($key), serialize(array('expire'=>TIME+$timeout,'data'=>$val)));
    }
    
    public function delete($key)
    {
        $file = $this->_getCacheFile($key);
        if(is_file($file))
            return @unlink($file);
        return false;
    }

    public function flush($type=null)
    {
        if($type == null)
            _rmdir(VAR_PATH . '/cache');
        else
            _rmdir(VAR_PATH. "/cache/$type");

    }
}