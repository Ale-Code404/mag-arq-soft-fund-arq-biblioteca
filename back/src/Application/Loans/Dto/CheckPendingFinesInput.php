<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

final readonly class CheckPendingFinesInput
{
    public function __construct(
        public string $readerIdentification,
    ) {}
}
