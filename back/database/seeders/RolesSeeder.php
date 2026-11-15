<?php

namespace Database\Seeders;

use App\Domain\Security\Permission;
use App\Infrastructure\Persistence\Models\RoleModel;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public const ROLES = [
        'Administrator' => [Permission::USER_MANAGE, Permission::SETTINGS_UPDATE],
        'Librarian' => [
            Permission::SHELF_MANAGE, Permission::BOOK_REGISTER, Permission::COPY_MANAGE,
            Permission::READER_REGISTER, Permission::LOAN_MANAGE, Permission::LOAN_VIEW,
            Permission::FINE_REGISTER_PAYMENT, Permission::CAPACITY_VIEW,
        ],
        'Director' => [Permission::CAPACITY_VIEW, Permission::LOAN_VIEW],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $name => $permissions) {
            RoleModel::firstOrCreate(['name' => $name])
                ->syncPermissions(array_map(fn (Permission $p) => $p->value, $permissions));
        }
    }
}
