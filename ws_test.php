<?php

use Workerman\Worker;
use \Workerman\Lib\Timer;

require_once __DIR__ . '/Workerman/Autoloader.php';

$http_worker = new Worker("websocket://127.0.0.1:2345");

// 在线人数
$connection_count = 0;

// 游客号码防止重复
$connection_number = 0;

// 这里进程数必须设置为1
$http_worker->count = 1;

// 连接时人数++
$http_worker->onConnect = function ($connection) use ($http_worker){
    global $connection_count;
    global $connection_number;
    
    ++$connection_count;
    ++$connection_number;
    
	foreach ($http_worker->connections as $connection) {
		$text = "{type:'count',count:'". $connection_count."'}";
		$connection->send(strval($text));
	}
    // $connection->send('有玩家成功加入');
    
    // echo "now connection_count=$connection_count\n";
};

// 定时返回在线人数
$http_worker->onWorkerStart = function ($worker) {
    // Timer::add(1, function () use ($worker) {
    //     global $connection_count;
        
    //     foreach ($worker->connections as $connection) {
    //         $text = "{type:'count',count:'". $connection_count."'}";
    //         $connection->send(strval($text));
    //     }
    // });
};

// 关闭连接时减掉在线人数
$http_worker->onClose = function ($connection) use ($http_worker) {
    global $connection_count;
    $connection_count--;
	
	
	foreach ($http_worker->connections as $connection) {
		$text = "{type:'count',count:'". $connection_count."'}";
		$connection->send(strval($text));
	}
};

$http_worker->onMessage = function ($connection, $data) use ($http_worker) {
    // 向每一个用户推送消息
    global $connection_number;

    foreach ($http_worker->connections as $connection) {
        //验证管理端秘钥

        $name = explode("name:", $data)[1];
        $mess = explode("name:", $data)[0];
        // $connection->send($data);
        // if ($text[1] == 'gi4lXpAOw6ddtFg') {
        // $connection->send($text[0]);
        // }
		
		// $connection->send(explode("name:", $data)[1]);
		
		// 当玩家没有输入姓名时
        if (strlen($name) <= 0 || $name == 'null') {
            $text = "{name:'游客". $connection_number ."',text:'". $mess."'}";
            $connection->send(strval($text));
        } else {
            $text = "{name:'". $name."',text:'". $mess."'}";
            $connection->send(strval($text));
        }
    }
};

Worker::runAll();
