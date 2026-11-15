<?php

declare(strict_types=1);

namespace App\Application\Inventory;

use App\Application\Inventory\Dto\CapacityOutput;
use App\Application\Inventory\Dto\GetCapacityInput;
use App\Application\Ports\CapacityCache;
use App\Domain\Catalog\Genre;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Repositories\LibraryRepository;

/**
 * CU-05 Get capacity in total, by shelf or by genre (formulas 6.4).
 * By genre, installed, used and available space are those of the whole library
 * (shelves are shared) and the average thickness is the genre's.
 */
final class GetCapacity
{
    public const SCOPES = ['total', 'shelf', 'genre'];

    public function __construct(
        private LibraryRepository $library,
        private CapacityCache $cache,
    ) {}

    public function execute(GetCapacityInput $e): CapacityOutput
    {
        $value = $e->scope === 'total' ? null : $e->value;
        if ($e->scope !== 'total' && ($value === null || $value === '')) {
            throw new BusinessRuleException('RF-05', "El alcance {$e->scope} requiere un valor.");
        }
        $genre = match ($e->scope) {
            'total', 'shelf' => null,
            'genre' => Genre::tryFrom($value) ?? throw new BusinessRuleException('RF-05', "La temática {$value} no existe."),
            default => throw new BusinessRuleException('RF-05', "El alcance {$e->scope} no es válido."),
        };

        $data = $this->cache->get("capacity:{$e->scope}:".($value ?? 'all'), function () use ($e, $value, $genre) {
            $library = $this->library->load();
            if ($e->scope === 'shelf') {
                $library = $library->onlyShelf($value);
            }

            return CapacityOutput::from($e->scope, $value, $library, $genre)->toArray();
        });

        return CapacityOutput::fromArray($data);
    }
}
