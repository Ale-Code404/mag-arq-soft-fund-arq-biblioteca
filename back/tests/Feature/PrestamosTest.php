<?php

use App\Infraestructura\Persistencia\Modelos\EjemplarModelo;
use App\Infraestructura\Persistencia\Modelos\MultaModelo;
use App\Infraestructura\Persistencia\Modelos\PersonaModelo;
use App\Infraestructura\Persistencia\Modelos\PrestamoModelo;
use Illuminate\Database\UniqueConstraintViolationException;

beforeEach(function () {
    $this->comoUsuario('bibliotecario');
    $this->prepararInventario();
    $this->registrarEjemplar('EJ-0001');
    $this->registrarEjemplar('EJ-0002');
    $this->registrarLector();
    $this->fijarHoy('2026-11-01');
});

function prestar(string $ejemplar = 'EJ-0001', string $lector = '1020304050')
{
    return test()->postJson('/api/prestamos', ['identificacionLector' => $lector, 'codigoEjemplar' => $ejemplar]);
}

/** Préstamo del 1 de noviembre devuelto el 20: 4 días de retraso, multa de 4000. */
function generarMulta(): int
{
    prestar()->assertCreated();
    test()->fijarHoy('2026-11-20');

    return test()->postJson('/api/devoluciones', ['codigoEjemplar' => 'EJ-0001'])->json('multa.id');
}

it('CU-06 registra un lector y rechaza identificación repetida', function () {
    $this->postJson('/api/lectores', ['identificacion' => '1020304050', 'nombre' => 'Otra'])
        ->assertUnprocessable()->assertJson(['regla' => 'RF-06']);
});

it('CU-07 presta un ejemplar por 15 días y registra quién lo hizo', function () {
    prestar()->assertCreated()->assertJson([
        'codigoEjemplar' => 'EJ-0001', 'fechaPrestamo' => '2026-11-01', 'fechaEntregaPrevista' => '2026-11-16', 'activo' => true,
    ]);

    expect(EjemplarModelo::where('codigo', 'EJ-0001')->value('estado'))->toBe('PRESTADO')
        ->and(PrestamoModelo::first()->registrado_por)->toBe(auth()->id());
});

it('R-04 rechaza un segundo préstamo del mismo ejemplar', function () {
    prestar()->assertCreated();
    $this->postJson('/api/lectores', ['identificacion' => '2', 'nombre' => 'Luis'])->assertCreated();

    prestar(lector: '2')->assertUnprocessable()->assertJson(['regla' => 'RF-07']);
    expect(PrestamoModelo::count())->toBe(1);
});

it('R-04 el índice único parcial impide dos préstamos activos aunque se salte el dominio', function () {
    prestar()->assertCreated();
    $p = PrestamoModelo::first();

    expect(fn () => PrestamoModelo::create($p->only(['ejemplar_id', 'lector_id', 'fecha_prestamo', 'fecha_entrega_prevista'])))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('RN-05 rechaza el préstamo a un lector con multa pendiente', function () {
    generarMulta();

    prestar('EJ-0002')->assertUnprocessable()->assertJson(['regla' => 'RN-05']);
});

it('CU-08 renueva una vez 15 días desde la primera entrega', function () {
    prestar();
    $this->fijarHoy('2026-11-10');

    $this->postJson('/api/prestamos/EJ-0001/renovacion')->assertOk()->assertJson(['fechaEntregaPrevista' => '2026-12-01', 'renovaciones' => 1]);
    $this->postJson('/api/prestamos/EJ-0001/renovacion')->assertUnprocessable()->assertJson(['regla' => 'RN-02']);
});

it('CU-08 no renueva un préstamo vencido', function () {
    prestar();
    $this->fijarHoy('2026-11-17');

    $this->postJson('/api/prestamos/EJ-0001/renovacion')->assertUnprocessable()
        ->assertExactJson(['regla' => 'RN-03', 'mensaje' => 'El préstamo está vencido.']);
});

it('CU-08 no renueva si el lector tiene multas pendientes', function () {
    generarMulta();
    $this->fijarHoy('2026-11-20');
    // préstamo vigente previo a la multa, creado directamente
    PrestamoModelo::create([
        'ejemplar_id' => EjemplarModelo::where('codigo', 'EJ-0002')->value('id'),
        'lector_id' => PersonaModelo::where('identificacion', '1020304050')->value('id'),
        'fecha_prestamo' => '2026-11-15', 'fecha_entrega_prevista' => '2026-11-30',
    ]);
    EjemplarModelo::where('codigo', 'EJ-0002')->update(['estado' => 'PRESTADO']);

    $this->postJson('/api/prestamos/EJ-0002/renovacion')->assertUnprocessable()
        ->assertJson(['regla' => 'RN-03', 'mensaje' => 'El lector tiene multas pendientes.']);
});

it('CU-09 y CU-15 la devolución tardía genera una multa pendiente', function () {
    prestar();
    $this->fijarHoy('2026-11-20');

    $this->postJson('/api/devoluciones', ['codigoEjemplar' => 'EJ-0001'])->assertOk()->assertJson([
        'prestamo' => ['fechaDevolucion' => '2026-11-20', 'activo' => false, 'diasRetraso' => 4],
        'multa' => ['valor' => 4000, 'diasRetraso' => 4, 'tarifaAplicada' => 1000, 'estado' => 'PENDIENTE'],
    ]);
    expect(EjemplarModelo::where('codigo', 'EJ-0001')->value('estado'))->toBe('DISPONIBLE');
    $this->postJson('/api/devoluciones', ['codigoEjemplar' => 'EJ-0001'])->assertNotFound();
});

it('CU-09 la devolución a tiempo no genera multa', function () {
    prestar();
    $this->fijarHoy('2026-11-16');

    $this->postJson('/api/devoluciones', ['codigoEjemplar' => 'EJ-0001'])->assertOk()->assertJsonPath('multa', null);
    expect(MultaModelo::count())->toBe(0);
});

it('CU-10 registra el pago de una multa una sola vez', function () {
    $id = generarMulta();
    $this->fijarHoy('2026-11-21');

    $this->postJson("/api/multas/{$id}/pago")->assertOk()->assertJson(['estado' => 'PAGADA', 'fechaPago' => '2026-11-21']);
    $this->postJson("/api/multas/{$id}/pago")->assertUnprocessable()->assertJson(['regla' => 'RF-10']);
    expect(MultaModelo::find($id)->pago_registrado_por)->toBe(auth()->id());
    prestar('EJ-0002')->assertCreated();
});

it('CU-11 consulta el historial con multas pendientes', function () {
    generarMulta();
    $this->comoUsuario('director');

    $this->getJson('/api/lectores/1020304050/historial')->assertOk()
        ->assertJsonPath('lector.tieneMultasPendientes', true)
        ->assertJsonPath('prestamos.0.multa.valor', 4000)
        ->assertJsonPath('totalMultasPendientes', 4000);
    $this->getJson('/api/lectores/999/historial')->assertNotFound();
});
