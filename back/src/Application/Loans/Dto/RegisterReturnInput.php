<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

final readonly class RegisterReturnInput
{
    public function __construct(
        public string $copyCode,
        public int $userId,
    ) {}
}
