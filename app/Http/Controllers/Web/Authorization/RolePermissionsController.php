<?php

namespace Vanguard\Http\Controllers\Web\Authorization;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Vanguard\Events\Role\PermissionsUpdated;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Repositories\Role\RoleRepository;

/**
 * Class RolePermissionsController
 * @package Vanguard\Http\Controllers
 */
class RolePermissionsController extends Controller
{
    /**
     * @var RoleRepository
     */
    private $roles;

    /**
     * RolePermissionsController constructor.
     * @param RoleRepository $roles
     */
    public function __construct(RoleRepository $roles)
    {
        $this->roles = $roles;
    }

    /**
     * Update permissions for each role.
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $roles = $request->get('roles');

        $allRoles = $this->roles->lists('id');

        foreach ($allRoles as $roleId) {
            $permissions = Arr::get($roles, $roleId, []);
            $this->roles->updatePermissions($roleId, $permissions);
        }

        event(new PermissionsUpdated);

        return redirect()->route('permissions.index')
            ->withSuccess(__('Permissions saved successfully.'));
    }
}
