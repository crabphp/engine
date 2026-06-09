<?php

namespace Crab\Engine\EvenLoop;

interface EventLoop
{
    /**
     * Registers a callback to be executed when the stream becomes readable.
     * @param resource $stream
     */
    public function onReadable(mixed $stream, callable $callback): void;

    /**
     * Removes a callback from the list of callbacks to be executed when the stream becomes readable.
     * @param resource $stream
     */
    public function offReadable(mixed $stream): void;

    /**
     * Registers a callback to be executed when the stream becomes writable.
     * @param resource $stream
     */
    public function onWritable(mixed $stream, callable $callback): void;

    /**
     * Removes a callback from the list of callbacks to be executed when the stream becomes writable.
     * @param resource $stream
     */
    public function offWritable(mixed $stream): void;

    public function run(): void;

    public function stop(): void;

    public function delay(float $seconds, callable $callback): int;

    public function repeat(float $second, callable $callback): int;

    public function cancelTimer(int $timerId): void;
}
