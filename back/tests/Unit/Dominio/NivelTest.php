<?php

use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Inventario\Nivel;

it('rechaza_libro_mas_alto', function () {
    $nivel = new Nivel(1, 90, 25);

    expect($nivel->admite(ejemplar('EJ-1', libro(altoCm: 30)), 0.4))->toBeFalse();
    $nivel->ubicar(ejemplar('EJ-1', libro(altoCm: 30)), 0.4);
})->throws(ReglaNegocioException::class);

it('rechaza_sin_espacio', function () {
    $nivel = new Nivel(1, 10, 32);
    $nivel->ubicar(ejemplar('EJ-1', libro(grosorCm: 4.6)), 0.4);
    $nivel->ubicar(ejemplar('EJ-2', libro(grosorCm: 4.6)), 0.4);   // llena exactamente 10 cm

    expect($nivel->espacioDisponible(0.4))->toEqualWithDelta(0.0, 1e-9)
        ->and($nivel->admite(ejemplar('EJ-3', libro(grosorCm: 0.1)), 0.4))->toBeFalse();
    $nivel->ubicar(ejemplar('EJ-3', libro(grosorCm: 0.1)), 0.4);
})->throws(ReglaNegocioException::class, 'El nivel no tiene espacio suficiente para el ejemplar.');

it('prestado_sigue_ocupando', function () {
    $nivel = new Nivel(1, 90, 32);
    $ej = ejemplar('EJ-1', libro(grosorCm: 3.2));
    $nivel->ubicar($ej, 0.4);
    $ej->marcarPrestado();

    expect($nivel->espacioUsado(0.4))->toEqualWithDelta(3.6, 1e-9);
});

it('rechaza_medidas_no_positivas', function () {
    new Nivel(1, 0, 32);
})->throws(ReglaNegocioException::class);
