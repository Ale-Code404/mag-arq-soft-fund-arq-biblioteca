<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Penalties\Fine;

interface FineRepository
{
    public function readerHasPending(string $identification): bool;

    public function findById(int $id): ?Fine;

    public function add(Fine $fine, int $loanId): void;

    /** Saves the payment and who registered it (RNF-06). */
    public function registerPayment(Fine $fine, int $userId): void;
}
