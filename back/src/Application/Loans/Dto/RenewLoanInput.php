<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

final readonly class RenewLoanInput
{
    public function __construct(
        public string $copyCode,
        public int $userId,
    ) {}
}
