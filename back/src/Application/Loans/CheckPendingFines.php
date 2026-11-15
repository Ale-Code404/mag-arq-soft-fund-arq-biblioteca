<?php

declare(strict_types=1);

namespace App\Application\Loans;

use App\Application\Loans\Dto\CheckPendingFinesInput;
use App\Domain\Repositories\FineRepository;

/** CU-14 Check whether the reader has pending fines (used by CU-07). */
final class CheckPendingFines
{
    public function __construct(private FineRepository $fines) {}

    public function execute(CheckPendingFinesInput $e): bool
    {
        return $this->fines->readerHasPending($e->readerIdentification);
    }
}
