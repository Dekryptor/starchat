<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$port = 8080;

$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      new StarchatWs()
      )
    ),
    $port
  );



?>
