<?php

namespace Vanguard\Announcements\Tests\Feature\Web;

use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Support\Arr;
use Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Mail\AnnouncementEmail;

class AnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolesSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
        $this->artisan('db:seed', ['--class' => 'CountriesSeeder']);
        $this->artisan('db:seed', ['--class' => 'AnnouncementsDatabaseSeeder']);
    }

    /** @test */
    public function guests_cannot_view_announcement_index_page()
    {
        $this->get('/announcements')->assertRedirect('login');
    }

    /** @test */
    public function users_without_appropriate_permission_cannot_view_announcement_list()
    {
        $user = UserFactory::user()->create();

        $this->actingAs($user)->get('/announcements')->assertForbidden();
    }

    /** @test */
    public function users_with_appropriate_permission_can_view_the_announcement_list()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        factory(Announcement::class)->create([
            'user_id' => $user->id,
            'title' => 'Foo Announcement'
        ]);

        $response = $this->actingAs($user)->get('/announcements')->assertOk();
        $response->assertSee('Foo Announcement');
    }

    /** @test */
    public function guests_cannot_create_announcements()
    {
        $this->post('/announcements', $this->validParams())->assertRedirect('login');

        $this->assertEquals(0, Announcement::count());
    }

    /** @test */
    public function users_without_appropriate_permission_cannot_create_announcements()
    {
        $user = UserFactory::user()->create();

        $this->actingAs($user)->post('/announcements', $this->validParams())->assertForbidden();

        $this->assertEquals(0, Announcement::count());
    }

    /** @test */
    public function users_with_appropriate_permission_can_create_announcements()
    {
        Mail::fake();

        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $data = $this->validParams();

        $this->actingAs($user)->post('/announcements', $data)
            ->assertRedirect('/announcements');

        $announcement = Announcement::first();

        $this->assertEquals($data['title'], $announcement->title);
        $this->assertEquals($data['body'], $announcement->body);

        Mail::assertNothingSent();
    }

    /** @test */
    public function title_field_is_required_when_creating_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $data = $this->validParams(['title' => '']);

        $this->actingAs($user)
            ->from('/announcements/create')
            ->post('/announcements', $data)
            ->assertRedirect('/announcements/create')
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function body_field_is_required_when_creating_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $data = $this->validParams(['body' => '']);

        $this->actingAs($user)
            ->from('/announcements/create')
            ->post('/announcements', $data)
            ->assertRedirect('/announcements/create')
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function an_email_notification_can_be_triggered_when_an_announcement_is_created()
    {
        Mail::fake();

        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $data = $this->validParams(['email_notification' => '1']);

        $this->actingAs($user)
            ->from('/announcements/create')
            ->post('/announcements', $data)
            ->assertRedirect('/announcements');

        $announcement = Announcement::first();

        Mail::assertQueued(AnnouncementEmail::class, function ($mail) use ($announcement) {
            return $mail->announcement->id === $announcement->id;
        });
    }

    /** @test */
    public function guests_cannot_view_an_announcement()
    {
        $announcement = factory(Announcement::class)->create();

        $this->get('/announcements/' . $announcement->id)
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_users_can_see_an_announcement()
    {
        $this->withoutExceptionHandling();

        $user = UserFactory::user()->create();
        $admin = UserFactory::admin()->create();

        $data = Arr::except($this->validParams(['user_id' => $admin->id]), 'email_notification');

        $announcement = factory(Announcement::class)->create($data);

        $this->actingAs($user)->get('/announcements/' . $announcement->id)
            ->assertOk()
            ->assertSee($data['title']);

        $this->actingAs($admin)->get('/announcements/' . $announcement->id)
            ->assertOk()
            ->assertSee($data['title']);
    }

    /** @test */
    public function guests_cannot_edit_announcements()
    {
        $announcement = factory(Announcement::class)->create();

        $this->get('/announcements/' . $announcement->id)->assertRedirect('login');
        $this->put('/announcements/' . $announcement->id, $this->validParams())->assertRedirect('login');

        $this->assertEquals($announcement->title, $announcement->fresh()->title);
        $this->assertEquals($announcement->body, $announcement->fresh()->body);
    }

    /** @test */
    public function users_with_appropriate_permissions_can_edit_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $announcement = factory(Announcement::class)->create();

        $data = $this->validParams();

        $this->actingAs($user)
            ->from("/announcements/{$announcement->id}/edit")
            ->put('/announcements/' . $announcement->id, $data)
            ->assertRedirect('/announcements')
            ->assertSessionDoesntHaveErrors();

        $this->assertEquals($data['title'], $announcement->fresh()->title);
        $this->assertEquals($data['body'], $announcement->fresh()->body);
    }

    /** @test */
    public function title_field_is_required_when_updating_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $announcement = factory(Announcement::class)->create();

        $data = $this->validParams(['title' => '']);

        $this->actingAs($user)
            ->from("/announcements/{$announcement->id}/edit")
            ->put('/announcements/' . $announcement->id, $data)
            ->assertRedirect("/announcements/{$announcement->id}/edit")
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function body_field_is_required_when_updating_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $announcement = factory(Announcement::class)->create();

        $data = $this->validParams(['body' => '']);

        $this->actingAs($user)
            ->from("/announcements/{$announcement->id}/edit")
            ->put('/announcements/' . $announcement->id, $data)
            ->assertRedirect("/announcements/{$announcement->id}/edit")
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function guests_cannot_delete_an_announcement()
    {
        $announcement = factory(Announcement::class)->create();

        $this->delete('/announcements/' . $announcement->id)->assertRedirect('login');

        $this->assertNotNull($announcement->fresh());
    }

    /** @test */
    public function users_without_appropriate_permission_cannot_delete_an_announcement()
    {
        $user = UserFactory::user()->create();
        $announcement = factory(Announcement::class)->create();

        $this->actingAs($user)
            ->delete('/announcements/' . $announcement->id)
            ->assertForbidden();

        $this->assertNotNull($announcement->fresh());
    }

    /** @test */
    public function users_with_appropriate_permission_can_delete_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();
        $announcement = factory(Announcement::class)->create();

        $this->actingAs($user)
            ->delete('/announcements/' . $announcement->id)
            ->assertRedirect('/announcements');

        $this->assertNull($announcement->fresh());
    }

    /** @test */
    public function guests_cannot_view_announcement_list()
    {
        $this->get('/announcements/list')->assertRedirect('login');
    }

    /** @test */
    public function any_authenticated_user_can_view_announcement_list()
    {
        $user = UserFactory::user()->create();

        $announcementA = factory(Announcement::class)->create();
        $announcementB = factory(Announcement::class)->create();

        $this->actingAs($user)
            ->get('/announcements/list')
            ->assertSee($announcementA->title)
            ->assertSee($announcementA->parsed_body)
            ->assertSee($announcementB->title)
            ->assertSee($announcementB->parsed_body);
    }

    /** @test */
    public function authenticated_users_can_see_the_announcements_header_section()
    {
        $user = UserFactory::user()->create();

        $data = ['title' => 'some random announcement'];

        factory(Announcement::class)->create($data);

        $this->actingAs($user)
            ->get('/')
            ->assertSee('id="announcements-icon"')
            ->assertSee('id="announcementsDropdown"')
            ->assertSee($data['title']);
    }

    /** @test */
    public function a_red_dot_indicator_is_displayed_if_user_has_unread_announcements()
    {
        factory(Announcement::class)->create([
            'created_at' => now()->subMinutes(3)
        ]);

        $userA = UserFactory::user()->create([
            'announcements_last_read_at' => now()
        ]);

        $userB = UserFactory::user()->create([
            'announcements_last_read_at' => now()->subMinutes(5)
        ]);


        $this->actingAs($userA)
            ->get('/')
            ->assertDontSee('activity-badge');

        $this->actingAs($userB)
            ->get('/')
            ->assertSee('activity-badge');
    }

    /** @test */
    public function user_announcements_can_be_marked_as_read()
    {
        $user = UserFactory::user()->create([
            'announcements_last_read_at' => null
        ]);

        Carbon::setTestNow(now());

        $this->actingAs($user)
            ->post("/announcements/read");

        $this->assertEquals(
            now()->format('Y-m-d H:i:s'),
            $user->fresh()->announcements_last_read_at
        );
    }

    private function validParams(array $overrides = [])
    {
        return array_merge([
            'title' => 'Foo Announcement',
            'body' => 'This is the announcement body.',
            'email_notification' => '0'
        ], $overrides);
    }
}
