<?php

namespace Tests\Setup;

use Vanguard\Permission;
use Vanguard\Role;

class RoleFactory
{
    protected $permissions = [];

    protected $removable = false;

    public function withPermissions($permissions)
    {
        $this->permissions = func_get_args();

        return $this;
    }

    public function removable()
    {
        $this->removable = true;

        return $this;
    }

    public function unremovable()
    {
        $this->removable = false;

        return $this;
    }

    public function create()
    {
        $role = factory(Role::class)->create([
            'removable' => $this->removable
        ]);

        foreach ($this->permissions as $name) {
            $role->attachPermission(Permission::where('name', $name)->first());
        }

        return $role;
    }
}
