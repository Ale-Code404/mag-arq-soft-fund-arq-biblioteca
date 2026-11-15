<?php

declare(strict_types=1);

namespace App\Application\Inventory\Dto;

final readonly class RegisterShelfInput
{
    public function __construct(
        public string $code,
        /** @var array<int, array{number: int, lengthCm: float, heightCm: float}> */
        public array $levels,
        public int $userId,
    ) {}
}
