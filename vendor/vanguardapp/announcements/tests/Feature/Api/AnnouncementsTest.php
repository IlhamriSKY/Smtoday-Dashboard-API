<?php

namespace Vanguard\Announcements\Tests\Feature\Api;

use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Mail;
use Tests\Feature\ApiTestCase;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Mail\AnnouncementEmail;
use Vanguard\Announcements\Transformers\AnnouncementTransformer;

class AnnouncementsTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'AnnouncementsDatabaseSeeder']);
    }

    /** @test */
    public function guests_cannot_paginate_announcements()
    {
        $this->getJson('/api/announcements')->assertStatus(401);
    }

    /** @test */
    public function any_authenticated_users_can_paginate_announcements()
    {
        $user = UserFactory::user()->create();
        $announcements = factory(Announcement::class)->times(11)->create();

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/announcements")
            ->assertOk();

        $transformed = $this->transformCollection($announcements->take(10), new AnnouncementTransformer);

        $this->assertEquals($response->original['data'], $transformed);
        $this->assertEquals($response->original['meta'], [
            'current_page' => 1,
            'from' => 1,
            'to' => 10,
            'last_page' => 2,
            'prev_page_url' => null,
            'next_page_url' => url("api/announcements?page=2"),
            'total' => 11,
            'per_page' => 10
        ]);
    }

    /** @test */
    public function paginate_announcements_with_more_records_per_page_than_allowed()
    {
        $this->actingAs($this->validUser(), 'api')
            ->getJson("/api/announcements?per_page=140")
            ->assertStatus(422);
    }

    /** @test */
    public function guests_cannot_create_announcements()
    {
        $this->postJson("/api/announcements", $this->validParams())
            ->assertUnauthorized();

        $this->assertEquals(0, Announcement::count());
    }

    /** @test */
    public function users_without_appropriate_permission_cannot_create_announcements()
    {
        $user = UserFactory::user()->create();

        $this->actingAs($user, 'api')
            ->postJson("/api/announcements", $this->validParams())
            ->assertForbidden();

        $this->assertEquals(0, Announcement::count());
    }

    /** @test */
    public function users_with_appropriate_permission_can_create_announcements()
    {
        $user = $this->validUser();
        $data = $this->validParams();

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/announcements", $data)
            ->assertStatus(201);

        $announcement = Announcement::first();

        $response->assertExactJson((new AnnouncementTransformer)->transform($announcement));

        $this->assertEquals($user->id, $announcement->user_id);
        $this->assertEquals($data['title'], $announcement->title);
        $this->assertEquals($data['body'], $announcement->body);
    }

    /** @test */
    public function an_email_notification_can_be_triggered_when_an_announcement_is_created()
    {
        Mail::fake();

        $data = $this->validParams(['email_notification' => true]);

        $this->actingAs($this->validUser(), 'api')
            ->postJson('/api/announcements', $data)
            ->assertStatus(201);

        $announcement = Announcement::first();

        Mail::assertQueued(AnnouncementEmail::class, function ($mail) use ($announcement) {
            return $mail->announcement->id === $announcement->id;
        });
    }

    /** @test */
    public function title_field_is_required_when_creating_an_announcement()
    {
        $data = $this->validParams(['title' => '']);

        $this->actingAs($this->validUser(), 'api')
            ->postJson("/api/announcements", $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');

        $this->assertEquals(0, Announcement::count());
    }

    /** @test */
    public function body_field_is_required_when_creating_an_announcement()
    {
        $data = $this->validParams(['body' => '']);

        $this->actingAs($this->validUser(), 'api')
            ->postJson("/api/announcements", $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');

        $this->assertEquals(0, Announcement::count());
    }

    /** @test */
    public function guests_cannot_view_an_announcement()
    {
        $announcement = factory(Announcement::class)->create();

        $this->getJson("/api/announcements/{$announcement->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function authenticated_users_can_view_any_announcement()
    {
        $user = UserFactory::user()->create();
        $announcement = factory(Announcement::class)->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/announcements/{$announcement->id}")
            ->assertOk()
            ->assertExactJson((new AnnouncementTransformer)->transform($announcement));
    }

    /** @test */
    public function guests_cannot_update_an_announcement()
    {
        $announcement = factory(Announcement::class)->create();

        $this->putJson("/api/announcements/{$announcement->id}", $this->validParams())
            ->assertUnauthorized();
    }

    /** @test */
    public function users_without_approprite_permission_cannot_update_an_announcement()
    {
        $user = UserFactory::user()->create();
        $announcement = factory(Announcement::class)->create();

        $this->actingAs($user, 'api')
            ->putJson("/api/announcements/{$announcement->id}", $this->validParams())
            ->assertForbidden();
    }

    /** @test */
    public function users_with_approprite_permission_can_update_an_announcement()
    {
        $user = $this->validUser();
        $announcement = factory(Announcement::class)->create();

        $this->actingAs($user, 'api')
            ->putJson("/api/announcements/{$announcement->id}", $this->validParams())
            ->assertOk()
            ->assertExactJson((new AnnouncementTransformer)->transform($announcement->fresh()));
    }

    /** @test */
    public function title_field_is_required_when_updating_an_announcement()
    {
        $data = $this->validParams(['title' => '']);
        $announcement = factory(Announcement::class)->create();

        $this->actingAs($this->validUser(), 'api')
            ->putJson("/api/announcements/{$announcement->id}", $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    /** @test */
    public function body_field_is_required_when_updating_an_announcement()
    {
        $data = $this->validParams(['body' => '']);
        $announcement = factory(Announcement::class)->create();

        $this->actingAs($this->validUser(), 'api')
            ->putJson("/api/announcements/{$announcement->id}", $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function guests_cannot_delete_an_announcement()
    {
        $announcement = factory(Announcement::class)->create();

        $this->deleteJson("/api/announcements/{$announcement->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function users_without_appropriate_permission_cannot_delete_an_announcement()
    {
        $user = UserFactory::user()->create();
        $announcement = factory(Announcement::class)->create();

        $this->actingAs($user, 'api')
            ->deleteJson("/api/announcements/{$announcement->id}")
            ->assertForbidden();
    }

    /** @test */
    public function users_with_appropriate_permission_can_delete_an_announcement()
    {
        $announcement = factory(Announcement::class)->create();

        $this->actingAs($this->validUser(), 'api')
            ->deleteJson("/api/announcements/{$announcement->id}")
            ->assertOk();

        $this->assertNull($announcement->fresh());
    }

    /** @test */
    public function user_announcements_can_be_marked_as_read()
    {
        $user = UserFactory::user()->create([
            'announcements_last_read_at' => null
        ]);

        Carbon::setTestNow(now());

        $this->actingAs($user, 'api')
            ->post("/api/announcements/read");

        $this->assertEquals(
            now()->format('Y-m-d H:i:s'),
            $user->fresh()->announcements_last_read_at
        );
    }

    /**
     * @return mixed
     */
    private function validUser()
    {
        return UserFactory::user()->withPermissions('announcements.manage')->create();
    }

    /**
     * @param array $overrides
     * @return array
     */
    private function validParams(array $overrides = [])
    {
        return array_merge([
            'title' => 'Foo Announcement',
            'body' => 'This is the announcement body.',
            'email_notification' => false
        ], $overrides);
    }
}
