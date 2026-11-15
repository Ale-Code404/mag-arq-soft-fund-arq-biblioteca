<?php

declare(strict_types=1);

namespace App\Application\Inventory\Dto;

final readonly class PlaceCopyInput
{
    public function __construct(
        public string $code,
        public string $shelf,
        public int $level,
        public int $userId,
    ) {}
}
