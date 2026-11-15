<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Catalog\Copy;
use App\Domain\Exceptions\BusinessRuleException;

final class Level
{
    /** @param Copy[] $copies */
    public function __construct(
        private int $number,
        private float $lengthCm,
        private float $heightCm,
        private array $copies = [],
    ) {
        if ($number < 1) {
            throw new BusinessRuleException('RF-01', 'El número de nivel debe ser positivo.');
        }
        if ($lengthCm <= 0 || $heightCm <= 0) {
            throw new BusinessRuleException('RF-01', 'Las medidas del nivel deben ser positivas.');
        }
    }

    public function installedCapacity(): float
    {
        return $this->lengthCm;
    }

    /** RN-09: loaned copies keep their space. */
    public function usedSpace(float $clearance): float
    {
        return array_sum(array_map(fn (Copy $e) => $e->occupiedSpace($clearance), $this->copies));
    }

    public function availableSpace(float $clearance): float
    {
        return $this->lengthCm - $this->usedSpace($clearance);
    }

    /** RN-07: fits if it is not taller than the level and there is room for thickness + clearance. */
    public function accepts(Copy $e, float $clearance): bool
    {
        return $this->fitsHeight($e) && $this->fitsLength($e, $clearance);
    }

    public function place(Copy $e, float $clearance): void
    {
        if (! $this->fitsHeight($e)) {
            throw new BusinessRuleException('RN-07', "El libro mide {$e->book()->getHeightCm()} cm de alto y el nivel solo {$this->heightCm} cm.");
        }
        if (! $this->fitsLength($e, $clearance)) {
            throw new BusinessRuleException('RN-07', 'El nivel no tiene espacio suficiente para el ejemplar.');
        }
        $this->copies[] = $e;
    }

    public function contains(string $copyCode): bool
    {
        foreach ($this->copies as $e) {
            if ($e->code() === $copyCode) {
                return true;
            }
        }

        return false;
    }

    public function remove(string $copyCode): ?Copy
    {
        foreach ($this->copies as $i => $e) {
            if ($e->code() === $copyCode) {
                array_splice($this->copies, $i, 1);

                return $e;
            }
        }

        return null;
    }

    private function fitsHeight(Copy $e): bool
    {
        return $e->book()->getHeightCm() <= $this->heightCm;
    }

    private function fitsLength(Copy $e, float $clearance): bool
    {
        // Rounded so that summing decimals (0.4 + 0.4...) does not reject an exact fit
        return round($e->occupiedSpace($clearance) - $this->availableSpace($clearance), 6) <= 0;
    }

    public function number(): int
    {
        return $this->number;
    }

    public function lengthCm(): float
    {
        return $this->lengthCm;
    }

    public function heightCm(): float
    {
        return $this->heightCm;
    }

    /** @return Copy[] */
    public function copies(): array
    {
        return $this->copies;
    }
}
