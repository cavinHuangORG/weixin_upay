<?php

/**
 * 添加系统日志
 */
function addlog($arr = array(), &$m = null) {
    $m = $m ?: new \lib\model();
    $data = array(
        'from' => 'php',
        'title' => $arr['title'],
        'detail' => $arr['detail'],
        'action' => $arr['action'],
    );
    $m->save($data, "system_log");
}


/**
 * 判断是否用手机或电脑浏览
 * @return boolean 如果是用手机或微信浏览则返回true，如果用电脑浏览则返回false
 */
function judgeMobileBrowse() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'MicroMessenger') || strpos($user_agent, 'Android') || strpos($user_agent, 'Android') || strpos($user_agent, 'iPhone')) {
        return true;
    } else {
        return false;
    }
}

function errmsg($content) {
    header("Content-type: text/html; charset=utf-8");
    echo "<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"></head><div style='margin:60px auto;text-align:center;color:#666;'><h3>$content</h3></div></html>";
    die;
}

/**
 * 返回JSON数据
 * @param type $param
 */
function ajax($param) {
    header('Access-Control-Allow-Origin:*');
    // 返回JSON数据格式到客户端 包含状态信息
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($param));
}



/**
 * 发送socket消息
 * @param type $send_socket_data    发送的数据
 * @param type $mch_id  商户ID
 * @return boolean
 */
function sendsocket($data = array()) {
    $ip = '127.0.0.1';
    $port = 4188;
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    //发送socket
    if ($socket < 0) {
        addlog(array('title' => '创建socket失败', 'action' => CONTROLLER . '/' . ACTION, 'detail' => '无法连接本地4188服务端'));
        return false;
    } else {
        $result = socket_connect($socket, $ip, $port);
        if ($result < 0) {
            addlog(array('title' => '创建socket失败', 'action' => CONTROLLER . '/' . ACTION, 'detail' => '无法连接本地4188服务端'));
            return false;
        } else {
            $in = json_encode($data);
            if (!socket_write($socket, $in, strlen($in))) {
                //echo "发送失败";
                addlog(array('title' => 'socket发送数据失败', 'action' => CONTROLLER . '/' . ACTION, 'detail' => '连接4188服务端后，无法发送数据'));
                return false;
            } else {
                //echo "发送成功";
                sleep(1);
                socket_close($socket);
                return true;
            }
        }
    }
}
