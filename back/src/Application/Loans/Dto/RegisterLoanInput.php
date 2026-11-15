<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

final readonly class RegisterLoanInput
{
    public function __construct(
        public string $readerIdentification,
        public string $copyCode,
        public int $userId,
    ) {}
}
