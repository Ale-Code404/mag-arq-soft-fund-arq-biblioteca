<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Exceptions\ResourceNotFoundException;

final class Shelf
{
    /** @var Level[] */
    private array $levels = [];

    /** @param Level[] $levels */
    public function __construct(private string $code, array $levels)
    {
        if (trim($code) === '') {
            throw new BusinessRuleException('RF-01', 'El código de la estantería es obligatorio.');
        }
        if ($levels === []) {
            throw new BusinessRuleException('RF-01', 'La estantería debe tener al menos un nivel.');
        }
        foreach ($levels as $n) {
            $this->addLevel($n);
        }
    }

    public function addLevel(Level $n): void
    {
        foreach ($this->levels as $existing) {
            if ($existing->number() === $n->number()) {
                throw new BusinessRuleException('RF-01', "El nivel {$n->number()} está repetido en la estantería {$this->code}.");
            }
        }
        $this->levels[] = $n;
    }

    public function installedCapacity(): float
    {
        return array_sum(array_map(fn (Level $n) => $n->installedCapacity(), $this->levels));
    }

    public function usedSpace(float $clearance): float
    {
        return array_sum(array_map(fn (Level $n) => $n->usedSpace($clearance), $this->levels));
    }

    public function level(int $number): Level
    {
        foreach ($this->levels as $n) {
            if ($n->number() === $number) {
                return $n;
            }
        }
        throw new ResourceNotFoundException("La estantería {$this->code} no tiene el nivel {$number}.");
    }

    public function code(): string
    {
        return $this->code;
    }

    /** @return Level[] */
    public function levels(): array
    {
        return $this->levels;
    }
}
