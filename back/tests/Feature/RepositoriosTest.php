<?php

use App\Dominio\Repositorios\RepositorioBiblioteca;
use App\Dominio\Repositorios\RepositorioLibros;
use Illuminate\Support\Facades\DB;

it('carga la biblioteca completa con una consulta por relación (RNF-08)', function () {
    $this->comoUsuario('bibliotecario');
    $this->prepararInventario();
    foreach (range(1, 5) as $n) {
        $this->registrarEjemplar("EJ-000{$n}", nivel: $n % 2 + 1);
    }

    DB::enableQueryLog();
    $biblioteca = app(RepositorioBiblioteca::class)->cargar();
    $consultas = count(DB::getQueryLog());

    expect($biblioteca->capacidadInstalada())->toEqualWithDelta(180.0, 1e-9)
        ->and($biblioteca->capacidadUsada())->toEqualWithDelta(18.0, 1e-9)
        ->and($consultas)->toBeLessThanOrEqual(6);   // biblioteca, estanterías, niveles, ejemplares, libros, configuración
});

it('traduce el libro entre modelo y dominio sin perder datos', function () {
    $this->comoUsuario('bibliotecario');
    $this->prepararInventario('ENSAYO', 1.75);

    $libro = app(RepositorioLibros::class)->buscarPorIsbn('9789580000001');

    expect($libro->tematica()->value)->toBe('ENSAYO')
        ->and($libro->getGrosorCm())->toBe(1.75)
        ->and(app(RepositorioLibros::class)->buscarPorIsbn('no-existe'))->toBeNull();
});
