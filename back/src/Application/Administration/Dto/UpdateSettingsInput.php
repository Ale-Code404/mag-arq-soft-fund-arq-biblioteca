<?php

declare(strict_types=1);

namespace App\Application\Administration\Dto;

final readonly class UpdateSettingsInput
{
    public function __construct(
        public int $loanDays,
        public int $renewalDays,
        public int $maxRenewals,
        public int $dailyFineValue,
        public float $clearanceCm,
        public float $defaultThicknessCm,
        public int $userId,
    ) {}
}
