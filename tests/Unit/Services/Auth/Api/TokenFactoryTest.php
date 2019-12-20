<?php

namespace Tests\Unit\Services\Auth\Api;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery as m;
use Tests\TestCase;
use Vanguard\Services\Auth\Api\Token;
use Vanguard\Services\Auth\Api\TokenFactory;
use Vanguard\User;

class TokenFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function token_generation_for_user()
    {
        Carbon::setTestNow(Carbon::now());

        $user = factory(User::class)->create();

        $factory = new TokenFactory($this->mockRequest(), $this->mockConfig());

        $token = $factory->forUser($user);

        $this->assertDatabaseHas('api_tokens', [
            'id' => $token->id,
            'user_id' => $user->id,
            'ip_address' => '123.456.789.012',
            'user_agent' => 'fooooo',
            'expires_at' => Carbon::now()->addMinutes(10)
        ]);
    }

    /** @test */
    public function expired_tokens_cleanup()
    {
        Carbon::setTestNow(Carbon::now());

        $user = factory(User::class)->create();

        $expiredToken = factory(Token::class)->create([
            'user_id' => $user->id,
            'expires_at' => Carbon::now()->subDay()
        ]);

        $factory = new TokenFactory(
            $this->mockRequest(),
            $this->mockConfig(10, [100, 100])
        );

        $token = $factory->forUser($user);

        $this->assertDatabaseHas('api_tokens', [
            'id' => $token->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('api_tokens', [
            'id' => $expiredToken->id
        ]);
    }

    /**
     * @param string $ip
     * @param string $userAgent
     * @return m\MockInterface
     */
    private function mockRequest($ip = '123.456.789.012', $userAgent = 'fooooo')
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('ip')->once()->andReturn($ip);
        $request->shouldReceive('header')->once()->with('User-Agent')->andReturn($userAgent);

        return $request;
    }

    /**
     * @param int $ttl
     * @param array $lottery
     * @return m\MockInterface
     */
    private function mockConfig($ttl = 10, $lottery = [5, 100])
    {
        $config = m::mock(\Illuminate\Contracts\Config\Repository::class);
        $config->shouldReceive('get')->with('jwt.ttl')->andReturn($ttl);
        $config->shouldReceive('get')->with('jwt.lottery')->andReturn($lottery);

        return $config;
    }
}
