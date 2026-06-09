<?php

declare(strict_types=1);

namespace Crab\Engine;

class Crab
{
    public static function run(): void
    {
        echo "Crab runtime starting\n";

        while (true) {
            sleep(1);
            echo "tick\n";
        }
    }
}
