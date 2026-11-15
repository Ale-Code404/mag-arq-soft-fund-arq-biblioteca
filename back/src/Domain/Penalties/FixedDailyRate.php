<?php

declare(strict_types=1);

namespace App\Domain\Penalties;

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Loans\Loan;
use DateTimeImmutable;

/** RN-04: fixed amount per day late. */
final class FixedDailyRate implements FinePolicy
{
    public function __construct(private int $dailyValue)
    {
        if ($dailyValue <= 0) {
            throw new BusinessRuleException('RN-04', 'El valor diario de la multa debe ser positivo.');
        }
    }

    public function calculate(Loan $loan, DateTimeImmutable $date): int
    {
        return $loan->daysLate($date) * $this->dailyValue;
    }
}
