<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Infrastructure\Persistence\Models\{
    PersonModel,
    RoleModel,
    UserModel
};

class UsersSeeder extends Seeder
{
    public const PASSWORD = 'secret';

    private const USERS = [
        ['login' => 'admin', 'identification' => '900000001', 'name' => 'Administrador del sistema', 'position' => 'Administrator', 'role' => 'Administrator'],
        ['login' => 'librarian', 'identification' => '900000002', 'name' => 'Bibliotecario de turno', 'position' => 'Librarian', 'role' => 'Librarian'],
        ['login' => 'director', 'identification' => '900000003', 'name' => 'Dirección de biblioteca', 'position' => 'Director', 'role' => 'Director'],
    ];

    public function run(): void
    {
        foreach (self::USERS as $u) {
            $person = PersonModel::firstOrCreate(
                ['type' => PersonModel::STAFF, 'identification' => $u['identification']],
                ['name' => $u['name'], 'position' => $u['position']],
            );
            $user = UserModel::firstOrCreate(
                ['login' => $u['login']],
                ['person_id' => $person->id, 'password' => Hash::make(self::PASSWORD), 'active' => true],
            );
            $user->roles()->syncWithoutDetaching([RoleModel::where('name', $u['role'])->value('id')]);
        }
    }
}
