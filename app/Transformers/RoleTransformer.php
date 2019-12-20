<?php

namespace Vanguard\Transformers;

use League\Fractal\TransformerAbstract;
use Vanguard\Role;

class RoleTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['permissions'];

    public function transform(Role $role)
    {
        return [
            'id' => (int) $role->id,
            'name' => $role->name,
            'display_name' => $role->display_name,
            'description' => $role->description,
            'removable' => (boolean) $role->removable,
            'users_count' => is_null($role->users_count) ? null : (int) $role->users_count,
            'updated_at' => (string) $role->updated_at,
            'created_at' => (string) $role->created_at
        ];
    }

    public function includePermissions(Role $role)
    {
        return $this->collection(
            $role->cachedPermissions(),
            new PermissionTransformer
        );
    }
}
