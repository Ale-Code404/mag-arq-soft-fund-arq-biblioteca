<?php

use App\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\UsersSeeder;

beforeEach(fn () => $this->actingAsUser('admin'));

$newUser = [
    'login' => 'auxiliar', 'password' => 'Segura123*', 'identification' => '900000010',
    'name' => 'Auxiliar de biblioteca', 'position' => 'Auxiliar', 'roles' => ['Director'],
];

it('CU-12 creates a user who can log in', function () use ($newUser) {
    $this->postJson('/api/users', $newUser)->assertCreated()->assertJson(['login' => 'auxiliar', 'active' => true, 'roles' => ['Director']]);

    $this->postJson('/api/auth/login', ['login' => 'auxiliar', 'password' => 'Segura123*'])->assertOk();
    $this->postJson('/api/users', $newUser)->assertUnprocessable()->assertJson(['rule' => 'RF-12']);
    $this->postJson('/api/users', ['login' => 'other', 'identification' => '1', 'roles' => ['Nonexistent']] + $newUser)
        ->assertUnprocessable()->assertJson(['message' => 'Roles inexistentes: Nonexistent.']);
});

it('CU-12 assigning roles changes permissions immediately (cache invalidated)', function () {
    $director = UserModel::where('login', 'director')->first();
    $this->actingAsUser('director');
    $this->postJson('/api/readers', ['identification' => '1', 'name' => 'Ana'])->assertForbidden();

    $this->actingAsUser('admin');
    $this->putJson("/api/users/{$director->id}/roles", ['roles' => ['Director', 'Librarian']])->assertOk();

    $this->actingAsUser('director');
    $this->postJson('/api/readers', ['identification' => '1', 'name' => 'Ana'])->assertCreated();
});

it('CU-12 edits and deactivates a user revoking their tokens', function () {
    $director = UserModel::where('login', 'director')->first();
    $token = $this->postJson('/api/auth/login', ['login' => 'director', 'password' => UsersSeeder::PASSWORD])->json('token');

    $this->putJson("/api/users/{$director->id}", ['position' => 'Directora', 'active' => false])->assertOk()
        ->assertJson(['position' => 'Directora', 'active' => false]);

    expect($director->tokens()->count())->toBe(0);
    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson('/api/auth/me')->assertUnauthorized();
    $this->postJson('/api/auth/login', ['login' => 'director', 'password' => UsersSeeder::PASSWORD])->assertUnauthorized();
});

it('CU-12 prevents the administrator from deactivating themselves', function () {
    $this->putJson('/api/users/'.auth()->id(), ['active' => false])->assertUnprocessable()->assertJson(['rule' => 'RF-12']);
});

it('CU-13 reads and updates the current settings', function () {
    $this->getJson('/api/settings')->assertOk()->assertExactJson([
        'loanDays' => 15, 'renewalDays' => 15, 'maxRenewals' => 1,
        'dailyFineValue' => 1000, 'clearanceCm' => 0.4, 'defaultThicknessCm' => 2.5,
    ]);

    $newSettings = ['loanDays' => 10, 'renewalDays' => 7, 'maxRenewals' => 2, 'dailyFineValue' => 1500, 'clearanceCm' => 0.5, 'defaultThicknessCm' => 3.0];
    $this->putJson('/api/settings', $newSettings)->assertOk()->assertJson($newSettings);
    $this->getJson('/api/settings')->assertJson($newSettings);
    $this->putJson('/api/settings', ['clearanceCm' => 0] + $newSettings)->assertUnprocessable();
});

it('CU-13 the new term applies to later loans and the capacity cache is invalidated', function () {
    $this->actingAsUser('librarian');
    $this->prepareInventory();
    $this->registerCopy('EJ-0001');
    $this->registerReader();
    $this->getJson('/api/capacity?scope=total')->assertJson(['usedCm' => 3.6]);

    $this->actingAsUser('admin');
    $this->putJson('/api/settings', ['loanDays' => 7, 'renewalDays' => 7, 'maxRenewals' => 1, 'dailyFineValue' => 1000, 'clearanceCm' => 1.0, 'defaultThicknessCm' => 2.5])->assertOk();

    $this->actingAsUser('librarian');
    $this->setToday('2026-11-01');
    $this->getJson('/api/capacity?scope=total')->assertJson(['usedCm' => 4.2]);
    $this->postJson('/api/loans', ['readerIdentification' => '1020304050', 'copyCode' => 'EJ-0001'])
        ->assertJson(['dueOn' => '2026-11-08']);
});
