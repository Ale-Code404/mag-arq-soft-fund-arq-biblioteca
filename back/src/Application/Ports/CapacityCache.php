<?php

declare(strict_types=1);

namespace App\Application\Ports;

interface CapacityCache
{
    /** Returns the metrics stored under the key, or calculates and stores them (RNF-08). */
    public function get(string $key, callable $calculate): array;

    /** Discards all stored metrics. */
    public function invalidate(): void;
}
