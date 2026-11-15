<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

use App\Application\Shared\Output;

final readonly class HistoryOutput implements Output
{
    /** @param LoanOutput[] $loans */
    public function __construct(
        public ReaderOutput $reader,
        public array $loans,
        public int $totalPendingFines,
    ) {}

    public function toArray(): array
    {
        return [
            'reader' => $this->reader->toArray(),
            'loans' => array_map(fn (LoanOutput $p) => $p->toArray(), $this->loans),
            'totalPendingFines' => $this->totalPendingFines,
        ];
    }
}
