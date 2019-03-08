<?php

namespace lib;

class yredis extends \Redis {

    public function __construct() {
        parent::__construct();
        //连接  redis  服务器
        $this->connect("127.0.0.1", 6379);
        //redis 服务器验证密码
        $this->auth('kyle');
        //end
    }

}
