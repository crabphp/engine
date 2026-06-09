<?php

namespace Crab\Engine\Net;

use InvalidArgumentException;

final readonly class SocketAddress
{
    public function __construct(
        public string $scheme,
        public string $host,
        public int $port,
    ) {}

    public static function parse(string $address): self
    {
        $parts = parse_url($address);

        if (!is_array($parts) || !isset($parts['scheme'], $parts['host'], $parts['port'])) {
            throw new InvalidArgumentException("Invalid socket address: $address");
        }

        return new self($parts['scheme'], $parts['host'], (int) $parts['port']);
    }

    public function streamUri(): string
    {
        return "tcp://$this->host:$this->port";
    }
}
