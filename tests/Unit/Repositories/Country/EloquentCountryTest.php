<?php

namespace Tests\Unit\Repositories\Country;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vanguard\Country;
use Vanguard\Repositories\Country\EloquentCountry;

class EloquentCountryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var EloquentCountry
     */
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(EloquentCountry::class);
    }

    /** @test */
    public function lists()
    {
        $countries = factory(Country::class)->times(8)->create();
        $countries = $countries->sortBy(function ($country) {
            return $country->name;
        })->pluck('name', 'id');

        $this->assertEquals($countries->toArray(), $this->repo->lists()->toArray());
    }
}
