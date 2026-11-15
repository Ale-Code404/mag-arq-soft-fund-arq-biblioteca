<?php

declare(strict_types=1);

namespace App\Application\Inventory;

use App\Application\Inventory\Dto\ShelfOutput;
use App\Application\Shared\CollectionOutput;
use App\Application\Shared\QueryInput;
use App\Domain\Inventory\Shelf;
use App\Domain\Repositories\LibraryRepository;

/** Supporting query for CU-01: shelves with the occupancy of each level. */
final class ListShelves
{
    public function __construct(private LibraryRepository $library) {}

    public function execute(QueryInput $e): CollectionOutput
    {
        $library = $this->library->load();
        $clearance = $library->settings()->clearanceCm;

        return new CollectionOutput(array_map(
            fn (Shelf $shelf) => ShelfOutput::from($shelf, $clearance),
            $library->shelves(),
        ));
    }
}
