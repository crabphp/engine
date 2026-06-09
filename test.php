<?php

use Crab\Engine\EvenLoop\SelectLoop;
use Crab\Engine\Net\SocketAddress;
use Crab\Engine\Net\TcpServer;

require_once __DIR__ . '/vendor/autoload.php';

$loop = new SelectLoop();
$address = SocketAddress::parse('tcp://0.0.0.0:9000');

$server = new TcpServer($loop, $address, function ($client, string $remote) use ($loop): void {
    echo "client connected: $remote" . PHP_EOL;

    $loop->onReadable($client, function ($client) use ($loop): void {
        $data = fread($client, 8192);

        if ($data === '' || $data === false) {
            $loop->offReadable($client);
            fclose($client);
            return;
        }

        if (trim($data) === 'help') {
            fwrite($client, "/version check verison\n /name check the name");
        } else {
            fwrite($client, 'echo: wrong command\n');
        }
    });
});

$server->listen();
$loop->run();
