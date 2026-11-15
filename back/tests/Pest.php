<?php

use App\Dominio\Catalogo\Dimensiones;
use App\Dominio\Catalogo\Ejemplar;
use App\Dominio\Catalogo\Libro;
use App\Dominio\Catalogo\Tematica;
use App\Dominio\Configuracion\ConfiguracionBiblioteca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
| Fábricas del dominio para las pruebas unitarias (sin Laravel ni base de datos).
*/

function configuracion(
    int $diasPrestamo = 15,
    int $diasRenovacion = 15,
    int $maxRenovaciones = 1,
    int $valorMultaDiaria = 1000,
    float $holguraCm = 0.4,
    float $grosorPorDefectoCm = 2.5,
): ConfiguracionBiblioteca {
    return new ConfiguracionBiblioteca($diasPrestamo, $diasRenovacion, $maxRenovaciones, $valorMultaDiaria, $holguraCm, $grosorPorDefectoCm);
}

function libro(
    float $grosorCm = 3.0,
    float $altoCm = 23.0,
    Tematica $tematica = Tematica::NARRATIVA,
    string $isbn = '9789580000001',
): Libro {
    return new Libro($isbn, 'Cien años de soledad', 'Gabriel García Márquez', 'Sudamericana', 1967, $tematica, new Dimensiones($altoCm, 15.0, $grosorCm));
}

function ejemplar(string $codigo = 'EJ-0001', ?Libro $libro = null): Ejemplar
{
    return ($libro ?? libro())->agregarEjemplar($codigo);
}

function fecha(string $fecha): DateTimeImmutable
{
    return new DateTimeImmutable($fecha);
}
