<?php

declare(strict_types=1);

namespace App\Application\Shared;

final readonly class CollectionOutput implements Output
{
    /** @param Output[] $items */
    public function __construct(public array $items) {}

    public function toArray(): array
    {
        return array_map(fn (Output $s) => $s->toArray(), $this->items);
    }
}
