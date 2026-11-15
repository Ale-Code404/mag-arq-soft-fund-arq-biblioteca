<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

final readonly class GetLoansAndFinesInput
{
    public function __construct(
        public string $readerIdentification,
        public int $userId,
    ) {}
}
