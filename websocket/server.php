<?php
require dirname(__DIR__) . '/vendor/autoload.php';
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Handler implements WampServerInterface {
    public function onSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}

$handler = new Handler;

// Setup wamp server
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                $handler
            )
        )
    )
);

// Mission complete
$loop->run();