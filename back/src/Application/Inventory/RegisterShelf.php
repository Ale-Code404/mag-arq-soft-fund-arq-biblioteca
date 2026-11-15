<?php

declare(strict_types=1);

namespace App\Application\Inventory;

use App\Application\Inventory\Dto\RegisterShelfInput;
use App\Application\Inventory\Dto\ShelfOutput;
use App\Application\Ports\CapacityCache;
use App\Application\Ports\Transaction;
use App\Domain\Inventory\Level;
use App\Domain\Inventory\Shelf;
use App\Domain\Repositories\LibraryRepository;

/** CU-01 Register a shelf with its levels. */
final class RegisterShelf
{
    public function __construct(
        private LibraryRepository $library,
        private CapacityCache $cache,
        private Transaction $transaction,
    ) {}

    public function execute(RegisterShelfInput $e): ShelfOutput
    {
        $library = $this->library->load();
        $shelf = new Shelf($e->code, array_map(
            fn (array $n) => new Level((int) $n['number'], (float) $n['lengthCm'], (float) $n['heightCm']),
            $e->levels,
        ));
        $library->addShelf($shelf);   // unique code

        $this->transaction->execute(fn () => $this->library->saveShelf($shelf));
        $this->cache->invalidate();

        return ShelfOutput::from($shelf, $library->settings()->clearanceCm);
    }
}
