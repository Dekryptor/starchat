<?php
require __DIR__ . '../vendor/autoload.php';
require 'config.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class StarchatWs implements MessageComponentInterface {
  // Client sessions
  protected $csessions;

  public function __construct() {
    $this->csessions = new \SplObjectStorage;
  }

  public function onOpen(ConnectionInterface $conn) {
    $this->csessions->attach($conn);
  }

  public function onMessage(ConnectionInterface $from, $msg) {
    foreach ($this->csessions as $user) {
      $user->send($msg);
    }
  }

  public function onClose(ConnectionInterface $conn) {
    $this->csessions->detach($conn);
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo "Error: {$e->getMessage()}";

    $conn->close();
  }
}

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
