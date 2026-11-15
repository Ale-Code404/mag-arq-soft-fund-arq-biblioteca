<?php

declare(strict_types=1);

namespace App\Application\Inventory\Dto;

use App\Application\Shared\Output;
use App\Domain\Inventory\Level;
use App\Domain\Inventory\Shelf;

final readonly class ShelfOutput implements Output
{
    public function __construct(
        public string $code,
        public float $installedCm,
        public float $usedCm,
        public float $availableCm,
        /** @var array<int, array{number: int, lengthCm: float, heightCm: float, usedCm: float, availableCm: float, copies: int}> */
        public array $levels,
    ) {}

    public static function from(Shelf $e, float $clearance): self
    {
        return new self(
            $e->code(),
            round($e->installedCapacity(), 1),
            round($e->usedSpace($clearance), 1),
            round($e->installedCapacity() - $e->usedSpace($clearance), 1),
            array_map(fn (Level $n) => [
                'number' => $n->number(),
                'lengthCm' => round($n->lengthCm(), 1),
                'heightCm' => round($n->heightCm(), 1),
                'usedCm' => round($n->usedSpace($clearance), 1),
                'availableCm' => round($n->availableSpace($clearance), 1),
                'copies' => count($n->copies()),
            ], $e->levels()),
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
