<?php

use App\Domain\Catalog\Dimensions;
use App\Domain\Exceptions\BusinessRuleException;

it('rejects_out_of_range', function (float $height, float $width, float $thickness) {
    expect(fn () => new Dimensions($height, $width, $thickness))->toThrow(BusinessRuleException::class);
})->with([
    'alto bajo' => [4.9, 15, 3],
    'alto alto' => [60.1, 15, 3],
    'ancho bajo' => [23, 4.9, 3],
    'ancho alto' => [23, 50.1, 3],
    'grosor bajo' => [23, 15, 0.05],
    'grosor alto' => [23, 15, 15.1],
]);

it('accepts_the_limits', function () {
    expect(new Dimensions(5, 50, 0.1))->toBeInstanceOf(Dimensions::class);
});
