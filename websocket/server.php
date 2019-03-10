<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class StarchatWs implements MessageComponentInterface {
  // Client sessions
  protected $csessions;
  private $client_info;

  public function __construct() {
    $this->csessions = new \SplObjectStorage;
  }

  public function onOpen(ConnectionInterface $conn) {
    $this->csessions->attach($conn);
    $params = $conn->httpRequest->getUri()->getQuery();
    $this->client_info[$conn->resourceId] = $params;
  }

  public function onMessage(ConnectionInterface $from, $msg) {
    global $conn;
    foreach ($this->csessions as $user) {
      $user_token = $this->client_info[$user->resourceId];
      $msg_json = json_decode($msg, true);

      $tokens = $conn->prepare("SELECT username FROM tokens WHERE token=?");
      $tokens->bind_param('s', $user_token);
      $tokens->execute();
      $tresults = $tokens->get_result();
      $tresults_rows = $tresults->num_rows;

      if ($tresults_rows === 1) {
        while($row = $tresults->fetch_assoc()) {
          $req_username = $row["username"];
          $get_info = $conn->prepare("SELECT contacts FROM accounts WHERE username=?");
          $get_info->bind_param('s', $req_username);
          $get_info->execute();
          $get_info_results = $get_info->get_result();
          $get_info_results_rows = $get_info_results->num_rows;

          if ($get_info_results_rows === 1) {
            while($row_info = $get_info_results->fetch_assoc()) {
              $req_contacts = $row_info["contacts"];
              $req_json = json_decode($req_contacts, true);
              foreach($req_json as $item) {
                if ($item["chat_id"] === $msg_json["id"]) {
                  $user->send($msg);
                }
              }
            }
          }
        }
      }
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
