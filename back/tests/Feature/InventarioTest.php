<?php

use App\Infraestructura\Persistencia\Modelos\EjemplarModelo;
use App\Infraestructura\Persistencia\Modelos\NivelModelo;

beforeEach(fn () => $this->comoUsuario('bibliotecario'));

it('CU-01 registra y lista estanterías con sus niveles', function () {
    $this->postJson('/api/estanterias', [
        'codigo' => 'E-01',
        'niveles' => [['numero' => 1, 'largoCm' => 90, 'altoCm' => 32], ['numero' => 2, 'largoCm' => 90, 'altoCm' => 32]],
    ])->assertCreated()->assertJson(['codigo' => 'E-01', 'instaladaCm' => 180.0]);

    $this->getJson('/api/estanterias')->assertOk()->assertJsonCount(1)->assertJsonPath('0.niveles.1.numero', 2);
});

it('CU-01 rechaza código repetido y medidas inválidas', function () {
    $this->prepararInventario();

    $this->postJson('/api/estanterias', ['codigo' => 'E-01', 'niveles' => [['numero' => 1, 'largoCm' => 50, 'altoCm' => 30]]])
        ->assertUnprocessable()->assertJson(['regla' => 'RF-01']);
    $this->postJson('/api/estanterias', ['codigo' => 'E-02', 'niveles' => [['numero' => 1, 'largoCm' => 0, 'altoCm' => 30]]])
        ->assertUnprocessable()->assertJsonValidationErrorFor('niveles.0.largoCm', 'errores');
});

it('CU-02 registra un libro y rechaza ISBN repetido y dimensiones fuera de rango', function () {
    $this->prepararInventario();
    $libro = ['isbn' => '9789580000001', 'titulo' => 'Otro', 'autor' => 'Otro', 'editorial' => 'X', 'anio' => 2000, 'tematica' => 'ENSAYO', 'altoCm' => 23, 'anchoCm' => 15, 'grosorCm' => 2];

    $this->postJson('/api/libros', $libro)->assertUnprocessable()->assertJson(['regla' => 'RF-02']);
    $this->postJson('/api/libros', ['isbn' => '9789580000099', 'altoCm' => 61] + $libro)->assertUnprocessable()->assertJson(['regla' => 'R-02']);
});

it('CU-03 registra un ejemplar y lo ubica en el mismo paso', function () {
    $this->prepararInventario();

    $this->registrarEjemplar('EJ-0001')->assertCreated()
        ->assertJson(['codigo' => 'EJ-0001', 'estado' => 'DISPONIBLE', 'estanteria' => 'E-01', 'nivel' => 1]);
    $this->registrarEjemplar('EJ-0001')->assertUnprocessable()->assertJson(['regla' => 'RN-08']);
});

it('CU-03 no deja el ejemplar registrado si no cabe en el nivel (transacción)', function () {
    $this->prepararInventario();
    NivelModelo::where('numero', 2)->update(['alto_cm' => 20]);

    $this->registrarEjemplar('EJ-0001', nivel: 2)->assertUnprocessable()->assertJson(['regla' => 'RN-07']);
    expect(EjemplarModelo::where('codigo', 'EJ-0001')->exists())->toBeFalse();
});

it('CU-04 reubica un ejemplar en otro nivel', function () {
    $this->prepararInventario();
    $this->registrarEjemplar('EJ-0001');

    $this->putJson('/api/ejemplares/EJ-0001/ubicacion', ['estanteria' => 'E-01', 'nivel' => 2])->assertOk()->assertJson(['nivel' => 2]);
    $this->putJson('/api/ejemplares/EJ-0001/ubicacion', ['estanteria' => 'E-09', 'nivel' => 1])->assertNotFound();
    $this->putJson('/api/ejemplares/EJ-9999/ubicacion', ['estanteria' => 'E-01', 'nivel' => 1])->assertNotFound();
});

it('CU-05 calcula la capacidad por temática como en el contrato 9.2', function () {
    $this->prepararInventario('POESIA', 3.2);
    $this->registrarEjemplar('EJ-0001');

    $this->getJson('/api/capacidad?alcance=tematica&valor=POESIA')->assertOk()->assertExactJson([
        'alcance' => 'tematica', 'valor' => 'POESIA', 'instaladaCm' => 180.0, 'usadaCm' => 3.6, 'disponibleCm' => 176.4,
        'grosorPromedioCm' => 3.2, 'teoricaLibros' => 50, 'disponibleLibros' => 49, 'usaGrosorPorDefecto' => false,
    ]);
    expect($this->getJson('/api/capacidad?alcance=total')->getContent())->toContain('"instaladaCm":180.0');
    $this->getJson('/api/capacidad?alcance=estanteria&valor=E-01')->assertOk()->assertJson(['instaladaCm' => 180.0]);
    $this->getJson('/api/capacidad?alcance=tematica&valor=ENSAYO')->assertOk()->assertJson(['grosorPromedioCm' => 2.5, 'usaGrosorPorDefecto' => true]);
    $this->getJson('/api/capacidad?alcance=tematica')->assertUnprocessable();
});

it('CU-05 responde desde caché y se invalida al ubicar un ejemplar', function () {
    $this->prepararInventario();
    $this->getJson('/api/capacidad?alcance=total')->assertJson(['instaladaCm' => 180.0, 'usadaCm' => 0.0]);

    // Un cambio por fuera de los casos de uso no se ve: la segunda consulta sale de caché
    NivelModelo::where('numero', 1)->update(['largo_cm' => 100]);
    $this->getJson('/api/capacidad?alcance=total')->assertJson(['instaladaCm' => 180.0]);

    $this->registrarEjemplar('EJ-0001')->assertCreated();
    $this->getJson('/api/capacidad?alcance=total')->assertJson(['instaladaCm' => 190.0, 'usadaCm' => 3.6]);
});
