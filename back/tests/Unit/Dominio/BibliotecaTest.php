<?php

use App\Dominio\Catalogo\Tematica;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Inventario\Biblioteca;
use App\Dominio\Inventario\Estanteria;
use App\Dominio\Inventario\Nivel;

function bibliotecaDosNiveles(): Biblioteca
{
    return new Biblioteca('UAN', configuracion(), [
        new Estanteria('E-01', [new Nivel(1, 90, 32), new Nivel(2, 90, 32)]),
    ]);
}

it('metricas_de_capacidad', function () {
    $b = bibliotecaDosNiveles();
    $b->ubicar(ejemplar('EJ-1', libro(grosorCm: 3.2, tematica: Tematica::POESIA)), 'E-01', 1);

    expect($b->capacidadInstalada())->toEqualWithDelta(180.0, 1e-9)
        ->and($b->capacidadUsada())->toEqualWithDelta(3.6, 1e-9)
        ->and($b->capacidadDisponible())->toEqualWithDelta(176.4, 1e-9)
        ->and($b->grosorPromedio(Tematica::POESIA))->toEqualWithDelta(3.2, 1e-9)
        ->and($b->capacidadTeorica(Tematica::POESIA))->toBe(50)
        ->and($b->capacidadDisponibleLibros(Tematica::POESIA))->toBe(49);
});

it('promedio_por_tematica_y_por_defecto', function () {
    $b = bibliotecaDosNiveles();

    expect($b->grosorPromedio())->toBe(2.5)
        ->and($b->usaGrosorPorDefecto())->toBeTrue()
        ->and($b->capacidadTeorica())->toBe(62);   // floor(180 / 2.9)

    $b->ubicar(ejemplar('EJ-1', libro(grosorCm: 2.0, tematica: Tematica::ENSAYO, isbn: '1')), 'E-01', 1);
    $b->ubicar(ejemplar('EJ-2', libro(grosorCm: 4.0, tematica: Tematica::ENSAYO, isbn: '2')), 'E-01', 1);
    $b->ubicar(ejemplar('EJ-3', libro(grosorCm: 6.0, tematica: Tematica::INFANTIL, isbn: '3')), 'E-01', 2);

    expect($b->grosorPromedio())->toEqualWithDelta(4.0, 1e-9)
        ->and($b->grosorPromedio(Tematica::ENSAYO))->toEqualWithDelta(3.0, 1e-9)
        ->and($b->grosorPromedio(Tematica::POESIA))->toBe(2.5)
        ->and($b->usaGrosorPorDefecto(Tematica::POESIA))->toBeTrue();
});

it('reubica_un_ejemplar_entre_niveles', function () {
    $b = bibliotecaDosNiveles();
    $ej = ejemplar('EJ-1');
    $b->ubicar($ej, 'E-01', 1);
    $b->ubicar($ej, 'E-01', 2);

    expect($b->estanteria('E-01')->nivel(1)->ejemplares())->toBe([])
        ->and($b->estanteria('E-01')->nivel(2)->ejemplares())->toBe([$ej]);
});

it('conserva_la_ubicacion_si_la_reubicacion_falla', function () {
    $b = new Biblioteca('UAN', configuracion(), [
        new Estanteria('E-01', [new Nivel(1, 90, 32), new Nivel(2, 90, 20)]),
    ]);
    $ej = ejemplar('EJ-1', libro(altoCm: 25));
    $b->ubicar($ej, 'E-01', 1);

    expect(fn () => $b->ubicar($ej, 'E-01', 2))->toThrow(ReglaNegocioException::class)
        ->and($b->estanteria('E-01')->nivel(1)->contiene('EJ-1'))->toBeTrue();
});

it('rechaza_estanteria_repetida_y_nivel_inexistente', function () {
    $b = bibliotecaDosNiveles();

    expect(fn () => $b->agregarEstanteria(new Estanteria('E-01', [new Nivel(1, 90, 32)])))->toThrow(ReglaNegocioException::class)
        ->and(fn () => $b->ubicar(ejemplar(), 'E-01', 9))->toThrow(RecursoNoEncontradoException::class);
});
