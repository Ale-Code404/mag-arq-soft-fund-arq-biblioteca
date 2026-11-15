<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

final readonly class RegisterReaderInput
{
    public function __construct(
        public string $identification,
        public string $name,
        public ?string $address,
        public int $userId,
    ) {}
}
