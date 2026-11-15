<?php

namespace Tests;

use App\Aplicacion\Puertos\Reloj;
use App\Infraestructura\Persistencia\Modelos\UsuarioModelo;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Soporte\RelojFijo;

abstract class TestCase extends BaseTestCase
{
    /** Las pruebas de API parten de la biblioteca, la configuración, los roles y los usuarios semilla. */
    protected bool $seed = true;

    private static bool $baseDePruebasLista = false;

    /** Crea la base de pruebas la primera vez, para no depender de scripts de Docker. */
    protected function beforeRefreshingDatabase()
    {
        if (self::$baseDePruebasLista) {
            return;
        }
        $base = config('database.connections.pgsql.database');
        config(['database.connections.administracion' => ['database' => 'postgres'] + config('database.connections.pgsql')]);
        if (! DB::connection('administracion')->selectOne('select 1 from pg_database where datname = ?', [$base])) {
            DB::connection('administracion')->statement("CREATE DATABASE \"{$base}\"");
        }
        DB::purge('administracion');
        self::$baseDePruebasLista = true;
    }

    protected function comoUsuario(string $login): UsuarioModelo
    {
        $usuario = UsuarioModelo::where('login', $login)->firstOrFail();
        Sanctum::actingAs($usuario);

        return $usuario;
    }

    protected function fijarHoy(string $fecha): void
    {
        $this->app->instance(Reloj::class, new RelojFijo(new DateTimeImmutable($fecha)));
    }

    /** Estantería E-01 con dos niveles de 90 × 32 cm y el libro de la sección 9.2. */
    protected function prepararInventario(string $tematica = 'NARRATIVA', float $grosorCm = 3.2): void
    {
        $this->postJson('/api/estanterias', [
            'codigo' => 'E-01',
            'niveles' => [['numero' => 1, 'largoCm' => 90, 'altoCm' => 32], ['numero' => 2, 'largoCm' => 90, 'altoCm' => 32]],
        ])->assertCreated();
        $this->postJson('/api/libros', [
            'isbn' => '9789580000001', 'titulo' => 'Cien años de soledad', 'autor' => 'Gabriel García Márquez',
            'editorial' => 'Sudamericana', 'anio' => 1967, 'tematica' => $tematica,
            'altoCm' => 23, 'anchoCm' => 15, 'grosorCm' => $grosorCm,
        ])->assertCreated();
    }

    protected function registrarEjemplar(string $codigo, int $nivel = 1, string $isbn = '9789580000001')
    {
        return $this->postJson("/api/libros/{$isbn}/ejemplares", ['codigo' => $codigo, 'estanteria' => 'E-01', 'nivel' => $nivel]);
    }

    protected function registrarLector(string $identificacion = '1020304050'): void
    {
        $this->postJson('/api/lectores', ['identificacion' => $identificacion, 'nombre' => 'Ana María Ruiz', 'direccion' => 'Calle 10'])->assertCreated();
    }
}
