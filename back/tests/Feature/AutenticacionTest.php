<?php

use App\Infraestructura\Persistencia\Modelos\UsuarioModelo;
use Database\Seeders\UsuariosSeeder;

it('responde el estado de la base de datos y de Redis', function () {
    $this->getJson('/api/salud')->assertOk()->assertJson(['estado' => 'ok', 'db' => 'ok', 'redis' => 'ok']);
});

it('inicia sesión y entrega un token con los permisos del rol', function () {
    $respuesta = $this->postJson('/api/auth/login', ['login' => 'director', 'clave' => UsuariosSeeder::CLAVE])
        ->assertOk()
        ->assertJsonPath('usuario.roles', ['Director'])
        ->assertJsonPath('usuario.permisos', ['CAPACIDAD_CONSULTAR', 'PRESTAMO_CONSULTAR']);

    $this->withToken($respuesta->json('token'))->getJson('/api/auth/yo')->assertOk()->assertJsonPath('login', 'director');
});

it('rechaza credenciales inválidas y limita a 5 intentos por minuto', function () {
    foreach (range(1, 5) as $intento) {
        $this->postJson('/api/auth/login', ['login' => 'admin', 'clave' => 'equivocada'])
            ->assertUnauthorized()->assertJson(['mensaje' => 'Credenciales inválidas.']);
    }
    $this->postJson('/api/auth/login', ['login' => 'admin', 'clave' => UsuariosSeeder::CLAVE])->assertStatus(429);
});

it('cierra sesión revocando el token', function () {
    $token = $this->postJson('/api/auth/login', ['login' => 'admin', 'clave' => UsuariosSeeder::CLAVE])->json('token');

    $this->withToken($token)->postJson('/api/auth/logout')->assertNoContent();
    expect(UsuarioModelo::where('login', 'admin')->first()->tokens()->count())->toBe(0);
});

it('responde 401 sin token', function () {
    $this->getJson('/api/capacidad?alcance=total')->assertUnauthorized()->assertExactJson(['mensaje' => 'No autenticado.']);
});

it('responde 403 sin detalles cuando el rol no tiene el permiso', function () {
    $this->comoUsuario('director');

    $this->postJson('/api/estanterias', ['codigo' => 'E-01', 'niveles' => [['numero' => 1, 'largoCm' => 90, 'altoCm' => 32]]])
        ->assertForbidden()->assertExactJson(['mensaje' => 'No autorizado.']);
    $this->getJson('/api/capacidad?alcance=total')->assertOk();
});
