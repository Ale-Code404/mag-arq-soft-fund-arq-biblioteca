<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Catalog\Copy;
use App\Domain\Catalog\Genre;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Settings\LibrarySettings;

/** Root of the physical inventory: Library → Shelf → Level → Copy (composition). */
final class Library
{
    /** @var Shelf[] */
    private array $shelves = [];

    /** @param Shelf[] $shelves */
    public function __construct(
        private string $name,
        private LibrarySettings $settings,
        array $shelves = [],
    ) {
        foreach ($shelves as $e) {
            $this->addShelf($e);
        }
    }

    public function addShelf(Shelf $e): void
    {
        foreach ($this->shelves as $existing) {
            if ($existing->code() === $e->code()) {
                throw new BusinessRuleException('RF-01', "Ya existe la estantería {$e->code()}.");
            }
        }
        $this->shelves[] = $e;
    }

    public function installedCapacity(): float
    {
        return array_sum(array_map(fn (Shelf $e) => $e->installedCapacity(), $this->shelves));
    }

    public function usedCapacity(): float
    {
        $h = $this->settings->clearanceCm;

        return array_sum(array_map(fn (Shelf $e) => $e->usedSpace($h), $this->shelves));
    }

    public function availableCapacity(): float
    {
        return $this->installedCapacity() - $this->usedCapacity();
    }

    // Overloading in PHP: an optional parameter replaces two signatures
    // averageThickness() for the whole library and averageThickness($genre) for one genre (RN-10)
    public function averageThickness(?Genre $t = null): float
    {
        $thicknesses = $this->thicknesses($t);

        return $thicknesses === []
            ? $this->settings->defaultThicknessCm
            : array_sum($thicknesses) / count($thicknesses);
    }

    public function usesDefaultThickness(?Genre $t = null): bool
    {
        return $this->thicknesses($t) === [];
    }

    /** DA-13: books that fit in the installed capacity at the average thickness. */
    public function theoreticalCapacity(?Genre $t = null): int
    {
        return self::booksThatFit($this->installedCapacity(), $this->averageThickness($t) + $this->settings->clearanceCm);
    }

    /** Books that still fit in the available space. */
    public function availableCapacityBooks(?Genre $t = null): int
    {
        return self::booksThatFit($this->availableCapacity(), $this->averageThickness($t) + $this->settings->clearanceCm);
    }

    /** CU-04: places (or relocates) a copy on a level applying RN-06 and RN-07. */
    public function place(Copy $copy, string $shelfCode, int $levelNumber): void
    {
        $target = $this->shelf($shelfCode)->level($levelNumber);
        $source = $this->levelOf($copy->code());
        $source?->remove($copy->code());

        try {
            $target->place($copy, $this->settings->clearanceCm);
        } catch (BusinessRuleException $e) {
            $source?->place($copy, $this->settings->clearanceCm);
            throw $e;
        }
    }

    public function shelf(string $code): Shelf
    {
        foreach ($this->shelves as $e) {
            if ($e->code() === $code) {
                return $e;
            }
        }
        throw new ResourceNotFoundException("La estantería {$code} no existe.");
    }

    /** View of the library limited to one shelf, to measure its capacity with the same formulas. */
    public function onlyShelf(string $code): self
    {
        return new self($this->name, $this->settings, [$this->shelf($code)]);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function settings(): LibrarySettings
    {
        return $this->settings;
    }

    /** @return Shelf[] */
    public function shelves(): array
    {
        return $this->shelves;
    }

    private function levelOf(string $copyCode): ?Level
    {
        foreach ($this->shelves as $e) {
            foreach ($e->levels() as $n) {
                if ($n->contains($copyCode)) {
                    return $n;
                }
            }
        }

        return null;
    }

    /** @return float[] */
    private function thicknesses(?Genre $t): array
    {
        $thicknesses = [];
        foreach ($this->placedCopies() as $copy) {
            if ($t === null || $copy->book()->genre() === $t) {
                $thicknesses[] = $copy->book()->getThicknessCm();
            }
        }

        return $thicknesses;
    }

    /** @return iterable<Copy> */
    private function placedCopies(): iterable
    {
        foreach ($this->shelves as $e) {
            foreach ($e->levels() as $n) {
                yield from $n->copies();
            }
        }
    }

    /** RNF-05: books always round down; rounded first to avoid 48.999... from decimals. */
    private static function booksThatFit(float $spaceCm, float $spacePerBookCm): int
    {
        return max(0, (int) floor(round($spaceCm / $spacePerBookCm, 6)));
    }
}
