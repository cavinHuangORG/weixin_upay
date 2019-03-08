<?php

ini_set('display_errors', 1);
header('X-Powered-By: 316686606@qq.com weixin:xianglou');
date_default_timezone_set("Asia/Shanghai");

include_once ROOTPATH . 'lib/DbMysql.php';
include_once ROOTPATH . "lib/model.php";
include_once ROOTPATH . "lib/yredis.php";
include_once ROOTPATH . "lib/function.php";

$r = @filter_input(INPUT_GET, "s");
$r = trim($r,'/');
$controller = 'home';
$action = 'index';
if (!empty($r)) {
    if (strpos($r, "/") !== false) {
        list($controller, $action) = explode('/', $r);
    } else {
        $controller = $r;
    }
    if (empty($action)) {
        $action = 'index';
    }
}
define("CONTROLLER", $controller);
define("ACTION", $action);

$c_file = ROOTPATH . '/controller/' . $controller . '.php';
if (file_exists($c_file)) {
    include_once $c_file;
} else {
    errmsg("访问方式错误 ，类文件不存在！");
}

$class_file = "\\controller\\" . $controller;
$class_ = new $class_file;
$class = new \ReflectionClass($class_file);
if ($class->hasMethod(ACTION)) {
    $before = $class->getMethod(ACTION);
    if ($before->isPublic()) {
        $before->invoke($class_, $_REQUEST);
    }
} else {
    errmsg("访问方式错误，方法不存在");
}


