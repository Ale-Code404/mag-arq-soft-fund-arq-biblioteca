<?php

declare(strict_types=1);

namespace App\Application\Shared;

final readonly class QueryInput
{
    public function __construct(
        public int $userId,
    ) {}
}
