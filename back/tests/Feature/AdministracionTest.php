<?php

use App\Infraestructura\Persistencia\Modelos\UsuarioModelo;
use Database\Seeders\UsuariosSeeder;

beforeEach(fn () => $this->comoUsuario('admin'));

$nuevo = [
    'login' => 'auxiliar', 'clave' => 'Segura123*', 'identificacion' => '900000010',
    'nombre' => 'Auxiliar de biblioteca', 'cargo' => 'Auxiliar', 'roles' => ['Director'],
];

it('CU-12 crea un usuario que puede iniciar sesión', function () use ($nuevo) {
    $this->postJson('/api/usuarios', $nuevo)->assertCreated()->assertJson(['login' => 'auxiliar', 'activo' => true, 'roles' => ['Director']]);

    $this->postJson('/api/auth/login', ['login' => 'auxiliar', 'clave' => 'Segura123*'])->assertOk();
    $this->postJson('/api/usuarios', $nuevo)->assertUnprocessable()->assertJson(['regla' => 'RF-12']);
    $this->postJson('/api/usuarios', ['login' => 'otro', 'identificacion' => '1', 'roles' => ['Inexistente']] + $nuevo)
        ->assertUnprocessable()->assertJson(['mensaje' => 'Roles inexistentes: Inexistente.']);
});

it('CU-12 asignar roles cambia los permisos de inmediato (caché invalidada)', function () {
    $director = UsuarioModelo::where('login', 'director')->first();
    $this->comoUsuario('director');
    $this->postJson('/api/lectores', ['identificacion' => '1', 'nombre' => 'Ana'])->assertForbidden();

    $this->comoUsuario('admin');
    $this->putJson("/api/usuarios/{$director->id}/roles", ['roles' => ['Director', 'Bibliotecario']])->assertOk();

    $this->comoUsuario('director');
    $this->postJson('/api/lectores', ['identificacion' => '1', 'nombre' => 'Ana'])->assertCreated();
});

it('CU-12 edita y desactiva un usuario revocando sus tokens', function () {
    $director = UsuarioModelo::where('login', 'director')->first();
    $token = $this->postJson('/api/auth/login', ['login' => 'director', 'clave' => UsuariosSeeder::CLAVE])->json('token');

    $this->putJson("/api/usuarios/{$director->id}", ['cargo' => 'Directora', 'activo' => false])->assertOk()
        ->assertJson(['cargo' => 'Directora', 'activo' => false]);

    expect($director->tokens()->count())->toBe(0);
    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson('/api/auth/yo')->assertUnauthorized();
    $this->postJson('/api/auth/login', ['login' => 'director', 'clave' => UsuariosSeeder::CLAVE])->assertUnauthorized();
});

it('CU-12 impide que el administrador se desactive a sí mismo', function () {
    $this->putJson('/api/usuarios/'.auth()->id(), ['activo' => false])->assertUnprocessable()->assertJson(['regla' => 'RF-12']);
});

it('CU-13 consulta y cambia la configuración vigente', function () {
    $this->getJson('/api/configuracion')->assertOk()->assertExactJson([
        'diasPrestamo' => 15, 'diasRenovacion' => 15, 'maxRenovaciones' => 1,
        'valorMultaDiaria' => 1000, 'holguraCm' => 0.4, 'grosorPorDefectoCm' => 2.5,
    ]);

    $nueva = ['diasPrestamo' => 10, 'diasRenovacion' => 7, 'maxRenovaciones' => 2, 'valorMultaDiaria' => 1500, 'holguraCm' => 0.5, 'grosorPorDefectoCm' => 3.0];
    $this->putJson('/api/configuracion', $nueva)->assertOk()->assertJson($nueva);
    $this->getJson('/api/configuracion')->assertJson($nueva);
    $this->putJson('/api/configuracion', ['holguraCm' => 0] + $nueva)->assertUnprocessable();
});

it('CU-13 el nuevo plazo se aplica a los préstamos siguientes y se invalida la caché de capacidad', function () {
    $this->comoUsuario('bibliotecario');
    $this->prepararInventario();
    $this->registrarEjemplar('EJ-0001');
    $this->registrarLector();
    $this->getJson('/api/capacidad?alcance=total')->assertJson(['usadaCm' => 3.6]);

    $this->comoUsuario('admin');
    $this->putJson('/api/configuracion', ['diasPrestamo' => 7, 'diasRenovacion' => 7, 'maxRenovaciones' => 1, 'valorMultaDiaria' => 1000, 'holguraCm' => 1.0, 'grosorPorDefectoCm' => 2.5])->assertOk();

    $this->comoUsuario('bibliotecario');
    $this->fijarHoy('2026-11-01');
    $this->getJson('/api/capacidad?alcance=total')->assertJson(['usadaCm' => 4.2]);
    $this->postJson('/api/prestamos', ['identificacionLector' => '1020304050', 'codigoEjemplar' => 'EJ-0001'])
        ->assertJson(['fechaEntregaPrevista' => '2026-11-08']);
});
