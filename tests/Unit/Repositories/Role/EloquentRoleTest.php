<?php

namespace Tests\Unit\Repositories\Role;

use Facades\Tests\Setup\RoleFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Facades\Tests\Setup\UserFactory;
use Tests\TestCase;
use Vanguard\Events\Role\Created;
use Vanguard\Repositories\Role\EloquentRole;
use Vanguard\Role;

class EloquentRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var EloquentRole
     */
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(EloquentRole::class);
    }

    /** @test */
    public function all()
    {
        $roles = factory(Role::class)->times(4)->create();

        $this->assertEquals(
            $roles->toArray(),
            $this->repo->all()->toArray()
        );
    }

    /** @test */
    public function get_all_with_users_count()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::create();
        $roleC = RoleFactory::create();

        UserFactory::role($roleA)->create();
        UserFactory::role($roleA)->create();
        UserFactory::role($roleB)->create();

        $roleA->users_count = 2;
        $roleB->users_count = 1;
        $roleC->users_count = 0;

        $this->assertEquals(
            [$roleA->toArray(), $roleB->toArray(), $roleC->toArray()],
            $this->repo->getAllWithUsersCount()->toArray()
        );
    }

    /** @test */
    public function create()
    {
        $this->expectsEvents(Created::class);

        $data = ['name' => 'foo', 'display_name' => 'Foo'];
        $role = $this->repo->create($data);

        $this->assertDatabaseHas('roles', $data + ['id' => $role->id]);
    }

    /** @test */
    public function update()
    {
        $this->expectsEvents(\Vanguard\Events\Role\Updated::class);

        $role = factory(Role::class)->create();

        $data = ['name' => 'foo', 'display_name' => 'Foo'];

        $this->repo->update($role->id, $data);

        $this->assertDatabaseHas('roles', $data + ['id' => $role->id]);
    }

    /** @test */
    public function delete_role()
    {
        $this->expectsEvents(\Vanguard\Events\Role\Deleted::class);

        $role = factory(Role::class)->create();

        $this->repo->delete($role->id);

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    /** @test */
    public function updatePermissions()
    {
        $role = factory(Role::class)->create();
        $permissions = factory(\Vanguard\Permission::class)->times(2)->create();

        $this->repo->updatePermissions($role->id, $permissions->pluck('id')->toArray());

        $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $permissions[0]->id]);
        $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $permissions[1]->id]);
    }

    /** @test */
    public function lists()
    {
        $roles = factory(Role::class)->times(4)->create();
        $roles = $roles->pluck('name', 'id');

        $this->assertEquals($roles->toArray(), $this->repo->lists()->toArray());
    }
}
