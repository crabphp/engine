<?php

namespace Crab\Engine\EventLoop;

use Crab\Engine\EventLoop\EventLoop;

class SelectLoop implements EventLoop
{
    private bool $running = false;

    /** @var array<int, resource> */
    private array $readStreams = [];

    /** @var array<int, callable> */
    private array $readCallbacks = [];

    /** @var array<int, resource> */
    private array $writeStreams = [];

    /** @var array<int, callable> */
    private array $writeCallbacks = [];

    public function onReadable(mixed $stream, callable $callback): void
    {
        // when cast to int, php take the id of resource as the internal id
        // this will ensure us the stream id is unique. cool
        $key = (int) $stream;
        $this->readStreams[$key] = $stream;
        $this->readCallbacks[$key] = $callback;
    }

    public function offReadable(mixed $stream): void
    {
        $key = (int) $stream;
        unset($this->readStreams[$key]);
        unset($this->readCallbacks[$key]);
    }

    public function onWritable(mixed $stream, callable $callback): void
    {
        // when cast to int, php take the id of resource as the internal id
        // this will ensure us the stream id is unique. cool
        $key = (int) $stream;
        $this->writeStreams[$key] = $stream;
        $this->writeCallbacks[$key] = $callback;
    }

    public function offWritable(mixed $stream): void
    {
        $key = (int) $stream;
        unset($this->writeStreams[$key]);
        unset($this->writeCallbacks[$key]);
    }

    public function run(): void
    {
        $this->running = true;

        while ($this->running) {
            $read = $this->readStreams;
            $write = $this->writeStreams;
            $except = [];

            if ($read === [] && $write === []) {
                $this->runDueTimers();
                continue;
            }

            $ready = @stream_select($read, $write, $except, 0, $this->selectTimeoutMicroseconds());

            if ($ready === false) {
                continue;
            }

            foreach ($read as $stream) {
                $key = (int) $stream;
                ($this->readCallbacks[$key] ?? static fn() => null)($stream);
            }

            foreach ($write as $stream) {
                $key = (int) $stream;
                ($this->writeCallbacks[$key] ?? static fn() => null)($stream);
            }

            $this->runDueTimers();
        }
    }

    public function stop(): void
    {
        $this->running = false;
    }

    /** @var array<int, array{time: float, interval: ?float, callback: callable}> */
    private array $timers = [];

    private int $nextTimerId = 1;

    public function delay(float $seconds, callable $callback): int
    {
        $id = $this->nextTimerId++;

        $this->timers[$id] = [
            'time' => microtime(true) + $seconds,
            'interval' => null,
            'callback' => $callback,
        ];

        return $id;
    }

    public function repeat(float $seconds, callable $callback): int
    {
        $id = $this->nextTimerId++;

        $this->timers[$id] = [
            'time' => microtime(true) + $seconds,
            'interval' => $seconds,
            'callback' => $callback,
        ];

        return $id;
    }

    public function cancelTimer(int $timerId): void
    {
        unset($this->timers[$timerId]);
    }

    private function runDueTimers(): void
    {
        $now = microtime(true);

        foreach ($this->timers as $id => $timer) {
            if ($timer['time'] > $now) {
                continue;
            }

            $timer['callback']();

            if (!isset($this->timers[$id])) {
                continue;
            }

            if ($timer['interval'] === null) {
                unset($this->timers[$id]);
                continue;
            }

            $this->timers[$id]['time'] = $now + $timer['interval'];
        }
    }

    private function selectTimeoutMicroseconds(): int
    {
        if ($this->timers === []) {
            return 200_000;
        }

        $nextTime = min(array_column($this->timers, 'time'));
        $remaining = max(0.0, $nextTime - microtime(true));

        return min(200_000, (int) ($remaining * 1_000_000));
    }
}
