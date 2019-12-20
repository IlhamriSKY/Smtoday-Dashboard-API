<?php

namespace Tests\Feature\Http\Controllers\Api\Authorization;

use Facades\Tests\Setup\UserFactory;
use Tests\Feature\ApiTestCase;
use Vanguard\Permission;
use Vanguard\Transformers\PermissionTransformer;
use Vanguard\User;

class PermissionsControllerTest extends ApiTestCase
{
    /** @test */
    public function unauthenticated()
    {
        $this->getJson('/api/permissions')->assertStatus(401);
    }

    /** @test */
    public function get_users_without_permission()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/permissions')
            ->assertForbidden();
    }

    /** @test */
    public function get_permissions()
    {
        factory(Permission::class)->times(3)->create();

        $response = $this->actingAs($this->getUser(), 'api')
            ->getJson("/api/permissions")
            ->assertOk()
            ->assertJson(
                $this->transformCollection(Permission::all(), new PermissionTransformer)
            );

        // 7 default permissions + 3 newly created
        $this->assertCount(10, $response->original);
    }

    /** @test */
    public function get_permission()
    {
        $permission = factory(Permission::class)->create();

        $this->actingAs($this->getUser(), 'api')
            ->getJson("/api/permissions/{$permission->id}")
            ->assertOk()
            ->assertJson(
                (new PermissionTransformer)->transform($permission)
            );
    }

    /** @test */
    public function create_permission()
    {
        $data = [
            'name' => 'foo',
            'display_name' => 'Foo Permission',
            'description' => 'This is foo permission.'
        ];

        $this->actingAs($this->getUser(), 'api')
            ->postJson("/api/permissions", $data)
            ->assertOk()
            ->assertJson($data);


        $this->assertDatabaseHas('permissions', $data);
    }

    /** @test */
    public function create_permission_with_invalid_name()
    {
        $this->actingAs($this->getUser(), 'api')
            ->postJson("/api/permissions")
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $existingPermission = Permission::first();

        $this->postJson("/api/permissions", ['name' => $existingPermission->name])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->postJson("/api/permissions", ['name' => 'foo bar'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /** @test */
    public function partially_update_permission()
    {
        $this->getUser();

        $permission = factory(Permission::class)->create();

        $data = ['name' => 'foo'];
        $expected = $data + ['id' => $permission->id];

        $this->actingAs($this->getUser(), 'api')
            ->patchJson("/api/permissions/{$permission->id}", $data)
            ->assertJson($expected);

        $this->assertDatabaseHas('permissions', $expected);
    }

    /** @test */
    public function update_permission()
    {
        $permission = factory(Permission::class)->create();

        $data = [
            'name' => 'foo',
            'display_name' => 'Foo Role',
            'description' => 'This is foo role.'
        ];
        $expected = $data + ['id' => $permission->id];

        $this->actingAs($this->getUser(), 'api')
            ->patchJson("/api/permissions/{$permission->id}", $data)
            ->assertJson($expected);

        $this->assertDatabaseHas('permissions', $expected);
    }

    /** @test */
    public function remove_permission()
    {
        $permission = factory(Permission::class)->create(['removable' => true]);

        $this->actingAs($this->getUser(), 'api')
            ->deleteJson("/api/permissions/{$permission->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    /** @test */
    public function remove_non_removable_permission()
    {
        $permission = factory(Permission::class)->create(['removable' => false]);

        $this->actingAs($this->getUser(), 'api')
            ->deleteJson("/api/permissions/{$permission->id}")
            ->assertStatus(403);
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        return UserFactory::user()->withPermissions('permissions.manage')->create();
    }
}
