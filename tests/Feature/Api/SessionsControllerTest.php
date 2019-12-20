<?php

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Support\Str;
use Tests\Feature\ApiTestCase;
use Vanguard\Repositories\Session\SessionRepository;
use Vanguard\Transformers\SessionTransformer;
use Vanguard\User;

class SessionsControllerTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'database']);
    }

    /** @test */
    public function unauthenticated()
    {
        $user = factory(User::class)->create();

        $session = $this->createSession($user);

        $this->getJson("/api/sessions/{$session->id}")
            ->assertStatus(401);
    }

    /** @test */
    public function get_session_which_belongs_to_other_user()
    {
        $this->login();

        $user2 = factory(User::class)->create();

        $session = $this->createSession($user2);

        $this->getJson("/api/sessions/{$session->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function get_session()
    {
        $user = $this->login();

        $session = $this->createSession($user);

        $this->getJson("/api/sessions/{$session->id}")
            ->assertStatus(200)
            ->assertJson(
                (new SessionTransformer)->transform($session)
            );
    }

    /** @test */
    public function invalidate_his_own_session()
    {
        $user = $this->login();

        $session = $this->createSession($user);

        $this->deleteJson("/api/sessions/{$session->id}")
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function invalidate_session_for_other_user()
    {
        $user = UserFactory::withPermissions('users.manage')->create();
        $user2 = factory(User::class)->create();
        $session = $this->createSession($user2);

        $this->actingAs($user, 'api')
            ->deleteJson("/api/sessions/{$session->id}")
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function invalidate_session_for_other_user_without_permission()
    {
        $this->login();
        $user2 = factory(User::class)->create();
        $session = $this->createSession($user2);

        $this->deleteJson("/api/sessions/{$session->id}")
            ->assertStatus(403);
    }

    private function createSession(User $user)
    {
        $sessionId = Str::random(40);

        $data = [
            'id' => $sessionId,
            'user_id' => $user->id,
            'ip_address' => "127.0.0.1",
            'user_agent' => 'foo',
            'payload' => Str::random(),
            'last_activity' => Carbon::now()->timestamp
        ];

        \DB::table('sessions')->insert($data);

        return app(SessionRepository::class)->find($sessionId);
    }
}
