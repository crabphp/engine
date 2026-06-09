<?php

use Crab\Engine\EventLoop\SelectLoop;
use Crab\Engine\EventLoop\TcpConnection;
use Crab\Engine\Net\SocketAddress;
use Crab\Engine\Net\TcpServer;

require_once __DIR__ . '/vendor/autoload.php';

$loop = new SelectLoop();
$address = SocketAddress::parse('tcp://0.0.0.0:9000');

$server = new TcpServer($loop, $address, function ($client, string $remote) use ($loop): void {
    $connection = new TcpConnection($loop, $client, $remote);

    $connection->onMessage = function (TcpConnection $connection, string $data): void {
        $connection->send("echo: $data");
    };
});

$server->listen();
$loop->run();
