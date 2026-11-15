<?php

use App\Dominio\Excepciones\ReglaNegocioException;

it('espacio_es_grosor_mas_holgura', function () {
    expect(ejemplar('EJ-1', libro(grosorCm: 3.2))->espacioOcupado(0.4))->toEqualWithDelta(3.6, 1e-9);
});

it('rechaza_codigo_repetido_en_el_mismo_libro', function () {
    $libro = libro();
    $libro->agregarEjemplar('EJ-1');
    $libro->agregarEjemplar('EJ-1');
})->throws(ReglaNegocioException::class);
