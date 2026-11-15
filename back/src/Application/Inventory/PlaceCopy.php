<?php

declare(strict_types=1);

namespace App\Application\Inventory;

use App\Application\Inventory\Dto\CopyOutput;
use App\Application\Inventory\Dto\PlaceCopyInput;
use App\Application\Ports\CapacityCache;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Repositories\CopyRepository;
use App\Domain\Repositories\LibraryRepository;

/** CU-04 Place (or relocate) a copy on a level with RN-06 and RN-07. */
final class PlaceCopy
{
    public function __construct(
        private CopyRepository $copies,
        private LibraryRepository $library,
        private CapacityCache $cache,
    ) {}

    public function execute(PlaceCopyInput $e): CopyOutput
    {
        $copy = $this->copies->findByCode($e->code)
            ?? throw new ResourceNotFoundException("No existe el ejemplar {$e->code}.");

        $this->library->load()->place($copy, $e->shelf, $e->level);
        $this->library->saveLocation($e->code, $e->shelf, $e->level);
        $this->cache->invalidate();

        return CopyOutput::from($copy, $e->shelf, $e->level);
    }
}
