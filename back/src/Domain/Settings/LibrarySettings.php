<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use App\Domain\Exceptions\BusinessRuleException;

/** Current library settings. Immutable value object (CU-13). */
final readonly class LibrarySettings
{
    public function __construct(
        public int $loanDays,
        public int $renewalDays,
        public int $maxRenewals,
        public int $dailyFineValue,
        public float $clearanceCm,
        public float $defaultThicknessCm,
    ) {
        foreach (get_object_vars($this) as $name => $value) {
            if ($value <= 0) {
                throw new BusinessRuleException('RF-13', "El parámetro {$name} debe ser positivo.");
            }
        }
    }
}
