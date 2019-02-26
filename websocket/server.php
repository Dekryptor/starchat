<?php
require __DIR__ . '/vendor/autoload.php';
require 'config.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      new StarchatWs()
      )
    ),
    $wsport
  );

$server->run();
?>
