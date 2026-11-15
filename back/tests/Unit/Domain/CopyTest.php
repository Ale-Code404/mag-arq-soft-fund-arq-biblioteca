<?php

use App\Domain\Exceptions\BusinessRuleException;

it('space_is_thickness_plus_clearance', function () {
    expect(makeCopy('EJ-1', book(thicknessCm: 3.2))->occupiedSpace(0.4))->toEqualWithDelta(3.6, 1e-9);
});

it('rejects_duplicate_code_in_same_book', function () {
    $book = book();
    $book->addCopy('EJ-1');
    $book->addCopy('EJ-1');
})->throws(BusinessRuleException::class);
