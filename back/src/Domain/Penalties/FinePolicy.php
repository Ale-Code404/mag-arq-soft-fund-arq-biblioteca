<?php

declare(strict_types=1);

namespace App\Domain\Penalties;

use App\Domain\Loans\Loan;
use DateTimeImmutable;

/** Fine calculation strategy. Loan::returnCopy() uses it without knowing the implementation. */
interface FinePolicy
{
    public function calculate(Loan $loan, DateTimeImmutable $date): int;
}
