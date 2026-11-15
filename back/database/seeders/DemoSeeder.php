<?php

namespace Database\Seeders;

use App\Aplicacion\Inventario\Dto\RegistrarEjemplarEntrada;
use App\Aplicacion\Inventario\Dto\RegistrarEstanteriaEntrada;
use App\Aplicacion\Inventario\Dto\RegistrarLibroEntrada;
use App\Aplicacion\Inventario\RegistrarEjemplar;
use App\Aplicacion\Inventario\RegistrarEstanteria;
use App\Aplicacion\Inventario\RegistrarLibro;
use App\Aplicacion\Prestamos\Dto\RegistrarLectorEntrada;
use App\Aplicacion\Prestamos\Dto\RegistrarPrestamoEntrada;
use App\Aplicacion\Prestamos\RegistrarLector;
use App\Aplicacion\Prestamos\RegistrarPrestamo;
use App\Aplicacion\Puertos\Reloj;
use App\Infraestructura\Persistencia\Modelos\EstanteriaModelo;
use App\Infraestructura\Persistencia\Modelos\UsuarioModelo;
use DateTimeImmutable;
use Illuminate\Database\Seeder;

/** Datos de demostración (solo local). Pasan por los casos de uso para respetar las reglas de negocio. */
class DemoSeeder extends Seeder
{
    private const LIBROS = [
        ['9789580000001', 'Cien años de soledad', 'Gabriel García Márquez', 'Sudamericana', 1967, 'NARRATIVA', 23, 15, 3.2],
        ['9789580000002', 'La vorágine', 'José Eustasio Rivera', 'Cromos', 1924, 'NARRATIVA', 21, 14, 2.4],
        ['9789580000003', 'Pedagogía del oprimido', 'Paulo Freire', 'Siglo XXI', 1970, 'ENSAYO', 21, 14, 1.8],
        ['9789580000004', 'La educación encierra un tesoro', 'Jacques Delors', 'UNESCO', 1996, 'ENSAYO', 24, 17, 1.5],
        ['9789580000005', 'Veinte poemas de amor', 'Pablo Neruda', 'Nascimento', 1924, 'POESIA', 18, 12, 0.8],
        ['9789580000006', 'Antología poética', 'José Asunción Silva', 'Norma', 1996, 'POESIA', 20, 13, 1.2],
        ['9789580000007', 'Rin rin renacuajo', 'Rafael Pombo', 'Panamericana', 1867, 'INFANTIL', 28, 22, 0.6],
        ['9789580000008', 'El principito', 'Antoine de Saint-Exupéry', 'Reynal & Hitchcock', 1943, 'INFANTIL', 23, 16, 1.0],
    ];

    private const LECTORES = [
        ['1020304050', 'Ana María Ruiz', 'Calle 10 # 5-20'],
        ['1030405060', 'Luis Fernando Gómez', 'Carrera 7 # 45-10'],
        ['1040506070', 'Camila Torres', null],
    ];

    public function run(): void
    {
        if (EstanteriaModelo::exists()) {
            return;
        }
        $usuario = UsuarioModelo::where('login', 'bibliotecario')->value('id');

        foreach (['E-01', 'E-02'] as $codigo) {
            app(RegistrarEstanteria::class)->ejecutar(new RegistrarEstanteriaEntrada($codigo, [
                ['numero' => 1, 'largoCm' => 90, 'altoCm' => 32],
                ['numero' => 2, 'largoCm' => 90, 'altoCm' => 32],
                ['numero' => 3, 'largoCm' => 90, 'altoCm' => 26],
            ], $usuario));
        }

        foreach (self::LIBROS as [$isbn, $titulo, $autor, $editorial, $anio, $tematica, $alto, $ancho, $grosor]) {
            app(RegistrarLibro::class)->ejecutar(new RegistrarLibroEntrada($isbn, $titulo, $autor, $editorial, $anio, $tematica, $alto, $ancho, $grosor, $usuario));
        }

        // 20 ejemplares: 3, 3, 3, 3, 2, 2, 2, 2 por libro; los libros de 28 cm van a niveles de 32 cm
        $copias = [3, 3, 3, 3, 2, 2, 2, 2];
        $n = 0;
        foreach (self::LIBROS as $i => [$isbn]) {
            for ($c = 1; $c <= $copias[$i]; $c++) {
                $n++;
                app(RegistrarEjemplar::class)->ejecutar(new RegistrarEjemplarEntrada(
                    $isbn, sprintf('EJ-%04d', $n), $n <= 10 ? 'E-01' : 'E-02', ($n % 2) + 1, $usuario,
                ));
            }
        }

        foreach (self::LECTORES as [$identificacion, $nombre, $direccion]) {
            app(RegistrarLector::class)->ejecutar(new RegistrarLectorEntrada($identificacion, $nombre, $direccion, $usuario));
        }

        // Un préstamo vencido: se registra con un reloj de hace 20 días (entrega prevista hace 5)
        app()->instance(Reloj::class, new class implements Reloj
        {
            public function hoy(): DateTimeImmutable
            {
                return new DateTimeImmutable('today -20 days');
            }
        });
        app(RegistrarPrestamo::class)->ejecutar(new RegistrarPrestamoEntrada('1030405060', 'EJ-0001', $usuario));
        app()->forgetInstance(Reloj::class);
    }
}
