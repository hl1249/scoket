<?php

use Workerman\Worker;
use \Workerman\Lib\Timer;

require_once __DIR__ . '/Workerman/Autoloader.php';

$http_worker = new Worker("websocket://0.0.0.0:2345");

// 在线人数
$connection_count = 0;

// 这里进程数必须设置为1
$http_worker->count = 1;

// 连接时人数++
$http_worker->onConnect = function($connection){
	
	global $connection_count;
	
	++$connection_count;
	
	// $connection->send('有玩家成功加入');
	
	echo "now connection_count=$connection_count\n";
	
};

// 定时返回在线人数
$http_worker->onWorkerStart = function($worker){
	Timer::add(1,function()use($worker)
	{
		global $connection_count;
		
		foreach($worker->connections as $connection)
		{
			$connection->send($connection_count);
		}
	});
};

// 关闭连接时减掉在线人数
$http_worker->onClose = function($connection)
{
	global $connection_count;
	$connection_count--;
};

$http_worker->onMessage = function ($connection, $data) use ($http_worker) {
    // 向每一个用户推送消息

    foreach ($http_worker->connections as $connection) {
        //验证管理端秘钥

        // $text = explode("KEY:", $data);
        $connection->send($data);
        // if ($text[1] == 'gi4lXpAOw6ddtFg') {
            // $connection->send($text[0]);
        // }
    }
};

Worker::runAll();
