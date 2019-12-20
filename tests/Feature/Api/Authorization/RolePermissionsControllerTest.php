<?php

namespace Tests\Feature\Http\Controllers\Api\Authorization;

use Facades\Tests\Setup\UserFactory;
use Tests\Feature\ApiTestCase;
use Vanguard\Permission;
use Vanguard\Role;
use Vanguard\Transformers\PermissionTransformer;
use Vanguard\User;

class RolePermissionsControllerTest extends ApiTestCase
{
    /** @test */
    public function unauthenticated()
    {
        $role = factory(Role::class)->create();

        $this->getJson("/api/roles/{$role->id}/permissions")
            ->assertStatus(401);
    }

    /** @test */
    public function get_settings_without_permission()
    {
        $role = factory(Role::class)->create();

        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/roles/{$role->id}/permissions")
            ->assertStatus(403);
    }

    /** @test */
    public function get_role_permissions()
    {
        $role = factory(Role::class)->create();
        $permission = factory(Permission::class)->create();

        $role->attachPermission($permission);

        $this->actingAs($this->getUser(), 'api')
            ->getJson("/api/roles/{$role->id}/permissions")
            ->assertOk()
            ->assertJson(
                $this->transformCollection(collect([$permission]), new PermissionTransformer)
            );
    }

    /** @test */
    public function update_role_permissions()
    {
        $role = factory(Role::class)->create();
        $permissions1 = factory(Permission::class)->times(2)->create();
        $permissions2 = factory(Permission::class)->times(3)->create();

        $role->attachPermissions($permissions1);

        $this->actingAs($this->getUser(), 'api')
            ->putJson("/api/roles/{$role->id}/permissions", [
                'permissions' => $permissions2->pluck('id')
            ])
            ->assertOk()
            ->assertJson(
                $this->transformCollection($permissions2, new PermissionTransformer)
            );

        foreach ($permissions2 as $perm) {
            $this->assertDatabaseHas('permission_role', [
                'permission_id' => $perm->id,
                'role_id' => $role->id
            ]);
        }
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        return UserFactory::user()->withPermissions('permissions.manage')->create();
    }
}
