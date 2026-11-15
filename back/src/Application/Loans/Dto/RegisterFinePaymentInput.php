<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

final readonly class RegisterFinePaymentInput
{
    public function __construct(
        public int $fineId,
        public int $userId,
    ) {}
}
