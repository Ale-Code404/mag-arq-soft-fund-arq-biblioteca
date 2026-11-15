<?php

declare(strict_types=1);

namespace App\Application\Shared;

/** Every use case output can be expressed as an array for the API. */
interface Output
{
    public function toArray(): array;
}
