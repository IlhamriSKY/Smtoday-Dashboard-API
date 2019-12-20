<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Tests\UpdatesSettings;
use Vanguard\User;

class ApiTestCase extends TestCase
{
    use RefreshDatabase, UpdatesSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');

        $this->setSettings(['auth.expose_api' => true]);
    }

    /**
     * @return mixed
     */
    protected function login()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'api');

        return $user;
    }

    /**
     * @return mixed
     */
    protected function loginSuperUser()
    {
        $user = $this->createSuperUser();

        $this->be($user, 'api');

        return $user;
    }

    /**
     * Transform provided collection of items.
     * @param Collection $collection
     * @param $transformer
     * @return array
     */
    protected function transformCollection(Collection $collection, $transformer)
    {
        $transformed = [];

        foreach ($collection as $item) {
            $transformed[] = $transformer->transform($item);
        }

        return $transformed;
    }

    /**
     * Check if JWT token in response contains
     * specified jti claim.
     * @param TestResponse $response
     * @param $jti
     * @return $this
     */
    protected function assertJwtTokenContains(TestResponse $response, $jti)
    {
        $response = $response->original;

        $parts = explode(".", $response['token']);

        $claims = json_decode(base64_decode($parts[1]));

        $this->assertEquals($jti, $claims->jti);

        return $this;
    }
}
