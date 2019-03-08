<?php

/**
 * Description of model
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace lib;

class model {

    protected $db = null;
    public $prefix = null;
    protected $table = null;
    protected $field = null;
    protected $join = null;
    protected $where = null;
    public $link = null;
    public $lastsql = null;

    /**
     * 
     * @param string $name  模型名称
     * @param string $prefix   前缀
     */
    public function __construct($name = '') {

        $this->db = new DbMysql();
        $this->link = $this->db->link;
    }

    public function connect() {
        if ($this->db && $this->link) {
            $this->db->close();
        }
        $this->db = new DbMysql();
        $this->link = $this->db->link;
        $this->prefix = $this->db->pre;
    }

    public function dbversion() {
        return $this->db->version();
    }

    public function get_field($tabname) {
        return $this->db->getFields($this->prefix . $tabname);
    }

    /**
     * 过滤POST数组内，不存在的表字段
     * @param type $tabname  表名
     */
    public function filter_field($tabname = null, $post = array()) {
        if ($tabname && $post) {
            $field_arr = $this->db->getFields($this->prefix . $tabname);
            if ($field_arr) {
                foreach ($post as $k => $v) {
                    if (is_string($post[$k]))
                        $post[$k] = $this->db->escapeString(trim($post[$k]));
                }

                $rr = array_diff_key($post, $field_arr);
                foreach ($rr as $key => $value) {
                    unset($post[$key]);
                }
                return $post;
            }
        }
        return false;
    }

    /**
     * 过滤 
     * @param type $str
     * @return type
     */
    public function escapeString($str) {
        return $this->db->escapeString($str);
    }

    public function g($str) {
        return $this->escapeString($str);
    }

    /**
     * mysql 原生 查询 
     * @param string $sql
     * @return type  mysql 语句  执行结果
     */
    public function query($sql) {
        return $this->db->query($sql);
    }

    /**
     * 执行select 查询 ，并返回 数组 
     * @param type $sql
     * @return type
     */
    public function _query($sql) {
        return $this->db->_query($sql);
    }

    /**
     * 执行select 查询 ，并返回 数组 
     * @param type $sql
     * @return type
     */
    private function getall($sql) {
        return $this->db->_query($sql);
    }

    //框架改版升级后用这个方法
    public function select($sql) {
        return self::getall($sql);
    }

    /**
     * 从一条语句 获取 一行的一个字段 
     * @return string|boolean
     */
    private function getone($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            $row = mysqli_fetch_row($res);
            if ($row !== false) {
                return $row[0];
            } else {
                return '';
            }
        } else {
            return false;
        }
    }

    //框架改版升级后用这个方法
    public function field($sql) {
        return self::getone($sql);
    }

    /**
     * 获取一行数据 
     * @param type $sql
     * @return boolean
     */
    private function getrow($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            return mysqli_fetch_assoc($res);
        } else {
            return false;
        }
    }

    //框架改版升级后用这个方法
    public function find($sql) {
        return self::getrow($sql);
    }

    /**
     * 更新或插入数据
     * @param array $data  数据
     * @param string $tableName  表名,不要加前缀
     * @param type $act  操作类型，i为插入，u为更新
     * @param type $where  更新数据($act等于u)的where的条件, 前面不要加 where 字符
     */
    private function sData($data = null, $tableName = null, $act = 'i', $where = null) {
        if (!empty($data) and ! empty($tableName)) {
            if ($act == 'i') {
                $field = null;
                $val = null;
                foreach ($data as $key => $value) {
                    $field .= "`$key`,";
                    if ($value == 'now()') {
                        $val .= "$value,";
                    } elseif (substr($value, -3) == '---') {
                        $val .= substr($value, 0, -3) . ",";
                    } else {
                        $val .= "'".$this->g($value)."',";
                    }
                }
                $field = substr($field, 0, -1);
                $val = substr($val, 0, -1);
                $sql = "insert into `" . $this->prefix . "$tableName` ($field) values ($val)";
                $this->lastsql = $sql;
                $result = $this->query($sql);
                if ($result) {
                    return mysqli_insert_id($this->db->link);
                }
            } elseif ($act == 'u') {
                $val = null;
                foreach ($data as $key => $value) {
                    if ($value == 'now()') {
                        $val .= "`$key`=$value,";
                    } elseif (substr($value, -3) == '---') {  //尾部加3个-符号，表示这个字段不要加单引号
                        $val .= "`$key`=" . substr($value, 0, -3) . ",";
                    } else {
                        $val .= "`$key`='".$this->g($value)."',";
                    }
                }
                $val = substr($val, 0, -1);
                $sql = "update `" . $this->prefix . "$tableName` set $val where $where";
                $this->lastsql = $sql;
                $result = $this->query($sql);
                if ($result) {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    //框架改版升级后用这个方法
    public function save($data = null, $tableName = null, $act = 'i', $where = null) {
        return self::sData($data, $tableName, $act, $where);
    }

    /**
     * 删除数据  
     * @param type $tablename  表名 ,不要加前缀
     * @param type $where  where条件，不要加 where 关键字
     * @return boolean
     */
    private function sDelete($tablename = null, $where = null) {
        $sql = "delete from `" . $this->prefix . "$tablename` where $where";
        $result = $this->query($sql);
        if ($result) {
            return true;
        }
    }

    //框架改版升级后用这个方法
    public function delete($tablename = null, $where = null) {
        return self::sDelete($tablename, $where);
    }

}
