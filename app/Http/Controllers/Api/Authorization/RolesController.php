<?php

namespace Vanguard\Http\Controllers\Api\Authorization;

use Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vanguard\Http\Controllers\Api\ApiController;
use Vanguard\Http\Requests\Role\CreateRoleRequest;
use Vanguard\Http\Requests\Role\RemoveRoleRequest;
use Vanguard\Http\Requests\Role\UpdateRoleRequest;
use Vanguard\Repositories\Role\RoleRepository;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Role;
use Vanguard\Transformers\RoleTransformer;

/**
 * Class RolesController
 * @package Vanguard\Http\Controllers\Api\Users
 */
class RolesController extends ApiController
{
    /**
     * @var RoleRepository
     */
    private $roles;

    public function __construct(RoleRepository $roles)
    {
        $this->roles = $roles;
        $this->middleware('auth');
        $this->middleware('permission:roles.manage');
    }

    /**
     * Get all system roles with users count for each role.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->respondWithCollection(
            $this->roles->getAllWithUsersCount(),
            new RoleTransformer
        );
    }

    /**
     * Create new role from the request.
     * @param CreateRoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateRoleRequest $request)
    {
        $role = $this->roles->create(
            $request->only(['name', 'display_name', 'description'])
        );

        return $this->respondWithItem($role, new RoleTransformer);
    }

    /**
     * Return info about specified role.
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Role $role)
    {
        return $this->respondWithItem($role, new RoleTransformer);
    }

    /**
     * Update specified role.
     * @param Role $role
     * @param UpdateRoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Role $role, UpdateRoleRequest $request)
    {
        $input = collect($request->all());

        $role = $this->roles->update(
            $role->id,
            $input->only(['name', 'display_name', 'description'])->toArray()
        );

        return $this->respondWithItem($role, new RoleTransformer);
    }

    /**
     * Remove specified role (if role is removable).
     * @param Role $role
     * @param UserRepository $users
     * @param RemoveRoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role, UserRepository $users, RemoveRoleRequest $request)
    {
        $userRole = $this->roles->findByName('User');

        $users->switchRolesForUsers($role->id, $userRole->id);

        $this->roles->delete($role->id);

        Cache::flush();

        return $this->respondWithSuccess();
    }
}
