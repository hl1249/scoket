<?php

use Workerman\Worker;
use \Workerman\Lib\Timer;

require_once __DIR__ . '/Workerman/Autoloader.php';

$http_worker = new Worker("websocket://0.0.0.0:2345");

// 这里进程数必须设置为1

$http_worker->count = 1;


$http_worker->onMessage = function ($connection, $data) use ($http_worker) {
  // 向每一个用户推送消息

  foreach ($http_worker->connections as $connection) {
    //验证管理端秘钥

    // $text = explode("KEY:", $data);
     $connection->send($data);
    // if ($text[1] == 'gi4lXpAOw6ddtFg') {
    //   $connection->send($text[0]);
    // }
  }
};

Worker::runAll();
