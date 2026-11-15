<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\People\StaffMember;
use App\Domain\Repositories\UserRepository;
use App\Domain\Security\Permission;
use App\Domain\Security\Role;
use App\Domain\Security\User;
use App\Infrastructure\Persistence\Models\PersonModel;
use App\Infrastructure\Persistence\Models\RoleModel;
use App\Infrastructure\Persistence\Models\UserModel;

class EloquentUserRepository implements UserRepository
{
    public function all(): array
    {
        return UserModel::with(['person', 'roles'])->orderBy('login')->get()
            ->map(fn (UserModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findById(int $id): ?User
    {
        $model = UserModel::with(['person', 'roles'])->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function loginExists(string $login, ?int $exceptId = null): bool
    {
        return UserModel::where('login', $login)
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->exists();
    }

    public function staffMemberExists(string $identification): bool
    {
        return PersonModel::where('type', PersonModel::STAFF)->where('identification', $identification)->exists();
    }

    public function rolesByName(array $names): array
    {
        return RoleModel::whereIn('name', $names)->orderBy('name')->get()
            ->map(fn (RoleModel $r) => $this->roleToDomain($r))
            ->all();
    }

    public function save(User $user): void
    {
        $person = PersonModel::updateOrCreate(
            ['type' => PersonModel::STAFF, 'identification' => $user->person()->identification()],
            ['name' => $user->person()->getName(), 'position' => $user->person()->position()],
        );
        $data = $this->toModel($user) + ['person_id' => $person->id];
        $model = $user->id() === null
            ? UserModel::create($data)
            : tap(UserModel::findOrFail($user->id()))->update($data);
        $model->roles()->sync(RoleModel::whereIn('name', array_map(fn (Role $r) => $r->name(), $user->roles()))->pluck('id'));

        if (! $user->active()) {
            $model->tokens()->delete();   // CU-12: deactivating revokes access
        }
        $user->assignId($model->id);
    }

    private function toDomain(UserModel $m): User
    {
        return new User(
            $m->id, $m->login, $m->password, $m->active,
            new StaffMember($m->person->identification, $m->person->name, $m->person->position ?? 'Sin cargo'),
            $m->roles->map(fn (RoleModel $r) => $this->roleToDomain($r))->all(),
        );
    }

    private function toModel(User $u): array
    {
        return ['login' => $u->login(), 'password' => $u->passwordHash(), 'active' => $u->active()];
    }

    private function roleToDomain(RoleModel $r): Role
    {
        return new Role($r->name, array_map(fn (string $p) => Permission::from($p), $r->permissions()));
    }
}
