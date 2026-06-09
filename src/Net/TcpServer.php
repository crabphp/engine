<?php

namespace Crab\Engine\Net;

use Crab\Engine\EventLoop\EventLoop;
use RuntimeException;

final class TcpServer
{
    /** @var resource|null */
    private mixed $socket = null;

    public function __construct(
        private EventLoop $loop,
        private SocketAddress $address,
        private mixed $onClient,
    ) {}

    public function listen(): void
    {
        $errorCode = 0;
        $errorMessage = '';

        $this->socket = @stream_socket_server(
            $this->address->streamUri(),
            $errorCode,
            $errorMessage,
            flags: STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
        );

        if (!is_resource($this->socket)) {
            throw new RuntimeException("Listen failed: $errorMessage, $errorCode");
        }

        stream_set_blocking($this->socket, false);

        $this->loop->onReadable($this->socket, $this->accept(...));
    }

    public function close(): void
    {
        if (is_resource($this->socket)) {
            $this->loop->offReadable($this->socket);
            fclose($this->socket);
        }

        $this->socket = null;
    }

    private function accept(mixed $serverSocket): void
    {
        $client = @stream_socket_accept($serverSocket, 0, $remoteAddress);

        if (!is_resource($client)) {
            return;
        }

        stream_set_blocking($client, false);

        ($this->onClient)($client, $remoteAddress ?: 'unknown');
    }
}
