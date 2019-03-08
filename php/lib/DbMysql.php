<?php

/**
 * Description of mysql
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */
namespace lib;

class DbMysql {
    
    // 是否使用持久连接
    protected $pconnect   = false;
    // 当前SQL指令
    protected $sql   = '';
    // 错误信息
    protected $error      = '';
    // 数据库连接
    public $link     = null;
    // 当前查询
    protected $query    = null;
    // 是否已经连接数据库
    protected $connected  = false;
    // 数据库连接参数配置
    protected $config     = '';
    // 影响记录数
    protected $numRows = 0;
    public $pre = "";
    public $dbversion = null;


    /**
     * 构造函数
     */
    public function __construct() {
        if ( !extension_loaded('mysql') ) {
            exit('Does not support MYSQL extension ! ');
        }
        
        $config = $this->reutrncfg();
        if(empty($config) || !is_array($config)){
            exit('MYSQL configuration errors ! ');
        }
        
        $this->connect($config);
        unset($config);
    }
    
    protected function reutrncfg(){
        $config['DB_HOST'] = "127.0.0.1";
        $config['DB_USER'] = "root";
        $config['DB_PASS'] = "root";
        $config['DB_PORT'] = "3306";
        $config['DB_PCONNECT'] = 0;
        $config['DB_CHARSET'] = "utf8";
        $config['DB_NAME'] = "upay";
        return $config;
    }


    /**
     * 连接数据库
     * @param type $config
     */
    public function connect($config) {
            $host = empty($config['DB_HOST']) ? "localhost" : $config['DB_HOST'];
            $user = empty($config['DB_USER']) ? "admin" : $config['DB_USER'];
            $pass = empty($config['DB_PASS']) ? "" : $config['DB_PASS'];
            $port = empty($config['DB_PORT']) ? "3306" : $config['DB_PORT'];
            if($config['DB_PCONNECT']){ $this->pconnect = true; }

            if(!$this->pconnect){
                $this->link = mysqli_connect($host, $user, $pass, $config['DB_NAME'], $port);
            }else{
                $this->link = mysqli_connect($host, $user, $pass, $config['DB_NAME'], $port);
                //$this->link = mysqli_pconnect($host, $user, $pass);
            }
            if($this->link) $this->connected = true;
            mysqli_select_db($this->link,$config['DB_NAME']);
            $dbVersion = mysqli_get_server_info($this->link);
            $this->dbversion = $dbVersion;
            //使用UTF8存取数据库
            mysqli_query($this->link,"SET NAMES '".$config['DB_CHARSET']."'");
            //设置 sql_model
            if($dbVersion >'5.0.1'){
                mysqli_query($this->link,"SET sql_mode=''");
            }
            unset($config);
    }
    
    /**
     * 释放查询结果
     */
    public function free() {
        mysqli_free_result($this->query);
        $this->query = null;
    }
    
    public function version() {
        return $this->dbversion;
    }
    
    /**
     * 取得数据表的字段信息
     * @return array
     */
    public function getFields($tableName) {
        $result =   $this->_query('SHOW COLUMNS FROM '.$this->parseKey($tableName));
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[$val['Field']] = array(
                    'name'    => $val['Field'],
                    'type'    => $val['Type'],
                    'notnull' => (bool) ($val['Null'] === ''), // not null is empty, null is yes
                    'default' => $val['Default'],
                    'primary' => (strtolower($val['Key']) == 'pri'),
                    'autoinc' => (strtolower($val['Extra']) == 'auto_increment'),
                );
            }
        }
        return $info;
    }

    /**
     * 取得数据库的表信息
     * @return array
     */
    public function getTables($dbName='') {
        if(!empty($dbName)) {
           $sql    = 'SHOW TABLES FROM '.$dbName;
        }else{
           $sql    = 'SHOW TABLES ';
        }
        $result =   $this->_query($sql);
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }
    
    /**
     * 执行mysql原生查询
     * @param type $sql
     * @return type
     */
    public function query($sql) {
        $this->sql = $sql;
        $row = mysqli_query($this->link,$sql);
        return $row;
    }
    
    /**
     * 执行查询 返回数据集
     * @param type $str
     * @return boolean
     */
    public function _query($str) {
        //释放前次的查询结果
        if ( $this->query ) {    $this->free();    }
        $this->query = $this->query($str);
        if ( false === $this->query ) {
            return false;
        } else {
            $this->numRows = mysqli_num_rows($this->query);
            return $this->getAll();
        }
    }
    
     /**
     * SQL指令安全过滤
     * @param string $str  SQL字符串
     * @return string
     */
    public function escapeString($str) {
        if($this->link) {
            return mysqli_real_escape_string($this->link,$str);
        }else{
            return mysql_escape_string($str);
        }
    }
    
    
    /**
     * 获得所有的查询数据
     * @return array
     */
    private function getAll() {
        //返回数据集
        $result = array();
        if($this->numRows >0) {
            while($row = mysqli_fetch_assoc($this->query)){
                //如果表中有 id 字段，则以 id 字段为key
                if( isset($row['yixinu']) ){
                    $result[$row['yixinu']] = $row;
                }else{
                    $result[]   =   $row;
                }
//                $result[]   =   $row;
            }
            mysqli_data_seek($this->query,0);
        }
        return $result;
    }
    
    /**
     * 字段和表名处理添加`
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        $key   =  trim($key);
        if(!preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
           $key = '`'.$key.'`';
        }
        return $key;
    }
    
    /**
     * 创建一个UTF8编码的数据库
     * @param type $name   数据库名
     */
    public function createDatabase($name = NULL) {
        if(!empty($name)){
            $sql = "CREATE DATABASE  `$name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
            $result = mysqli_query($this->link,$sql);
        }
    }
    
    
    /**
     * 关闭数据库
     * @return void
     */
    public function close() {
        if ($this->link){
           @mysql_close($this->link);
        }
        $this->link = null;
    }
    
    /**
     * 析构函数
     */
    public function __destruct() {
        //如果使用了持久连接，则需要使用 close 函数 关闭mysql链接。
        if($this->pconnect){
            $this->close();
        }
    }
}
