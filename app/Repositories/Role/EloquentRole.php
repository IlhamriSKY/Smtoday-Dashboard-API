<?php

namespace Vanguard\Repositories\Role;

use Vanguard\Events\Role\Created;
use Vanguard\Events\Role\Deleted;
use Vanguard\Events\Role\Updated;
use Vanguard\Role;

class EloquentRole implements RoleRepository
{
    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return Role::all();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllWithUsersCount()
    {
        return Role::withCount('users')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return Role::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $role = Role::create($data);

        event(new Created($role));

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        $role = $this->find($id);

        $role->update($data);

        event(new Updated($role));

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $role = $this->find($id);

        event(new Deleted($role));

        return $role->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function updatePermissions($roleId, array $permissions)
    {
        $role = $this->find($roleId);

        $role->syncPermissions($permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function lists($column = 'name', $key = 'id')
    {
        return Role::pluck($column, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function findByName($name)
    {
        return Role::where('name', $name)->first();
    }
}
