<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

use App\Application\Shared\Output;
use App\Domain\Penalties\Fine;

final readonly class FineOutput implements Output
{
    public function __construct(
        public ?int $id,
        public int $value,
        public int $daysLate,
        public int $rateApplied,
        public string $status,
        public ?string $paidOn,
    ) {}

    public static function from(Fine $m): self
    {
        return new self($m->id(), $m->value(), $m->daysLate(), $m->rateApplied(), $m->status()->value, $m->paidOn()?->format('Y-m-d'));
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
