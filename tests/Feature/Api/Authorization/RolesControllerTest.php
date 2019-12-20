<?php

namespace Tests\Feature\Http\Controllers\Api\Authorization;

use Carbon\Carbon;
use Facades\Tests\Setup\RoleFactory;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Support\Collection;
use Tests\Feature\ApiTestCase;
use Tests\Feature\FunctionalOldTestCase;
use Vanguard\Country;
use Vanguard\Role;
use Vanguard\Services\Logging\UserActivity\Activity;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\Transformers\ActivityTransformer;
use Vanguard\Transformers\RoleTransformer;
use Vanguard\Transformers\SessionTransformer;
use Vanguard\Transformers\UserTransformer;
use Vanguard\User;

class RolesControllerTest extends ApiTestCase
{
    /** @test */
    public function unauthenticated()
    {
        $this->getJson('/api/roles')
            ->assertStatus(401);
    }

    /** @test */
    public function get_settings_without_permission()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/roles')
            ->assertStatus(403);
    }

    /** @test */
    public function get_roles()
    {
        factory(Role::class)->times(4)->create();

        $roles = Role::withCount('users')->get();

        $response = $this->actingAs($this->getUser(), 'api')
            ->getJson("/api/roles")
            ->assertOk()
            ->assertJson(
                $this->transformCollection($roles, new RoleTransformer)
            );

        $this->assertCount(7, $response->original);
    }

    /** @test */
    public function get_role()
    {
        $userRole = Role::whereName('User')->first();

        $this->actingAs($this->getUser(), 'api')
            ->getJson("/api/roles/{$userRole->id}")
            ->assertOk()
            ->assertJson(
                (new RoleTransformer)->transform($userRole)
            );
    }

    /** @test */
    public function create_role()
    {
        $this->getUser();

        $data = [
            'name' => 'foo',
            'display_name' => 'Foo Role',
            'description' => 'This is foo role.'
        ];

        $this->actingAs($this->getUser(), 'api')
            ->postJson("/api/roles", $data)
            ->assertOk()
            ->assertJson($data);

        $this->assertDatabaseHas('roles', $data);
    }

    /** @test */
    public function create_role_with_invalid_name()
    {
        $this->be($this->getUser(), 'api');

        $this->postJson("/api/roles")
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->postJson("/api/roles", ['name' => 'User'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->postJson("/api/roles", ['name' => 'foo bar'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /** @test */
    public function update_role()
    {
        $user = $this->getUser();

        $data = ['name' => 'foo'];
        $expected = $data + ['id' => $user->role_id];

        $this->actingAs($user, 'api')
            ->patchJson("/api/roles/{$user->role_id}", $data)
            ->assertOk()
            ->assertJson($expected);

        $this->assertDatabaseHas('roles', $expected);
    }

    /** @test */
    public function partially_update_role()
    {
        $user = $this->getUser();

        $data = [
            'name' => 'foo',
            'display_name' => 'Foo Role',
            'description' => 'This is foo role.'
        ];
        $expected = $data + ['id' => $user->role_id];

        $this->actingAs($user, 'api')
            ->patchJson("/api/roles/{$user->role_id}", $data)
            ->assertOk()
            ->assertJson($expected);

        $this->assertDatabaseHas('roles', $expected);
    }

    /** @test */
    public function remove_role()
    {
        $userRole = Role::whereName('User')->first();
        $role = RoleFactory::removable()->withPermissions('roles.manage')->create();
        $user = UserFactory::role($role)->create();

        $this->actingAs($user, 'api')
            ->deleteJson("/api/roles/{$role->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertEquals($userRole->id, $user->fresh()->role_id);
    }

    /** @test */
    public function remove_non_removable_role()
    {
        $role = RoleFactory::withPermissions('roles.manage')->create();

        $this->actingAs($this->getUser(), 'api')
            ->deleteJson("/api/roles/{$role->id}")
            ->assertForbidden();
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        return UserFactory::user()->withPermissions('roles.manage')->create();
    }
}
