<?php

use Faker\Generator as Faker;
use Vanguard\Country;

$factory->define(Country::class, function (Faker $faker) {
    return [
        'country_code' => $faker->countryCode,
        'iso_3166_2' => strtoupper($faker->randomLetter . $faker->randomLetter),
        'iso_3166_3' => $faker->countryISOAlpha3,
        'name' => $faker->country,
        'region_code' => 123,
        'sub_region_code' => 123
    ];
});
