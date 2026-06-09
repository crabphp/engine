<?php

declare(strict_types=1);

namespace Crab\Engine\Support;

class Cli
{
    public static function command(array $argv): string
    {
        $command = $argv[1] ?? 'help';

        return match ($command) {
            'start', 'stop', 'reload', 'status', 'help' => $command,
            default => 'help',
        };
    }
}
