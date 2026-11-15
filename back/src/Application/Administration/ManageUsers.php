<?php

declare(strict_types=1);

namespace App\Application\Administration;

use App\Application\Administration\Dto\ManageUsersInput;
use App\Application\Administration\Dto\UserAction;
use App\Application\Administration\Dto\UserOutput;
use App\Application\Ports\PasswordHasher;
use App\Application\Ports\PermissionCache;
use App\Application\Ports\Transaction;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\People\StaffMember;
use App\Domain\Repositories\UserRepository;
use App\Domain\Security\Role;
use App\Domain\Security\User;

/** CU-12 Manage users: create, edit (including deactivate) and assign roles. */
final class ManageUsers
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hash,
        private PermissionCache $permissionCache,
        private Transaction $transaction,
    ) {}

    public function execute(ManageUsersInput $e): UserOutput
    {
        $user = match ($e->action) {
            UserAction::CREATE => $this->create($e),
            UserAction::EDIT => $this->edit($e),
            UserAction::ASSIGN_ROLES => $this->assignRoles($e),
        };

        $this->transaction->execute(fn () => $this->users->save($user));
        $this->permissionCache->invalidate($user->id());

        return UserOutput::from($user);
    }

    private function create(ManageUsersInput $e): User
    {
        $login = $e->login ?? '';
        if ($this->users->loginExists($login)) {
            throw new BusinessRuleException('RF-12', "El login {$login} ya está en uso.");
        }
        if ($this->users->staffMemberExists($e->identification ?? '')) {
            throw new BusinessRuleException('RF-12', 'Ya existe un funcionario con esa identificación.');
        }

        return new User(
            null,
            $login,
            $this->hash->hash($e->password ?? ''),
            true,
            new StaffMember($e->identification ?? '', $e->name ?? '', $e->position ?? ''),
            $this->roles($e->roles ?? []),
        );
    }

    private function edit(ManageUsersInput $e): User
    {
        $user = $this->find($e);
        if ($e->login !== null) {
            if ($this->users->loginExists($e->login, $user->id())) {
                throw new BusinessRuleException('RF-12', "El login {$e->login} ya está en uso.");
            }
            $user->changeLogin($e->login);
        }
        if ($e->password !== null) {
            $user->changePassword($this->hash->hash($e->password));
        }
        if ($e->name !== null || $e->position !== null) {
            $person = $user->person();
            $person->updateDetails($e->name ?? $person->getName(), $e->position ?? $person->position());
        }
        if ($e->active === true) {
            $user->activate();
        }
        if ($e->active === false) {
            if ($user->id() === $e->performedBy) {
                throw new BusinessRuleException('RF-12', 'No puede desactivar su propio usuario.');
            }
            $user->deactivate();
        }

        return $user;
    }

    private function assignRoles(ManageUsersInput $e): User
    {
        $user = $this->find($e);
        $user->assignRoles($this->roles($e->roles ?? []));

        return $user;
    }

    private function find(ManageUsersInput $e): User
    {
        return $this->users->findById($e->userId ?? 0)
            ?? throw new ResourceNotFoundException("No existe el usuario {$e->userId}.");
    }

    /** @param string[] $names @return Role[] */
    private function roles(array $names): array
    {
        $names = array_values(array_unique($names));
        if ($names === []) {
            throw new BusinessRuleException('RF-12', 'El usuario debe tener al menos un rol.');
        }
        $roles = $this->users->rolesByName($names);
        $missing = array_diff($names, array_map(fn (Role $r) => $r->name(), $roles));
        if ($missing !== []) {
            throw new BusinessRuleException('RF-12', 'Roles inexistentes: '.implode(', ', $missing).'.');
        }

        return $roles;
    }
}
