<?php

namespace Tests\Support;

use App\Application\Ports\Clock;
use DateTimeImmutable;

final class FixedClock implements Clock
{
    public function __construct(private DateTimeImmutable $today) {}

    public function today(): DateTimeImmutable
    {
        return $this->today;
    }
}
