<?php

declare(strict_types=1);

namespace App\Application\Inventory\Dto;

use App\Application\Shared\Output;
use App\Domain\Catalog\Copy;

final readonly class CopyOutput implements Output
{
    public function __construct(
        public string $code,
        public string $isbn,
        public string $title,
        public string $status,
        public ?string $shelf,
        public ?int $level,
    ) {}

    public static function from(Copy $e, ?string $shelf, ?int $level): self
    {
        return new self($e->code(), $e->book()->isbn(), $e->book()->title(), $e->status()->value, $shelf, $level);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
