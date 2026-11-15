<?php

namespace App\Infrastructure\Services;

use App\Application\Ports\Clock;
use DateTimeImmutable;

class SystemClock implements Clock
{
    public function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('today');
    }
}
