<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Administration\Dto\ManageUsersInput;
use App\Application\Administration\Dto\UserAction;
use App\Application\Administration\ManageUsers;
use App\Infrastructure\Http\Requests\AssignRolesRequest;
use App\Infrastructure\Http\Requests\CreateUserRequest;
use App\Infrastructure\Http\Requests\EditUserRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function store(CreateUserRequest $request, ManageUsers $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new ManageUsersInput(
            action: UserAction::CREATE,
            performedBy: $this->userId($request),
            login: $request->input('login'),
            password: $request->input('password'),
            identification: $request->input('identification'),
            name: $request->input('name'),
            position: $request->input('position'),
            roles: $request->input('roles'),
        )), 201);
    }

    public function update(int $id, EditUserRequest $request, ManageUsers $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new ManageUsersInput(
            action: UserAction::EDIT,
            performedBy: $this->userId($request),
            userId: $id,
            login: $request->input('login'),
            password: $request->input('password'),
            name: $request->input('name'),
            position: $request->input('position'),
            active: $request->has('active') ? $request->boolean('active') : null,
        )));
    }

    public function roles(int $id, AssignRolesRequest $request, ManageUsers $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new ManageUsersInput(
            action: UserAction::ASSIGN_ROLES,
            performedBy: $this->userId($request),
            userId: $id,
            roles: $request->input('roles'),
        )));
    }
}
