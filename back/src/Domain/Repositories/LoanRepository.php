<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Loans\Loan;

interface LoanRepository
{
    public function findActiveByCopy(string $copyCode): ?Loan;

    /** Inserts or updates; on insert it assigns the id and records who did it (RNF-06). */
    public function save(Loan $loan, int $userId): void;

    /** @return Loan[] newest first */
    public function historyOfReader(string $identification): array;
}
