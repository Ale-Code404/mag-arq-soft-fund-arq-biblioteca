<?php

use App\Domain\Repositories\BookRepository;
use App\Domain\Repositories\LibraryRepository;
use Illuminate\Support\Facades\DB;

it('loads the whole library with one query per relation (RNF-08)', function () {
    $this->actingAsUser('librarian');
    $this->prepareInventory();
    foreach (range(1, 5) as $n) {
        $this->registerCopy("EJ-000{$n}", level: $n % 2 + 1);
    }

    DB::enableQueryLog();
    $library = app(LibraryRepository::class)->load();
    $queries = count(DB::getQueryLog());

    expect($library->installedCapacity())->toEqualWithDelta(180.0, 1e-9)
        ->and($library->usedCapacity())->toEqualWithDelta(18.0, 1e-9)
        ->and($queries)->toBeLessThanOrEqual(6);   // library, shelves, levels, copies, books, settings
});

it('maps the book between model and domain without losing data', function () {
    $this->actingAsUser('librarian');
    $this->prepareInventory('ESSAY', 1.75);

    $book = app(BookRepository::class)->findByIsbn('9789580000001');

    expect($book->genre()->value)->toBe('ESSAY')
        ->and($book->getThicknessCm())->toBe(1.75)
        ->and(app(BookRepository::class)->findByIsbn('no-exists'))->toBeNull();
});
