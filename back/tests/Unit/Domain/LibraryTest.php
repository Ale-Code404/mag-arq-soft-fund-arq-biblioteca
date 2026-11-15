<?php

use App\Domain\Catalog\Genre;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Inventory\Level;
use App\Domain\Inventory\Library;
use App\Domain\Inventory\Shelf;

function libraryWithTwoLevels(): Library
{
    return new Library('UAN', settings(), [
        new Shelf('E-01', [new Level(1, 90, 32), new Level(2, 90, 32)]),
    ]);
}

it('capacity_metrics', function () {
    $b = libraryWithTwoLevels();
    $b->place(makeCopy('EJ-1', book(thicknessCm: 3.2, genre: Genre::POETRY)), 'E-01', 1);

    expect($b->installedCapacity())->toEqualWithDelta(180.0, 1e-9)
        ->and($b->usedCapacity())->toEqualWithDelta(3.6, 1e-9)
        ->and($b->availableCapacity())->toEqualWithDelta(176.4, 1e-9)
        ->and($b->averageThickness(Genre::POETRY))->toEqualWithDelta(3.2, 1e-9)
        ->and($b->theoreticalCapacity(Genre::POETRY))->toBe(50)
        ->and($b->availableCapacityBooks(Genre::POETRY))->toBe(49);
});

it('average_by_genre_and_default', function () {
    $b = libraryWithTwoLevels();

    expect($b->averageThickness())->toBe(2.5)
        ->and($b->usesDefaultThickness())->toBeTrue()
        ->and($b->theoreticalCapacity())->toBe(62);   // floor(180 / 2.9)

    $b->place(makeCopy('EJ-1', book(thicknessCm: 2.0, genre: Genre::ESSAY, isbn: '1')), 'E-01', 1);
    $b->place(makeCopy('EJ-2', book(thicknessCm: 4.0, genre: Genre::ESSAY, isbn: '2')), 'E-01', 1);
    $b->place(makeCopy('EJ-3', book(thicknessCm: 6.0, genre: Genre::CHILDREN, isbn: '3')), 'E-01', 2);

    expect($b->averageThickness())->toEqualWithDelta(4.0, 1e-9)
        ->and($b->averageThickness(Genre::ESSAY))->toEqualWithDelta(3.0, 1e-9)
        ->and($b->averageThickness(Genre::POETRY))->toBe(2.5)
        ->and($b->usesDefaultThickness(Genre::POETRY))->toBeTrue();
});

it('relocates_a_copy_between_levels', function () {
    $b = libraryWithTwoLevels();
    $copy = makeCopy('EJ-1');
    $b->place($copy, 'E-01', 1);
    $b->place($copy, 'E-01', 2);

    expect($b->shelf('E-01')->level(1)->copies())->toBe([])
        ->and($b->shelf('E-01')->level(2)->copies())->toBe([$copy]);
});

it('keeps_location_when_relocation_fails', function () {
    $b = new Library('UAN', settings(), [
        new Shelf('E-01', [new Level(1, 90, 32), new Level(2, 90, 20)]),
    ]);
    $copy = makeCopy('EJ-1', book(heightCm: 25));
    $b->place($copy, 'E-01', 1);

    expect(fn () => $b->place($copy, 'E-01', 2))->toThrow(BusinessRuleException::class)
        ->and($b->shelf('E-01')->level(1)->contains('EJ-1'))->toBeTrue();
});

it('rejects_duplicate_shelf_and_missing_level', function () {
    $b = libraryWithTwoLevels();

    expect(fn () => $b->addShelf(new Shelf('E-01', [new Level(1, 90, 32)])))->toThrow(BusinessRuleException::class)
        ->and(fn () => $b->place(makeCopy(), 'E-01', 9))->toThrow(ResourceNotFoundException::class);
});
