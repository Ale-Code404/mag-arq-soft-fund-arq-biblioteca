<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

use App\Application\Shared\Output;

final readonly class ReturnOutput implements Output
{
    public function __construct(
        public LoanOutput $loan,
        public ?FineOutput $fine,
    ) {}

    public function toArray(): array
    {
        return ['loan' => $this->loan->toArray(), 'fine' => $this->fine?->toArray()];
    }
}
