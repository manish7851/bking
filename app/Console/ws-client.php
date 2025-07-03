<?php
require __DIR__ . '/../../vendor/autoload.php';

use Ratchet\Client\Connector;
use React\EventLoop\Factory;

$loop = Factory::create();
$connector = new Connector($loop);

$url = 'ws://103.90.84.153:8081';

$connector($url, [], [
    'Origin' => 'http://localhost' // set Origin header if needed
])->then(function(\Ratchet\Client\WebSocket $conn) use ($loop) {
    echo "Connected to WebSocket server\n";

    $conn->on('message', function($msg) use ($conn) {
        echo "Received: {$msg}\n";
        // Decode JSON and handle message here if you want
    });

    $conn->on('close', function($code = null, $reason = null) use ($loop) {
        echo "Connection closed ({$code} - {$reason})\n";
        $loop->stop();
    });

}, function(Exception $e) use ($loop) {
    echo "Could not connect: {$e->getMessage()}\n";
    $loop->stop();
});

$loop->run();
