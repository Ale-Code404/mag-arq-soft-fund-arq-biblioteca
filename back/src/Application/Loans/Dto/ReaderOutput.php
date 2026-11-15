<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

use App\Application\Shared\Output;
use App\Domain\People\Reader;

final readonly class ReaderOutput implements Output
{
    public function __construct(
        public string $identification,
        public string $name,
        public ?string $address,
        public bool $hasPendingFines,
    ) {}

    public static function from(Reader $l): self
    {
        return new self($l->identification(), $l->getName(), $l->address(), $l->hasPendingFines());
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
