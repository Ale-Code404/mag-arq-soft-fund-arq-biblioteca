<?php

use App\Dominio\Catalogo\Dimensiones;
use App\Dominio\Excepciones\ReglaNegocioException;

it('rechaza_fuera_de_rango', function (float $alto, float $ancho, float $grosor) {
    expect(fn () => new Dimensiones($alto, $ancho, $grosor))->toThrow(ReglaNegocioException::class);
})->with([
    'alto bajo' => [4.9, 15, 3],
    'alto alto' => [60.1, 15, 3],
    'ancho bajo' => [23, 4.9, 3],
    'ancho alto' => [23, 50.1, 3],
    'grosor bajo' => [23, 15, 0.05],
    'grosor alto' => [23, 15, 15.1],
]);

it('acepta_los_limites', function () {
    expect(new Dimensiones(5, 50, 0.1))->toBeInstanceOf(Dimensiones::class);
});
