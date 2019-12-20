<?php

namespace Tests\Feature\Http\Controllers\Api\Users;

use Facades\Tests\Setup\UserFactory;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\Feature\ApiTestCase;
use Vanguard\Events\User\UpdatedByAdmin;
use Vanguard\User;

class AvatarControllerTest extends ApiTestCase
{
    /** @test */
    public function upload_user_avatar_unauthenticated()
    {
        $user = factory(User::class)->create();

        $server = $this->transformHeadersToServerVars([
            'Accept' => 'application/json',
            'Content-Type' => 'image/jpeg'
        ]);

        $this->call('PUT', "/api/users/{$user->id}/avatar", [], [], [], $server)
            ->assertStatus(401);
    }

    /** @test */
    public function upload_avatar_without_permission()
    {
        $user = factory(User::class)->create();

        $server = $this->transformHeadersToServerVars([
            'Accept' => 'application/json',
            'Content-Type' => 'image/jpeg'
        ]);

        $this->actingAs($user, 'api')
            ->call('PUT', "/api/users/{$user->id}/avatar", [], [], [], $server)
            ->assertForbidden();
    }

    /** @test */
    public function upload_avatar_image()
    {
        $this->expectsEvents(UpdatedByAdmin::class);

        Storage::fake('public');

        $user = UserFactory::withPermissions('users.manage')->create();

        $file = UploadedFile::fake()->image('avatar.png', 500, 500);

        $fileContent = file_get_contents($file->getRealPath());

        $server = $this->transformHeadersToServerVars([
            'Accept' => 'application/json',
            'Content-Type' => 'image/jpeg'
        ]);

        $response = $this->actingAs($user, 'api')
            ->call('PUT', "/api/users/{$user->id}/avatar", [], [], [], $server, $fileContent)
            ->assertOk();

        $this->assertNotNull($response->original['avatar']);

        $uploadedFile = str_replace(url(''), '', $response->original['avatar']);
        $uploadedFile = ltrim($uploadedFile, "/");

        Storage::disk('public')->assertExists($uploadedFile);

        list($width, $height) = getimagesizefromstring(
            Storage::disk('public')->get($uploadedFile)
        );

        $this->assertEquals(160, $width);
        $this->assertEquals(160, $height);
    }

    /** @test */
    public function upload_invalid_image()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        Storage::fake('public');

        $file = UploadedFile::fake()->create('avatar.png', 500);

        $fileContent = file_get_contents($file->getRealPath());

        $server = $this->transformHeadersToServerVars([
            'Accept' => 'application/json',
            'Content-Type' => 'image/jpeg'
        ]);

        $this->actingAs($user, 'api')
            ->call('PUT', "/api/users/{$user->id}/avatar", [], [], [], $server, $fileContent)
            ->assertStatus(422)
            ->assertJsonFragment([
                'file' => [
                    trans('validation.image', ['attribute' => 'file'])
                ]
            ]);
    }

    /** @test */
    public function update_avatar_from_external_source()
    {
        $this->expectsEvents(UpdatedByAdmin::class);

        $user = UserFactory::withPermissions('users.manage')->create();

        $url = 'http://google.com';

        $this->actingAs($user, 'api')
            ->putJson("/api/users/{$user->id}/avatar/external", ['url' => $url])
            ->assertOk()
            ->assertJson(['avatar' => $url]);
    }

    /** @test */
    public function update_avatar_with_invalid_external_source()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        $this->actingAs($user, 'api')
            ->putJson("/api/users/{$user->id}/avatar/external", ['url' => 'foo'])
            ->assertStatus(422);
    }

    /** @test */
    public function delete_user_avatar()
    {
        $this->expectsEvents(UpdatedByAdmin::class);

        $user = UserFactory::withPermissions('users.manage')->create();

        $user->forceFill(['avatar' => 'http://google.com'])->save();

        $this->actingAs($user, 'api')
            ->deleteJson("api/users/{$user->id}/avatar")
            ->assertOk()
            ->assertJson([
                'avatar' => url('assets/img/profile.png') // default profile image
            ]);
    }
}
