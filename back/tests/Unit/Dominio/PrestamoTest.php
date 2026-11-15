<?php

use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Penalizaciones\EstadoMulta;
use App\Dominio\Penalizaciones\Multa;
use App\Dominio\Penalizaciones\PoliticaMulta;
use App\Dominio\Penalizaciones\TarifaFijaPorDia;
use App\Dominio\Personas\Lector;
use App\Dominio\Prestamos\Prestamo;

function prestamo(string $desde = '2026-11-01', ?Lector $lector = null): Prestamo
{
    return new Prestamo(null, ejemplar(), $lector ?? new Lector('1020304050', 'Ana Ruiz'), fecha($desde), configuracion());
}

it('fija_entrega_a_15_dias', function () {
    $p = prestamo('2026-11-01');

    expect($p->fechaEntregaPrevista())->toEqual(fecha('2026-11-16'))
        ->and($p->ejemplar()->estaDisponible())->toBeFalse();
});

it('rechaza_prestar_un_ejemplar_prestado', function () {
    $ej = ejemplar();
    new Prestamo(null, $ej, new Lector('1', 'Ana'), fecha('2026-11-01'), configuracion());
    new Prestamo(null, $ej, new Lector('2', 'Luis'), fecha('2026-11-01'), configuracion());
})->throws(ReglaNegocioException::class, 'El ejemplar no está disponible.');

it('renueva_una_vez_desde_primera_entrega', function () {
    $p = prestamo('2026-11-01');
    $p->renovar(fecha('2026-11-10'), configuracion());

    expect($p->fechaEntregaPrevista())->toEqual(fecha('2026-12-01'))
        ->and($p->renovaciones())->toBe(1);
});

it('rechaza_segunda_renovacion', function () {
    $p = prestamo('2026-11-01');
    $p->renovar(fecha('2026-11-10'), configuracion());
    $p->renovar(fecha('2026-11-20'), configuracion());
})->throws(ReglaNegocioException::class, 'El préstamo ya fue renovado.');

it('rechaza_renovar_vencido', function () {
    prestamo('2026-11-01')->renovar(fecha('2026-11-17'), configuracion());
})->throws(ReglaNegocioException::class, 'El préstamo está vencido.');

it('rechaza_renovar_con_multas', function () {
    $lector = new Lector('1020304050', 'Ana Ruiz', null, [Multa::pendiente(3000, 3, 1000)]);
    prestamo('2026-11-01', $lector)->renovar(fecha('2026-11-10'), configuracion());
})->throws(ReglaNegocioException::class, 'El lector tiene multas pendientes.');

it('devolucion_tardia_genera_multa', function () {
    $p = prestamo('2026-11-01');
    $multa = $p->devolver(fecha('2026-11-20'), new TarifaFijaPorDia(1000));

    expect($multa->valor())->toBe(4000)
        ->and($multa->diasRetraso())->toBe(4)
        ->and($multa->tarifaAplicada())->toBe(1000)
        ->and($multa->estado())->toBe(EstadoMulta::PENDIENTE)
        ->and($p->ejemplar()->estaDisponible())->toBeTrue();
});

it('devolucion_tardia_usa_la_politica_recibida (polimorfismo)', function () {
    $doble = new class implements PoliticaMulta
    {
        public function calcular(Prestamo $prestamo, DateTimeImmutable $fecha): int
        {
            return $prestamo->diasRetraso($fecha) * 2500;
        }
    };

    expect(prestamo('2026-11-01')->devolver(fecha('2026-11-18'), $doble)->valor())->toBe(5000);
});

it('devolucion_a_tiempo_sin_multa', function () {
    $p = prestamo('2026-11-01');

    expect($p->devolver(fecha('2026-11-16'), new TarifaFijaPorDia(1000)))->toBeNull()
        ->and($p->estaActivo())->toBeFalse();
});

it('rechaza_devolver_dos_veces', function () {
    $p = prestamo('2026-11-01');
    $p->devolver(fecha('2026-11-05'), new TarifaFijaPorDia(1000));
    $p->devolver(fecha('2026-11-06'), new TarifaFijaPorDia(1000));
})->throws(ReglaNegocioException::class, 'El préstamo ya fue devuelto.');
