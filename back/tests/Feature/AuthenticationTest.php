<?php

use App\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\UsersSeeder;

it('reports database and Redis status', function () {
    $this->getJson('/api/health')->assertOk()->assertJson(['status' => 'ok', 'db' => 'ok', 'redis' => 'ok']);
});

it('logs in and returns a token with the role permissions', function () {
    $response = $this->postJson('/api/auth/login', ['login' => 'director', 'password' => UsersSeeder::PASSWORD])
        ->assertOk()
        ->assertJsonPath('user.roles', ['Director'])
        ->assertJsonPath('user.permissions', ['CAPACITY_VIEW', 'LOAN_VIEW']);

    $this->withToken($response->json('token'))->getJson('/api/auth/me')->assertOk()->assertJsonPath('login', 'director');
});

it('rejects invalid credentials and limits to 5 attempts per minute', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/auth/login', ['login' => 'admin', 'password' => 'wrong'])
            ->assertUnauthorized()->assertJson(['message' => 'Credenciales inválidas.']);
    }
    $this->postJson('/api/auth/login', ['login' => 'admin', 'password' => UsersSeeder::PASSWORD])->assertStatus(429);
});

it('logs out revoking the token', function () {
    $token = $this->postJson('/api/auth/login', ['login' => 'admin', 'password' => UsersSeeder::PASSWORD])->json('token');

    $this->withToken($token)->postJson('/api/auth/logout')->assertNoContent();
    expect(UserModel::where('login', 'admin')->first()->tokens()->count())->toBe(0);
});

it('responds 401 without a token', function () {
    $this->getJson('/api/capacity?scope=total')->assertUnauthorized()->assertExactJson(['message' => 'No autenticado.']);
});

it('responds 403 without details when the role lacks the permission', function () {
    $this->actingAsUser('director');

    $this->postJson('/api/shelves', ['code' => 'E-01', 'levels' => [['number' => 1, 'lengthCm' => 90, 'heightCm' => 32]]])
        ->assertForbidden()->assertExactJson(['message' => 'No autorizado.']);
    $this->getJson('/api/capacity?scope=total')->assertOk();
});
