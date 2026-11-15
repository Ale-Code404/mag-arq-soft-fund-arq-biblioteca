<?php

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Inventory\Level;

it('rejects_taller_book', function () {
    $level = new Level(1, 90, 25);

    expect($level->accepts(makeCopy('EJ-1', book(heightCm: 30)), 0.4))->toBeFalse();
    $level->place(makeCopy('EJ-1', book(heightCm: 30)), 0.4);
})->throws(BusinessRuleException::class);

it('rejects_without_space', function () {
    $level = new Level(1, 10, 32);
    $level->place(makeCopy('EJ-1', book(thicknessCm: 4.6)), 0.4);
    $level->place(makeCopy('EJ-2', book(thicknessCm: 4.6)), 0.4);   // fills exactly 10 cm

    expect($level->availableSpace(0.4))->toEqualWithDelta(0.0, 1e-9)
        ->and($level->accepts(makeCopy('EJ-3', book(thicknessCm: 0.1)), 0.4))->toBeFalse();
    $level->place(makeCopy('EJ-3', book(thicknessCm: 0.1)), 0.4);
})->throws(BusinessRuleException::class, 'El nivel no tiene espacio suficiente para el ejemplar.');

it('loaned_copy_still_occupies_space', function () {
    $level = new Level(1, 90, 32);
    $copy = makeCopy('EJ-1', book(thicknessCm: 3.2));
    $level->place($copy, 0.4);
    $copy->markOnLoan();

    expect($level->usedSpace(0.4))->toEqualWithDelta(3.6, 1e-9);
});

it('rejects_non_positive_measures', function () {
    new Level(1, 0, 32);
})->throws(BusinessRuleException::class);
